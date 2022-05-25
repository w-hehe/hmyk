<?php

namespace app\shop\controller;

use think\Db;
use think\Session;

class Login extends Base {

    public function _initialize() {
        parent::_initialize();
        if(session::has('user')){
            header('location: ' . url('/'));
            die;
        }
    }

    public function index() {
        if($this->site['login'] == 0){
            if($this->site['tourist_buy'] < 1){
                return $this->errorPage("站长已关闭游客购买和系统登录功能!", "当前站点已设置未登录用户无法购买任何商品！如有其他问题，请联系网站客服人员", "");
            }else{
                $this->error('网站登录功能已关闭！');
            }

        }
        if($this->request->isAjax()){
            // sleep(3);
            $post = $this->request->param();
            $account_type = getAccountType($post['account']);
            $account_type = $account_type == 'username' ? 'mobile' : $account_type;

            $field = "u.id, u.consume, u.nickname, u.password, u.salt, u.email, u.mobile, u.avatar, u.agent, u.money,";
            $field .= "u.score, u.createtime, g.name grade_name, g.discount";
            $user = db::name('user')->alias('u')
                ->join('user_grade g', 'u.agent=g.id', 'left')
                ->field($field)
                ->where([$account_type => $post['account']])->find();
            if(!$user) $this->error('帐号不存在');

            $password = $this->getEncryptPassword($post['password'], $user['salt']);
            if($password != $user['password']) $this->error('密码错误');
            session::set('user', $user);
            $this->success('登录成功');
        }


        return view(ROOT_PATH . 'public/content/template/common/login.html');

        // return view($this->template_path . 'login.html');
    }

}
