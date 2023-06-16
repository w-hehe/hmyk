<?php

namespace app\index\controller;

use app\common\controller\IndexCommon;
use hehe\Verify;
use think\Db;


class Plugin extends IndexCommon {

    protected $layout = 'default';

    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];



    public function _initialize() {

        parent::_initialize();

        if(!$this->request->isPjax()){
        }

    }


    public function payPlugin() {


        $plugin_name = $this->request->param('plugin_name');

        $params = $this->request->param();

        include ROOT_PATH . "content/{$plugin_name}/{$plugin_name}.php";

        doAction($plugin_name . '_plugin', $params);


    }





}
