<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 投诉
 *
 * @icon fa fa-circle-o
 */
class Complain extends Backend
{

    /**
     * Complain模型对象
     * @var \app\admin\model\Complain
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Complain;

    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 投诉开关控制
     */
    public function _switch(){
        $update = [
            'option_content' => $this->options['complain'] == 1 ? 0 : 1,
        ];
        $result = db::name('options')->where(['option_name' => 'complain'])->update($update);

        if($update['option_content'] == 1){
            return json(['code' => 200, 'msg' => '已开启', 'data' => 1]);
        }else{
            return json(['code' => 200, 'msg' => '已关闭', 'data' => 0]);
        }

    }


}
