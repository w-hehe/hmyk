<?php

namespace app\common\controller\dock;


use think\Cache;
use think\Db;
use think\Session;
// use app\common\controller\dock\Dock;
/**
 *
 */
class Yile{

    static public $gateway = ".api.yilesup.net/";

    /**
     * 获取商品列表
     */
    static public function get_goods_list($site){

        $data = [
            'api_token' => $site['account'],
            'timestamp' => time(),
        ];
        $data['sign'] = Yile::getSign($data, $site['password']);
        $domain = rtrim($site['domain'], '/');
//        $gateway_url = $domain . ".api.94sq.cn/api/goods/list";
        $gateway_url = $domain . self::$gateway . "api/goods/list";

        $result = hmCurl($gateway_url, $data, true);

        $result = json_decode($result, true);

//        print_r($result);die;

        if($result['status'] != 0){
            echo json_encode(['code' => 1, 'msg' => $result['message']]);die;
        }

        $list = $result['data'];
        return $list;
    }

    /**
     * 获取订单详情
    */
    static public function getOrderDetail($order, $siteInfo){
        $data = [
            'api_token' => $siteInfo['account'],
            'timestamp' => time(),
            'id' => $order['remote_order_no'],
        ];
        $data['sign'] = self::getSign($data, $siteInfo['password']);
//        echo '<pre>'; print_r($data);die;
        $domain = rtrim($siteInfo['domain'], '/');

        $gateway_url = $domain . self::$gateway . "api/order/query";

        $result = hmCurl($gateway_url, http_build_query($data), true);


        $result = json_decode($result, true);

//        echo '<pre>';print_r($result);die;

        if($result['status'] != 0){
            die('订单详情获取失败');
        }

        $result = $result['data'];

        $data = [];

        switch($result['status']){
            case 0:
                $data['status'] = '待处理';
            break;
            case 1:
                $data['status'] = '处理中';
            break;
            case 2:
                $data['status'] = '退单中';
            break;
            case 3:
                $data['status'] = '有异常';
            break;
            case 4:
                $data['status'] = '补单中';
            break;
            case 5:
                $data['status'] = '已更新';
            break;
            case 90:
                $data['status'] = '已完成';
            break;
            case 91:
                $data['status'] = '已退单';
            break;
            case 92:
                $data['status'] = '已退款';
            break;

        }

        return $data;


        echo '<pre>'; print_r($result);die;
    }


    /**
     * 获取单个商品信息
     * 对接站点商品id
     * 对接站点配置id
    */
    static public function getGoodsInfo($goods_id, $dockSiteInfo){

        $data = [
            'api_token' => $dockSiteInfo['account'],
            'timestamp' => time(),
            'gid' => $goods_id,
        ];
        $data['sign'] = self::getSign($data, $dockSiteInfo['password']);

        $domain = rtrim($dockSiteInfo['domain'], '/');

        $gateway_url = $domain . self::$gateway . "api/goods/info";

        $result = hmCurl($gateway_url, http_build_query($data), true);

        $result = json_decode($result, true);

         if(empty($result['data'])){
             die($result['message']);
         }

        $inputs = $result['data']['inputs'];
        foreach($inputs as &$val){
            $val = [
                'title' => $val[0],
                'name' => $val[2],
                'placeholder' => $val[1]
            ];
        }

        $goods = [
            'name' => $result['data']['name'],
            'cover' => $result['data']['image'],
            'content' => $result['data']['desc'],
            'goods_url' => "{$dockSiteInfo['domain']}home/order/{$result['data']['gid']}",
            'inputs' => $inputs,
            'buy_min' => $result['data']['limit_min'],
            'buy_max' => $result['data']['limit_max'],
            'buy_price' => $result['data']['price'],
            'goods_type' => 'ds',
        ];

        return $goods;

    }


    /**
     * 获取商户信息
    */
    static public function getInfo($site_id){

        $site = Dock::getSiteInfo($site_id);

        $url = $site["domain"] . "/dockapi/index/userinfo.html";
        $params = [
            "userid" => $site['account'], //用户id
        ];

        $params["sign"] = self::getSign($params, $site["password"]);


        $result = json_decode(hmCurl($url, http_build_query($params), true), true);

        if($result['code'] == 1){
            $data = $result['data'];
        }
        return $data;

    }

    /**
     * 下单
     */
    static public function placeOrder($goods, $order, $siteInfo){
        $siteInfo['domain'] = rtrim($siteInfo['domain'], '/');
        $url = $siteInfo['domain'] . self::$gateway . "api/order";

        $data = [
            'api_token' => $siteInfo['account'],
            'timestamp' => time(),
            'gid' => $goods['remote_id'],
            'num' => $goods['buy_default'] * $order['goods_num'],
        ];


        $inputs = json_decode($order['inputs'], true);
        $i = 1;
        foreach($inputs as $key => $val){
            $data['value' . $i] = $val;
            $i++;
        }
        $data['sign'] = self::getSign($data, $siteInfo['password']);
//        echo '<pre>'; print_r($data);
        $result = json_decode(hmCurl($url, $data, true), true);
//        echo $url;die;
//        echo '<pre>'; print_r($result);die;
        if(empty($result)){
            $update = [
                'dock_status' => 'fail',
                'dock_explain' => '接口请求失败',
            ];
            db::name('order')->where(['id' => $order['id']])->update($update);
        }else{

            if($result['status'] != 0){
                $update = [
                    'dock_status' => 'fail',
                    'dock_explain' => $result['message'],
                ];
                db::name('order')->where(['id' => $order['id']])->update($update);
            }else{
                $update = [
                    'status' => 'success',
                    'dock_status' => 'success',
                    'remote_order_no' => $result['id']
                ];
                db::name('order')->where(['id' => $order['id']])->update($update);
            }
        }
    }


    /**
     * 生成签名
    */
    static public function getSign($param, $key){
        $signPars = "";
        ksort($param);
        foreach ($param as $k => $v) {
            if ("sign" != $k && "" !== $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = trim($signPars, '&');
        $signPars .= $key;
        $sign = md5($signPars);
        return $sign;
    }





}
