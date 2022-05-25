<?php

namespace app\shop\controller;

use app\common\controller\Fun;
use app\common\controller\Hm;
use think\Db;
use think\Session;

class Page extends Base {

    public function neice(){
        return view();
    }

    public function index() {
        $page_html = $this->request->param('page');
//        var_dump($page_html);die;
        $this->assign([
            'page' => 'index',
//            'category_id' => $category_id,
//            'search_result' => $search_result,
//            'keyword' => $keyword,
            'navi' => $page_html
        ]);
        return view($this->template_path . $page_html . ".html");
    }

}
