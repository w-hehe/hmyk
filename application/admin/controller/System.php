<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 测试管理
 *
 * @icon fa fa-circle-o
 */
class System extends Backend {

    /**
     * System模型对象
     * @var \app\admin\model\System
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\System;

    }


    public function index() {

        if (false === $this->request->isPost()) {
            $res = db::name('options')->select();
            $options = [];
            foreach($res as $val){
                $options[$val['name']] = $val['value'];
            }
            $options['buy_input'] = unserialize($options['buy_input']);
            $this->assign([
                'options' => $options
            ]);
            return $this->view->fetch();
        }
        $params = $this->request->post();
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        if(isset($params['row'])) unset($params['row']);

        $params['buy_input'] = array_filter($params['buy_input']);
        $params['buy_input'] = serialize($params['buy_input']);

//        print_r($params);die;

        $result = false;
        Db::startTrans();
        try {
            foreach($params as $key => $val){
                $result = db::name('options')->where(['name' => $key])->update(['value' => $val]);
            }
            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
