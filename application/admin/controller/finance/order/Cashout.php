<?php

namespace app\admin\controller\finance\order;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * 提现记录
 *
 * @icon fa fa-circle-o
 */
class Cashout extends Backend
{

    /**
     * Cashout模型对象
     * @var \app\admin\model\finance\order\Cashout
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\order\Cashout;

    }

    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model->with(['user'])->where($where)->order('status asc, id desc')->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function unchuli(){
        $id = $this->request->param('ids');
        $res = db::name('cashout')->where(['id' => $id])->find();
        if(!$res) $this->error('记录未找到');
        if($res['status'] != 1) $this->error('状态错误');
        $update = [
            'status' => 0,
            'complete_time' => null
        ];
        db::name('cashout')->where(['id' => $id])->update($update);
        $this->success();
    }

    public function chuli(){
        $id = $this->request->param('ids');
        $res = db::name('cashout')->where(['id' => $id])->find();
        if(!$res) $this->error('记录未找到');
        if($res['status'] != 0) $this->error('状态错误');
        $update = [
            'status' => 1,
            'complete_time' => time()
        ];
        db::name('cashout')->where(['id' => $id])->update($update);
        $this->success();
    }


    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = $this->model->where($pk, 'in', $ids)->select();

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                if($item->status == 0) throw new Exception('无法删除未处理的提现记录');
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
