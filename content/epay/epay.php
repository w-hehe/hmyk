<?php



function pay($param, $info = null) {
    if($info == null){
        $plugin_path = ROOT_PATH . "content/epay/";
        $info = include_once "{$plugin_path}setting.php";
    }

    $host = \hehe\Network::getHostDomain();
    
    
    $data = [
        "pid"         => $info['appid'],//商户ID
        "type"       => $param['pay_type'],//支付方式
        "out_trade_no"     => $param['out_trade_no'], //商户订单号
        "notify_url" => "{$host}/index/notify/index/plugin/epay/hm_type/{$param['hm_type']}",//异步通知地址
        "return_url" => "{$host}/index/notify/ret/plugin/epay/hm_type/{$param['hm_type']}",//同步通知地址
        "name" => $param['subject'], //商品名称
        "money"      => sprintf('%.2f', $param['money']),//订单金额
        
    ];


    $data['sign'] = getSign($data, $info['secret_key']);
    $data['sign_type'] = 'MD5';
    $gateway_url = rtrim($info['gateway_url'], '/') . '/submit.php';
    $data['gateway_url'] = $gateway_url;
    return [
        'code' => 200,
        'data' => base64_encode(json_encode($data)),
        'mode' => 'form'
    ];

}

/**
 * 验签
 */
function checkSign($params = null){
    $plugin_path = ROOT_PATH . "content/epay/";
    $info = include_once "{$plugin_path}setting.php";

    $sign = $params['sign'];
    $server_sign = getSign($params, $info['secret_key']);
    if($server_sign == $sign) {
        return [
            'out_trade_no' => $params['out_trade_no'],
            'trade_no' => $params['trade_no']
        ];
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


    // print_r($data);die;

    foreach ($data as $key=>$val) {
        $prestr.=$key."=".$val."&";
    }
    //去掉最后一个&字符
    $prestr = substr($prestr, 0, -1);


    $sign = md5($prestr . $secret_key);

    return $sign;
}
