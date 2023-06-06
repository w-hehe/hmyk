<?php

function pay($param, $info = null) {
//    print_r($param);die;
    if($info == null){
        $plugin_path = ROOT_PATH . "content/alipay/";
        $info = include_once "{$plugin_path}setting.php";
    }
    
    // print_r($info);die;

    $equipment = is_mobile() ? 'wap' : 'pc';

    $host = \hehe\Network::getHostDomain();

    $data = [
        'app_id' => trim($info['app_id']), //应用id
        'format' => 'JSON', //返回数据类型
        'charset' => 'UTF-8',
        'sign_type' => 'RSA2', //加密方式
        'timestamp' => date('Y-m-d H:i:s', time()), //发送请求的时间
        'version' => '1.0', //api版本
        'notify_url' => "{$host}/index/notify/index/plugin/alipay/hm_type/{$param['hm_type']}", //支付完成后的异步回调通知
    ];
    
//$param['money'] = 0.01;

    $biz_content = [
        'subject' => $param['subject'], //商品名称
        'out_trade_no' => $param['out_trade_no'], //商户订单号
        'timeout_express' => '10m', //关闭订单时间
        'total_amount' => sprintf('%.2f', $param['money']), //订单金额，单位/元
    ];

    $sub_type = 'sub';

    // print_r($biz_content);die;
    if($equipment == 'wap' && in_array('wap', $info['pay_type']['alipay'])){
        $data['method'] = 'alipay.trade.wap.pay'; //接口名称 - 手机网站支付
        $biz_content['product_code'] = 'QUICK_WAP_WAY'; //销售产品码， 商家和支付宝签约的产品码
        $data['return_url'] = $host . "/index/notify/ret/plugin/alipay/hm_type/{$param['hm_type']}"; //付款完成后跳转的地址
        $biz_content['goods_type'] = 0; //商品主类型 0虚拟 1实物
        $biz_content['quit_url'] = $host;
    }elseif($equipment == 'pc' && in_array('pc', $info['pay_type']['alipay'])){
        $data['method'] = 'alipay.trade.page.pay'; //接口名称 - pc网站支付
        $biz_content['product_code'] = 'FAST_INSTANT_TRADE_PAY'; //销售产品码， 商家和支付宝签约的产品码
        $data['return_url'] = $host . "/index/notify/ret/plugin/alipay/hm_type/{$param['hm_type']}"; //付款完成后跳转的地址
        $biz_content['goods_type'] = 0; //商品主类型 0虚拟 1实物
        $biz_content['quit_url'] = $host;
    }elseif($equipment == 'pc' && in_array('sm', $info['pay_type']['alipay'])){
        $data['method'] = 'alipay.trade.precreate'; //接口名称  - 当面付
        $sub_type = 'sm';
    }elseif($equipment == 'wap' && in_array('sm', $info['pay_type']['alipay']) && !in_array('wap', $info['pay_type']['alipay'])){
        $data['method'] = 'alipay.trade.precreate'; //接口名称  - 当面付
        $sub_type = 'sm';
    }else{
        return [
            'code' => 400,
            'msg' => '官方支付宝支付插件未开启合适的支付方式！请联系管理员处理'
        ];
    }

    $data['biz_content'] = json_encode($biz_content); //请求参数的集合
    $data['sign'] = getAlipaySign($data, ['private_key' => trim($info['private_key'])]);

    if(empty($data['sign'])) return ['code' => 400, 'msg' => '支付配置错误，签名生成失败。'];

    $gateway_url = "https://openapi.alipay.com/gateway.do"; //支付宝支付网关

    if($sub_type == 'sub'){
        // 发起wap支付或pc支付
        $data['gateway_url'] = $gateway_url;
        return [
            'code' => 200,
            'data' => base64_encode(json_encode($data)),
            'mode' => 'form'
        ];

    }else{ //当面付
//         print_r($data);die;
        $resultStr = hmCurl($gateway_url, http_build_query($data), true);
        $result = json_decode($resultStr, true);

        if(json_last_error() == 5){
            $resultStr = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($resultStr));
            $result = json_decode($resultStr, true);
        }
        if (empty($result)){
            return [
                'code' => 400,
                'msg' => '支付请求失败，请重试',
            ];

        }
        $result = $result['alipay_trade_precreate_response'];

//        print_r($result);die;

        if ($result['code'] == 10000){
            return [
                'code' => 200,
                'mode' => 'scan',
                'data' => base64_encode(json_encode([
                    'qr_code' => $result['qr_code'],
                    'out_trade_no' => $param['out_trade_no'],
                    'hm_type' => $param['hm_type'],
                    'pay_type' => 'alipay',
                ]))
            ];
        } else{
            if($result['code'] == 40003 && $result['sub_code'] == 'isv.app-unbind-partner'){
                return [
                    'code' => 400,
                    'msg' => '无效应用或应用未绑定商户',
                ];
            }
            // print_r($result);die;
            return [
                'code' => 400,
                'msg' => '错误的支付配置！'
            ];

        }
    }

}

/**
 * 支付宝签名
 */
function getAlipaySign($data, $alipay){
    ksort($data);
    $data_str = "";
    foreach($data as $key => $val) {
        if ($key != "sign"){
            $data_str .= $key . "=" . $val . "&";
        }
    }
    $data_str = rtrim($data_str, "&");
    $sign = "";
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(trim($alipay['private_key']), 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
    try {
        openssl_sign($data_str, $sign, $private_key, OPENSSL_ALGO_SHA256);
    } catch (\Exception $e) {
        return false;
        return [
            'code' => 400,
            'msg' => '支付配置错误: ' . $e->getMessage(),
        ];
    }

    $sign = base64_encode($sign);
    return $sign;
}





function checkSign($params = null) {
    $plugin_path = ROOT_PATH . "content/alipay/";
    $info = include_once "{$plugin_path}setting.php";

    
    $params = file_get_contents("php://input");;
    

    
    parse_str($params, $params);
    
    $sign = $params['sign'];
    

    if(!empty($params['trade_status']) && $params['trade_status'] != 'TRADE_SUCCESS') return false;

    if(!empty($params['mode_type'])) unset($params['mode_type']);
    if(!empty($params['sign'])) unset($params['sign']);
    if(!empty($params['sign_type'])) unset($params['sign_type']);
    if(!empty($params['pay_plugin'])) unset($params['pay_plugin']);
    
    
    ksort($params);
    


	$stringToBeSigned = "";
	$i = 0;
	foreach ($params as $k => $v) {
		if (false === checkEmpty($v) && "@" != substr($v, 0, 1)) {

			// 转换成目标字符集
// 			$v = mb_convert_encoding($v, 'gbk', 'utf-8');

			if ($i == 0) {
				$stringToBeSigned .= "$k" . "=" . "$v";
			} else {
				$stringToBeSigned .= "&" . "$k" . "=" . "$v";
			}
			$i++;
		}
	}

	unset ($k, $v);
    


    $pubKey = trim($info['public_key']);
    
    $public_key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    

    
    $result = (openssl_verify($stringToBeSigned, base64_decode($sign), $public_key, OPENSSL_ALGO_SHA256) === 1);
    

    
    if ($result) {
        return [
            'out_trade_no' => $params['out_trade_no'],
            'trade_no' => $params['trade_no']
        ];
    } else {
        return false;
    }
}

/**
 * 校验$value是否非空
 *  if not set ,return true;
 *    if is null , return true;
 **/
function checkEmpty($value) {
    if (!isset($value)) return true;
    if ($value === null) return true;
    if (trim($value) === "") return true;

    return false;
}
