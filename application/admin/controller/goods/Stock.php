<?php

namespace app\admin\controller\goods;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 库存数据
 *
 * @icon fa fa-circle-o
 */
class Stock extends Backend {

    /**
     * Stock模型对象
     * @var \app\admin\model\goods\Stock
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\goods\Stock;
//        $this->view->assign("statusList", $this->model->getStatusList());
    }


    public function edit($ids = null)
    {
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

    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $this->request->post("ids");
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $ids_arr = explode(',', $ids);

        foreach($ids_arr as $ids){
            $stock = db::name('stock')->where(['id' => $ids])->find();
            if($stock){
                db::name('stock')->where(['id' => $stock['id']])->delete();
                if($stock['sale_time'] == null){
                    if($stock['num'] > 0){
                        db::name('sku')->where(['id' => $stock['sku_id']])->setDec('stock', $stock['num']);
                        db::name('goods')->where(['id' => $stock['goods_id']])->setDec('stock', $stock['num']);
                    }
                }
            }
        }


        $this->success('删除成功');

    }


    public function alone_0() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            $goods_id = $this->request->param('ids');
            $this->assign('goods_id', $goods_id);
            return $this->view->fetch();
        }
        $goods_id = $this->request->param('ids');

        [$where, $sort, $order, $offset, $limit] = $this->buildparams();

        $list = db::name('stock')->where(['goods_id' => $goods_id])->order('id desc')->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
    public function alone_1() {
    //设置过滤方法
    $this->request->filter(['strip_tags', 'trim']);
    if (false === $this->request->isAjax()) {
        $goods_id = $this->request->param('ids');

        $sku = db::name('sku')->where(['goods_id' => $goods_id])->select();
        $this->assign([
            'sku' => $sku
        ]);
        $this->assign('goods_id', $goods_id);
        return $this->view->fetch();
    }
    $goods_id = $this->request->param('ids');

	[$where, $sort, $order, $offset, $limit] = $this->buildparams();

    $model = new \app\admin\model\goods\Stock();

    $list = $model->with(['sku'])->where(['goods_id' => $goods_id])->where($where)->order('id desc')->paginate($limit);
    $result = ['total' => $list->total(), 'rows' => $list->items()];
    return json($result);
}

    public function add(){
        $goods_id = $this->request->param('ids');
        $goods = db::name('goods')->where(['id' => $goods_id])->find();
//        $goods = $this->goodsDetail($goods);
        //        echo '<pre>'; print_r($goods);die;
        if($this->request->isPost()){
            $params = $this->request->post();
            $timestamp = time();

            Db::startTrans();
            try {
                if($goods['type'] == 'invented'){ //虚拟
                    if($goods['is_sku'] == 0){
                        $where = [
                            'goods_id' => $goods_id
                        ];
                        $e = db::name('stock')->where($where)->find();
                        $sku_id = db::name('sku')->where(['goods_id' => $goods_id])->value('id');
                        if($e){
                            $update = [
                                'num' => $params['num']
                            ];
                            $res = db::name('stock')->where(['id' => $e['id']])->update($update);
                        }else{
                            $insert = [
                                'goods_id' => $goods_id,
                                'num' => $params['num'],
                                'sku_id' => $sku_id
                            ];
                            $res = db::name('stock')->insert($insert);
                        }
                        db::name('goods')->where(['id' => $goods_id])->update(['stock' => $params['num']]);
                        db::name('sku')->where(['id' => $sku_id])->update(['stock' => $params['num']]);
                        $res = true;
                    }
                    if($goods['is_sku'] == 1){
                        $stock = json_decode($params['row']['stock'], true);
                        $stockNum = 0;
                        foreach($stock as $val){
                            $num = empty($val['stock']) || $val['stock'] < 0 ? 0 : $val['stock'];
                            $stockNum += $num;
                            $where = [
                                'goods_id' => $goods_id,
                                'sku_id' => $val['id']
                            ];
                            $res = db::name('stock')->where($where)->find();
                            if($res){
                                $update = [
                                    'num' => $num,
                                ];
                                db::name('stock')->where(['id' => $res['id']])->update($update);
                            }else{
                                $insert = [
                                    'goods_id' => $goods_id,
                                    'sku_id' => $val['id'],
                                    'num' => $num
                                ];
                                db::name('stock')->insert($insert);
                            }
                            db::name('sku')->where(['id' => $val['id']])->update(['stock' => $num]);
                        }
                        db::name('goods')->where(['id' => $goods_id])->update(['stock' => $stockNum]);
                        $res = true;
                    }

                }
                if($goods['type'] == 'fixed'){ //固定卡密
                    if($goods['is_sku'] == 0){
                        $where = [
                            'goods_id' => $goods_id,
                        ];
                        $res = db::name('stock')->where($where)->find();
                        $sku_id = db::name('sku')->where(['goods_id' => $goods_id])->value('id');
                        if($res){
                            db::name('stock')->where(['id' => $res['id']])->update($params);
                        }else{
                            $params['goods_id'] = $goods_id;
                            $params['sku_id'] = $sku_id;
                            $params['create_time'] = $timestamp;
                            db::name('stock')->insert($params);
                        }
                        db::name('goods')->where(['id' => $goods_id])->update(['stock' => $params['num']]);
                        db::name('sku')->where(['id' => $sku_id])->update(['stock' => $params['num']]);
                        $res = true;
                    }
                    if($goods['is_sku'] == 1){
                        $stock = json_decode($params['row']['stock'], true);
//                        print_r($stock);die;
                        $stockNum = 0;
                        foreach($stock as $val){
                            $num = empty($val['num']) || $val['num'] < 0 ? 0 : $val['num'];
                            $stockNum += $num;
                            $where = [
                                'goods_id' => $goods_id,
                                'sku_id' => $val['id']
                            ];
                            $res = db::name('stock')->where($where)->find();
                            if($res){
                                $update = [
                                    'content' => $val['content'],
                                    'num' => $num,
                                ];
                                db::name('stock')->where(['id' => $res['id']])->update($update);
                            }else{
                                $insert = [
                                    'goods_id' => $goods_id,
                                    'sku_id' => $val['id'],
                                    'content' => $val['content'],
                                    'create_time' => $timestamp,
                                    'num' => $num
                                ];
                                db::name('stock')->insert($insert);
                            }
                            db::name('sku')->where(['id' => $val['id']])->update(['stock' => $num]);
                        }
                        db::name('goods')->where(['id' => $goods_id])->update(['stock' => $stockNum]);
                        $res = true;
                    }
                }
                if($goods['type'] == 'alone'){ //独立卡密
                    if($goods['is_sku'] == 0){
                        $params['stock'] = explode("\r\n", $params['stock']);
                        $params['stock'] = array_filter($params['stock']);
                        if(empty($params['stock'])) throw new \Exception('库存数据还没填写呢');
                        $insert = [];
                        $inc = 0;
                        $sku_id = db::name('sku')->where(['goods_id' => $goods_id])->value('id');
                        foreach($params['stock'] as $val){
                            $insert[] = [
                                'goods_id' => $goods_id,
                                'content' => $val,
                                'create_time' => $timestamp,
                                'sku_id' => $sku_id
                            ];
                            $inc++;
                        }
                        db::name('stock')->insertAll($insert);
                        db::name('sku')->where(['id' => $sku_id])->setInc('stock', $inc);
                        $res = db::name('goods')->where(['id' => $goods_id])->setInc('stock', $inc);
                    }
                    if($goods['is_sku'] == 1){
                        $params['sku'] = array_filter($params['sku']);
                        if(empty($params['sku'])) throw new \Exception('您还没有选择要添加库存的规格呢');
                        $params['stock'] = explode("\r\n", $params['stock']);
                        $params['stock'] = array_filter($params['stock']);
                        if(empty($params['stock'])) throw new \Exception('库存数据还没填写呢');

                        foreach($params['sku'] as $val){
                            $inc = 0;
                            $insert = [];
                            foreach($params['stock'] as $v){
                                $insert[] = [
                                    'goods_id' => $goods_id,
                                    'sku_id' => $val,
                                    'content' => $v,
                                    'create_time' => $timestamp
                                ];
                                $inc++;
                            }
                            db::name('stock')->insertAll($insert);
                            db::name('sku')->where(['id' => $val])->setInc('stock', $inc);
                            $res = db::name('goods')->where(['id' => $goods_id])->setInc('stock', $inc);
                        }
                    }
                }


                db::commit();
            }catch (\Exception $e){
                db::rollback();
                $this->error($e->getMessage());
            }
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }

        if($goods['is_sku'] == 1){
            $sku = db::name('sku')->where(['goods_id' => $goods['id']])->select();
            $this->assign('sku', $sku);
        }

        if($goods['type'] == 'fixed'){
            if($goods['is_sku'] == 0){
                $where = [
                    'goods_id' => $goods_id,
                ];
                $stock = db::name('stock')->where($where)->find();
                $this->assign('stock', $stock);
            }
            if($goods['is_sku'] == 1){
                $stock = db::name('stock')->where(['goods_id' => $goods['id']])->select();
                $sku = db::name('sku')->where(['goods_id' => $goods_id])->select();
                $data = [];
                foreach($sku as $key => $val){
                    $data[$key] = [
                        'sku' => $val['sku'],
                        'num' => '',
                        'content' => '',
                        'id' => $val['id']
                    ];
                    foreach($stock as $v){
                        if($v['sku_id'] == $val['id']){
                            $data[$key]['num'] = $v['num'];
                            $data[$key]['content'] = $v['content'];
                        }
                    }
                }
                $this->assign('data', json_encode($data));
            }

        }
        if($goods['type'] == 'invented'){
            if($goods['is_sku'] == 0){
                $where = [
                    'goods_id' => $goods_id,
                ];
                $stock = db::name('stock')->where($where)->find();
                $this->assign('stock', $stock);
            }
            if($goods['is_sku'] == 1){
                $stock = db::name('stock')->where(['goods_id' => $goods['id']])->select();
                $sku = db::name('sku')->where(['goods_id' => $goods_id])->select();
                $data = [];
                foreach($sku as $key => $val){
                    $data[$key] = [
                        'sku' => $val['sku'],
                        'stock' => '',
                        'id' => $val['id']
                    ];
                    foreach($stock as $v){
                        if($v['sku_id'] == $val['id']){
                            $data[$key]['stock'] = $v['num'];
                        }
                    }
                }
                $this->assign('data', json_encode($data));
            }
        }



        $this->assign([
            'goods' => $goods
        ]);
        return view();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
