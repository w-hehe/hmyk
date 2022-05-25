<?php

namespace app\common\controller\dock;


use think\Cache;
use think\Db;
/**
 *
 */
class Space{


    /**
     * 获取订单详情
    */
    static public function getOrderDetail($order, $siteInfo){
        $siteInfo['domain'] = rtrim($siteInfo['domain'], '/');
        $url = $siteInfo['domain'] . '.skysq.top/api/user/order/query';

        $data = [
            'id' => $order['remote_order_no'],
        ];
//echo '<pre>'; print_r($data);die;
        $result = json_decode(hmCurl($url, $data, true), true);
//        echo '<pre>'; print_r($result);die;
        if(empty($result)){
            return ['status' => '订单状态获取失败'];
        }else{
            if($result['status'] != 0){
                return ['status' => $result['message']];
            }else{
                $data = [];
                $result = $result['data'][0];
                switch($result['status']){
                    case 0:
                        $data['status'] = '队列中';
                        break;
                    case 1:
                        $data['status'] = '未开始';
                        break;
                    case 2:
                        $data['status'] = '进行中';
                        break;
                    case 3:
                        $data['status'] = '已完成';
                        break;
                    case 4:
                        $data['status'] = '已退单';
                        break;
                    case 5:
                        $data['status'] = '退单中';
                        break;
                    case 6:
                        $data['status'] = '续费中';
                        break;
                    case 7:
                        $data['status'] = '补单中';
                        break;
                    case 8:
                        $data['status'] = '改密中';
                        break;
                    case 9:
                        $data['status'] = '登录失败';
                        break;

                }

                return $data;
            }


        }






    }

