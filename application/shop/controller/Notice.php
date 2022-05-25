<?php

namespace app\shop\controller;
use app\common\controller\Fun;
use think\Db;

class Notice extends Auth {


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

        $is_notice = Db::name('notice')->where($where)->find();

        $this->assign([
            'footer_active' => 'notice',
            'is_notice' => $is_notice
        ]);
        return view($this->template_path . "notice.html");
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
