<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use think\Cache;
use think\Db;
use think\Exception;
use think\Validate;
use think\Session;

/**
 * 系统配置
 */
class Buy extends Backend {

    /**
     * @var \app\common\model\Config
     */
    protected $model = null;
    protected $noNeedRight = ['check', 'rulelist', 'selectpage', 'get_fields_list'];

    public function _initialize() {
        parent::_initialize();


    }

    /**
     * 查看
     */
    public function index() {
        if($this->request->isPost()){
            $params = $this->request->param();
            $row = $params['row'];
            $data = [
                'buy_name' => $row['buy_name'],
                'buy_data' => $row['buy_data'],
                'cdk_order' => $row['cdk_order']
            ];

            foreach($data as $key => $val){
                db::name('options')->where(['option_name' => $key])->update(['option_content' => $val]);
            }
            $this->success('已保存');
        }


        return $this->view->fetch();
    }


}
