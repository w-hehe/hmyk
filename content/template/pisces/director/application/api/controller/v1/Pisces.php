<?php

namespace app\api\controller\v1;

use app\common\controller\Email;
use app\common\controller\Hm;
use fast\QRcode;
use fast\Random;
use think\Config;
use think\Db;
use think\Session;
use think\Controller;
use think\Cache;

class Pisces extends Controller {

    public $timestamp = null;
    public $user = [];
    public $host = null;
    public $options = [];
    public $site = [];

    public function _initialize() {
        parent::_initialize();
        check_cors_request(); //跨域请求检测
        $this->timestamp = time();
        $this->host = getHostDomain();
        $this->user = Hm::getUser();

//        print_r($this->user);die;

        #########测试数据#########
//        $this->user['id'] = 1;
//        $this->user['login'] = true;
//        $this->user['money'] = 10;
//        $this->user['agent'] = 2;
        #########测试数据#########

        $options = db::name('options')->select();
        foreach($options as $val) $this->options[$val['option_name']] = $val['option_content'];
        $this->options['buy_data'] = json_decode($this->options['buy_data'], true);
        $this->site = Config::get("site");
        $this->options['goods_eject'] = empty(strip_tags($this->options['goods_eject'])) ? '' : $this->options['goods_eject'];
        $this->options['index_eject'] = empty(strip_tags($this->options['index_eject'])) ? '' : $this->options['index_eject'];
        includeAction();
    }




