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

    //订单发货
    static public function handleOrder($goods, $order, $site){
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
            if($goods['goods_type'] == 'fixed'){ //固定
                $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} limit 1;";
            }
            if($goods['goods_type'] == 'alone'){ //独立
                if($site['cdk_order'] == 'random') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by rand() limit {$order['buy_num']};";
                if($site['cdk_order'] == 'asc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by id asc limit {$order['buy_num']};";
                if($site['cdk_order'] == 'desc') $sql_cdkey = "select {$ckd_field} from {$prefix}cdkey where goods_id = {$goods['id']} order by id desc limit {$order['buy_num']};";
            }
            $cdkey = db::query($sql_cdkey);
        }
        if($goods['dock_id'] > 0){ //对接商品
            $result = db::name('dock')->where(['id' => $goods['dock_id']])->find();
            $view = ROOT_PATH . '/public/content/dock/' . $result['type'] . '/select_goods.html';
            include ROOT_PATH . '/public/content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
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
        db::name('order')->where(['id' => $order['id']])->update($order_update); //更新订单状态
        if($status == true){
            db::name('goods')->where(['id' => $goods['id']])->setInc('sales', $order['buy_num']); //更新商品销量
            db::name('goods')->where(['id' => $goods['id']])->setDec('stock', $order['buy_num']); //更新商品库存
            db::name('goods')->where(['id' => $goods['id']])->setInc('sales_money', $order['money']); //更新商品销售额
        }

        $data = [
            'out_trade_no' => $order['order_no'],
            'cdk' => $kami,
            'stock' => db::name('goods')->where(['id' => $goods['id']])->value('stock')
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
        foreach($goods['grade_price'] as $val){
            if($user['agent'] == $val['grade_id']){
                $goods['real_price'] = $val['price'];
            }
        }
        if(!empty($user['discount']) && empty($goods['real_price'])){
            $goods['real_price'] = sprintf("%.2f", $goods['price'] - sprintf("%.2f",floor(($goods['price'] * ($user['discount'] / 100)) * 100) / 100));
        }
        if(empty($goods['real_price'])){
            $goods['real_price'] = $goods['price'];
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
        if($options != null && $options['stock_show_switch'] == 1 && is_numeric($goods['stock'])){
            $stock_show = json_decode($options['stock_show'], true);
            foreach($stock_show as $val){
                if($goods['stock'] > $val['less'] && $goods['stock'] < $val['greater']) $goods['stock_show'] = $val['content'];
            }
        }

        return $goods;
    }

    /**
     * 获取商品信息
     */
    static public function getGoodsInfo($goods_id, $agent, $options = null){
        if(!isset($model)){
            $model = new \app\admin\model\Goods;
        }

        $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where(['goods.id' => $goods_id])->find();
        $goods = $goods->toArray();
        if(!$goods){
            return null;
        }

        $goods = self::handle_goods($goods, $agent, $options);

        return $goods;
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

        $timestamp = time();

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
//        return $list;
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
                session::delete('user');
                return self::getUser();
            }
        }else{ //游客
            $user = ['agent' => 0];
            $tourist = cookie('tourist');
            if(!$tourist){ //老游客查找
                $timestamp = time();
                $tourist = $timestamp . mt_rand(1000, 9999); //游客标识
                cookie('tourist', $tourist, $timestamp + 365 * 24 * 3600);
            }
            $user['id'] = $tourist;
        }
        return $user;
    }




}
