<?php

namespace app\user\controller;

use app\shop\controller\Auth;
use think\Db;
use think\Session;

class Balance extends Auth {

    /**
     * 账户充值
     */
    public function recharge(){
        if($this->request->has('pay_type') && $this->request->has('money')){
            $pay_type = $this->request->param('pay_type');
            $money = $this->request->param('money');
            $money = $money <= 0 ? 0.01 : $money;



            $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
            $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

            //        var_dump($active_pay);die;

            $pay_plugins = [];
            foreach($active_pay as $key => $val){
                if(in_array($pay_type, $val['pay_type'])) $pay_plugins[] = $key;
            }

            $pay_plugin = $pay_plugins[array_rand($pay_plugins)]; //epay_pay


            $insert = [
                'user_id' => $this->user['id'],
                'out_trade_no' => date('YmdHis', $this->timestamp) . mt_rand(1000, 9999),
                'money' => $money,
                'create_time' => $this->timestamp,
                'pay_type' => $pay_type,
                'pay_plugin' => $pay_plugin
            ];
            $recharge_id = db::name('recharge')->insertGetId($insert);
            $pluginPath = ROOT_PATH . 'public/content/plugin/' . $pay_plugin . '/' . $pay_plugin . '.php';
            require_once $pluginPath;
            $order = [
                'order_no' => $insert['out_trade_no'],
                'money' => $insert['money'],
            ];
            $goods = [
                'name' => '会员充值'
            ];
            pay($order, $goods, $pay_type, 'recharge');
            die;
        }
        $where = [
            'option_name' => 'active_pay',
        ];
        $pay_list = [
            'alipay' => false,
            'wxpay' => false,
            'qqpay' => false,
        ];

        $active_pay = db::name('options')->where($where)->value('option_content');
        $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

        //    echo '<pre>'; print_r($active_pay);die;
        foreach($active_pay as $val){
            if(in_array('alipay', $val['pay_type'])) $pay_list['alipay'] = true;
            if(in_array('wxpay', $val['pay_type'])) $pay_list['wxpay'] = true;
            if(in_array('qqpay', $val['pay_type'])) $pay_list['qqpay'] = true;
        }
        $this->assign([
            'pay_list' => $pay_list
        ]);
        return view();
    }

    /**
     * 对接信息
     */
    public function dockInfo(){
        $error = 0;

        if(empty($this->user['secret']) || $this->user['secret'] == 0) $this->resetSecret();

        $this->assign([
            'error' => $error,
            'navi' => 'dock_info'
        ]);

        return view();
    }

    /**
     * 个人资料
     */
    public function info(){
        $error = 0;

        if($this->request->isPost()) {

            $post = $this->request->param();

            $where = [
                'email' => $post['email'],
                'id' => ['neq', $this->user['id']]
            ];
            $user = db::name('user')->where($where)->find();
            if ($user) {
                $error = 1;
            } else {
                $update = [
                    'nickname' => $post['nickname'], 'email' => $post['email'], 'updatetime' => time(),
                ];
                if (!empty($post['password'])) {
                    $update['password'] = $this->getEncryptPassword($post['password'], $this->user['salt']);
                }
                db::name('user')->where(['id' => $this->user['id']])->update($update);
                $field = "u.id, u.consume, u.nickname, u.password, u.salt, u.email, u.mobile, u.avatar, u.agent, u.money,";
                $field .= "u.score, u.createtime, g.name grade_name, g.discount";
                $user = db::name('user')->alias('u')
                    ->join('user_grade g', 'u.agent=g.id', 'left')
                    ->field($field)
                    ->where(['u.id' => $this->user['id']])->find();
                session::set('user', $user);
                $error = 'ok';
            }
        }

        $this->assign([
            'error' => $error,
            'navi' => 'userInfo',
        ]);
        return view();
    }

    //个人中心
    public function index(){
        $goods = db::name('goods')->order('id desc')->limit(3)->select();
        $this->assign([
            'navi' => 'user',
            'goods' => $goods,
        ]);
        return view();
    }

}
