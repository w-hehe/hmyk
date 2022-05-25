<?php

namespace app\shop\controller;

use think\Db;
use think\Session;

class Wallet extends Auth {

    //充值
    public function recharge(){
        $params = $this->request->param();

        if($this->request->has('money')){
            $money = $this->request->param('money');

            Db::startTrans();
            try {
                //写入账单记录
                $order_no = $this->generateOrderNo();
                $insert = [
                    'order_no' => $order_no,
                    'uid' => $this->uid,
                    'money' => $money,
                    'pay_type' => '支付宝',
                    'createtime' => time(),
                    'type' => 'recharge', //类型 = 充值
                    'description' => '充值'
                ];
                Db::name('money_bill')->insert($insert);


                $alipay_info = db::name('pay')->where(['type' => 'alipay'])->find();
                $alipay_info = json_decode($alipay_info['value'], true);
                Db::commit();

            }catch(\Exception $e){
                Db::rollback();
                echo $e->getMessage();die;
            }
            $params = [
                'app_id'          => $alipay_info['app_id'], //应用id
                'public_key'      => $alipay_info['public_key'], //支付宝公钥
                'private_key'     => $alipay_info['private_key'], //应用私钥
                'timestamp'       => time(), //发送请求的时间
                'notify_url' => $this->domain . 'shop/notify/recharge/type/alipay', //回调通知 - 充值回调
                'return_url' =>     $this->domain . 'shop/wallet/yue',  //付款完成后跳转的地址
                'body'            => '', //对一笔交易的具体描述信息
                'subject'         => '余额充值', //商品标题
                'out_trade_no'    => $order_no, //商户订单号
                'timeout_express' => '15m', //关闭订单时间
                'total_amount'    => $money, //订单金额，单位/元
                'goods_type'      => 0, //商品主类型 0虚拟 1实物
                'quit_url'        => $this->domain . 'shop/wallet/recharge', //用户取消付款返回商户网站的地址
            ];

            $alipay = new Alipay();
//            $alipay->wap_pay($params);

            $alipay->precreate_pay($params, 'money_bill');
            die;

        }
        return view($this->template_path . "recharge.html");
    }

    //账单
    public function bill(){

        $where = "uid=$this->uid and ((type='recharge' and status=1) or type='system' or type='cashout')";
        if($this->request->isAjax()){
            $post = $this->request->param();
            $start = ($post['page'] - 1) * $post['pageSize'];
            $list = db::name('money_bill')->where($where)->order('id desc')->limit($start, $post['pageSize'])->select();
            foreach($list as &$val){
                $val['createtime'] = date('Y-m-d H:i', $val['createtime']);
            }
            $data = [
                'data' => $list,
                'info' => 'ok',
                'status' => 0
            ];
            return json($data);
        }
        $is_bill = db::name('money_bill')->where($where)->find();
        $this->assign([
            'is_bill' => $is_bill
        ]);
        return view($this->template_path . "bill.html");
    }

    //提现
    public function cashout(){

        $timestamp = time();
        $starttime = date('Y-m-d 00:00:00', $timestamp);
        $endtime = date('Y-m-d 23:59:59', $timestamp);
        $where = [
            'uid' => $this->uid,
            'type' => 'cashout',
            'createtime' => ['between', "$starttime, $endtime"]
        ];

        $cashout_num = db::name('money_bill')->where($where)->count();

        if($this->request->isAjax()){
            $post = $this->request->post();
            $money = $post['money'];
            if($this->site['min_cashout'] > $money){
                $this->error('提现金额有误！');
            }

            db::startTrans();
            try {

                if($this->site['max_cashout_num'] > 0 && $cashout_num >= $this->site['max_cashout_num']){
                    throw new \Exception("您当日提现次数已达上限，明日再来吧~");
                }
                $user = db::name('user')->where(['id' => $this->uid])->find();
                if($user['money'] < $money){
                    throw new \Exception("余额不足");
                }
                $insert = [
                    'uid' => $this->uid,
                    'description' => '提现',
                    'createtime' => time(),
                    'type' => 'cashout',
                    'money' => $money,
                    'charged' => $this->site['cashout_charged'],
                ];
                //实际到账金额
                $insert['actual'] = $insert['charged'] <= 0 ? $money : $money - sprintf("%.2f", ($money * ($insert['charged'] / 100)));
                db::name('user')->where(['id' => $this->uid])->setDec('money', $money);
                db::name('money_bill')->insert($insert);
                db::commit();
            }catch (\Exception $e){
                db::rollback();
                $this->error($e->getMessage());
            }
            $this->success('已发起提现申请，请等待管理员审核！');
        }

        //判断用户是否绑定了支付宝
        $alipay = Db::name('user_alipay')->where(['uid' => $this->uid])->find();



        $this->assign([
            'alipay' => $alipay,
            'cashout_num' => $cashout_num
        ]);

        return view($this->template_path . "cashout.html");
    }

    //余额
    public function balance(){
        $user = db::name('user')->where(['id' => $this->uid])->find();

        $hot_buy = db::name('goods')->order('sales desc')->limit(10)->select();

        $hot_buy = $this->handle_goods($hot_buy);

        $this->assign([
            'user' => $user,
            'hot_buy' => $hot_buy
        ]);
        return view($this->template_path . "balance.html");
    }

}
