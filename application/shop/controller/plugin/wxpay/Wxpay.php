<?php

namespace app\shop\controller\plugin\wxpay;

use think\Controller;
use think\Cache;


/**
 * 微信jsapi支付
 */
class Wxpay extends Controller {


    public function isPay(){
        $out_trade_no = $this->request->param('out_trade_no');
        $this->assign([
            'out_trade_no' => $out_trade_no
        ]);
        return view();
    }



    public function index(){
        $code = $this->request->param('code');
        $state = $this->request->param('state');
        $plugin_path = ROOT_PATH . "content/plugin/wxpay_pay/";
        $info = file_get_contents("{$plugin_path}wxpay_pay_setting.json");
        $info = json_decode($info, true);

        $appid = $info['appid'];
        $secret = $info['secret'];

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";



        $result = json_decode(hmCurl($url), true);

        // echo '<pre>'; print_r($result);die;
        if(!empty($result['errcode']) && $result['errcode'] == 40125) die('Secret配置错误');

        $openid = $result['openid'];


        $pluginPath = ROOT_PATH . 'content/plugin/wxpay_pay/wxpay_pay.php';

//        echo $pluginPath;die;

        $data = Cache::get($state);

        $order = $data['order'];
        $goods = $data['goods'];
        $pay_type = $data['pay_type'];

        require_once $pluginPath;


        $host = getHostDomain();

        $data = [
            'pay_type' => $pay_type,
            'openid' => $openid,
            'notify_url' => $host . '/shop/notify/index/pay_plugin/wxpay',
            'return_url' => $host . '/api/v1/pisces/callback_return/pay_plugin/wxpay',
            'quit_url' => $host . "/#/goods/goods_id={$goods['id']}",
        ];
        pay($order, $goods, $data);



    }






}
