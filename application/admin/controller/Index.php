<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend {

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize() {

        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 后台首页
     */
    public function index() {
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login() {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->redirect($url);
        }

        $admin_error_login = Session::has('admin_error_login') ? Session::get('admin_error_login') : 0;

        $captcha = false;
        if($admin_error_login >= 3){
            $captcha = true;
        }

        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username' => 'require|length:3,30', 'password' => 'require|length:3,30', '__token__' => 'require|token',
            ];
            $data = [
                'username' => $username, 'password' => $password, '__token__' => $token,
            ];
            if ($captcha) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                session::set('admin_error_login', $admin_error_login + 1);
                $res = [
                    'token' => $this->request->token(),
                    'admin_error_login' => $admin_error_login + 1,
                ];
                $this->error($validate->getError(), $url, $res);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                session::delete('admin_error_login');
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                session::set('admin_error_login', $admin_error_login + 1);
                $res = [
                    'token' => $this->request->token(),
                    'admin_error_login' => $admin_error_login + 1,
                ];
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, $res);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $this->view->assign('title', __('Login'));
//        Hook::listen("admin_login_init", $this->request);



        $this->assign([
            'captcha' => $captcha
        ]);
        return $this->view->fetch();
    }

    /**
     * 退出登录
     */
    public function logout() {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->redirect("index/login");
    }

}
