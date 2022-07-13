<?php

namespace app\api\controller\v1;

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
        if(empty($user)) $this->user = Hm::getUser();

        #########测试数据#########
//        $this->user['id'] = 1;
//        $this->user['login'] = true;
//        $this->user['money'] = 10;
        #########测试数据#########

        $options = db::name('options')->select();
        foreach($options as $val) $this->options[$val['option_name']] = $val['option_content'];
        $this->site = Config::get("site");
    }
    //退出登录
    public function logout() {
        session::delete('user');
        return json(['code' => 1, 'msg' => '已退出']);
    }


    public function register(){
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

    /**
     * template
     */
    public function template(){

        if(Cache::has('pisces_template')){
            $data = cache::get('pisces_template');
        }else{
            $path = ROOT_PATH . "public/content/template/pisces/";
            $info = file_get_contents("{$path}setting.json");
            $data = json_decode($info, true);
            cache::set('pisces_template', $data);
        }
        $data['options'] = $this->options;
        $data['site'] = $this->site;
        $data['user'] = $this->user;
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
     * 提交商品支付
     */
    public function goodsPay(){
        $post = $this->request->post();

        if(empty($post['buy_num']) || $post['buy_num'] < 1){
            return json(['code' => 0, 'msg' => '请输入您要购买的商品数量']);
        }

        $goods = Hm::getGoodsInfo($post['goods_id'], $this->user);
        $order_money = sprintf('%.2f', $goods['real_price'] * $post['buy_num']);

        if($order_money > 0 && empty($post['pay_type'])) return json(['code' => 0, 'msg' => '请选择支付方式']);

        if($post['pay_type'] == 'balance' || $order_money == 0){
            if($this->user['money'] < $order_money) return json(['code' => 0, 'msg' => '余额不足，请充值']);
            db::name('user')->where(['id' => $this->user['id']])->setDec('money', $order_money);
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
            'goods_money' => $goods['real_price'], //商品单价
            'money'       => $order_money, //订单金额
            'remote_money' => $goods['buy_price'] * $post['buy_num'], //进货价
            'inputs' => json_encode($inputs), //对接订单参数
            'ip' => getClientIp(),
            'pay_plugin' => $pay_plugin, //支付插件
            'status' => 'wait-pay'
        ];
        if($order_money == 0) unset($insert['pay_type']);
        $insert['email'] = empty($post['email']) ? '' : $post['email'];
        $insert['password'] = empty($post['password']) ? '' : $post['password'];
        $order_id = db::name('order')->insertGetId($insert);
        $order = $insert;

        $order['id'] = $order_id;
        if($post['pay_type'] == 'balance' || $order_money == 0){
            //给商品发货或去对接站购买商品
            Hm::handleOrder($goods, $order, $this->site);
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


        $pluginPath = ROOT_PATH . 'public/content/plugin/' . $pay_plugin . '_pay/' . $pay_plugin . '_pay.php';
        $goods['name'] = empty($this->site['diy_name']) ? $goods['name'] : $this->site['diy_name'];
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
        return json($result);
    }

    public function callback_return($content = null, $i = 0){
        try{
            $content = $content == null ? $this->request->get() : $content;
            $pay_plugin = $content['pay_plugin'];
            $pluginPath = ROOT_PATH . 'public/content/plugin/' . $pay_plugin . '_pay/' . $pay_plugin . '_pay.php';
            require_once $pluginPath;
            $out_trade_no = checkSign($content);

            $order = db::name('order')->where(['order_no' => $out_trade_no])->find();
            if(!$order) die('非法注入');
            if($order['status'] == 'wait-pay'){ //重复通知
                sleep(3);
                $i++;
                if($i >= 2){
                    header("location: /#/order/detail?out_trade_no={$out_trade_no}"); die;
                }else{
                    $this->callback_return($content, $i);
                }
            }else{
                header("location: /#/order/detail?out_trade_no={$out_trade_no}"); die;
            }
        }catch(\Exception $e){
            echo $e->getMessage();
            echo '<br>';
            echo $e->getLine();
        }

    }

    public function qrCode(){
        $qr_code = $this->request->param('qr_code');
        QRcode::png($qr_code,false, 'L', 7, 2);
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
            if(empty($post['password'])) return json(['code' => 0, 'msg' => '请输入查单密码']);
            $where['password'] = $post['password'];
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
        $field = "id, order_no as out_trade_no, buy_num, money, status, create_time, pay_time, goods_id";
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
                include ROOT_PATH . '/public/content/dock/' . $dock['type'] . '/' . ucfirst($dock['type']) . '.php';
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
        $user = Hm::getUser();

        $goods = Hm::getGoodsInfo($goods_id, $user, $this->options);
        if(!$goods || !empty($goods['deletetime']) || $goods['shelf'] == 1){
            return json(['code' => '404', 'msg' => '该商品已下架或被删除']);
        }

        $data = [
            'goods' => $goods,
            'options' => $this->options
        ];
        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    /**
     * 商品页接口
     */
    public function goods(){
        $goods_id = $this->request->param('goods_id');
        $user = Hm::getUser();

        $goods = Hm::getGoodsInfo($goods_id, $user, $this->options);
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
            $model = new \app\admin\model\Goods;

            $category = db::name('category')->where(['status' => 'normal'])->order('weigh desc')->select();

            foreach($category as $key => $val){

                $where = [
                    'shelf' => 0,
                    'deletetime' => ['exp', Db::raw('is null')],
                    'category_id' => $val['id'],
                ];
                if($val['goods_sort'] == 0 || $val['goods_sort'] == 1){ //id降序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, id desc')->select();
                }else if($val['goods_sort'] == 2){ //id升序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, id asc')->select();
                }else if($val['goods_sort'] == 3){ //价格降序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, price desc')->select();
                }else if($val['goods_sort'] == 4){ //价格升序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, price asc')->select();
                }else if($val['goods_sort'] == 5){ //销量降序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, sales desc')->select();
                }else if($val['goods_sort'] == 6){ //销量升序
                    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, sales asc')->select();
                }
                $goods = $goods->toArray();

//        echo '<pre>'; print_r($goods);die;

                $list[$key] = $val;
                $list[$key]['goods'] = [];
                $list[$key]['goods_num'] = 0;

                foreach($goods as $k => $v){

                    if($val['id'] == $v['category_id']){
                        $list[$key]['goods'][] = Hm::handle_goods($goods[$k], $this->user, $this->options);
                        unset($goods[$k]);
                        $list[$key]['goods_num']++;
                    }
                }
            }
        }catch (\Exception $e){
            echo $e->getMessage();
            return [];
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



}
