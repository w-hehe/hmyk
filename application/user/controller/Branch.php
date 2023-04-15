<?php

namespace app\user\controller;

use app\common\controller\Frontend;

use think\Db;
class Branch extends Frontend {

    protected $layout = 'default';
    protected $noNeedRight = '*';

    public function _initialize() {
        parent::_initialize();
        if (!$this->request->isPjax()) {
            $this->view->engine->layout('default/layout/' . $this->layout);
        }
    }

    public function index(){

        $p1 = db::name('user')->where(['p1' => $this->user['id']])->order('id desc')->paginate(10);
        $p2 = db::name('user')->where(['p2' => $this->user['id']])->order('id desc')->paginate(10);
        $p3 = db::name('user')->where(['p3' => $this->user['id']])->order('id desc')->paginate(10);

        $this->assign([
            'p1' => $p1,
            'p2' => $p2,
            'p3' => $p3
        ]);

        return view('default/branch/index');
    }

}
