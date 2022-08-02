<?php

namespace app\shop\controller;

use app\common\controller\Fun;
use app\common\controller\Hm;
use think\Config;
use think\Db;
use think\Session;

class Index extends Base {
    public function index() {
        $this->site = Config::get("site");
        echo $this->site['statistics'];
        return view($this->template_path . "index.html");
    }
}
