<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
/**
 * 余额账单
 *
 * @icon fa fa-circle-o
 */
class Cashout extends Backend
{
    
    /**
     * Cashout模型对象
     * @var \app\admin\model\Cashout
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Cashout([], config('database.prefix') . 'money_bill');

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


    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model->with('user')->where($where)->where(['type' => 'cashout'])->order($sort, $order)->paginate($limit);

            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }
        return $this->view->fetch();
    }

    public function status(){
        $post = $this->request->param();
        $where = [
            'id' => $post['id'],
        ];
        $update = [
            'handletime' => time(),
            'status' => $post['status'],
        ];
        db::name('money_bill')->where($where)->update($update);
        return json(['code' => 200, 'msg' => '操作成功']);
    }
}
