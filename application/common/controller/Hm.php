<?php

namespace app\common\controller;


use app\common\model\Category;
use think\Cache;
use think\Db;
use think\Session;

/**
 * 公共方法类
 */
class Hm{


    /**
     * getParams
     */
    static public function getParams($type){
        if($type == 'input'){
            $data = file_get_contents("php://input");
        }
        if($type == 'get'){
            $data = input('get.');
        }
        if($type == 'post'){
            $data = input('post.');
        }
        return $data;
    }


    /**
     * getPayList
     */

    static public function payList(){
        $where = [
            'option_name' => 'active_pay',
        ];

        $active_pay = db::name('options')->where($where)->value('option_content');
        $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);
        $data = [];
        $pays = [];
        foreach($active_pay as $val){

            if(in_array('alipay', $val['pay_type']) && !in_array('alipay', $pays)){
                $data[] = [
                    'name' => '支付宝',
                    'key' => 'alipay'
                ];
                $pays[] = 'alipay';
            }
            if(in_array('wxpay', $val['pay_type']) && !in_array('wxpay', $pays)) {
                $data[] = [
                    'name' => '微信支付',
                    'key' => 'wxpay'
                ];
                $pays[] = 'wxpay';
            }
            if(in_array('qqpay', $val['pay_type']) && !in_array('qqpay', $pays)) {
                $data[] = [
                    'name' => 'QQ支付',
                    'key' => 'qqpay'
                ];
                $pays[] = 'qqpay';
            }
            if(in_array('apple_pay', $val['pay_type']) && !in_array('apple_pay', $pays)) {
                $data[] = [
                    'name' => '苹果支付',
                    'key' => 'apple_pay'
                ];
                $pays[] = 'apple_pay';
            }
            if(in_array('card', $val['pay_type']) && !in_array('card', $pays)) {
                $data[] = [
                    'name' => '银行卡',
                    'key' => 'card'
                ];
                $pays[] = 'card';
            }

        }

