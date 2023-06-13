<?php

namespace app\index\controller;

use app\common\controller\IndexCommon;
use hehe\Verify;
use think\Db;


class Custom extends IndexCommon {

    protected $layout = 'default';

    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];



    public function _initialize() {

        parent::_initialize();

        if(!$this->request->isPjax()){
        }

    }


    public function index() {

        $action = $this->request->param('action');

        return view($this->template . $action);
    }







}
