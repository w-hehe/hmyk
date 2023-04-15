<?php

namespace addons\umeditor\controller;

use think\addons\Controller;
use think\Config;

/**
 * Umeditor
 *
 */
class Index extends Controller
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    public function get_map_config()
    {
        $config = get_addon_config('umeditor');
        $params = [
            'baidumapkey' => $config['baidumapkey'] ?? ''
        ];
        return json($params);
    }

}
