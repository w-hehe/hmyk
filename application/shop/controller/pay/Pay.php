<?php
namespace app\shop\controller\pay;

use app\common\controller\dock\Dock;
use app\common\controller\Email;
use think\Cache;
use think\Db;
use app\common\controller\Hm;
use think\Session;
use app\shop\controller\Base;

/**
 * 提交支付订单
 */
class Pay extends Base {

    public function _initialize() {
        parent::_initialize();
        //不允许游客购买
        if ($this->site['tourist_buy'] < 1 && empty($this->user['id'])) $this->redirect(url("/login"));
    }

    /**
     * 展示二维码页面
     */
    public function aliprecreate(){
        $out_trade_no = $this->request->param('out_trade_no');
        $cmd = $this->request->param('cmd');
        $user = Hm::getUser();
        $order = [];
        $data = [];
        if($cmd == 'order'){
            $where = ['order_no' => $out_trade_no, "uid" => $user['id'],];
            $order = db::name('order')->where($where)->find();
            $pay_type = $order['pay_type'];
            $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
            $this->assign([
                'goods' => $goods,
            ]);
            if(time() - $order['create_time'] >= 600){
                $order['status'] = 'overdue'; //先这么用着吧。等后面再更新
            }
            $data['money'] = $order['money'];
            $data['create_time'] = $order['create_time'];
            $qr_code = $order['qr_code'];
        }else{
            $recharge = db::name('recharge')->where(['out_trade_no' => $out_trade_no])->find();
            $pay_type = $recharge['pay_type'];
            if(time() - $recharge['create_time'] >= 600){
                $order['status'] = 'overdue'; //先这么用着吧。等后面再更新
            }
            $data['money'] = $recharge['money'];
            $data['create_time'] = $recharge['create_time'];
            $qr_code = $recharge['qr_code'];
        }
        $data['out_trade_no'] = $out_trade_no;

        $pay_type_show = "未知";
        if($pay_type == 'alipay') $pay_type_show = '支付宝';
        if($pay_type == 'wxpay') $pay_type_show = '微信';
        if($pay_type == 'qqpay') $pay_type_show = 'QQ';


        $this->assign([
            'qr_code' => $qr_code,
            'img' => $this->request->has('img') ? true : false,
            "alipay_wap" => '',
            "is_mobile" => is_mobile(),
            'pay_type' => $pay_type_show,
            "order" => $order,
            'data' => $data,
            'cmd' => $cmd

        ]);

        return view(ROOT_PATH . "public/content/template/common/scan_code.html");
    }


    /**
     * 确认订单页面
     */
    public function confirm(){
        if(empty($this->request->get('post'))) $this->error('订单已失效，请重新提交订单');
        $post = $this->request->get('post');
        try{
            $post = explode('n', $post);
            $buy_num = $post[1];
            $goods_id = explode('g', $post[0])[1];
        }catch (\Exception $e){
            $this->error($e->getMessage());
        }

        $user = Hm::getUser();
        $goods = Hm::getGoodsInfo($goods_id, $user);

        //监控价格
        $goods = $this->checkPrice($goods);

        $attach = []; //订单附加数据

        $inputs = [];

//        echo '<pre>'; print_r($goods);die;
        $order = db::name('order')->where(['uid' => $user['id']])->order('id desc')->find();
        $this->assign([
            'post' => $post,
            'attach' => $attach,
            'goods' => $goods,
            'inputs' => $inputs,
            'buy_num' => $buy_num,
            'order' => $order
        ]);

        return view($this->template_path . "confirm_order.html");
    }


    /**
     * 提交订单支付
     * 1，读取临时缓存订单数据
     * 2，判断商品库存
     * 3，获取当前用户信息
     * 4，订单写入数据库
     * 5，分析用户支付方式
     * 6，发起支付
     */
    //提交订单支付
    public function pay(){

        if($this->request->isGet()){
//            echo '为绿色健康互联网贡献绵薄之力';die;
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . url('/'));
            die;
        }

        $post = $this->request->post();

//        echo '<pre>'; print_r($post);die;