    /**
     * 下单
     */
    static public function placeOrder($goods, $order, $siteInfo){
        $siteInfo['domain'] = rtrim($siteInfo['domain'], '/');
        $url = $siteInfo['domain'] . '.skysq.top/api/user/order';

        $data = [
            'api_user' => $siteInfo['account'],
            'api_pwd' => $siteInfo['password'],
            'sid' => $goods['remote_id'],
            'num' => $goods['buy_default'] * $order['goods_num'],
        ];
        $inputs = json_decode($order['inputs'], true);
        foreach($inputs as $key => $val){
            $data[$key] = $val;
        }
//        echo '<pre>'; print_r($data);die;
        $result = json_decode(hmCurl($url, $data, true), true);
        if(empty($result)){
            $update = [
                'dock_status' => 'fail',
                'dock_explain' => '接口请求失败',
            ];
            db::name('order')->where(['id' => $order['id']])->update($update);
        }else{
//            echo '<pre>'; print_r($result);die;
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
                    'remote_order_no' => $result['message']
                ];
                db::name('order')->where(['id' => $order['id']])->update($update);
            }


        }

    }

    /**
     * 获取商品信息
    */
    static public function getGoodsInfo($goods_id, $dockSiteInfo){
//        echo '<pre>'; print_r($dockSiteInfo);die;
        $account = $dockSiteInfo['account'];
        $dockSiteInfo['domain'] = rtrim($dockSiteInfo['domain'], '/');
        $url = $dockSiteInfo['domain'] . '.skysq.top/api/user/goods/info';
        $data = [
            'api_user' => $account,
            'sid' => $goods_id
        ];
        $result = json_decode(hmCurl($url, $data, true), true);
//        echo '<pre>'; print_r($result);die;
        if(empty($result)){
            echo json_encode(['code' => 1, 'msg' => '接口请求失败']);die;
        }
        if($result['status'] == 1){
            echo json_encode(['code' => 1, 'msg' => $result['message']]);die;
        }
        $inputs = $result['data']['post'];
        foreach($inputs as &$val){
            $val = [
                'title' => $val['name'],
                'name' => $val['param'],
                'placeholder' => $val['name']
            ];
        }
//        echo '<pre>'; print_r($result);die;
        $goods = [
            'name' => $result['data']['name'],
            'cover' => $result['data']['image_url'],
            'content' => base64_decode($result['data']['content']),
            'goods_url' => "{$dockSiteInfo['domain']}home/order/{$result['data']['goodsid']}",
            'inputs' => $inputs,
            'buy_min' => $result['data']['minnum'],
            'buy_max' => $result['data']['maxnum'],
            'buy_price' => $result['data']['price'],
            'goods_type' => 'ds',
        ];
        return $goods;
    }

    static public function get_goods_list($site){
        $cache_name = "dock_goods_list_{$site['id']}";
        if (Cache::has($cache_name)){
            $list = Cache::get($cache_name);
        } else{
            $account = $site['account'];
            $site['domain'] = rtrim($site['domain'], '/');
            $url = $site['domain'] . '.skysq.top/api/user/goods/list';
//            echo $url;die;
            $data = [
                'api_user' => $account
            ];
            $result = json_decode(hmCurl($url, $data, true), true);
            if(empty($result)){
                echo json_encode(['code' => 1, 'msg' => '接口请求失败']);die;
            }
            if($result['status'] == 1){
                echo json_encode(['code' => 1, 'msg' => $result['message']]);die;
            }
            $list = $result['data'];
            Cache::set($cache_name, $list, 60);
        }
        return $list;
    }



    /**
     * 获取订单所需参数
     */
    static public function getParams($url, $site){

        return self::getParamsJiuwu($url, $site);

    }


    /**
     * 获取玖伍商品的参数
     * @param $url
     * @param $data
     * @return array
     */
    static public function getParamsJiuwu($url, $site){
        // $info = json_decode($site['info'], true);
        //开始模拟登录
        $login_url = "{$site['domain']}index.php?m=Home&c=User&a=login";
        $cookie = dirname(__FILE__) . '/jiuwu' . time() . '.txt';

        $post = "username={$site['account']}&username_password={$site['password']}";


        $curl = curl_init();//初始化curl模块
        curl_setopt($curl, CURLOPT_URL, $login_url);//登录提交的地址
        curl_setopt($curl, CURLOPT_HEADER, false);//不自动输出头信息
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//不自动输出数据
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie);//设置Cookie信息保存在指定的文件中
        curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);//要提交的信息
        curl_exec($curl);//执行cURL
        curl_close($curl);//关闭cURL资源，并且释放系统资源


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);//读取cookie
        $html = curl_exec($ch);//执行cURL抓取页面内容
        curl_close($ch);

        try {
            unlink($cookie);
        }catch (\Exception $e){

        }



        $html = preg_replace("/[\t\n\r]+/", "", $html);

        $html = preg_replace("/<!--[^\!\[]*?(?<!\/\/)-->/", "", $html); //过滤掉html注释

        // echo $html;die;


        $partern = '/<form role="form" method="post" class="order_post_form" action=".*?">(.*?)<\/form>/';

        preg_match_all($partern, $html, $result);

        $html = $result[1][0];

        $partern = '/<li>(.*?)<input type="hidden"/';

        preg_match_all($partern, $html, $result);

        $html = $result[1][0];

        $partern = '/<span class="fixed-width-right-80">(.*?)：<\/span>/';

        preg_match_all($partern, $html, $result);

        $params_title = $result[1];

        $partern = '/<input.*?name="(.*?)".*?>/';

        preg_match_all($partern, $html, $result);

        $params_name = $result[1];

        $partern = '/<input.*?placeholder="(.*?)".*?>/';

        preg_match_all($partern, $html, $result);


        $params_placeholder = $result[1];

        $order_params = [];

        foreach($params_name as $key => $val) {
            if ($val != 'need_num_0'){
                $order_params[] = [
                    'name' => "inputs[{$val}]",
                    'title' => $params_title[$key],
                    'placeholder' => $params_placeholder[$key]
                ];
            }
        }

        return $order_params;
    }


    /**
     * 计算点数价格
     */
    static public function calc_price($price, $num = 1, $min){
        $num *= 10;
        $price *= 10;

        if ($price < 0.1 || $num <= $min){
            return self::calc_price($price, $num, $min);
        }else{
            $num = $num / 10;
            $price = $price / 10;
        }
        return ['num' => $num, 'price' => $price];

    }


    static public function look_num($num){
        if ($num == 1000){
            return '1千';
        } else if ($num == 10000){
            return '1万';
        } else if ($num == 100000){
            return '10万';
        } else{
            return $num;
        }
    }


    /**
     * 下单
    */
    static public function buy($goods, $order){
        $site = db::name('docking_site')->where(['id' => $goods['site_id']])->find();
        $site_info = json_decode($site['info'], true);

        $dock_data = json_decode($goods['dock_data'], true);


        $url = $site["domain"] . "/dockapi/index/buy.html";
        $params = [
            "userid" => $site_info['account'], //用户id
            'goodsid' => $goods['remote_id'], //商品id
            'buynum' => $order['goods_num'], //购买数量
        ];

        $dock_data = json_decode($goods['dock_data'], true);
        $inputs = empty($dock_data['inputs']) ? [] : $dock_data['inputs'];
        if(!empty($inputs)){
            $attach = json_decode($order['attach'], true);
            foreach($attach as $val){
                $params["attach"][] = $val;
            }
            $params["attach"] = json_encode($params["attach"]);
        }

        $params["sign"] = self::getSign($params, $site_info["password"]);


        $result = json_decode(hmCurl($url, http_build_query($params), true), true);

        if($result['code'] == -1){ //在对接站点下单失败
            $update = [
                'dock_explain' => $result['msg'],
                'dock_status' => 'fail',
            ];
            db::name('order')->where(['id' => $order['id']])->update($update);
        }

        if($result['code'] == 1){
            $update = [
                'dock_status' => 'success'
            ];
            db::name('order')->where(['id' => $order['id']])->update($update);
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
