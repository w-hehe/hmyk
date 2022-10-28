<?php
/*
Plugin Name: 微信支付
Version: 1.83
Plugin URL:
Description: 官方微信支付，支持native、jsapi和h5支付
Author: 云商学院
Author URL: https://blog.ysxue.net/
*/


use app\common\controller\Hm;
use think\Db;
use think\Cache;

!defined('ROOT_PATH') && exit('access deined!');


function pay($order, $goods, $params = []) {

    $plugin_path = ROOT_PATH . "content/plugin/wxpay_pay/";
    $info = file_get_contents("{$plugin_path}wxpay_pay_setting.json");
    $info = json_decode($info, true);

    $equipment = is_mobile() ? 'wap' : 'pc';

    if($equipment == 'pc'){
        $t = 'native';
    }else{ //手机访问
        if(isWeixin()){
            if(!empty($info['pay_type']['jsapi'])){
                $t = 'jsapi';
            }else{
                $t = 'native';
            }
        }else{
            if(empty($info['pay_type']['h5'])){
                if(empty($info['pay_type']['jsapi'])){
                    $t = 'native';
                }else{
                    $t = 'jsapi';
                }
            }else{
                $t = 'h5';
            }
        }
    }


    $host = getHostDomain();


    // echo $t;die;

    if($t == 'native'){ //扫码  你没有开通native
        $data = [
            'appid' => $info['appid'], // 应用id
            'mchid' => $info['mchid'], //商户号
            'description' => $goods['name'], //商品描述
            'out_trade_no' => $order['order_no'], //商户订单号
            'notify_url' => $host . "/shop/notify/index/pay_plugin/wxpay", //支付完成后的异步回调通知地址
            'amount' => [
                'total' => (int)($order['money'] * 100) //以分为单位
            ]
        ];





        // 商户相关配置
        $merchantId = $info['mchid']; // 商户号
        $serial_no = $info['serial_no']; // 商户API证书序列号
        $nonce_str = getNonce(); //随机字符串
        $timestamp = time(); //时间戳

        $native_gateway_url = "https://api.mch.weixin.qq.com/v3/pay/transactions/native"; //native

        $message = "POST\n". "/v3/pay/transactions/native\n". $timestamp."\n". $nonce_str."\n". json_encode($data) . "\n";
        openssl_sign($message, $raw_sign, $info['private_key'], 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048 ';
        $token = $schema . sprintf('mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"', $merchantId, $nonce_str, $sign, $timestamp, $serial_no);
        $header = [
            'Authorization: ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: https://zh.wikipedia.org/wiki/User_agent',
        ];
        $result = curlJson($native_gateway_url, $data, true, $header);
        $result = json_decode($result, true);
        if(empty($result['code_url'])){
            return [
                'code' => 400,
                'msg' => $result['message']
            ];
        }
        //写入支付二维码
        db::name('order')->where(['order_no' => $order['order_no']])->update(['qr_code' => $result['code_url']]);
        return [
            'code' => 200,
            'qr_code' => $result['code_url'],
            'mode' => 'local',
            'pay_type' => 'wxpay'
        ];
    }elseif($t == 'h5'){
        $data = [
            'appid' => $info['appid'], // 应用id
            'mchid' => $info['mchid'], //商户号
            'description' => $goods['name'], //商品描述
            'out_trade_no' => $order['order_no'], //商户订单号
            'notify_url' => $host . "/shop/notify/index/pay_plugin/wxpay", //支付完成后的异步回调通知地址
            'amount' => [
                'total' => (int)($order['money'] * 100) //以分为单位
            ],
            'scene_info' => [ //场景信息
                              'payer_client_ip' => getClientIp(), //用户终端IP
                              'h5_info' => [
                                  'type' => 'Wap'
                              ]
            ],
        ];
        // 商户相关配置
        $merchantId = $info['mchid']; // 商户号
        $serial_no = $info['serial_no']; // 商户API证书序列号
        $nonce_str = getNonce(); //随机字符串
        $timestamp = time(); //时间戳

        $message = "POST\n". "/v3/pay/transactions/h5\n". $timestamp."\n". $nonce_str."\n". json_encode($data) . "\n";
        openssl_sign($message, $raw_sign, $info['private_key'], 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048 ';
        $token = $schema . sprintf('mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"', $merchantId, $nonce_str, $sign, $timestamp, $serial_no);

        $header = [
            'Authorization: ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: https://zh.wikipedia.org/wiki/User_agent',
        ];
        $gateway_url = "https://api.mch.weixin.qq.com/v3/pay/transactions/h5";

        $result = curlJson($gateway_url, $data, true, $header);

        $result = json_decode($result, true);
        // print_r($result);die;
        if(empty($result['h5_url'])){
            return [
                'code' => 400,
                'msg' => '支付配置有误，请联系网站管理员'
            ];
        }else{

            return [
                'code' => 200,
                'gateway_url' => $result['h5_url'] . '&redirect_url=' . urlencode($host . '/shop/plugin/wxpay/wxpay/isPay/out_trade_no/' . $order['order_no']),
                'mode' => 'jump'
            ];

        }

    }elseif($t == 'jsapi'){
        if(empty($params['openid'])){
            $state = 'wxpay_jsapi_' . $order['order_no'];
            $cache = [
                'order' => $order,
                'goods' => $goods,
                'pay_type' => $order['pay_type']
            ];
            Cache::set($state, $cache);
            $redirect_uri = urlencode($host . "/shop/plugin/wxpay/wxpay/index");
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$info['appid']}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
            if(isWeixin()){
                return [
                    'code' => 200,
                    'gateway_url' => $url,
                    'mode' => 'jump'
                ];
                header('location: ' . $url); die;
            }else{
                return [
                    'code' => 200,
                    'gateway_url' => $url,
                    'mode' => 'jump'
                ];
            }
        }
        $data = [
            'appid' => $info['appid'], // 应用id
            'mchid' => $info['mchid'], //商户号
            'description' => $goods['name'], //商品描述
            'out_trade_no' => $order['order_no'], //商户订单号
            'notify_url' => $params['notify_url'], //支付完成后的异步回调通知地址
            'amount' => [
                'total' => (int)($order['money'] * 100) //以分为单位
            ],
            'payer' => [
                'openid' => $params['openid']
            ]
        ];

        // 商户相关配置
        $merchantId = $info['mchid']; // 商户号
        $serial_no = $info['serial_no']; // 商户API证书序列号
        $nonce_str = getNonce(); //随机字符串
        $timestamp = time(); //时间戳

        $jsapi_gateway_url = "https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi"; //jsapi

        $message = "POST\n". "/v3/pay/transactions/jsapi\n". $timestamp."\n". $nonce_str."\n". json_encode($data) . "\n";

        openssl_sign($message, $raw_sign, $info['private_key'], 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $schema = 'WECHATPAY2-SHA256-RSA2048 ';
        $token = $schema . sprintf('mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"', $merchantId, $nonce_str, $sign, $timestamp, $serial_no);
        $header = [
            'Authorization: ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: https://zh.wikipedia.org/wiki/User_agent',
        ];

        $result = curlJson($jsapi_gateway_url, $data, true, $header);
        $result = json_decode($result, true);

        $prepay_id = $result['prepay_id'];


        $data = [
            'appid' => $info['appid'],
            'timeStamp' => time(),
            'nonceStr' => getNonce(),
            'package' => $prepay_id,
            'signType' => 'RSA',
        ];


        $message = "{$data['appid']}\n". "{$data['timeStamp']}\n". $data['nonceStr'] . "\n". "prepay_id={$prepay_id}" . "\n";

        openssl_sign($message, $raw_sign, $info['private_key'], 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $data['paySign'] = $sign;

        $html = <<<html
        <script>
        function onBridgeReady() {
            WeixinJSBridge.invoke('getBrandWCPayRequest', {
                "appId": "{$data['appid']}",     //公众号ID，由商户传入     
                "timeStamp": "{$data['timeStamp']}",     //时间戳，自1970年以来的秒数     
                "nonceStr": "{$data['nonceStr']}",      //随机串     
                "package": "prepay_id={$prepay_id}",
                "signType": "RSA",     //微信签名方式：     
                "paySign": "{$data['paySign']}" //微信签名 
            },
            function(res) {
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                    location.href="/#/order/detail?out_trade_no={$order['order_no']}"
                }
            });
        }
        if (typeof WeixinJSBridge == "undefined") {
            if (document.addEventListener) {
                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
            } else if (document.attachEvent) {
                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
            }
        } else {
            onBridgeReady();
        }
        </script>
        
html;

        echo $html;

    }



}


function isWeixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

function checkSign($params = null){

    $content = Hm::getParams('input');

    $post = json_decode($content, true);

    if($post['event_type'] == 'TRANSACTION.SUCCESS') {
        $plugin_path = ROOT_PATH . "content/plugin/wxpay_pay/";
        $info = file_get_contents("{$plugin_path}wxpay_pay_setting.json");
        $info = json_decode($info, true);
        $result = decryptToString($post['resource']['associated_data'], $post['resource']['nonce'], $post['resource']['ciphertext'], $info);
        $result = json_decode($result, true);
        $order_no = $result['out_trade_no'];
        return $order_no;
    }
}

/**
 * 生成随机字符串
 */
function getNonce(){
    static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 32; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}




function loadCertificate($info){
    return $info['cert'];
}






/**
 * Decrypt AEAD_AES_256_GCM ciphertext
 *
 * @param string $associatedData AES GCM additional authentication data
 * @param string $nonceStr AES GCM nonce
 * @param string $ciphertext AES GCM cipher text
 *
 * @return string|bool      Decrypted string on success or FALSE on failure
 */
function decryptToString($associatedData, $nonceStr, $ciphertext, $info) {
    // phpinfo();die;

    $ciphertext = base64_decode($ciphertext);


    // ext-sodium (default installed on >= PHP 7.2)
    if (function_exists('sodium_crypto_aead_aes256gcm_is_available') && sodium_crypto_aead_aes256gcm_is_available()) {
        return sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $info['aesKey']);
    }

    // ext-libsodium (need install libsodium-php 1.x via pecl)
    if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
        return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $info['aesKey']);
    }

    // openssl (PHP >= 7.1 support AEAD)
    if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
        $ctext = substr($ciphertext, 0, -16);
        $authTag = substr($ciphertext, -16);

        return \openssl_decrypt($ctext, 'aes-256-gcm', $info['aesKey'], \OPENSSL_RAW_DATA, $nonceStr, $authTag, $associatedData);
    }

    echo 'AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php';
}
