<?php

namespace app\api\controller;

use think\Db;

class Order extends Base {


    /**
     * 获取订单信息
     */
    public function info(){
        $order_no = $this->get['out_trade_no'];
        $field = "id, order_no as out_trade_no, buy_num, money, status, create_time, pay_time, goods_id";
        $order = db::name('order')->field($field)->where(['order_no' => $order_no])->find();
        $order_id = $order['id'];
        $goods_id = $order['goods_id'];
        unset($order['goods_id']);
        unset($order['id']);
        $cdk = [];
        if(!$order) return json(['code' => 400, 'msg' => '订单不存在']);
        if($order['status'] == 'success'){
            $sold = db::name('sold')->where(['order_id' => $order_id])->select();
            foreach($sold as $val){
                $cdk[] = $val['content'];
            }
        }else{
            // echo '<pre>'; print_r($order);die;
            $goods = db::name('goods')->where(['id' => $goods_id])->find();
            if(!$goods) return json(['code' => 400, 'msg' => '数据缺失，无法获取']);
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
        return json(['code' => 200, 'msg' => 'success', 'data' => $order]);
    }







########################################################################################################################################################################################
    /**
     * 获取订单列表
     */
    public function list() {

        $offset = input("offset");
        $limit = input("limit");
        $uid = session::has("uid") ? session::get("uid") : session::get("tourist");

        $where = [
            'uid' => $uid,
        ];

        $list = db::name('order')->where($where)->order('id desc')->limit($offset, $limit)->select();

        $timestamp = time();

        foreach($list as &$val){

            if($val['status'] == -1){
                $val['s'] = '订单已失效';
            }elseif($val['pay'] == 0 && $timestamp - $val['createtime'] >= 600){
                $val['s'] = '订单已失效';
                db::name('order')->where(['id' => $val['id']])->update(['status' => -1]);
                $val['status'] = -1;
            }elseif($val['pay'] == 0){
                $val['s'] = '待付款';
            }elseif($val['pay'] == 1 && $val['status'] == 1){
                $val['s'] = '待发货';
            }elseif($val['pay'] == 1 && $val['status'] == 2){
                $val['s'] = '待收货';
            }elseif($val['pay'] == 1 && $val['status'] == 9){
                $val['s'] = '交易完成';
                $val['s_color'] = '#52c41a';
            }else{
                $val['s'] = '订单状态错误';
                $val['s_color'] = '#d20707';
            }
            $val['timestamp'] = date('Y-m-d H:i:s', $val['createtime']);
        }
        return json_encode(['data' => $list, 'info' => 'ok', 'status' => 0]);

    }

    //查看订单内容
    public function orderContent(){

        $order_id = $this->request->param('order_id');
        $where = [
            'uid' => $this->uid == null ? $this->tourist : $this->uid,
            'id' => $order_id
        ];
        $order = db::name('order')->where($where)->find();

        $kami = $order['kami'];

        $this->assign([
            'title' => '订单详情    ',
            'order' => $order,
            'kami' => $kami,
        ]);
        return view($this->template_path . "orderContent.html");
    }

    //确认收货
    public function shouhuo(){
        $order_id = $this->request->param('order_id');
        $update = [
            'status' => 9
        ];
        $where = [
            "id" => $order_id,
            'uid' => $this->uid == null ? $this->tourist : $this->uid,
        ];
        db::name('order')->where($where)->update($update);
        return json(['msg' => '已收货', 'code' => 200]);
    }

    //删除订单
    public function del(){
        $order_id = $this->request->param('order_id');
        $where = [
            "id" => $order_id,
            'uid' => $this->uid == null ? $this->tourist : $this->uid,
        ];
//        print_r($where);die;
        db::name('order')->where($where)->delete();
        return json(['msg' => '已删除', 'code' => 200]);
    }

    /**
     * 获取订单的支付状态
     */
    public function getorderstatus(){
        $order_no = $this->request->param('out_trade_no');
        $table = $this->request->param('table');
        if($table == 'order'){
            $order = db::name($table)->where(['order_no' => $order_no])->find();

            if(time() - $order['createtime'] >= 600){
                return json(['msg' => '订单已过期', 'code' => 400]);
            }
            if($order['pay'] == 0){
                return json(['code' => 200, 'msg' => '未支付', 'data' => -1]);
            }else{
                return json(['code' => 200, 'msg' => '已支付', 'data' => 1]);
            }

            $pay = $pay && $order['pay'] != 1 ? true : false;
        }else if($table == 'money_bill'){
            $pay = db::name($table)->where(['order_no' => $order_no])->value('status');
            $pay = $pay && $pay != 0 ? true : false;
        }

        if(!$pay){
            return json(['code' => 200, 'msg' => '未支付', 'data' => -1]);
        }else{
            return json(['code' => 200, 'msg' => '已支付', 'data' => 1]);
        }
    }



}
