<?php
/*
Plugin Name: 易支付
Version: 1.1
Plugin URL:
Description: 支持市面上大多数易支付
Author: 云商学院
Author URL: https://www.ysxue.cc/
*/


use app\common\controller\Hm;

!defined('ROOT_PATH') && exit('access deined!');


function pay($order, $goods, $pay_type) {
    $plugin_path = ROOT_PATH . "public/content/plugin/epay_pay/";

    $info = file_get_contents("{$plugin_path}epay_pay_setting.json");
    $info = json_decode($info, true);

    if(isset($_SERVER['REQUEST_SCHEME'])){
        $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
    }else{
        $host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
    }

    $data = [
        "pid"         => $info['appid'],//商户ID
        "type"       => $pay_type,//支付方式
        "out_trade_no"     => $order['order_no'], //商户订单号
        "notify_url" => $host . 'notify/get/notify/' . $order['order_no'],//异步通知地址
        "return_url" => $host . 'notify/get/return/' . $order['order_no'],//同步通知地址
        "name" => $goods['name'], //商品名称
        "money"      => $order['money'],//订单金额
    ];


    $data['sign'] = getSign($data, $info['secret_key']);
    $data['sign_type'] = 'MD5';
    $gateway_url = $info['gateway_url'] . 'submit.php';
    Hm::submitForm($gateway_url, $data);

}

/**
 * 验签
 */
function checkSign($data){
    $plugin_path = ROOT_PATH . "public/content/plugin/epay_pay/";
    $info = file_get_contents("{$plugin_path}epay_pay_setting.json");
    $info = json_decode($info, true);
    $sign = $data['sign'];
    $server_sign = getSign($data, $info['secret_key']);
    if($server_sign == $sign){
        return true;
    }
    return false;
}


/**
 * 生成签名结果
 * return 签名结果字符串
 */
function getSign($data, $secret_key) {
    foreach($data as $key => $val){
        if($key == "sign" || $key == "sign_type"){
            unset($data[$key]);
        }
    }
    ksort($data);
    reset($data);
    //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    $prestr  = "";
    foreach ($data as $key=>$val) {
        $prestr.=$key."=".$val."&";
    }
    //去掉最后一个&字符
    $prestr = substr($prestr, 0, -1);

    $sign = md5($prestr . $secret_key);

    return $sign;
}
