<?php

namespace app\common\controller;

use app\common\library\Email as Mail;
use think\Validate;
use think\Db;

/**
 * 邮件发送
 */
class Email{

    /**
     * 发送给站长
    */
    static public function sendOrderBoss($goods, $order_id, $site){


        if($site['admin_order_email'] != 1){
            return;
        }

        if(empty($site['mail_smtp_host']) || empty($site['mail_smtp_pass']) || empty($site['mail_smtp_user']) || empty($site['mail_from'])){
            return false;
        }

        $order = db::name('order')->where(['id' => $order_id])->find();
        // $admin = db::name('')

        // $receiver = $order['account'];

        $receiver = db::name('admin')->where(['id' => 1])->value('email');

        if (!Validate::is($receiver, "email")) {
            return false;
        }


        $style = self::getStyle();

        $pay_time = date('Y-m-d H:i:s', time());

        $kami_result = db::name('sold')->where(['order_id' => $order['id']])->select();

        $kami = "";

        foreach($kami_result as $val){
            if($kami == ""){
                $kami .= $val['content'];
            }else{
                $kami .= "<br>" . $val['content'];
            }
        }

        $params = "";

        $order_attach = json_decode($order['attach'], true);
        $order_inputs = json_decode($order['inputs'], true);

        $goods_inputs = json_decode($goods['inputs'], true);
        if($order_attach){
            foreach($order_attach as $key => $val){
                $params .= $key . "：" . $val . "<br>";
            }
        }


        // var_dump($goods_inputs);die;
        if($goods_inputs){
            foreach($goods_inputs as $key => $val){
                foreach($order_inputs as $k =>$v){
                    if($val['name'] == "inputs[$k]"){
                        $params .= $val['title'] . "：" . $v . "<br>";
                    }
                }
            }
        }


        $html = <<<html
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    {$style}
</head>
<body>
<div id="" class="layui-layer-content">
    <table class="table table-condensed table-hover layui-table" id="orderItem">
        <tbody>
            <tr>
                <td colspan="6" style="text-align:center" class="orderTitle"><b>订单基本信息</b></td>
            </tr>
            
            <tr>
                <td class="info orderTitle">订单编号</td>
                <td colspan="5" class="orderContent">{$order['order_no']}</td>
            </tr>
            
            <tr>
                <td class="info orderTitle">商品名称</td>
                <td colspan="5" class="orderContent">{$goods['name']}</td>
            </tr>
            <tr>
                <td class="info orderTitle">订单金额</td>
                <td colspan="5" class="orderContent">{$order['money']}元</td>
            </tr>
            <tr>
                <td class="info orderTitle">购买时间</td>
                <td colspan="5">{$pay_time}</td>
            </tr>
html;

            if(!empty($params)){
                $html .= <<<html
                <tr> 
                    <td class="info orderTitle">下单信息</td>
                    <td colspan="5" class="orderContent">{$params}</td>
                </tr>
html;
            }




            $html .= <<<html
            <tr>
                <td class="info orderTitle">订单状态</td>
                <td colspan="5" class="orderContent">
                    <span class="layui-badge layui-bg-green">交易完成</span>
                </td>
            </tr>
html;

            if(!empty($kami)){
                $html .= <<<html
                <tr>
                    <td colspan="6" style="text-align:center" class="orderTitle"><b>以下是用户购买的卡密信息</b></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center" class="orderContent">{$kami}</td>
                </tr>

html;
            }

            $html .= <<<html
                                    </tbody>
                    </table>
                </div>
                
                </body>
                </html>
html;



        // echo $html;die;

        $email = new Mail($site);
        $result = $email
            ->to($receiver)
            ->subject($site['shop_pet_name'] . " - 新订单通知")
            ->message($html)
            ->send();


        if ($result) {

        } else {

        }
    }