        $user = Hm::getUser();
        $goods = Hm::getGoodsInfo($post['goods_id'], $user);
        if (!$goods) $this->error('商品不存在');
        if ($post['buy_num'] > $goods['stock']) $this->error('库存不足');
        if(empty($post['buy_num']) || $post['buy_num'] < 1) $this->error('禁止非法注入！');
        if(!empty($post['coupon'])){
            $coupon = db::name('coupon')->where(['value' => $post['coupon']])->find();
            if(!$coupon) $this->error('优惠券不存在');
            if(!empty($coupon['goods_ids']) && !in_array($goods['id'], explode(',', $coupon['goods_ids']))) $this->error('该优惠券不适用于当前商品');
            if(!empty($coupon['category_ids']) && !in_array($goods['category_id'], explode(',', $coupon['category_ids']))) $this->error('该优惠券不适用于当前商品分类');
            $coupon_insert = [
                'coupon_id' => $coupon['id'],
                'create_time' => $this->timestamp
            ];
            if($coupon['single'] == 1){ //只能使用一次
                if(strlen($user['id']) > 10) $this->error('该优惠券必须登录后才能使用');
                $where = [
                    'coupon_id' => $coupon['id'],
                    'uid' => $user['id']
                ];
                $coupon_log = db::name('coupon_log')->where($where)->find();
                if($coupon_log) $this->error('该优惠券已失效');
                $coupon_insert['uid'] = $user['id'];
            }
            if($coupon['max_use'] > 0){
                $where = [
                    'coupon_id' => $coupon['id']
                ];
                $coupon_log_count = db::name('coupon_log')->where($where)->count();
                if($coupon_log_count >= $coupon['max_use']) $this->error('该优惠券已失效');
            }
            if(!empty($coupon['expire_time']) && $this->timestamp > $coupon['expire_time']) $this->error('该优惠券已失效');
            if($coupon['type'] == 0){ //百分比
                $goods['real_price'] = $goods['real_price'] * ($coupon['discount'] / 100);
            }else{ //固定金额
                $goods['real_price'] = $goods['real_price'] - $coupon['discount'];
                $goods['real_price'] = $goods['real_price'] < 0 ? 0 : $goods['real_price'];
            }
        }
        if($goods['quota'] > 0){ //该商品是否开启限制购买数量
            $where = [
                'ip' => getClientIp(),
                'pay_time' => ['>', 0],
                'goods_id' => $goods['id']
            ];
            $today_ip_pay = db::name('order')->where($where)->count();
            if($today_ip_pay >= $goods['quota'] || $post['buy_num'] + $today_ip_pay > $goods['quota']) $this->error('已超出购买限制');
        }


        //写入订单
        $order_money = sprintf('%.2f', $goods['real_price'] * $post['buy_num']); //订单金额


        $inputs = [];
        if(!empty($post['inputs'])){
            foreach($post['inputs'] as $key => $val){
                $inputs[$key] = $val;
            }
        }
//        echo '<pre>'; print_r($inputs);die;

        //写入订单表
        $insert = [
            'order_no' => $this->generateOrderNo(), //订单号
            'create_time' => $this->timestamp, //订单生成时间
            'pay_type' => empty($post['pay_type']) ? '' : $post['pay_type'], //支付方式
            'uid'         => $user["id"], //用户id
            'goods_id'    => $post['goods_id'], //商品id
            'buy_num'   => $post['buy_num'], //购买数量
            'goods_money' => $goods['real_price'], //商品单价
            'money'       => $order_money, //订单金额
            'remote_money' => $goods['buy_price'] * $post['buy_num'], //进货价
            'inputs' => json_encode($inputs), //对接订单参数
            'ip' => getClientIp(),
        ];

        if($insert['money'] == 0) unset($insert['pay_type']);

        $insert['account'] = empty($post['account']) ? '' : $post['account'];
        $insert['password'] = empty($post['password']) ? '' : $post['password'];
        $id = db::name('order')->insertGetId($insert);
        $order = $insert;
        $order['id'] = $id;
        if(!empty($post['coupon'])){ //记录优惠券的使用
            $coupon_insert['order_id'] = $id;
            db::name('coupon_log')->insert($coupon_insert);
            db::name('coupon')->where(['id' => $coupon_insert['coupon_id']])->setInc('use_num');
        }

        if($order['goods_money'] > 0 && $order['pay_type'] == 'moneypay'){
            if($this->user['money'] < $order['money']) $this->error('余额不足，即将跳转到充值页面', url('/user/recharge'));
            db::name('user')->where(['id' => $user['id']])->setDec('money', $order['money']);
            $order['goods_money'] = 0;
        }

