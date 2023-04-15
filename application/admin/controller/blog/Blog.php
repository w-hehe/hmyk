<?php

namespace app\admin\controller\blog;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Blog extends Backend {

	/**
	 * Blog模型对象
	 * @var \app\admin\model\blog\Blog
	 */
	protected $model = null;

	public function _initialize() {
		parent::_initialize();
		$this->model = new \app\admin\model\blog\Blog;
		$this->category_model = new \app\admin\model\blog\Category;
		$tree = Tree::instance();
		$tree->init(collection($this->category_model->order('weigh desc,id desc')->select())->toArray(), 'pid');
		$categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
		$categorydata = [0 => ['id' => null, 'name' => __('请选择文章类目')]];
		foreach ($categorylist as $k => $v) {
			$categorydata[$v['id']] = $v;
		}

		$this->assign([
			'category' => $categorydata, //分类
		]);
	}

	//

	public function add() {
		if (false === $this->request->isPost()) {
			return $this->view->fetch();
		}
		$params = $this->request->post('row/a');
		if (empty($params)) {
			$this->error(__('Parameter %s can not be empty', ''));
		}
		$params = $this->preExcludeFields($params);

		if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
			$params[$this->dataLimitField] = $this->auth->id;
		}


		$result = false;
		Db::startTrans();
		try {
			//是否采用模型验证
			if ($this->modelValidate) {
				$name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
				$validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
				$this->model->validateFailException()->validate($validate);
			}
			$result = $this->model->allowField(true)->save($params);
			Db::commit();
		} catch (ValidateException|PDOException|Exception $e) {
			Db::rollback();
			$this->error($e->getMessage());
		}
		if ($result === false) {
			$this->error(__('No rows were inserted'));
		}
		$this->success();
	}


	public function edit($ids = null) {
		$row = $this->model->get($ids);
		if (!$row) {
			$this->error(__('No Results were found'));
		}
		$adminIds = $this->getDataLimitAdminIds();
		if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
			$this->error(__('You have no permission'));
		}
		if (false === $this->request->isPost()) {
			$this->view->assign('row', $row);
			return $this->view->fetch();
		}
		$params = $this->request->post('row/a');
		if (empty($params)) {
			$this->error(__('Parameter %s can not be empty', ''));
		}
		$params = $this->preExcludeFields($params);
		$result = false;
		Db::startTrans();
		try {
			//是否采用模型验证
			if ($this->modelValidate) {
				$name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
				$validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
				$row->validateFailException()->validate($validate);
			}
			$result = $row->allowField(true)->save($params);
			Db::commit();
		} catch (ValidateException|PDOException|Exception $e) {
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
