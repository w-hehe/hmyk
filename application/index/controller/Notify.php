<?php

namespace app\index\controller;

use app\common\controller\Frontend;

use think\Db;


class Notify extends Frontend {

    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];



    public function ret(){
        $params = $this->request->param();

        if($this->user){
            if($params['hm_type'] == 'goods'){
                if(isset($params['out_trade_no'])){
                    $order = db::name('goods_order')->where(['out_trade_no' => $params['out_trade_no']])->find();
                    if(empty($order['pay_time'])){
                        sleep(1);
                        $this->redirect('/index/notify/ret/hm_type/' . $params['hm_type'] . '/out_trade_no/' . $params['out_trade_no']);die;
                    }
                }
                $this->redirect(url('/order')); die;
            }
            if($params['hm_type'] == 'recharge'){
                if(isset($params['out_trade_no'])){
                    $order = db::name('recharge_order')->where(['out_trade_no' => $params['out_trade_no']])->find();
                    if(empty($order['pay_time'])){
                        sleep(1);
                        $this->redirect('/index/notify/ret/hm_type/' . $params['hm_type'] . '/out_trade_no/' . $params['out_trade_no']);die;
                    }
                }
                $this->redirect(url('/bill')); die;
            }
        }else{
            $order = db::name('goods_order')->where(['out_trade_no' => $params['out_trade_no']])->find();
            if(empty($order['pay_time'])){
                sleep(1);
                $this->redirect('/index/notify/ret/out_trade_no/' . $params['out_trade_no']);die;
            }
            $u = '';
            if(!empty($order['mobile'])){
                $u .= "mobile={$order['mobile']}&";
            }
            if(!empty($order['email'])){
                $u .= "email={$order['email']}&";
            }
            if(!empty($order['password'])){
                $u .= "password={$order['password']}&";
            }
            $u = rtrim($u, '&');

            $this->redirect(url('/find_order') . '?' . $u);die;
        }




    }






    public function index(){

        $params = $this->request->param();

        unset($params['hm_type']);
        unset($params['plugin']);

        $plugin = $this->request->param('plugin');
        $hm_type = $this->request->param('hm_type');



        include_once ROOT_PATH . "content/{$plugin}/{$plugin}.php";

        $result = checkSign($params);

        if($plugin == 'vmqpay'){
            $eo = 'success';
        }else{
            $eo = 'ok';
        }

        if($result){

            $order = db::name($hm_type . '_order')->where(['out_trade_no' => $result['out_trade_no']])->find();

            if(!$order || $order['pay_time']){
                echo $eo;die;
            }
            try{

                if($hm_type == 'recharge'){ //充值回调
                    db::name('recharge_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp, 'trade_no' => $result['trade_no']]);
                    $user = db::name('user')->where(['id' => $order['user_id']])->find();
                    db::name('user')->where(['id' => $order['user_id']])->setInc('money', $order['money']);

                    $bill_insert = [
                        'create_time' => $this->timestamp,
                        'user_id' => $user['id'],
                        'before' => $user['money'],
                        'after' => $user['money'] + $order['money'],
                        'value' => $order['money'],
                        'content' => '余额充值'
                    ];
                    db::name('bill')->insert($bill_insert);
                    echo $eo;die;
                }
                if($hm_type == 'goods'){ //商品回调
                    db::name('goods_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp, 'trade_no' => $result['trade_no']]);
                    $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
                    $this->notifyGoodsSuccess($goods, $order);
                    echo $eo;die;
                }

            }catch(\Exception $e){

                $insert = [
                    'name' => '代码错误',
                    'content' => $e->getMessage() . '---' . $e->getLine(),
                    'create_time' => date('Y-m-d H:i:s', $this->timestamp)
                ];
                db::name('test')->insert($insert);

            }

        }else{

            $insert = [
                'name' => '验签失败',
                'content' => '验签失败',
                'create_time' => date('Y-m-d H:i:s', $this->timestamp)
            ];
            db::name('test')->insert($insert);

        }

    }


    /**
     * 执行购买商品的回调操作
     * 1，写入发货表，更新库存表
     * 2，更新商品库存字段
     * 3，更新商品销量字段
     * 4，更新订单状态
     * 5，返佣给上级
     */
    protected function notifyGoodsSuccess($goods, $order) {
        db::name('goods_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp]);
        if($order['user_id']){
            $order['email'] = db::name('user')->where(['id' => $order['user_id']])->value('email');
        }
        if ($goods['type'] == 'alone') { //更新库存表并写入发货表
            $stock = db::name('stock')->field('id, content')->where(['sku_id' => $order['sku_id']])->whereNull('sale_time')->limit($order['goods_num'])->select();
            $stock_ids = array_column($stock, 'id');
            db::name('stock')->whereIn('id', $stock_ids)->update(['sale_time' => $this->timestamp]); //更新库存表
            $deliver = [];
            foreach ($stock as $val) {
                $deliver[] = [
                    'content' => $val['content'],
                    'order_id' => $order['id'],
                    'create_time' => $this->timestamp
                ];
            }
            db::name('deliver')->insertAll($deliver);
            doAction('send_goods', $order, $deliver);
        }
        if ($goods['type'] == 'fixed') { //更新库存表并写入发货表
            $stock = db::name('stock')->where(['sku_id' => $order['sku_id']])->limit($order['goods_num'])->find();

            $deliver = [];
            for ($i = 0; $i < $order['goods_num']; $i++) {
                $deliver[] = [
                    'content' => $stock['content'],
                    'create_time' => $this->timestamp,
                    'order_id' => $order['id']
                ];
            }
            db::name('deliver')->insertAll($deliver); //写入发货表
            db::name('stock')->where(['id' => $stock['id']])->setDec('num', $order['goods_num']); //更新库存表
            doAction('send_goods', $order, $deliver);
        }
        if ($goods['type'] == 'invented') {
            if ($goods['is_sku'] == 0) {
                $stock = db::name('stock')->where(['sku_id' => $goods['sku'][0]['id']])->find();
            }
            if ($goods['is_sku'] == 1) {
                $stock = db::name('stock')->where(['sku_id' => $order['sku_id']])->find();
            }
            db::name('stock')->where(['id' => $stock['id']])->setDec('num', $order['goods_num']); //更新库存表
        }
        db::name('goods')->where(['id' => $goods['id']])->setDec('stock', $order['goods_num']); //更新商品库存字段
        db::name('goods')->where(['id' => $goods['id']])->setInc('sales', $order['goods_num']); //更新商品销量字段
        db::name('goods_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp]); //更新订单状态
        db::name('sku')->where(['id' => $order['sku_id']])->setDec('stock', $order['goods_num']); //更新库存表

        // 计算该笔订单的利润
        $translate = $order['money'] - ($order['goods_cost'] * $order['goods_num']);
        if ($translate <= 0) {
            return true;
        }

        /**
         * 返佣给子站长
         */
        if (!$this->is_main) { //如果是子站下单
            // 获取返佣比例
            $rebate = db::name('merchant_grade')->where(['id' => $this->merchant['grade_id']])->value('rebate');
            // 计算佣金
            $commission = $translate * ($rebate / 100);
            // 记录分站长账单
            $merchant_user = db::name('user')->where(['id' => $this->merchant['user_id']])->find();
            $bill_insert = [
                'create_time' => $this->timestamp,
                'user_id' => $this->merchant['user_id'],
                'before' => $merchant_user['money'],
                'after' => $merchant_user['money'] + $commission,
                'value' => $commission,
                'content' => '子站用户购买商品返佣'
            ];
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $this->merchant['user_id']])->setInc('money', $commission);
        }

        /**
         * 返佣给上级
         * 1，获取返佣比例
         * 2，返佣给上级
         * 3，记录余额账单
         */
        if(empty($order['user_id'])) {
            return true;
        }
        $user = db::name('user')->where(['id' => $order['user_id']])->find();
        db::name('user')->where(['id' => $order['user_id']])->setInc('consume', $order['money']);

        //给上级返佣、记录余额账单
        $bill_insert = [
            'create_time' => $this->timestamp,
        ];
        if ($user['p1'] > 0 && $this->options['rebeat_1'] > 0) {
            $commission = $translate * ($this->options['rebeat_1'] / 100);
            $puser = db::name('user')->where(['id' => $user['p1']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '一级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p1']])->setInc('money', $commission);
        }
        if ($user['p2'] > 0 && $this->options['rebeat_2'] > 0) {
            $commission = $translate * ($this->options['rebeat_2'] / 100); //佣金
            $puser = db::name('user')->where(['id' => $user['p2']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '二级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p2']])->setInc('money', $commission);
        }
        if ($user['p3'] > 0 && $this->options['rebeat_3'] > 0) {
            $commission = $translate * ($this->options['rebeat_3'] / 100); //佣金
            $puser = db::name('user')->where(['id' => $user['p3']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '三级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p3']])->setInc('money', $commission);
        }

        return true;
    }


}
