<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Config;

/**
 * 示例接口
 */
class Base extends Controller {

    public $get = [];
    public $post = [];
    public $user = [];
    public $timestamp = "";
    public $site = [];

    public function _initialize() {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->get = $this->request->get();
        $this->post = $this->request->post();
        $this->timestamp = time();
        $this->site = Config::get("site");
        header("Content-type:application/json;charset=utf-8");
        if(!$this->request->has('user_id')) die(json_encode(['code' => 400, 'msg' => '参数user_id不能为空']));
        if(!$this->request->has('secret_key')) die(json_encode(['code' => 400, 'msg' => '参数secret_key不能为空']));

        $where = [
            'u.id' => $this->request->param('user_id')
        ];

        $field = "u.id, u.secret, u.consume, u.nickname, u.password, u.salt, u.email, u.mobile, u.avatar, u.agent, u.money,";
        $field .= "u.score, u.createtime, g.name grade_name, g.discount";
        $this->user = db::name('user')->alias('u')
            ->join('user_grade g', 'u.agent=g.id', 'left')
            ->field($field)->where($where)->find();


        if(!$this->user) die(json_encode(['code' => 400, 'msg' => '用户查找失败！']));



        if($this->user['secret'] != $this->request->param('secret_key')) die(json_encode(['code' => 400, 'msg' => '密钥错误']));

    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '') {
        return md5(md5($password) . $salt);
    }


    //生成订单号
    public function generateOrderNo(){
        $order_no = date('YmdHis', time()) . mt_rand(1000, 9999);
        return $order_no;
    }




}
