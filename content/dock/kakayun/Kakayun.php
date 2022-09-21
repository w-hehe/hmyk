<?php


class KakayunDock{

    /**
     * 获取单个商品信息
     */
    public function goodsInfo($dock, $goods_id){


        $host = rtrim($dock['kakayun_host'], '/');
        $url = $host . "/dockapi/v2/goodsdetails.html";
        $params = [
            "userid" => $dock['kakayun_id'], //用户id
            'goodsid' => $goods_id,
        ];
        $params["sign"] = $this->getSign($params, $dock["kakayun_key"]);
        $result = hmCurl($url, http_build_query($params), true);
        $result = json_decode($result, true);

        if(empty($result)){
            return ['code' => 400, 'msg' => '商品信息获取失败'];
        }
//        print_r($result);die;
        if($result['code'] == 1){
            return ['code' => 200, 'msg' => 'success', 'data' => $result['goodsdetails']];
        }else{
            return ['code' => 400, 'msg' => $result['msg']];
        }
    }

    /**
     * 查询订单信息
     */
    public function orderInfo($order, $dockInfo){
        $host = rtrim($dockInfo['kakayun_host'], '/');
        $url = $host . "/dockapi/index/queryorder.html";
        if(empty($order['remote_order_no'])){
            return ['code' => 400, 'msg' => '上游下单失败'];
        }
        $params = [
            "userid" => $dockInfo['kakayun_id'], //用户id
            'orderno' => $order['remote_order_no'],
        ];
        $params["sign"] = $this->getSign($params, $dockInfo["kakayun_key"]);
        $result = hmCurl($url, http_build_query($params), true);
        $result = json_decode($result, true);

//


        $status = '订单状态获取失败';
        if($result['code'] == 1){ //成功

            switch($result['data']['status']){
                case 0:
                    $result['data']['status'] = 'wait-send';
                    break;
                case 1:
                    $result['data']['status'] = 'wait-send';
                    break;
                case 3:
                    $result['data']['status'] = 'conduct';
                    break;
                case 5:
                    $result['data']['status'] = 'success';
                    break;
            }
        }
        if(!empty($result['cardlist'])){
            foreach($result['cardlist'] as $val){
                $result['data']['cdk'][] = $val;
            }
            $result['data']['status'] = 'success';
        }
//        echo '<pre>'; print_r($result);die;

        if($result['code'] != 1){
            return [
                'code' => 400,
                'msg' => $result['msg']
            ];
        }

        if($result['data']['status'] == 'success' && empty($result['cardlist'])){
            $result['data']['cdk'][] = '交易完成';
        }

//        echo '<pre>'; print_r($result);die;

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
        if(isset($_SERVER['REQUEST_SCHEME'])){
            $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
        }else{
            $domain = 'http://' . $_SERVER['HTTP_HOST'] . '/';
        }
        $host = rtrim($dockInfo['kakayun_host'], '/');
        $url = $host . "/dockapi/index/buy.html";
        $params = [
            "userid" => $dockInfo['kakayun_id'], //用户id
            'goodsid' => $goods['remote_id'], //商品id
            'buynum' => $goods['buy_default'] * $order['buy_num'], //购买数量
            'callbackurl' => $domain . 'shop/notify/dock_callback_order/dock_type/kakayun', //订单状态回调地址
        ];
        $attach = json_decode($order['inputs'], true);
        foreach($attach as $val) $params["attach"][] = $val;
        if(!empty($params['attach'])) $params['attach'] = json_encode($params['attach']);
        $params["sign"] = $this->getSign($params, $dockInfo["kakayun_key"]);
//        echo '<pre>'; print_r($params);die;
        $result = hmCurl($url, http_build_query($params), true);
        $result = json_decode($result, true);
        if($result['code'] == 1){ //下单成功
            $remote_order_no = $result['orderno']; //订单号
            $cdk = [];
            if(!empty($result['cardlist'])){ //有卡密
                foreach($result['cardlist'] as $val){
                    $cdk[] = $val;
                }
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'remote_order_no' => $remote_order_no,
                    'cdk' => $cdk
                ]
            ];
        }else{ //下单失败
            return ['code' => 400, 'msg' => $result['msg']];
        }
    }




    /**
     * 生成签名
     */
    public function getSign($param,$userkey){
        ksort($param); //排序post参数
        reset($param); //内部指针指向数组中的第一个元素
        $signtext='';
        foreach ($param AS $key => $val) { //遍历POST参数
            if ($val == '' || $key == 'sign') continue; //跳过这些不签名
            if ($signtext) $signtext .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
            $signtext .= "$key=$val"; //拼接为url参数形式
        }
        $newsign=md5($signtext.$userkey);
        return $newsign;
    }

    /**
     * 后台选择商品页的默认数据
     */
    public function dockSelectGoodsData($info){
        $info = json_decode($info, true);
        $param = [
            'userid' => $info['kakayun_id']
        ];

        $param['sign'] = $this->getSign($param, $info['kakayun_key']);
        $domain = rtrim($info['kakayun_host'], '/');
        $gateway_url = $domain . "/dockapi/v2/getallgoodsgroup";
        $result = hmCurl($gateway_url, http_build_query($param), true);
        $result = json_decode($result, true);
        $data = [
            'category' => $result['data']
        ];
        return $data;
        echo '<pre>'; print_r($result);die;
    }


    /**
     * 获取对接站点商品列表
     */
    public function goodsList($info, $params){
        $info = json_decode($info, true);
        $data = [
            'userid' => $info['kakayun_id'],
            'goodsgroupid' => $params['category']
        ];
        $data['sign'] = $this->getSign($data, $info['kakayun_key']);
        $domain = rtrim($info['kakayun_host'], '/');
        $gateway_url = $domain . "/dockapi/v2/getallgoods.html";
        $result = hmCurl($gateway_url, http_build_query($data), true);
        $result = json_decode($result, true);
//        print_r($result);die;
        return $result['data'];
    }


    /**
     * 添加或编辑对接网站时验证用户输入的数据并返回可入库的json
     */
    public function verify($data){

        if(empty($data['kakayun_host'])) die(json_encode(['code' => 0, 'msg' => '域名不能为空']));
        if(empty($data['kakayun_id'])) die(json_encode(['code' => 0, 'msg' => '商户id不能为空']));
        if(empty($data['kakayun_key'])) die(json_encode(['code' => 0, 'msg' => '商户key不能为空']));
        $info = [
            'kakayun_host' => $data['kakayun_host'],
            'kakayun_id' => $data['kakayun_id'],
            'kakayun_key' => $data['kakayun_key']
        ];
        return json_encode($info);
    }

}
