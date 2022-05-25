<?php

namespace app\shop\controller;

use app\common\controller\Hm;
use think\Db;
use think\Request;

class Search extends Base {



    public function index() {

        $user = Hm::getUser();

        $model = new \app\admin\model\Goods;

        $keyword = $this->request->param('keyword');

        $where = [
            'shelf' => 0,
            'deletetime' => ['exp', Db::raw('is null')],
            'name' => ['like', "%{$keyword}%"],
        ];

        $result = $model->with(['cdkey'])->where($where)->order('id desc')->paginate(10, false, [
            'query' => Request::instance()->param(),//不丢失已存在的url参数
        ]);



        $goods = $result->items();
//        echo '<pre>'; print_r($goods);die;

        foreach($goods as $key => $val){
            $goods[$key] = Hm::handle_goods($val, $user['agent'], $this->options);
        }

        $data = [
            'goods' => $goods,
            'page' => $result->render()
        ];



//        echo 2;die;
//        return $goods;

        $this->assign([
            'page' => 'index',
            'result' => $data,
            'keyword' => $keyword,
        ]);
        return view($this->template_path . "search.html");
    }

}