        if($order['goods_money'] == 0){ //免费商品
            $order = db::name('order')->where(['order_no' => $order['order_no']])->find();
            $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();

            //给商品发货或去对接站购买商品
            Hm::handleOrder($goods, $order, $this->site);

            if($this->site['user_order_email'] == 1){
                Email::sendOrderUser($goods, $order['id'], $this->site);
            }
            if($this->site['admin_order_email'] == 1){
                Email::sendOrderBoss($goods, $order['id'], $this->site);
            }
            header("location: " . url('/order') . "?order_no=" . $order['order_no']);die;
        }else{

            //发起支付
            $goods['name'] = empty($this->site['diy_name']) ? $goods['name'] : $this->site['diy_name'];
            $this->payment($order['pay_type'], $order, $goods);
        }


    }



    /**
     * 发起支付
     */
    public function payment($pay_type, $order, $goods) {

        $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
        $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

//        dump($active_pay);die;

        $pay_plugins = [];
        foreach($active_pay as $key => $val){
            if(in_array($pay_type, $val['pay_type'])) $pay_plugins[] = $key;
        }

//        dump($pay_plugins);

        $pay_plugin = $pay_plugins[array_rand($pay_plugins)]; //epay_pay

        db::name('order')->where(['id' => $order['id']])->update(['pay_plugin' => $pay_plugin]);

        $pluginPath = ROOT_PATH . 'public/content/plugin/' . $pay_plugin . '/' . $pay_plugin . '.php';

        require_once $pluginPath;
        pay($order, $goods, $pay_type);
    }


    public function buySuccess() {
        $goods_id = $this->request->param('goods_id');
        $this->assign([
            'goods_id' => $goods_id,
        ]);
        return view();
    }


    /**
     * 提交订单页面
     * @params $goods_id 商品id   or  @params $order_id 订单id
     */
    public function detail() {
        $buy_num = 1;
        $order_id = 0;
        if ($this->request->has('goods_id')) {
            $goods_id = $this->request->param('goods_id');
        } else {
            $order_id = $this->request->param('order_id');
            $order_info = db::name('order')->where(['id' => $order_id])->find();
            $goods_id = $order_info['goods_id'];
            $buy_num = $order_info['buy_num'];
        }
        $info = Fun::getGoodsInfo($goods_id);
        $this->assign([
            'info' => $info, 'buy_num' => $buy_num, 'order_id' => $order_id,
        ]);
        return view();
    }



    /**
     * 监控对接商品价格
     */
    public function checkPrice($goods){
        return $goods;
        if($goods['dock_id'] == 0 || $goods['increase_id'] == 0){
            return $goods;
        }

        $cache_name = "buy_" . $goods["id"];
        if(Cache::has($cache_name)){
            return $goods;
        }

        $siteInfo = Dock::getSiteInfo($goods['site_id']);
        $increase = db::name('increase')->where(['id' => $goods['increase_id']])->find();
        $price = json_decode($goods["price"], true);
        $change_price = false;

        if($siteInfo['type'] == 'kky'){
            $dock_goods = Kky::getGoodsInfo($goods['remote_id'], $siteInfo['id']);
        }
        if($siteInfo['type'] == 'jiuwu'){
            $dock_goods = Jiuwu::getGoodsInfo($goods['remote_id'], $siteInfo['id']);
        }
        if($siteInfo['type'] == 'yile'){
            $dock_goods = Yile::getGoodsInfo($goods['remote_id'], $siteInfo['id']);
        }
        $buy_price = $dock_goods['buy_price'] * $goods['buy_default']; //商品的当前成本价
        $max_agent_price = $price[count($price) - 1]; //最高级别的代理价格

        if($increase['type'] == 'follow'){ //跟随, 对接站售价变动多少，本站售价变动多少
            if($increase['effect'] == 1 && $buy_price > $max_agent_price){ //当前成本价大于本站售价
                $change = $buy_price - ($goods['buy_price'] * $goods['buy_default']);
            }elseif($increase['effect'] == 2 && $dock_goods['buy_price'] != $goods['buy_price']){ //当前成本价大于本站记录的成本价
                $change = $buy_price - ($goods['buy_price'] * $goods['buy_default']);
            }
        }
        if($increase['type'] == 'fixed'){ //固定
            if($increase['effect'] == 1 && $buy_price > $max_agent_price){ //当前成本价大于本站售价
                $change = $buy_price - $max_agent_price;
                $change += $increase['value'];
            }elseif($increase['effect'] == 2 && $buy_price != $goods['buy_price']){ //当前成本价大于本站记录的成本价
                $change = $buy_price - ($goods['buy_price'] * $goods['buy_default']);
                if($change > 0){
                    $change += $increase['value'];
                }
            }
        }
        if($increase['type'] == 'percent'){ //百分比
            if($increase['effect'] == 1 && $buy_price > $max_agent_price){ //当前成本价大于本站售价
                $change = $buy_price - $max_agent_price;
                $change += $buy_price * ($increase['value'] / 100);
            }elseif($increase['effect'] == 2 && $buy_price != $goods['buy_price']){ //当前成本价大于本站记录的成本价
                $change = $buy_price - ($goods['buy_price'] * $goods['buy_default']);
                if($change > 0){
                    $change += $buy_price * ($increase['value'] / 100);
                }
            }
        }

        if(isset($change)){
            $change = sprintf("%.2f", $change);
            foreach($price as $key => &$val){
                if($val == $goods['real_price']){
                    $real_price = sprintf("%.2f", $val + $change);
                }
                $val = sprintf("%.2f", $val + $change);
            }
            $update = [
                'price' => json_encode($price),
                'buy_price' => $buy_price,
            ];
            db::name('goods')->where(['id' => $goods['id']])->update($update);
            $goods['buy_price'] = $buy_price;
            $goods['price'] = json_encode($price);
            $goods['real_price'] = $real_price;
            //更新对接商品缓存
            $cache_name = "buy_" . $goods["id"];
            Cache::set($cache_name, $goods, $increase['expire'] * 60);
        }
        return $goods;
    }


}
