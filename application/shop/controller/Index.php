<?php

namespace app\shop\controller;

use app\common\controller\Fun;
use app\common\controller\Hm;
use think\Db;
use think\Session;

class Index extends Base {

    public function neice(){
        return view();
    }

    public function index() {
        $category_id = $this->request->has('category_id') ? $this->request->param('category_id') : 0;
        $keyword = $this->request->has('keyword') ? $this->request->param('keyword') : '';
        if(empty($keyword)){
            $search_result = [];
        }else{
            $search_result = db::name('goods')->where('name', 'like', "%{$keyword}%")->select();
            foreach($search_result as &$val){
                $val = Hm::handle_goods($val, Hm::getUser()['agent']);
            }
        }
        $this->assign([
            'page' => 'index',
            'category_id' => $category_id,
            'search_result' => $search_result,
            'keyword' => $keyword,
            'navi' => 'home'
        ]);
        
        
        
        return view($this->template_path . "index.html");
    }

}
