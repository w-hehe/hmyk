<?php

namespace app\user\controller;

use app\shop\controller\Auth;
use think\Db;
use think\Session;

class Index extends Auth {

    /**
     * 重置密钥
     */
    public function resetSecret(){
        $secret = md5($this->timestamp . $this->user['id'] . mt_rand(1000,9999) . $this->user['password']);
        db::name('user')->where(['id' => $this->user['id']])->update(['secret' => $secret]);
        header('location: ' . url('/dock_info'));die;
    }

    /**
     * 对接信息
     */
    public function dockInfo(){
        $error = 0;

        if(empty($this->user['secret']) || $this->user['secret'] == 0) $this->resetSecret();

        $this->assign([
            'error' => $error,
            'navi' => 'dock_info'
        ]);

        return view();
    }

    /**
     * 个人资料
     */
    public function info(){
        $error = 0;

        if($this->request->isPost()) {

            $post = $this->request->param();

            $where = [
                'email' => $post['email'],
                'id' => ['neq', $this->user['id']]
            ];
            $user = db::name('user')->where($where)->find();
            if ($user) {
                $error = 1;
            } else {
                $update = [
                    'nickname' => $post['nickname'], 'email' => $post['email'], 'updatetime' => time(),
                ];
                if (!empty($post['password'])) {
                    $update['password'] = $this->getEncryptPassword($post['password'], $this->user['salt']);
                }
                db::name('user')->where(['id' => $this->user['id']])->update($update);
                $field = "u.id, u.consume, u.nickname, u.password, u.salt, u.email, u.mobile, u.avatar, u.agent, u.money,";
                $field .= "u.score, u.createtime, g.name grade_name, g.discount";
                $user = db::name('user')->alias('u')
                    ->join('user_grade g', 'u.agent=g.id', 'left')
                    ->field($field)
                    ->where(['u.id' => $this->user['id']])->find();
                session::set('user', $user);
                $error = 'ok';
            }
        }

        $this->assign([
            'error' => $error,
            'navi' => 'userInfo',
        ]);
        return view();
    }

    //个人中心
    public function index(){
        $goods = db::name('goods')->order('id desc')->limit(3)->select();
        $this->assign([
            'navi' => 'user',
            'goods' => $goods,
        ]);
        return view();
    }

}