        return $data;
    }

    //订单发货
    static public function handleOrder($goods, $order, $options){
        $prefix = \think\Db::getConnection()->getConfig('prefix');
        $status = true;
        $timestamp = time();
        $cdkey = [];
        $kami = [];
        $order_update = [
            'pay_time' => $timestamp
        ];


        $order['buy_num'] = $order['buy_num'] < 1 ? 1 : $order['buy_num'];
        if($goods['dock_id'] == 0 && $goods['goods_type'] == 'manual'){ //自营商品 - 手动发货
            $order_update['status'] = 'wait-send';
        }
        if($goods['dock_id'] == 0 && ($goods['goods_type'] == 'alone' || $goods['goods_type'] == 'fixed')){ //自营商品 - 自动发货
            $order_update['status'] = 'success';
            $ckd_field = "id, goods_id, cdk";
            if(empty($goods['sku'])){
                if($goods['goods_type'] == 'fixed'){ //固定
                    $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} limit 1;";
                }
                if($goods['goods_type'] == 'alone'){ //独立
                    if($options['cdk_order'] == 'random') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by rand() limit {$order['buy_num']};";
                    if($options['cdk_order'] == 'asc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by id asc limit {$order['buy_num']};";
                    if($options['cdk_order'] == 'desc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by id desc limit {$order['buy_num']};";
                }
            }else{
                if(empty($order['buy_info'])) {
                    echo json_encode(['code' => 400, 'msg' => '订单规格错误']);
                    die;
                }
                $buy_info = json_decode($order['buy_info'], true);
                $where = "goods_id={$goods['id']} && sku_ids='{$buy_info['sku_ids']}'";
                if($goods['goods_type'] == 'fixed'){ //固定
                    $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id={$goods['id']} limit 1;";
                }
                if($goods['goods_type'] == 'alone'){ //独立
                    if($options['cdk_order'] == 'random') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where {$where} order by rand() limit {$order['buy_num']};";
                    if($options['cdk_order'] == 'asc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where {$where} order by id asc limit {$order['buy_num']};";
                    if($options['cdk_order'] == 'desc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where {$where} order by id desc limit {$order['buy_num']};";
                }
            }

            $cdkey = db::query($sql_cdkey);
        }
        if($goods['dock_id'] > 0){ //对接商品
            $result = db::name('dock')->where(['id' => $goods['dock_id']])->find();
            $view = ROOT_PATH . '/content/dock/' . $result['type'] . '/select_goods.html';
            include ROOT_PATH . '/content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
            $objName = ucfirst($result['type']) . 'Dock';
            $dockObj = new $objName();
            $dockInfo = json_decode($result['info'], true);
            $result = $dockObj->placeOrder($goods, $order, $dockInfo);
            if($result['code'] == 200){
                $order_update['remote_order_no'] = $result['data']['remote_order_no'];
                if(!empty($result['data']['cdk'])){
                    $order_update['status'] = 'success';
                    foreach($result['data']['cdk'] as $val){
                        $cdkey[]['cdk'] = $val;
                    }
                }else{
                    $order_update['status'] = 'wait-send';
                }
            }else{ //下单失败
                $order_update = [
                    'dock_explain' => $result['msg'],
                    'status' => 'fail'
                ];
                $status = false;
            }
        }


        $insert_sold = [];
        foreach($cdkey as $val){
            $kami[] = $val['cdk'];
            if(isset($val['id'])) $delete_cdk[] = $val['id'];
            $insert_sold[] = [
                'order_id' => $order['id'],
                'content' => $val['cdk'],
                'create_time' => $timestamp
            ];
        }
        db::name('sold')->insertAll($insert_sold);
        if($goods['goods_type'] == 'alone') db::name('cdkey')->where(['id' => ['in', implode(',', $delete_cdk)]])->delete(); //删除独立卡密库存
        if($goods['goods_type'] == 'fixed') db::name('cdkey')->where(['goods_id' => $goods['id']])->setDec('num', $order['buy_num']);
        db::name('order')->where(['id' => $order['id']])->update($order_update); //更新订单状态
        if($status == true){
            db::name('goods')->where(['id' => $goods['id']])->setInc('sales', $order['buy_num']); //更新商品销量
            db::name('goods')->where(['id' => $goods['id']])->setInc('sales_money', $order['money']); //更新商品销售额
        }

        $data = [
            'out_trade_no' => $order['order_no'],
            'cdk' => $kami,
            'stock' => '版本停用',
            'pay_time' => $timestamp
        ];
        return ['code' => 200, 'msg' => 'success', 'data' => $data];


    }

    /**
     * 构造表单并提交
     */
    static public function submitForm($url, $data){
        $sHtml = "<form id='form-box' action='" . $url . "' method='POST'>";
        foreach($data as $key => $val) {
            $val = str_replace("'", "&apos;", $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='提交支付中...'></form>";
        $sHtml = $sHtml . "<script>document.forms['form-box'].submit();</script>";
        echo $sHtml;
        die();
    }

    /**
     * 处理商品信息
     */
    static public function handle_goods($goods, $user, $options = null){
        $goods['images'] = explode(',', $goods['images']);
        $goods['cover'] = $goods['images'][0];
        $goods['eject'] = empty(strip_tags($goods['eject'])) ? '' : $goods['eject'];
        $goods['buy_msg'] = empty(strip_tags($goods['buy_msg'])) ? '' : $goods['buy_msg'];
        $goods['sku'] = json_decode($goods['sku'], true);

        if(empty($goods['cover'])) $goods['cover'] = '/assets/img/none.jpg';

//        测试
        // if(empty($goods['cover'])){
        //     $goods['cover'] = 'http://127.0.0.1:12345/assets/img/none.jpg';
        // }else{
        //     $goods['cover'] = 'http://127.0.0.1:12345' . $goods['cover'];
        // }
//      测试

        foreach($goods['price'] as $val){
            if($user['agent'] >= $val['grade_id'] && $val['sku_ids'] == $goods['price'][0]['sku_ids']){
                $goods['real_price'] = $val['price'];
            }
        }
        if(!empty($user['discount']) && empty($goods['real_price'])){
            $goods['real_price'] = sprintf("%.2f", $goods['price'] - sprintf("%.2f",floor(($goods['price'] * ($user['discount'] / 100)) * 100) / 100));
        }

        $inputs = [];
        if($goods['attach_id'] > 0){ //附加选项
            $attach_result = db::name('attach')->where(['id' => $goods['attach_id']])->find();
            if($attach_result){
                $attach_result = json_decode($attach_result['value_json'], true);
                if($attach_result){
                    foreach($attach_result as $key => $val){
                        $inputs[] = [
                            'name' => "inputs[{$key}]",
                            'title' => $key,
                            'placeholder' => $val,
                        ];
                    }
                }
            }
        }
        $inputs_result = json_decode($goods['inputs'], true);
        if($inputs_result){
            foreach($inputs_result as $key => $val){
                $inputs[] = [
                    'name' => "inputs[{$val['name']}]",
                    'title' => $val['title'],
                    'placeholder' => $val['placeholder'],
                ];
            }
        }
        $goods['inputs'] = $inputs;

        $goods['stock_show'] = empty($goods['stock']) ? 0 : $goods['stock'];
        if($options != null && $options['stock_show_switch'] == 1 && is_numeric($goods['stock'])){

            $stock_show = json_decode($options['stock_show'], true);
            foreach($stock_show as $val){
                if($goods['stock'] >= $val['less'] && $goods['stock'] <= $val['greater']) $goods['stock_show'] = $val['content'];
            }
        }

        return $goods;
    }

    /**
     * 获取商品信息
     */
    static public function getGoodsInfo($goods_id, $agent, $options = null){

        if(!isset($goods_model)) $goods_model = new \app\api\model\Goods;
        $goods = $goods_model->alias('g')
            ->join('cdkey c', 'g.id=c.goods_id')
            ->field('g.sales, g.details, g.dock_id, g.goods_type, g.shelf, g.id, g.name, g.images, g.eject, g.buy_msg, g.sku, g.attach_id, g.inputs, g.category_id, sum(c.num) stock')
            ->where(['g.shelf' => 0, 'g.deletetime' => ['exp', Db::raw('is null')]])
            ->with(['price' => function($withPrice){
                $withPrice->field('grade_id, price, sku, sku_ids, goods_id');
            }])->where(['g.id' => $goods_id])->find()->toArray();

        return $goods ? self::handle_goods($goods, $agent, $options) : null;
    }


    /**
     * 获取订单列表
     */
    static public function orderList($params = []) {

        $params = empty($params) ? input() : $params;
        $offset = $params['offset'];
        $limit = $params['limit'];
        $search_type = empty($params['search_type']) ? false : $params['search_type'];

        if($search_type == 'voucher'){
            $where = [
                'o.account' => $params['account'],
                'o.password' => $params['password']
            ];
            if(isset($params['search_password'])){
                unset($where['password']);
            }
        }else if($search_type == 'order_no'){
            $where = [
                'o.order_no' => $params['order_no']
            ];
        }else{
            $user = Hm::getUser();
            $where = [
                'o.uid' => $user['id'],
            ];
        }


        $list = db::name('order')->alias('o')
            ->join('goods g', 'g.id=o.goods_id')
            ->field('o.*, g.name goods_name')
            ->where($where)->order('o.id desc')->limit($offset, $limit)->select();

        foreach($list as &$val){
            if($val['status'] == 'fail'){
                $val['s'] = '下单失败，请联系客服';
                $val['s_color'] = '#d20707';
            }elseif($val['status'] == 'wait-pay' || $val['status'] == 'yiguoqi'){
                $val['s'] = '待付款';
            }elseif($val['status'] == 'wait-send'){
                $val['s'] = '待发货';
            }elseif($val['status'] == 'yifahuo'){
                $val['s'] = '待收货';
            }elseif($val['status'] == 'success'){
                $val['s'] = '交易完成';
                $val['s_color'] = '#52c41a';
            }else{
                $val['s'] = '订单状态错误';
                $val['s_color'] = '#d20707';
            }
            $val['timestamp'] = date('Y-m-d H:i:s', $val['create_time']);
        }
        return json_encode(['data' => $list, 'info' => 'ok', 'status' => 0]);
    }

    /**
     * 获取当前登录用户或游客信息
     */
    static public function getUser(){
        if(session::has('user')){ //已登录用户
            $field = "u.id, u.consume, u.tourist, u.nickname, u.email, u.mobile, u.avatar, u.agent, u.money, u.score, u.status, g.id gid, g.name gname, g.discount";
            $user = db::name('user')->alias('u')
                ->join('user_grade g', 'u.agent=g.id', 'left')
                ->where(['u.id' => session::get('user')['id']])
                ->field($field)
                ->find();
            if(!$user){
                $user['login'] = false;
                session::delete('user');
                return self::getUser();
            }else{
                $user['login'] = true;
            }
        }else{ //游客
            $user = ['agent' => 0, 'login' => false];
            $tourist = cookie('tourist');
            if(!$tourist){ //老游客查找
                $timestamp = time();
                $tourist = 'ys' . $timestamp . mt_rand(1000, 9999); //游客标识
                cookie('tourist', $tourist, $timestamp + 365 * 24 * 3600);
            }
            $user['id'] = $tourist;
        }
        return $user;
    }


    //生成订单号
    static public function generateOrderNo(){
        $order_no = date('YmdHis', time()) . mt_rand(1000, 9999);
        return $order_no;
    }


}
