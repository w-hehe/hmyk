<?php

namespace notice\smtp2;

use app\common\library\Email as Mail;
use think\Db;

class Smtp2 {

    public function getConfig(){
        $path = ROOT_PATH . 'extend/notice/smtp2/setting.php';
        return include $path;
    }

    public function nOrderUs($data){
        $_config = $this->getConfig();
        $email = new Mail($_config);
        $path = ROOT_PATH . 'application/extra/notice/n_order_us.tpl';
        $html = file_get_contents($path);

        $html = str_replace('{$host}', getHostDomain(), $html);
        $html = str_replace('{$out_trade_no}', $data['out_trade_no'], $html);
        $html = str_replace('{$goods_name}', $data['goods_name'], $html);
        $html = str_replace('{$buy_num}', $data['buy_num'], $html);
        $html = str_replace('{$goods_price}', $data['goods_price'], $html);
        $html = str_replace('{$order_money}', $data['order_money'], $html);
        if(empty($data['cdk'])){
            $html = str_replace('{$cdk}', '未发卡', $html);
        }else{
            $cdk = "";
            foreach($data['cdk'] as $val) $cdk .= "<p>{$val}</p>";
            $html = str_replace('{$cdk}', $cdk, $html);
        }

        $html = str_replace('{$create_time}', date('Y-m-d H:i:s', $data['create_time']), $html);
        if(empty($data['pay_type'])){
            $html = str_replace('{$pay_type}', '免费商品', $html);
        }else{
            $html = str_replace('{$pay_type}', $data['pay_type'], $html);
        }

        if(empty($data['pay_time'])){
            $html = str_replace('{$pay_time}', '免费商品', $html);
        }else{
            $html = str_replace('{$pay_time}', date('Y-m-d H:i:s', $data['pay_time']), $html);
        }


        try{
            $email->to($data['email'])->subject("客户订单通知")->message($html)->send();
        }catch (\Exception $e){}
    }

    public function nOrderAd($data){
        $_config = $this->getConfig();
        $email = new Mail($_config);
        $admin = Db::name('admin')->where(['id' => 1])->find();
        $path = ROOT_PATH . 'application/extra/notice/n_order_ad.tpl';
        $html = file_get_contents($path);

        $html = str_replace('{$host}', getHostDomain(), $html);
        $html = str_replace('{$out_trade_no}', $data['out_trade_no'], $html);
        $html = str_replace('{$goods_name}', $data['goods_name'], $html);
        $html = str_replace('{$buy_num}', $data['buy_num'], $html);
        $html = str_replace('{$goods_price}', $data['goods_price'], $html);
        $html = str_replace('{$order_money}', $data['order_money'], $html);
        if(empty($data['cdk'])){
            $html = str_replace('{$cdk}', '未发卡', $html);
        }else{
            $cdk = "";
            foreach($data['cdk'] as $val) $cdk .= "<p>{$val}</p>";
            $html = str_replace('{$cdk}', $cdk, $html);
        }

        $html = str_replace('{$create_time}', date('Y-m-d H:i:s', $data['create_time']), $html);
        if(empty($data['pay_type'])){
            $html = str_replace('{$pay_type}', '免费商品', $html);
        }else{
            $html = str_replace('{$pay_type}', $data['pay_type'], $html);
        }

        if(empty($data['pay_time'])){
            $html = str_replace('{$pay_time}', '免费商品', $html);
        }else{
            $html = str_replace('{$pay_time}', date('Y-m-d H:i:s', $data['pay_time']), $html);
        }


        try{
            $email->to($admin['email'])->subject("新订单通知")->message($html)->send();
        }catch (\Exception $e){}
    }

    public function nComplainAd($data){
        $_config = $this->getConfig();
        $email = new Mail($_config);
        $admin = Db::name('admin')->where(['id' => 1])->find();
        $path = ROOT_PATH . 'application/extra/notice/n_complain_ad.tpl';
        $html = file_get_contents($path);

        $html = str_replace('{$host}', getHostDomain(), $html);
        $html = str_replace('{$name}', $data['name'], $html);
        $html = str_replace('{$qq}', $data['qq'], $html);
        $html = str_replace('{$out_trade_no}', $data['out_trade_no'], $html);
        $html = str_replace('{$details}', $data['details'], $html);
        $html = str_replace('{$date}', date('Y-m-d H:i:s', time()), $html);
        try{
            $email->to($admin['email'])->subject("待处理投诉")->message($html)->send();
        }catch (\Exception $e){}
    }

}
