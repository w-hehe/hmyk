<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * 附加选项
 *
 * @icon fa fa-circle-o
 */
class Attach extends Backend {

    protected $searchFields = 'name';

    /**
     * Attach模型对象
     * @var \app\admin\model\Attach
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Attach;

    }

    public function import() {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 删除
     */
    public function del($ids = "") {
        if (!$this->request->isPost()) {
            $this->error("无效的参数");
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            // $pk = $this->model->getPk();
            // $adminIds = $this->getDataLimitAdminIds();
            // if (is_array($adminIds)) {
            //     $this->model->where($this->dataLimitField, 'in', $adminIds);
            // }
            $list = $this->model->where('id', 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $where = [
                        'attach_id' => $v->id,
                        'deletetime' => null
                    ];
                    $res = db::name('goods')->where($where)->find();
                    if($res){
                        throw new Exception("附加选项被占用，无法删除");
                    }
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error("删除参数");
    }

}
