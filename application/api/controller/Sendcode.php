<?php

namespace app\api\controller;

use app\common\library\Email;
use think\Db;
use think\Session;
use think\Validate;

/**
 * 发送验证码
 */
class Sendcode extends Base {



    /**
     */
    public function email() {
        $post = $this->request->param();
        if(empty($post['email'])){
            $this->error('邮箱不能为空！');
        }
        $type = getAccountType($post['email']);
        if($type != 'email'){
            $this->error('请填写正确的邮箱');
        }
        $code = mt_rand(100000, 999999);

        $title = "验证码";

        $timestamp = time();
        $riqi = date('Y年m月d日', $timestamp);
        $shijian = date('H:i', $timestamp);


        $content = <<<content
<table cellpadding="0" cellspacing="0" style="border:1px solid #cdcdcd;width:640px;margin:auto;font-size:16px;color:#1E2731;">
    <tr style="height:60px;"> 
        <td colspan="3" align="center" style="background-color:#454c6d;height:60px; "> 
            <a target="_blank" style="color: #fff; text-decoration: none;">请查收您的注册验证码</a> 
        </td> 
    </tr> 
    <tr> 
        <td width="20"> </td> 
        <td> 
            <p> 尊敬的用户： </p> 
            <p> 您好！您于北京时间
                <span style="border-bottom:1px dashed #ccc;" t="5">{$riqi}</span>
                {$shijian}申请绑定邮箱。 
            </p> 
            <p> 以下是您的验证码。 </p> 
            <p> 验证码：<span style="color: red;">{$code}</span></p> 
            <p> 红盟云官网：<a href="https://www.hmyblog.com/" rel="noopener" target="_blank">https://www.hmyblog.com/</a> </p>   
            <p> 在使用红盟云商城的过程中，如遇到任何问题，欢迎随时联系我们的工作人员： </p>  
            <p> 官方微信号：wwquanya </p> 
            <p> 官方QQ群：
                <span style="border-bottom:1px dashed #ccc;z-index:1" t="7" onclick="return false;" data="810434865">810434865</span>
            </p>
        </td> 
        <td width="20"> </td> 
    </tr> 
</table>
content;
        session::set('emailcode_' . $post['type'], ['email' => $post['email'], 'code' => $code]);
        $this->success('验证码已发送' . $code);die;
        $email = new Email;
        $result = $email->to($post['email'])->subject($title)->message($content)->send();
        if ($result) {
            session::set('emailcode_' . $post['type'], ['email' => $post['email'], 'code' => $code]);

            $this->success('验证码已发送');
        } else {
            $this->error($email->getError());
        }

    }
}
