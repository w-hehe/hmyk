<?php

namespace app\shop\controller;
use app\common\controller\Fun;
use think\Db;

class Feedback extends Auth {


    public function index() {
        return view();
    }

    public function detail(){
        $id = $this->request->param('id');
        $where = [
            'id' => $id,
            'uid' => $this->uid,
        ];
        $info = db::name('notice')->where($where)->find();

        if(!$info){
            header("location: " . url("index/index"));
            die;
        }
        Db::name('notice')->where(['id' => $id])->update(['status' => 1]);
        $info['createtime'] = date('Y-m-d H:i', $info['createtime']);

        $notice_result = Db::name('notice')->where(['status' => 0, 'uid' => $this->uid])->find();
        $notice_hd = $notice_result ? true : false;

        $this->assign([
            'footer_active' => 'notice',
            'info' => $info,
            'notice_hd' => $notice_hd
        ]);
        return view();
    }

}
