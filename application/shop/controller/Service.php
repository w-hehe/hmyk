<?php

namespace app\shop\controller;


class Service extends Base {


    public function index() {
        $this->assign([
            'footer_active' => 'service',
        ]);
        return view($this->template_path . "service.html");
    }

}
