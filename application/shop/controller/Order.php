<?php

namespace app\shop\controller;
use app\common\controller\Hm;

use think\Cache;
use think\Db;
use think\Session;
use app\common\controller\dock\Dock;
use app\common\controller\dock\Yile;
use app\common\controller\dock\Jiuwu;
use app\common\controller\dock\Kky;

class Order extends Base {

    public $include_dock = false;


    /**
     * 确认订单
     */
    public function postOrder(){
        $post = $this->request->param();
        if(!empty($post['goods_id'])){
            $goods_id = $post['goods_id'];
            $buy_num = $post['buy_num'];
            $post_order = [
                'goods_id' => $goods_id,
                'buy_num' => $buy_num
            ];
            session::set('post_order', $post_order);
        }else if(session::has('post_order')){
            $post_order = session::get('post_order');
            $goods_id = $post_order['goods_id'];
            $buy_num = $post_order['buy_num'];
        }else{
            $goods_id = null;
            $buy_num = null;
        }
        if($goods_id == null || $buy_num == null) $this->error('无效请求，请重新提交');

        header('location: /confirm?post=g' . $goods_id . 'n' . $buy_num);
        die;

        $user = Hm::getUser();
        $goods = Hm::getGoodsInfo($post['goods_id'], $user['agent']);

        $this->assign([
            'goods' => $goods,
            'buy_num' => $buy_num
        ]);

        return view($this->template_path . "confirm_order.html");
    }

    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

        if($this->request->has('order_no')){
            return $this->orderContent($this->request->param('order_no'));
        }
        $search_type = $this->request->get('search_type');

        $post = $this->request->param();

        $search_content = "";
        if($search_type == "voucher" && isset($post['account'])){
            $search_content = [
                "account" => $post["account"],
                "password" => empty($post['password']) ? '' : $post['password']
            ];
            $search_content = urlencode(base64_encode(serialize($search_content)));

        }
        if($search_type == "orderno" && isset($post['orderno'])){
            $search_content = [
                "order_no" => $post["orderno"]
            ];
            $search_content = urlencode(base64_encode(serialize($search_content)));
        }

        $this->assign([
            'title' => '订单列表',
            'search_type' => $search_type, //订单状态
            'post' => $post,
            "search_content" => $search_content,
            'navi' => 'look_order',
        ]);
        return view($this->template_path . "order.html");
    }

    /**
     * 导出卡密
     */
    public function exportCdk(){
        $order = db::name('order')->where(['order_no' => $this->request->param('order_no')])->find();

        $sold = db::name('sold')->where(['order_id' => $order['id']])->select();

        $content = "订单号：" . $order['order_no'] . "\r\n\r\n";

        foreach($sold as $val){
            $content .= $val['content'] . "\r\n";
        }

        Header("Content-type:application/octet-stream");
        Header("Accept-Ranges:bytes");
        header("Content-Disposition:attachment;filename=卡密_" . date('Y年m月d日H时i分s秒') . ".txt");
        header("Expires:0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0 ");
        header("Pragma:public");
        echo $content;die;
    }

    //查看订单内容
    public function orderContent($order_no){
        $user = Hm::getUser();

        if($this->request->param('search_content')){
            $search_content = unserialize(base64_decode(urldecode($this->request->param('search_content'))));
            if(isset($search_content['order_no'])){
                $where = [
                    'order_no' => $search_content['order_no']
                ];
            }else{
                $where = [
                    'account' => $search_content['account'],
                    'password' => $search_content['password'],
                    'order_no' => $order_no
                ];
            }

            if(empty($search_content['password'])){
                unset($where['password']);
            }
            $order = db::name('order')->where($where)->find();

        }else{
            $where = [
                'uid' => $user['id'],
                'order_no' => $order_no
            ];
            $order = db::name('order')->where($where)->find();
        }
        if(!$order){
            $this->error('订单不存在！');
        }

        $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
        $kami = db::name('sold')->where(['order_id' => $order['id']])->select();
        $params = [];
        $order_attach = json_decode($order['attach'], true);
        $order_inputs = json_decode($order['inputs'], true);
        $goods_inputs = json_decode($goods['inputs'], true);
        if($order_attach){
            foreach($order_attach as $key => $val){
                $params[] = [
                    'title' => $key,
                    'value' => $val
                ];
            }
        }

        if($goods_inputs){
            foreach($goods_inputs as $key => $val){
                foreach($order_inputs as $k =>$v){
                    if($val['name'] == $k){
                        $params[] = [
                            'title' => $val['title'],
                            'value' => $v
                        ];
                    }
                }
            }
        }


        switch($order['status']){
            case 'fail':
                $remote_order['status'] = '下单失败，请联系客服';
                break;
            case 'wait-pay':
                $remote_order['status'] = '未支付';
            break;
            case 'wait-send':
                $remote_order['status'] = '待发货';
            break;
            case 'conduct':
                $remote_order['status'] = '进行中';
            break;
            case 'success':
                $remote_order['status'] = '交易完成';
            break;

        }
        $this->assign([
            'title' => '订单详情',
            'order' => $order,
            'kami' => $kami,
            'goods' => $goods,
            'params' => $params,
            'remote_order' => $remote_order
        ]);

        if($this->equipment == 'mobile' && file_exists($this->template_path . "mobile/order_detail.html")){
            return view($this->template_path . "mobile/order_detail.html");
        }else{
            return view($this->template_path . "order_detail.html");
        }
    }

    //确认收货
    public function shouhuo(){
        $order_id = $this->request->param('order_id');
        $update = [
            'status' => 'success'
        ];
        $user = Hm::getUser(); //获取当前用户信息
        $where = [
            "id" => $order_id,
            'uid' => $user['id'],
        ];
        db::name('order')->where($where)->update($update);
        return json(['msg' => '已收货', 'code' => 200]);
    }

    //删除订单
    public function del(){
        $order_id = $this->request->param('order_id');
        $user = Hm::getUser();
        $where = [
            "id" => $order_id,
            'uid' => $user['id']
        ];
        db::name('order')->where($where)->delete();
        return json(['msg' => '已删除', 'code' => 200]);
    }

    /**
     * 获取订单的支付状态
     */
    public function get_recharge_status(){
        $out_trade_no = $this->request->param('out_trade_no');

        $recharge = db::name('recharge')->where(['out_trade_no' => $out_trade_no])->find();
        if(time() - $recharge['create_time'] >= 600){
            return json(['msg' => '订单已过期', 'code' => 400]);
        }
        if(!empty($recharge['pay_time'])){
            $data = [
                'order_no' => $out_trade_no,
            ];
            return json(['code' => 200, 'msg' => '已支付', 'data' => $data]);
        }else{
            return json(['code' => 401, 'msg' => '未支付', 'data' => -1]);
        }
    }

    /**
     * 获取订单的支付状态
     */
    public function getorderstatus(){
        $order_no = $this->request->param('out_trade_no');

        $order = db::name('order')->where(['order_no' => $order_no])->find();
        if(time() - $order['create_time'] >= 600){
            return json(['msg' => '订单已过期', 'code' => 400]);
        }
        if($order['pay_time'] > 0){
            $data = [
                'order_no' => $order_no,
            ];
            return json(['code' => 200, 'msg' => '已支付', 'data' => $data]);
        }else{
            return json(['code' => 401, 'msg' => '未支付', 'data' => -1]);
        }
    }
}