    /**
     * 发送给用户
    */
    static public function sendOrderUser($goods, $order_id, $site){

        if(empty($site['mail_smtp_host']) || empty($site['mail_smtp_pass']) || empty($site['mail_smtp_user']) || empty($site['mail_from'])){
            return false;
        }

        $order = db::name('order')->where(['id' => $order_id])->find();

        $receiver = $order['email'];



        if (!Validate::is($receiver, "email")) return false;

        $style = self::getStyle();

        $pay_time = date('Y-m-d H:i:s', time());

        $kami_result = db::name('sold')->where(['order_id' => $order['id']])->select();

        $kami = "";

        foreach($kami_result as $val){
            if($kami == ""){
                $kami .= $val['content'];
            }else{
                $kami .= "<br>" . $val['content'];
            }
        }

        $params = "";

        $order_attach = json_decode($order['attach'], true);
        $order_inputs = json_decode($order['inputs'], true);

        $goods_inputs = json_decode($goods['inputs'], true);
        if($order_attach){
            foreach($order_attach as $key => $val){
                $params .= $key . "：" . $val . "<br>";
            }
        }

        if($goods_inputs){
            foreach($goods_inputs as $val){
                foreach($order_inputs as $k =>$v){
                    if($val['name'] == "inputs[$k]"){
                        $params .= $val['title'] . "：" . $v . "<br>";
                    }
                }
            }
        }


        $html = <<<html
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    {$style}
</head>
<body>
<div id="" class="layui-layer-content">
    <table class="table table-condensed table-hover layui-table" id="orderItem">
        <tbody>
            <tr>
                <td colspan="6" style="text-align:center" class="orderTitle"><b>订单基本信息</b></td>
            </tr>
            
            <tr>
                <td class="info orderTitle">订单编号</td>
                <td colspan="5" class="orderContent">{$order['order_no']}</td>
            </tr>
            
            <tr>
                <td class="info orderTitle">商品名称</td>
                <td colspan="5" class="orderContent">{$goods['name']}</td>
            </tr>
            <tr>
                <td class="info orderTitle">订单金额</td>
                <td colspan="5" class="orderContent">{$order['money']}元</td>
            </tr>
            <tr>
                <td class="info orderTitle">购买时间</td>
                <td colspan="5">{$pay_time}</td>
            </tr>
html;

            if(!empty($params)){
                $html .= <<<html
                <tr> 
                    <td class="info orderTitle">下单信息</td>
                    <td colspan="5" class="orderContent">{$params}</td>
                </tr>
html;
            }




            $html .= <<<html
            <tr>
                <td class="info orderTitle">订单状态</td>
                <td colspan="5" class="orderContent">
                    <span class="layui-badge layui-bg-green">交易完成</span>
                </td>
            </tr>
            
html;

            if(!empty($kami)){
                $html .= <<<html
                <tr>
                    <td colspan="6" style="text-align:center" class="orderTitle"><b>以下是您购买的卡密信息</b></td>
                </tr>
                <tr>
                    <td colspan="6" style="text-align:center" class="orderContent">{$kami}</td>
                </tr>

html;
            }

            $html .= <<<html
                                    </tbody>
                    </table>
                </div>
                
                </body>
                </html>
html;


        // echo $html;die;

        $email = new Mail($site);
        $result = $email
            ->to($receiver)
            ->subject($site['shop_pet_name'] . " - 商品购买通知")
            ->message($html)
            ->send();


        if ($result) {
        } else {
        }
    }


    /**
     *
     */
    static public function getStyle(){
        $style = <<<style
        <style>
        blockquote, body, button, dd, div, dl, dt, form, h1, h2, h3, h4, h5, h6, input, li, ol, p, pre, td, textarea, th, ul {
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: rgba(0,0,0,0);
        }
        body { background-color: #f8f8f8; font-size: 14px; }
        .layui-table { width: 100%; background-color: #fff; color: #666; }
        .layui-table, .layui-table-view { margin: 10px 0; }
        table { border-collapse: collapse; border-spacing: 0; }
        .layui-table tr { transition: all .3s; -webkit-transition: all .3s; }
        .layui-table td, .layui-table th { position: relative; padding: 9px 15px; min-height: 20px; line-height: 20px; font-size: 14px; }
        .layui-table td, .layui-table th, .layui-table-col-set, .layui-table-fixed-r, .layui-table-grid-down, .layui-table-header, .layui-table-page, .layui-table-tips-main, .layui-table-tool, .layui-table-total, .layui-table-view, .layui-table[lay-skin=line], .layui-table[lay-skin=row] {
            border-width: 1px;
            border-style: solid;
            border-color: #e6e6e6;
        }
    </style>
style;
        return $style;
    }





}
