<?php

namespace app\shop\controller;

use app\common\controller\Fun;
use think\Db;

class Module extends Base {


    public function index() {
        $func = $this->request->param('func');
        $func();
    }

}