    /**
     * template
     */
    public function template(){

        if(Cache::has('pisces_template')){
            $data = cache::get('pisces_template');
        }else{
            $path = ROOT_PATH . "content/template/pisces/";
            $info = file_get_contents("{$path}setting.json");
            $data = json_decode($info, true);
            $data['notice'] = empty(strip_tags($data['notice'])) ? '' : $data['notice'];
            cache::set('pisces_template', $data);
        }
        $data['options'] = $this->options;
        $data['site'] = $this->site;
        $data['user'] = $this->user;
        $data['date'] = date('Y-m-d', $this->timestamp);

//        $data['logo'] = 'http://localhost:12345' . $data['logo'];

        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 首页接口
     */
    public function index(){
        $goods = $this->goods_list();
        $data = [
            'goods' => $goods
        ];
        return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }


    /**
     * 分类页接口
     */
    public function category_index(){
        $category_id = $this->request->param('category_id');

        $list = [];
        try {

            $goods_model = new \app\api\model\Goods;

            $goods_result = $goods_model->alias('g')
                ->join('cdkey c', 'g.id=c.goods_id', 'left')->group('g.id')
                ->field('g.id, g.name, g.images, g.eject, g.buy_msg, g.sku, g.attach_id, g.inputs, g.category_id, sum(c.num) stock, g.sales')
                ->where(['g.shelf' => 0, 'g.deletetime' => ['exp', Db::raw('is null')], 'g.category_id' => $category_id])
                ->order('g.sort desc, g.id desc')
                ->with(['price' => function($withPrice){
                    $withPrice->field('grade_id, price, sku, sku_ids, goods_id');
                }])->select()->toArray();

            $category = db::name('category')->where(['id' => $category_id])->find();




            foreach($goods_result as $k => $v){
                $list[] = Hm::handle_goods($v, $this->user, $this->options);
            }
        }catch (\Exception $e){
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

        $data = [
            'goods' => $list,
            'category' => $category
        ];
        return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }

    /**
     * 搜索接口
     */
    public function search(){
        $keyword = $this->request->param('keyword');

        $list = [];
        try {
            $goods_model = new \app\api\model\Goods;
            $where = [
                'shelf' => 0,
                'deletetime' => ['exp', Db::raw('is null')],
                'name' => ['like', '%' . $keyword . '%']
            ];
            $goods = $goods_model->alias('g')
                ->join('cdkey c', 'g.id=c.goods_id')
                ->field('g.details, g.dock_id, g.goods_type, g.shelf, g.id, g.name, g.images, g.eject, g.buy_msg, g.sku, g.attach_id, g.inputs, g.category_id, sum(c.num) stock')
                ->where(['g.shelf' => 0, 'g.deletetime' => ['exp', Db::raw('is null')]])
                ->with(['price' => function($withPrice){
                    $withPrice->field('grade_id, price, sku, sku_ids, goods_id');
                }])->order('g.sort desc, g.id desc')->select();

            $goods = $goods->toArray();

            foreach($goods as $k => $v){
                $list[] = Hm::handle_goods($v, $this->user, $this->options);
            }
        }catch (\Exception $e){
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }

        $data = [
            'goods' => $list,
        ];
        return json(['code' => 1, 'msg' => 'ok', 'data' => $data]);
    }

    public function getBuyInfo($goods, $sku_ids = null){
        $data = [
            "goods_money" => -1,
            "sku_name" => ''
        ];
        foreach($goods['price'] as $val){
            if(empty($sku_ids)){
                if($this->user['agent'] >= $val['grade_id']){
                    $data['goods_money'] = $val['price'];
                }
            }else{
                if($this->user['agent'] >= $val['grade_id'] && $sku_ids == $val['sku_ids']){
                    $data['goods_money'] = $val['price'];
                    $data['sku_name'] = $val['sku'];
                    $data['sku_ids'] = $sku_ids;
                }
            }

        }
        return $data;
    }


    /**
     * 提交商品支付
     */
    public function goodsPay(){
        $post = $this->request->post();
        $email = $this->payMail($post);
        if(empty($post['buy_num']) || $post['buy_num'] < 1) $post['buy_num'] = 1;
        $goods = Hm::getGoodsInfo($post['goods_id'], $this->user);
        if(empty($post['sku_ids'])){
            $buy_info = $this->getBuyInfo($goods);
        }else{
            $buy_info = $this->getBuyInfo($goods, implode(',', $post['sku_ids']));
        }
//        if($post['buy_num'] > $goods['stock']) return json(['code' => 0, 'msg' => '库存不足']);

        if($buy_info['goods_money'] > 0 && !empty($post['coupon'])){
            $coupon = db::name('coupon')->where(['value' => $post['coupon']])->find();
            if(!$coupon) return json(['code' => 0, 'msg' => '优惠券无效']);
            if(!empty($coupon['goods_ids']) && !in_array($goods['id'], explode(',', $coupon['goods_ids']))) return json(['code' => 0, 'msg' => '该优惠券不适用于当前商品']);
            if(!empty($coupon['category_ids']) && !in_array($goods['category_id'], explode(',', $coupon['category_ids']))) return json(['code' => 0, 'msg' => '该优惠券不适用于当前商品分类']);
            $coupon_insert = [
                'coupon_id' => $coupon['id'],
                'create_time' => $this->timestamp
            ];
            if($coupon['single'] == 1){ //只能使用一次
                if(strlen($this->user['id']) > 10) return json(['code' => 0, 'msg' => '该优惠券必须登录后才能使用']);
                $where = [
                    'coupon_id' => $coupon['id'],
                    'uid' => $this->user['id']
                ];
                $coupon_log = db::name('coupon_log')->where($where)->find();
                if($coupon_log) return json(['code' => 0, 'msg' => '优惠券已失效']);
                $coupon_insert['uid'] = $this->user['id'];
            }
            if($coupon['max_use'] > 0){
                $where = [
                    'coupon_id' => $coupon['id']
                ];
                $coupon_log_count = db::name('coupon_log')->where($where)->count();
                if($coupon_log_count >= $coupon['max_use'])  return json(['code' => 0, 'msg' => '优惠券已失效']);
            }
            if(!empty($coupon['expire_time']) && $this->timestamp > $coupon['expire_time'])  return json(['code' => 0, 'msg' => '优惠券已失效']);
            if($coupon['type'] == 0){ //百分比
                $buy_info['goods_money'] = $buy_info['goods_money'] * ($coupon['discount'] / 100);
                $buy_info['goods_money'] = $buy_info['goods_money'] < 0.01 ? 0 : $buy_info['goods_money'];
            }else{ //固定金额
                $buy_info['goods_money'] = $buy_info['goods_money'] - $coupon['discount'];
                $buy_info['goods_money'] = $buy_info['goods_money'] < 0.01 ? 0 : $buy_info['goods_money'];
            }
        }

        $order_money = sprintf('%.2f', $buy_info['goods_money'] * $post['buy_num']);

        if($order_money > 0 && empty($post['pay_type'])) return json(['code' => 0, 'msg' => '请选择支付方式']);

//        echo $goods['real_price'];die;

        if($post['pay_type'] == 'balance' || $order_money == 0){
            if($order_money > 0){
                if($this->user['money'] < $order_money) return json(['code' => 0, 'msg' => '余额不足，请充值']);
                db::name('user')->where(['id' => $this->user['id']])->setDec('money', $order_money);
            }
            $pay_plugin = '';
        }else{
            $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
            $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

            if(empty($active_pay)) return json(['code' => 0, 'msg' => '站点未配置收款接口']);

            $pay_plugins = [];
            foreach($active_pay as $key => $val){
                if(in_array($post['pay_type'], $val['pay_type'])) $pay_plugins[] = $key;
            }
            $pay_plugin = $pay_plugins[array_rand($pay_plugins)];
            $pay_plugin = substr($pay_plugin, 0, -4);
        }

        $inputs = [];
        if(!empty($post['inputs'])){
            $post['inputs'] = json_decode($post['inputs'], true);
            foreach($post['inputs'] as $key => $val){
                if(empty($val['value'])) return json(['code' => 0, 'msg' => '请输入' . $val['title']]);
                $inputs[$val['title']] = $val['value'];
            }
        }
        //写入订单表
        $insert = [
            'order_no' => Hm::generateOrderNo(), //订单号
            'create_time' => $this->timestamp, //订单生成时间
            'pay_type' => empty($post['pay_type']) ? '' : $post['pay_type'], //支付方式
            'uid'         => $this->user["id"], //用户id
            'goods_id'    => $post['goods_id'], //商品id
            'buy_num'   => $post['buy_num'], //购买数量
            'goods_money' => $buy_info['goods_money'], //商品单价
            'money'       => $order_money, //订单金额
            //            'remote_money' => $goods['buy_price'] * $post['buy_num'], //进货价
            'remote_money' => 0, //进货价
            'inputs' => json_encode($inputs), //对接订单参数
            'ip' => getClientIp(),
            'pay_plugin' => $pay_plugin, //支付插件
            'status' => 'wait-pay',
            'buy_info' => json_encode($buy_info)
        ];
        if($order_money == 0) unset($insert['pay_type']);
        $insert['email'] = empty($post['email']) ? '' : trim($post['email']);
        $insert['password'] = empty($post['password']) ? '' : trim($post['password']);
        $order_id = db::name('order')->insertGetId($insert);
        $order = $insert;
        $order['id'] = $order_id;

        if(!empty($post['coupon'])){ //记录优惠券的使用
            $coupon_insert['order_id'] = $order['id'];
            db::name('coupon_log')->insert($coupon_insert);
            db::name('coupon')->where(['id' => $coupon_insert['coupon_id']])->setInc('use_num');
        }


        if($post['pay_type'] == 'balance' || $order_money == 0){ //免费
            //给商品发货或去对接站购买商品
            $result = Hm::handleOrder($goods, $order, $this->options);
//            print_r($result);die;
            $n_order_data = [
                'goods_name' => $goods['name'],
                'out_trade_no' => $order['order_no'],
                'buy_num' => $order['buy_num'],
                'goods_price' => $buy_info['goods_money'],
                'order_money' => $order['money'],
                'cdk' => $result['data']['cdk'],
                'create_time' => $order['create_time'],
                'pay_type' => '',
                'pay_time' => '',
                'details' => $goods['details'],
                'stock' => $result['data']['stock']
            ];
            if(!empty($this->options['n_order_ad'])){
                $obj_path = "notice\\" . lcfirst($this->options['n_order_ad']) . "\\{$this->options['n_order_ad']}";
                $obj = new $obj_path;
                $obj->nOrderAd($n_order_data);
            }
            if(!empty($this->options['n_order_us']) && !empty($email)){
                $obj_path = "notice\\" . lcfirst($this->options['n_order_us']) . "\\{$this->options['n_order_us']}";
                $obj = new $obj_path;
                foreach($email as $val){
                    $n_order_data['email'] = $val;
                    $obj->nOrderUs($n_order_data);
                }
            }

            return json([
                'code' => 1,
                'msg' => 'success',
                'mode' => 'jump-router',
                'router' => [
                    'path' => '/order/detail',
                    'query' => [
                        'out_trade_no' => $order['order_no']
                    ]
                ]
            ]);
        }

        $pluginPath = ROOT_PATH . 'content/plugin/' . $pay_plugin . '_pay/' . $pay_plugin . '_pay.php';
        $goods['name'] = empty($this->options['buy_name']) ? $goods['name'] : $this->options['buy_name'];
        require_once $pluginPath;
        $host = getHostDomain();
        $params = [
            'notify_url' => $host . '/shop/notify/index?pay_plugin=' . $pay_plugin,
            'return_url' => $host . '/api/v1/pisces/callback_return?pay_plugin=' . $pay_plugin,
            'quit_url' => $host . "/#/goods/goods_id={$goods['id']}",
            'pay_type' => $post['pay_type']
        ];

        // echo $params['notify_url'];die;

        $result = pay($insert, $goods, $params);
        if($result['code'] == 400){
            return json(['code' => 0, 'msg' => $result['msg']]);
        }
        $result['code'] = 1;
        $result['expire_time'] = $this->timestamp + 600;
        $result['out_trade_no'] = $insert['order_no'];
        $result['goods_name'] = $goods['name'];
        $result['order_money'] = $insert['money'];
        $result['price'] = $goods['price'];
        $result['buy_num'] = $post['buy_num'];
        $result['is_mobile'] = is_mobile();

        return json($result);
    }

    /**
     * 同步回调
     */
    public function callback_return($content = null, $i = 0){
        $content = $content == null ? $this->request->get() : $content;
        $pay_plugin = $content['pay_plugin'];
        $pluginPath = ROOT_PATH . 'content/plugin/' . $pay_plugin . '_pay/' . $pay_plugin . '_pay.php';
        require_once $pluginPath;
        $out_trade_no = checkSign();

        if($out_trade_no){ //验签成功
            $order = db::name('order')->where(['order_no' => $out_trade_no])->lock(true)->find();
            if(!$order){
                Db::rollback();
                die('fail');
            }
            if($order['status'] != 'wait-pay'){ //重复通知
                Db::rollback();
                header("location: /#/order/detail?out_trade_no={$out_trade_no}"); die;
            }
            $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
            $update = [
                'status' => 'wait-send', // 通知后改为代发货
                'pay_time' => $this->timestamp, //支付时间
            ];
            $order['pay_time'] = $this->timestamp;
            db::name('order')->where(['id' => $order['id']])->update($update);
            $result = Hm::handleOrder($goods, $order, $this->options);
            Db::commit();

            $n_order_data = [
                'goods_name' => $goods['name'],
                'out_trade_no' => $order['order_no'],
                'buy_num' => $order['buy_num'],
                'goods_price' => $order['goods_money'],
                'order_money' => $order['money'],
                'cdk' => $result['data']['cdk'],
                'create_time' => $order['create_time'],
                'pay_type' => $order['pay_type'],
                'pay_time' => $order['pay_time'],
                'details' => $goods['details'],
                'stock' => $result['data']['stock']
            ];

            $email = [];
            if(!empty($this->options['buy_data'][0]['email']) && !empty($order['email'])) $email[] = $order['email'];
            if(!empty($this->options['buy_data'][1]['email']) && !empty($order['password'])) $email[] = $order['password'];

            if(!empty($this->options['n_order_ad'])){
                $obj_path = "notice\\" . lcfirst($this->options['n_order_ad']) . "\\{$this->options['n_order_ad']}";
                $obj = new $obj_path;
                $obj->nOrderAd($n_order_data);
            }
            if(!empty($this->options['n_order_us']) && !empty($email)){
                $obj_path = "notice\\" . lcfirst($this->options['n_order_us']) . "\\{$this->options['n_order_us']}";
                $obj = new $obj_path;
                foreach($email as $val){
                    $n_order_data['email'] = $val;
                    $obj->nOrderUs($n_order_data);
                }
            }

            try{
                doAction('order_notify', $order, $goods);
            }catch(\Exception $e){}

            header("location: /#/order/detail?out_trade_no={$out_trade_no}"); die;
            die;
        }else{
            Db::rollback();
            echo '验签失败'; die;
        }

    }

    public function qrCode(){
        $qr_code = $this->request->param('qr_code');
        QRcode::png(urldecode($qr_code),false, 'L', 7, 2);
        die;
    }

    /**
     * orderSearch
     */
    public function orderSearch(){
        $post = $this->request->param();
        $where = [];
        if($post['search_type'] == 'brower') $where['uid'] = $this->user['id'];
        if($post['search_type'] == 'password'){

            if(
                !empty($this->options['buy_data'][0]['search']) &&
                $this->options['buy_data'][0]['search'] == 'checked'
            ){
                if(empty($post['password'])) return json(['code' => 0, 'msg' => '请输入' . $this->options['buy_data'][0]['name']]);

                $where['email'] = $post['email'];
            }

            if(
                !empty($this->options['buy_data'][1]['search']) &&
                $this->options['buy_data'][1]['search'] == 'checked'
            ){
                if(empty($post['password'])) return json(['code' => 0, 'msg' => '请输入' . $this->options['buy_data'][1]['name']]);
                $where['password'] = $post['password'];
            }

            if(empty($post['email']) && empty($post['password'])) return json(['code' => 0, 'msg' => '违法查询']);

        }
        if($post['search_type'] == 'out_trade_no') $where['order_no'] = $post['out_trade_no'];

        $result = db::name('order')->alias('o')
            ->join('goods g', 'g.id=o.goods_id')
            ->field('o.*, g.name goods_name, g.images')
            ->where($where)
            ->where(['pay_time' => ['>', 0]])
            ->order('o.id desc')->select();
        $host = getHostDomain();
        foreach($result as &$val){
            $val['images'] = explode(',', $val['images']);
            $val['cover'] = $val['images'][0];
            if(empty($val['cover'])){
                $val['cover'] = $host . '/assets/img/none.jpg';
            }else{
                $val['cover'] = $host . $val['cover'];
            }
            $val['pay_time'] = date('Y-m-d H:i:s', $val['pay_time']);
        }
        return json(['code' => 1, 'msg' => 'success', 'data' => $result]);
    }

    /**
     * orderDetail
     */
    public function orderDetail(){
        $order_no = $this->request->param('out_trade_no');
        $field = "id, order_no as out_trade_no, buy_num, money, status, create_time, pay_time, goods_id, buy_info";
        $order = db::name('order')->field($field)->where(['order_no' => $order_no])->find();
        $order_id = $order['id'];
        $goods_id = $order['goods_id'];
        unset($order['goods_id']);
        unset($order['id']);
        $cdk = [];
        if(!$order) return json(['code' => 0, 'msg' => '订单不存在']);
        $goods = Hm::getGoodsInfo($goods_id, $this->user);
        if($order['status'] == 'success'){
            $sold = db::name('sold')->where(['order_id' => $order_id])->select();
            foreach($sold as $val){
                $cdk[] = $val['content'];
            }
        }else{
            // echo '<pre>'; print_r($order);die;
            if(!$goods) return json(['code' => 0, 'msg' => '数据缺失，无法获取']);
            if($goods['dock_id'] > 0){
                $dock = db::name('dock')->where(['id' => $goods['dock_id']])->find();
                include ROOT_PATH . 'content/dock/' . $dock['type'] . '/' . ucfirst($dock['type']) . '.php';
                $objName = ucfirst($dock['type']) . 'Dock';
                $dockObj = new $objName();
                $result = $dockObj->orderInfo($order, json_decode($dock['info'], true));
                if($result['code'] == 400) return json($result);
                $remote_order = $result['data']['order'];
                if($remote_order['status'] == 'success' || $remote_order['status'] == 'conduct'){
                    db::name('order')->where(['id' => $order_id])->update(['status' => $remote_order['status']]);
                    if(!empty($remote_order['cdk'])){
                        $insert_sold = [];
                        foreach($remote_order['cdk'] as $val){
                            $insert_sold[] = [
                                'order_id' => $order_id,
                                'content' => $val,
                                'create_time' => $this->timestamp
                            ];
                        }
                        db::name('sold')->insertAll($insert_sold);
                    }
                }
                $cdk = $remote_order['cdk'];
            }
        }
        $order['cdk'] = $cdk;
        $order['buy_info'] = json_decode($order['buy_info']);
        $data = [
            'goods' => $goods,
            'order' => $order
        ];
        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * payStatus
     */
    public function payStatus(){
        $out_trade_no = $this->request->param('out_trade_no');
        $where = [
            'order_no' => $out_trade_no
        ];
        $order = db::name('order')->where($where)->find();
        if(empty($order) || empty($order['pay_time'])){
            return json(['code' => 1, 'msg' => '订单未支付', 'data' => -1]);
        }
        return json(['code' => 1, 'msg' => '订单已支付', 'data' => 1]);
    }


    /**
     * payList
     */
    public function payList(){
        return json(['code' => 1, 'msg' => 'success', 'data' => Hm::payList()]);
    }

    /**
     * cfm
     */
    public function cfm(){
        $goods_id = $this->request->param('goods_id');

        $goods = Hm::getGoodsInfo($goods_id, $this->user, $this->options);
        if(!$goods || !empty($goods['deletetime']) || $goods['shelf'] == 1){
            return json(['code' => '404', 'msg' => '该商品已下架或被删除']);
        }
        $order = db::name('order')->where(['uid' => $this->user['id']])->order('id desc')->find();
        if(!$order){
            $order['email'] = '';
            $order['password'] = '';
        }
        $data = [
            'goods' => $goods,
            'options' => $this->options,
            'order' => $order,
        ];
        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 商品页接口
     */
    public function goods(){
        $goods_id = $this->request->param('goods_id');

        $goods = Hm::getGoodsInfo($goods_id, $this->user, $this->options);
        if(!$goods || !empty($goods['deletetime']) || $goods['shelf'] == 1){
            return json(['code' => '404', 'msg' => '该商品已下架或被删除']);
        }

        $data = [
            'goods' => $goods
        ];
        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 获取全部商品
     * @param category_id
     */
    public function goods_list(){
        $list = [];
        try {
            $model = new \app\api\model\Category;
            $result = $model->with(['goods' => function($withGoods){
                $withGoods->alias('g')->join('cdkey c', 'g.id=c.goods_id', 'left')->group('g.id');
                $withGoods->field('g.id, g.name, g.images, g.eject, g.buy_msg, g.sku, g.attach_id, g.inputs, g.category_id, sum(c.num) stock, g.sales');
                $withGoods->where(['g.shelf' => 0, 'g.deletetime' => ['exp', Db::raw('is null')]]);
                $withGoods->order('sort desc, id desc');
                $withGoods->with(['price' => function($withPrice){
                    $withPrice->field('grade_id, price, sku, sku_ids, goods_id');
                }]);
            }])->field('id, name')->where(['status' => 'normal'])->order('weigh desc')->select();
            $list = $result->toArray();

            foreach($list as $key => $val){
                $list[$key]['goods_num'] = count($val['goods']);
                foreach($val['goods'] as $k => $v){
                    $list[$key]['goods'][$k] = Hm::handle_goods($v, $this->user, $this->options);
                }
            }
        }catch (\Exception $e){
            echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);die;
        }
        return $list;
    }


    /**
     * 获取商品分类
     * @param null
     */
    public function category(){
        $where = [
            'status' => 'normal'
        ];
        $field = "id, name";
        $category = db::name('category')->field($field)->where($where)->order('weigh desc')->select();
        return json(['code' => 200, 'msg' => 'success', 'data' => $category]);
    }

    public function payMail($post){
        $email = [];
        if(!empty($post['email'])) $post['email'] = trim($post['email'], ' ');
        if(!empty($this->options['buy_data'][0]['buy'])){
            if(
                empty($post['email']) &&
                !empty($this->options['buy_data'][0]['required']) &&
                $this->options['buy_data'][0]['required'] == 'checked'
            ){
                echo json_encode(['code' => 0, 'msg' => '请输入' . $this->options['buy_data'][0]['name']]);die;
            }
            if(
                !empty($this->options['buy_data'][0]['email']) &&
                $this->options['buy_data'][0]['email'] == 'checked' &&
                !empty($post['email'])
            ){
                $is_email = filter_var($post['email'], FILTER_VALIDATE_EMAIL);
                if (!$is_email){
                    echo json_encode(['code' => 0, 'msg' => '请输入正确的邮箱格式']);die;
                }
                $email[] = $post['email'];
            }
        }
        if(!empty($post['password'])) $post['password'] = trim($post['password'], ' ');
        if(!empty($this->options['buy_data'][1]['buy'])){
            if(
                empty($post['password']) &&
                !empty($this->options['buy_data'][1]['required']) &&
                $this->options['buy_data'][1]['required'] == 'checked'
            ){
                echo json_encode(['code' => 0, 'msg' => '请输入' . $this->options['buy_data'][1]['name']]);die;
            }
            if(
                !empty($this->options['buy_data'][1]['email']) &&
                $this->options['buy_data'][1]['email'] == 'checked' &&
                !empty($post['password'])
            ){
                $is_email = filter_var($post['password'], FILTER_VALIDATE_EMAIL);
                if (!$is_email){
                    echo json_encode(['code' => 0, 'msg' => '请输入正确的邮箱格式']);die;
                }
                $email[] = $post['password'];
            }
        }
        return $email;
    }

    //退出登录
    public function logout() {
        session::delete('user');
        return json(['code' => 1, 'msg' => '已退出']);
    }


    public function register(){
        if($this->site['register'] == 0) return json(['code' => 0, 'msg' => '网站已关闭注册功能！']);
        $post = $this->request->param();
        Db::startTrans();
        try {
            $account_type = getAccountType($post['account']);
            if($account_type != "email"){
                throw new \Exception("请输入正确的邮箱");
            }
            if(empty($post['password'])) throw new \Exception("请输入密码");
            if(empty($post['repassword'])) throw new \Exception("两次密码输入不一致");
            if($post['password'] != $post['repassword']) throw new \Exception("两次密码输入不一致");
            $where = [
                "{$account_type}" => $post['account'],
            ];
            $user = db::name('user')->where($where)->find();
            if($user) throw new \Exception("邮箱被占用，请更换其他邮箱");
            $insert = [
                "{$account_type}" => $post['account'],
                "salt" => Random::alnum(),
                "nickname" => $post['account'],
                "createtime" => time(),
            ];
            $insert["password"] = $this->getEncryptPassword($post['password'], $insert['salt']);
            $uid = db::name("user")->insertGetId($insert);
            db::name("options")->where(["option_name" => "user_total"])->setInc("option_content");
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        if($uid){
            $user = db::name('user')->where(['id' => $uid])->find();
            session::set("user", $user);
            return json(['code' => 1, 'msg' => '注册成功']);
        }else{
            return json(['code' => 0, 'msg' => '注册失败']);
        }
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '') {
        return md5(md5($password) . $salt);
    }

    public function login(){
        if($this->site['login'] == 0) return json(['code' => 0, 'msg' => '网站已关闭登录功能！']);

        $post = $this->request->param();
        $account_type = getAccountType($post['account']);
        $account_type = $account_type == 'username' ? 'mobile' : $account_type;

        $field = "u.id, u.consume, u.nickname, u.password, u.salt, u.email, u.mobile, u.avatar, u.agent, u.money,";
        $field .= "u.score, u.createtime, g.name grade_name, g.discount";
        $user = db::name('user')->alias('u')
            ->join('user_grade g', 'u.agent=g.id', 'left')
            ->field($field)
            ->where([$account_type => $post['account']])->find();
        if(!$user) return json(['code' => 0, 'msg' => '账号不存在']);

        $password = $this->getEncryptPassword($post['password'], $user['salt']);
        if($password != $user['password']) return json(['code' => 0, 'msg' => '密码错误']);
        session::set('user', $user);
        return json(['code' => 1, 'msg' => '登录成功']);
    }

}
