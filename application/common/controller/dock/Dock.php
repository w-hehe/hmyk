<?php

namespace app\common\controller\dock;


use think\Cache;
use think\Db;
use think\Session;
use app\common\controller\dock\Kky;
use app\common\controller\dock\Yile;
use app\common\controller\dock\Space;

/**
 * 对接商品类
 */
class Dock {


    static public function getSiteInfo($site_id){
        $result = db::name('docking_site')->where(['id' => $site_id])->find();
        $site_info = json_decode($result['info'], true);
        unset($result['info']);
        $result = array_merge($result, $site_info);
        return $result;
    }




    /**
     * 通过对接站信息获取商品列表
     */
    static public function get_goods_list($site){

        $list = [];

        if ($site['type'] == 'space'){
            $list = Space::get_goods_list($site);
        }
        if ($site['type'] == 'jiuwu'){
            $list = Jiuwu::get_goods_list($site);
        }
        if($site['type'] == 'yile'){
            $list = Yile::get_goods_list($site);
        }

        if($site['type'] == 'kky'){
            $list = Kky::get_goods_list($site);
        }


        if($list == 'connect fail') self::error('对接站点连接失败');
        if($list == 'login fail') self::error('用户帐号或密码验证失败');
        if(empty($list)) self::error('未获取到任何商品');
        return $list;
    }

    static public function error($msg){
        echo json_encode(['code' => 1, 'msg' => $msg]);die;
    }


    //处理五九社区的商品信息列表
    static public function handle_list_wujiu($list){
        foreach($list as &$val) {
            $price = $val['goods_unitprice'];
            if($price == 0) continue;
            $price_info = self::calc_price($price, 1, $val['minbuynum_0']);
            $val['num'] = $price_info['num'];
            $val['price'] = upDecimal($price_info['price']);
            $look_num = self::look_num($val['num']);
            $val['look_price'] = $look_num . $val['unit'] . '=' . $val['price'] . '元';
        }

        return $list;
    }






}
