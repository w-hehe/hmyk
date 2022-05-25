<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 优惠券
 *
 * @icon fa fa-circle-o
 */
class Coupon extends Backend {

    /**
     * Coupon模型对象
     * @var \app\admin\model\Coupon
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Coupon;

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
     * 查看
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

            $list = $this->model->where($where)->order($sort, $order)->paginate($limit)->toArray();
            $rows = $list['data'];

            foreach($rows as &$val){
                $val['expire_time'] = empty($val['expire_time']) ? '长期' : date('Y-m-d H:i:s', $val['expire_time']);
            }


            $result = ["total" => $list['total'], "rows" => $rows];

            return json($result);
        }


        return $this->view->fetch();
    }

    /**
     * 优惠券开关控制
     */
    public function pon(){
        $update = [
            'option_content' => $this->options['coupon'] == 1 ? 0 : 1,
        ];
        $result = db::name('options')->where(['option_name' => 'coupon'])->update($update);

        if($update['option_content'] == 1){
            return json(['code' => 200, 'msg' => '已开启', 'data' => 1]);
        }else{
            return json(['code' => 200, 'msg' => '已关闭', 'data' => 0]);
        }

    }


    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
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
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $params['category_ids'] = array_filter($params['category_ids']);
                    $params['goods_ids'] = array_filter($params['goods_ids']);

                    if(empty($params['value'])) $this->error('优惠码不能为空');
                    if(empty($params['discount'])) $this->error('折扣不能为空');
                    if($params['type'] == 0 && $params['discount'] > 100) $this->error('百分比折扣不能大于100');
                    if($params['type'] == 0 && $params['discount'] < 0) $this->error('百分比折扣不能小于0');
                    if($params['type'] == 1 && $params['discount'] < 0) $this->error('固定金额折扣不能小于0');
                    if($params['apply'] == 1 && empty($params['category_ids'])) $this->error('请选择适用分类');
                    if($params['apply'] == 2 && empty($params['goods_ids'])) $this->error('请选择适用商品');
                    if(!empty($params['expire_time'])) $params['expire_time'] = strtotime($params['expire_time']);
                    if(!empty($params['expire_time']) && $params['expire_time'] < $this->timestamp) $this->error('过期时间不能小于当前时间');

                    if($params['apply'] == 0){
                        unset($params['category_ids']);
                        unset($params['goods_ids']);
                    }

                    if($params['apply'] == 1){
                        $params['category_ids'] = implode(',', $params['category_ids']);
                        unset($params['goods_ids']);
                    }
                    if($params['apply'] == 2){
                        $params['goods_ids'] = implode(',', $params['goods_ids']);
                        unset($params['category_ids']);
                    }

                    $coupon = db::name('coupon')->where(['value' => $params['value']])->find();
                    if($coupon) $this->error('优惠码已存在');
                    $params['create_time'] = $this->timestamp;
                    $result = db::name('coupon')->insert($params);

//                    echo '<pre>'; print_r($params);die;
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }


        $category = db::name('category')->field('id, name')->order('id asc')->select();
        $goods = db::name('goods')->field('id, name')->whereNull('deletetime')->order('id asc')->select();

        $this->assign([
            'category' => $category,
            'goods' => $goods,
        ]);


        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $params['category_ids'] = array_filter($params['category_ids']);
                    $params['goods_ids'] = array_filter($params['goods_ids']);

                    if(empty($params['value'])) $this->error('优惠码不能为空');
                    if(empty($params['discount'])) $this->error('折扣不能为空');
                    if($params['type'] == 0 && $params['discount'] > 100) $this->error('百分比折扣不能大于100');
                    if($params['type'] == 0 && $params['discount'] < 0) $this->error('百分比折扣不能小于0');
                    if($params['type'] == 1 && $params['discount'] < 0) $this->error('固定金额折扣不能小于0');
                    if($params['apply'] == 1 && empty($params['category_ids'])) $this->error('请选择适用分类');
                    if($params['apply'] == 2 && empty($params['goods_ids'])) $this->error('请选择适用商品');
                    if(!empty($params['expire_time'])) $params['expire_time'] = strtotime($params['expire_time']);
                    if(!empty($params['expire_time']) && $params['expire_time'] < $this->timestamp) $this->error('过期时间不能小于当前时间');

                    if($params['apply'] == 0){
                        $params['goods_ids'] = null;
                        $params['category_ids'] = null;
                    }

                    if($params['apply'] == 1){
                        $params['category_ids'] = implode(',', $params['category_ids']);
                        $params['goods_ids'] = null;
                    }
                    if($params['apply'] == 2){
                        $params['goods_ids'] = implode(',', $params['goods_ids']);
                        $params['category_ids'] = null;
                    }
                    $where = [
                        'value' => $params['value'],
                        'id' => ['neq', $ids]
                    ];
                    $coupon = db::name('coupon')->where($where)->find();
                    if($coupon) $this->error('优惠码已存在');
                    $params['create_time'] = $this->timestamp;
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $category = db::name('category')->field('id, name')->order('id asc')->select();
        $goods = db::name('goods')->field('id, name')->whereNull('deletetime')->order('id asc')->select();

        $this->assign([
            'category' => $category,
            'goods' => $goods,
        ]);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
