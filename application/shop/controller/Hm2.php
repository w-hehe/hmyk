<?php

namespace app\shop\controller;


use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

/**
 * 公共方法类
 */
class Hm{

    protected static $appid_azf = 10842;

    protected static $secret_key_azf = 'd565e95e935e324441f730d46207e914';

    protected static $api_azf = 'http://azf.5bma.cn/';



    /**
     * 获取订单列表
     */
    static public function orderList($params = []) {

        $params = empty($params) ? input() : $params;
        $offset = $params['offset'];
        $limit = $params['limit'];
        $user = Hm::getUser();

        $where = [
            'uid' => $user['id'],
        ];

        $list = db::name('order')->where($where)->order('id desc')->limit($offset, $limit)->select();

        $timestamp = time();

        foreach($list as &$val){

            if($val['status'] == -1){
                $val['s'] = '订单已失效';
            }elseif($val['pay'] == 0 && $timestamp - $val['createtime'] >= 600){
                $val['s'] = '订单已失效';
                db::name('order')->where(['id' => $val['id']])->update(['status' => -1]);
                $val['status'] = -1;
            }elseif($val['pay'] == 0){
                $val['s'] = '待付款';
            }elseif($val['pay'] == 1 && $val['status'] == 1){
                $val['s'] = '待发货';
            }elseif($val['pay'] == 1 && $val['status'] == 2){
                $val['s'] = '待收货';
            }elseif($val['pay'] == 1 && $val['status'] == 9){
                $val['s'] = '交易完成';
                $val['s_color'] = '#52c41a';
            }else{
                $val['s'] = '订单状态错误';
                $val['s_color'] = '#d20707';
            }
            $val['timestamp'] = date('Y-m-d H:i:s', $val['createtime']);
        }
//        return $list;
        return json_encode(['data' => $list, 'info' => 'ok', 'status' => 0]);

    }





    static public function pre($arr){
        echo '<pre>'; print_r($arr);die;
    }

    static public function getGoodsInfo($goods_id){
        $goods = db::name('goods')->where(['id' => $goods_id])->find();

        if(!$goods){
            return null;
        }

        if($goods['type'] == 'own'){
            $goods['images'] = explode(',', $goods['images']);
            $goods['cover'] = $goods['images'][0];
        }else if($goods['type'] == 'azf'){
            $goods_azf_all = self::getGoodsAzfAll();
            $azf_ids = array_column($goods_azf_all, 'id');

            $key = array_search($goods['remote_id'], $azf_ids);
            if($key !== false){
                $azf_goods_info = $goods_azf_all[$key];
//            self::pre($azf_goods_info);
                $goods['azf_price'] = $azf_goods_info['goodsprice'];
                $goods['name'] = $azf_goods_info['goodsname'];
                $goods['cover'] = $azf_goods_info['imgurl'];
                $goods['sales'] = $azf_goods_info['salesvolume'];
                $goods['details'] = $azf_goods_info['details'];
                $goods['stock'] = $azf_goods_info['stock'];
            }

        }
        return $goods;
    }


    //获取指定的爱转发商品信息
    static public function getGoodsAzfInfo($goods_azf_all = null, $id){

        $ids = array_column($goods_azf_all, 'id');
        $key = array_search($id, $ids);
        $goods_azf_info = $goods_azf_all[$key];
        return $goods_azf_info;
    }

    //获取所有爱转发商品信息
    static public function getGoodsAzfAll(){
        if(Cache::has('goods_azf')){
            $goods_azf = Cache::get('goods_azf');
        }else{
            $data = [
                'userid' => self::$appid_azf,
            ];
            $data['sign'] = self::getSign($data);

//            $result = Http::get(self::$api_azf . 'dockapi/index/getallgoods.html', $data);
            $result = [];
            if(!$result){
                return [];
            }
            $result = json_decode($result, true);
            if($result['code'] == -1){
                return [];
            }
            $list = $result['data'];

            $goods_azf = [];
            foreach($list as $val){
                foreach($val['goods'] as &$v){
                    $v['category_name'] = $val['groupname'];
                    $v['goodsprice'] /= 100;
                    $v['goodsprice'] = number_format($v['goodsprice'], 2);
                    $goods_azf[] = $v;
                }
            }
            Cache::set('goods_azf', $goods_azf, 3600);
        }
        return $goods_azf;
    }


    static private function getSign($data){
        ksort($data);
        $signtext='';
        foreach ($data AS $key => $val) { //遍历POST参数
            if ($val == '' || $key == 'sign') continue; //跳过这些不签名
            if ($signtext) $signtext .= '&'; //第一个字符串签名不加& 其他加&连接起来参数
            $signtext .= "$key=$val"; //拼接为url参数形式
        }
        $newsign=md5($signtext . self::$secret_key_azf);
        return $newsign;
    }

}
