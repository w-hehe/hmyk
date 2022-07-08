<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 */
class Indexs extends Api {
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {

        echo '{"code":1,"msg":"","time":1655450995,"data":{"site_name":"BuildAdmin","record_number":"渝ICP备8888888号-1"}}';die;
        $this->success('请求成功');
    }
}
