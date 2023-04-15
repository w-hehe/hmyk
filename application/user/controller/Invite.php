<?php

namespace app\user\controller;

use app\common\controller\Frontend;
use think\Cookie;


class Invite extends Frontend {


    protected $noNeedRight = '*';
    protected $noNeedLogin = ['index'];

    public function index(){
        $user_id = $this->request->param('u');
        if(empty($user_id)){
            $this->redirect(url('/'));
            die;
        }else{
            Cookie::set('invite_u', $user_id, 86400 * 30);
            $this->redirect(url('/'));
            die;
        }
    }

}
