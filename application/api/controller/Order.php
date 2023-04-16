<?php

namespace app\api\controller;

use app\common\controller\Api;

use think\Db;

/**
 *
 */
class Order extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 订单支付状态
     */
    public function orderPayStatus() {
        $out_trade_no = $this->request->get('out_trade_no');
        $type = $this->request->get('type');
        if($type == 'goods'){
            $order = db::name('goods_order')->where(['out_trade_no' => $out_trade_no])->find();
        }
        if($type == 'recharge'){
            $order = db::name('recharge_order')->where(['out_trade_no' => $out_trade_no])->find();
        }

        if($order['pay_time']){
            return json(['code' => 200, 'msg' => '已支付', 'data' => [
                'out_trade_no' => $order['out_trade_no']
            ]]);
        }else{
            return json(['code' => 400, 'msg' => '未支付']);
        }
    }
}
