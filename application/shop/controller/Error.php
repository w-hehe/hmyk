<?php

namespace app\shop\controller;


class Error extends Base {


    public function index() {
        $post = $this->request->param();
        $this->assign([
            'post' => $post
        ]);
        return view();
    }

}
