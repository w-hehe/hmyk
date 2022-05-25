<?php

namespace app\shop\controller;
use think\Db;

class Category extends Base {


    public function index() {
/*        $where = [
            'type' => 'goods',
            'status' => 'normal',
        ];
        $category = db::name('category')->where($where)->order('weigh asc')->select();
        $this->assign([
            'category' => $category,
            'footer_active' => 'category',
        ]);*/

echo 2;die;
        
    }
    
    public function list(){
        return view($this->template_path . 'list.html');
    }

}
