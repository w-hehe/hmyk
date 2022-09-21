<?php


class HongmengDock{

    /**
     * 获取单个商品信息
     */
    public function goodsInfo($dock, $goods_id){
        $host = rtrim($dock['hongmeng_host'], '/');
        $gateway_url = $host . "/api/goods/info";
        $params = [
            "user_id" => $dock['hongmeng_user_id'], //用户id
            "secret_key" => $dock['hongmeng_secret'],
            'goods_id' => $goods_id, //商品id
        ];

        $result = hmCurl($gateway_url, http_build_query($params));
        //        var_dump($result);die;
        $result = json_decode($result, true);
        if(empty($result)){
            return ['code' => 400, 'msg' => '商品信息获取失败'];
        }
        return $result;
    }

    /**
     * 查询订单信息
     */
    public function orderInfo($order, $dock){
        $host = rtrim($dock['hongmeng_host'], '/');
        $gateway_url = $host . "/api/order/info";
        if(empty($order['remote_order_no'])){
            return ['code' => 400, 'msg' => '上游下单失败'];
        }
        $params = [
            "user_id" => $dock['hongmeng_user_id'], //用户id
            "secret_key" => $dock['hongmeng_secret'],
            'out_trade_no' => $order['remote_order_no'],
        ];

        $result = hmCurl($gateway_url, http_build_query($params));
        //        var_dump($result);die;
        $result = json_decode($result, true);
        //        echo '<pre>'; print_r($result);die;
        if(empty($result)){
            return ['code' => 400, 'msg' => '订单信息获取失败'];
        }
        if($result['code'] == 400){
            return $result;
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'order' => $result['data']
            ]
        ];
    }


    /**
     * 下单
     */
    public function placeOrder($goods, $order, $dockInfo){
        $host = rtrim($dockInfo['hongmeng_host'], '/');
        $gateway_url = $host . "/api/goods/buy";
        $params = [
            "user_id" => $dockInfo['hongmeng_user_id'], //用户id
            "secret_key" => $dockInfo['hongmeng_secret'],
            'goods_id' => $goods['remote_id'], //商品id
            'buy_num' => $goods['buy_default'] * $order['buy_num'], //购买数量
        ];
        if(!empty($order['inputs']) && !empty(json_decode($order['inputs'], true))){
            $params['inputs'] = json_decode($order['inputs'], true);
        }
        $result = hmCurl($gateway_url, http_build_query($params));
        $result = json_decode($result, true);
        if($result['code'] == 200){ //下单成功
            $remote_order_no = $result['data']['out_trade_no']; //订单号
            $cdk = [];
            if(!empty($result['data']['cdk'])){ //有卡密
                foreach($result['data']['cdk'] as $val){
                    $cdk[] = $val;
                }
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'remote_order_no' => $remote_order_no,
                    'cdk' => $cdk,
                    'stock' => $result['data']['stock']
                ]
            ];
        }else{ //下单失败
            return ['code' => 400, 'msg' => $result['msg']];
        }

    }


    /**
     * 获取对接站点商品列表
     */
    public function goodsList($info, $params){
        $info = json_decode($info, true);
        $data = [
            'user_id' => $info['hongmeng_user_id'],
            'secret_key' => $info['hongmeng_secret'],
            'category_id' => $params['category']
        ];
        $host = rtrim($info['hongmeng_host'], '/');
        $gateway_url = $host . "/api/goods/goods_list";
        $result = hmCurl($gateway_url, http_build_query($data));
        $result = json_decode($result, true);
        return $result['data'];
    }

    /**
     * 后台选择商品页的默认数据
     */
    public function dockSelectGoodsData($info){
        $info = json_decode($info, true);
        $host = rtrim($info['hongmeng_host'], '/');
        $params = [
            'user_id' => $info['hongmeng_user_id'],
            'secret_key' => $info['hongmeng_secret']
        ];
        $gateway_url = $host . "/api/goods/category";
        $result = hmCurl($gateway_url, http_build_query($params));
        $result = json_decode($result, true);
        if(empty($result)) die('请求失败');
        if($result['code'] == 400){
            // echo '<pre>'; print_r($result);die;
            die($result['msg']);
        }
        $data = [
            'category' => $result['data']
        ];
        return $data;
    }

    /**
     * 添加或编辑对接网站时验证用户输入的数据并返回可入库的json
     */
    public function verify($data){
        if(empty($data['hongmeng_host'])) die(json_encode(['code' => 0, 'msg' => '域名不能为空']));
        if(empty($data['hongmeng_user_id'])) die(json_encode(['code' => 0, 'msg' => '商户ID不能为空']));
        if(empty($data['hongmeng_secret'])) die(json_encode(['code' => 0, 'msg' => '商户密钥不能为空']));
        $info = [
            'hongmeng_host' => $data['hongmeng_host'],
            'hongmeng_user_id' => $data['hongmeng_user_id'],
            'hongmeng_secret' => $data['hongmeng_secret']
        ];
        return json_encode($info);
    }

}
