<?php

namespace app\shop\controller;
use app\common\controller\Fun;
use think\Db;

class Information extends Base {


    public function index() {
        $where = [
            'uid' => $this->uid,
        ];
        if($this->request->isAjax()){
            $post = $this->request->param();
//            sleep(12);
            $start = ($post['page'] - 1) * $post['pageSize'];
            $list = db::name('notice')->where($where)->order('status asc, id desc')->limit($start, $post['pageSize'])->select();
            foreach($list as &$val){
                $val['createtime'] = date('Y-m-d H:i', $val['createtime']);
                $val['class'] = $val['status'] == 0 ? 'weidu' : 'yidu';
                $val['status'] = $val['status'] == 0 ? '未读' : '已读';
            }
            $data = [
                'data' => $list,
                'info' => 'ok',
                'status' => 0
            ];
            return json($data);
        }



        $this->assign([
            'footer_active' => 'notice',
        ]);
        return view();
    }

    public function detail(){
        $id = $this->request->param('id');
        $where = [
            'id' => $id,
        ];
        $info = db::name('information')->where($where)->find();

        if(!$info){
            header("location: " . url("index/index"));
            die;
        }

        $info['createtime'] = date('Y-m-d H:i', $info['createtime']);

        db::name('information')->where($where)->setInc('views');

        $this->assign([
            'footer_active' => '',
            'info' => $info,
        ]);
        return view();
    }

}
