<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\dock\Dock;
use app\common\controller\Hm;
use app\common\controller\Enum;
use think\Db;



/**
 *
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend {

    protected $searchFields = ['goods.name', 'category.name'];

    /**
     * Goods模型对象
     * @var \app\admin\model\Goods
     */
    protected $model = null;


    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Goods;

        $where = [
            'type' => 'goods',
        ];
        $category = db::name('category')->where($where)->select();
        $attach = db::name('attach')->select();

        $this->assign([
            'category' => $category,
            'attach' => $attach,
        ]);

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
     * 同步库存
     */
    public function update_stock(){
        $params = $this->request->param();
        include ROOT_PATH . '/public/content/dock/' . $params['docksite']['type'] . '/' . ucfirst($params['docksite']['type']) . '.php';
        $objName = ucfirst($params['docksite']['type']) . 'Dock';
        $dockObj = new $objName();
        $result = $dockObj->goodsInfo(json_decode($params['docksite']['info'], true), $params['remote_id']);
        if($result['code'] == 400) return json(['code' => 400, 'msg' => $result['msg']]);
        db::name('goods')->where(['id' => $params['id']])->update(['stock' => $result['data']['stock']]);
        return json(['code' => 200, 'msg' => '操作成功']);
    }

    /**
     * 配置库存显示
     */
    public function stockshow(){
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    $stock_show = json_decode($params['stock_show'], true);
                    foreach($stock_show as $key => $val){
                        if(empty($val['content'])){
                            throw new \Exception("显示内容不能存在空值");
                        }
                        if(empty($val['less'])) $stock_show[$key]['less'] = 0;
                        if(empty($val['greater'])) $stock_show[$key]['greater'] = 0;
                    }
                    $stock_show = json_encode($stock_show);
                    $stock_show_switch = $params['stock_show_switch'];
                    $result = db::name('options')->where(['option_name' => 'stock_show'])->update(['option_content' => $stock_show]);
                    $result = db::name('options')->where(['option_name' => 'stock_show_switch'])->update(['option_content' => $stock_show_switch]);
                    Db::commit();
                } catch (\Exception $e) {
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

        $stock_show = db::name('options')->where(['option_name' => 'stock_show'])->value('option_content');
        $stock_show_switch = db::name('options')->where(['option_name' => 'stock_show_switch'])->value('option_content');
        $this->assign([
            'stock_show' => $stock_show,
            'stock_show_switch' => $stock_show_switch
        ]);
        return $this->view->fetch();
    }

    /**
     * 编辑商品
     */
    public function edit($ids = null) {
        $row = $this->model->get($ids);
        $params = $this->request->param();
        if($this->request->isPost()) {
            $params = $params['row'];
//            print_r($params);die;
            if(empty($params['category_id'])) return $this->error('请选择商品分类');
            if(empty($params['name'])) return $this->error('商品名称不能为空');
            if($params['goods_type'] == 'dock' && empty($params['remote_id'])) return $this->error('请选择对接商品');
//            print_r($params);die;
            $params['price'] = empty($params['price']) || $params['price'] < 0 ? 0 : number_format($params['price'], 2);
//            print_r($params);die;
            $update = [
                "inputs" => $params["inputs"],
                'dock_id' => $params['dock_id'],
                'dock_data' => $params['dock_data'],
                'remote_id' => $params['remote_id'],
                'buy_default' => $params['buy_default'],
                'category_id' => $params['category_id'], //商品分类ID
                'attach_id' => $params['attach_id'], //附加选项ID
                'name' => $params['name'], //商品名称
                'price' => $params['price'], //商品售卖价格
                'buy_price' => $params['buy_price'], //成本价格
                'sales' => empty($params['sales']) ? 0 : $params['sales'], //销量
                'images' => empty($params['images']) ? '' : $params['images'], //商品图片
                'details' => $params['details'], //商品说明,
                'eject' => $params['eject'], //商品弹窗内容
                'goods_type' => $params['goods_type'], //商品类型
                'sort' => empty($params['sort']) ? 0 : $params['sort'], //排序字段
                'buy_msg' => $params['buy_msg'], //购买后的提示内容
                'quota' => empty($params['quota']) ? 0 : $params['quota'], //单IP单日限购数量
            ];
            if(!empty($params['stock'])) $update['stock'] = $params['stock']; //商品库存

            if($row['goods_type'] == 'dock' && $params['goods_type'] != 'dock'){
                $update['remote_id'] = 0;
                $update['inputs'] = '';
                $update['dock_id'] = 0;
                if($params['goods_type'] != 'manual') $update['stock'] = 0;
            }


            db::name('goods')->where(['id' => $ids])->update($update); //修改商品信息
            //处理代理的购买价格
            $grade_price = $params['grade_price'];
            $grade_price = json_decode($grade_price, true);
            foreach($grade_price as $val){
                $where = [
                    'goods_id' => $ids,
                    'grade_id' => $val['grade_id']
                ];
                if(!isset($val['price']) || $val['price'] == ""){
                    $res = db::name('price')->where($where)->find();
                    if($res){
                        db::name('price')->where(['id' => $res['id']])->delete();
                    }

                }else{
                    $res = db::name('price')->where($where)->find();
                    if($res){
                        $price_update = [
                            'price' => $val['price'],
                            'update_time' => $this->timestamp
                        ];
                        db::name('price')->where(['id' => $res['id']])->update($price_update);
                    }else{
                        $price_insert = [
                            'goods_id' => $ids,
                            'grade_id' => $val['grade_id'],
                            'price' => $val['price']
                        ];
                        db::name('price')->insert($price_insert);
                    }
                }
            }
            $this->success();
        }
        $grade_result = db::name('user_grade')->order('id asc')->select();
        $grade_price = db::name('price')->where(['goods_id' => $ids])->select();

        $grade = [];
        foreach($grade_result as $key => $val){
            $grade[$key] = [
                'grade_id' => $val['id'],
                'name' => $val['name']
            ];
            foreach($grade_price as $v){
                if($val['id'] == $v['grade_id']){
                    $grade[$key]['price'] = $v['price'];
                }
            }
        }
        $grade = json_encode($grade);

        $dock = db::name('dock')->select();
        $increase = db::name('increase')->select();

        $this->assign([
            'row' => $row,
            'grade' => $grade,
            'dock' => $dock,
            'increase' => $increase
        ]);

        return $this->view->fetch();
    }




    /**
     * 添加商品
     */
    public function add() {
        $params = $this->request->param();
        if($this->request->isPost()) {
            $params = $params['row'];
            if(empty($params['category_id'])) return $this->error('请选择商品分类');
            if(empty($params['name'])) return $this->error('商品名称不能为空');
            if($params['goods_type'] == 'dock' && empty($params['remote_id'])) return $this->error('请选择对接商品');
            $params['price'] = empty($params['price']) || $params['price'] < 0 ? 0 : number_format($params['price'], 2);
            $insert = [
                "inputs" => $params["inputs"],
                'dock_id' => $params['dock_id'],
                'dock_data' => $params['dock_data'],
                'remote_id' => $params['remote_id'],
                'buy_default' => $params['buy_default'],
                'category_id' => $params['category_id'], //商品分类ID
                'attach_id' => $params['attach_id'], //附加选项ID
                'name' => $params['name'], //商品名称
                'price' => $params['price'], //商品售卖价格
                'buy_price' => $params['buy_price'], //成本价格
                'sales' => empty($params['sales']) ? 0 : $params['sales'], //销量
                'images' => empty($params['images']) ? '' : $params['images'], //商品图片
                'details' => $params['details'], //商品说明,
                'eject' => $params['eject'], //商品弹窗内容
                'goods_type' => $params['goods_type'], //商品类型
                'sort' => empty($params['sort']) ? 0 : $params['sort'], //排序字段
                'buy_msg' => $params['buy_msg'], //购买后的提示内容
                'quota' => empty($params['quota']) ? 0 : $params['quota'], //单IP单日限购数量
                'stock' => $params['stock'], //商品库存
            ];
            $grade_price = $params['grade_price'];
            $grade_price = json_decode($grade_price, true);
            $price_insert = [];
            $goods_id = db::name('goods')->insertGetId($insert);
            foreach($grade_price as $val){
                if(isset($val['price']) && $val['price'] != ""){
                    $val['goods_id'] = $goods_id;
                    $price_insert[] = $val;
                }
            }
            if(!empty($price_insert)) db::name('price')->insertAll($price_insert);
            $this->success();
        }
        $grade_result = db::name('user_grade')->order('id asc')->select();
        $grade = [];
        foreach($grade_result as $val){
            $grade[] = [
                'grade_id' => $val['id'],
                'name' => $val['name']
            ];
        }
        $grade = json_encode($grade);

        $dock = db::name('dock')->select();
        $increase = db::name('increase')->select();

        $this->assign([
            'grade' => $grade,
            'dock' => $dock,
            'increase' => $increase
        ]);
        return $this->view->fetch();
    }

    /**
     * 选择对接站商品
     */
    public function dockselectgoods(){
        $dock_id = $this->request->param('dock_id');
        $result = db::name('dock')->where(['id' => $dock_id])->find();
        $view = ROOT_PATH . '/public/content/dock/' . $result['type'] . '/select_goods.html';
        include ROOT_PATH . '/public/content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
        $objName = ucfirst($result['type']) . 'Dock';
        $dockObj = new $objName();

        if($this->request->isAjax()){
//            echo $this->request->method();die;
            if($this->request->method() == 'POST'){ //绑定商品
                $goods_info = $this->request->param('goods_info');
                $this->success('已绑定商品', null, $goods_info);
            }
            $func = $this->request->param('func');
            $params = $this->request->param();
            $result = $dockObj->$func($result['info'], $params);
            return json(['code' => 200, 'msg' => 'ok', 'data' => $result]);
        }


        $data = $dockObj->dockSelectGoodsData($result['info']);


        $this->assign([
            'dock' => $result,
            'data' => $data
        ]);

        return $this->view->fetch($view);
    }

    /**
     * 获取对接站点商品列表
     */
    public function dockGoodsList(){
        $dock_id = $this->request->param('dock');
        $result = db::name('dock')->where(['id' => $dock_id])->find();
        include ROOT_PATH . '/public/content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
        $objName = ucfirst($result['type']) . 'Dock';
        $dockObj = new $objName();
        $list = $dockObj->goodsList($result['info']);
        print_r($list);
    }





    /**
     * 删除商品
     */
    public function del($ids = "") {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }

                $count = db::name('options')->where(['option_name' => 'goods_total'])->setDec('option_content', $count);

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    /**
     * 添加库存
     */

    public function stock_add() {
        $id = $this->request->param('ids'); //商品id
        $goods_info = db::name('goods')->where(['id' => $id])->find();
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $result = false;
            Db::startTrans();
            try {
                $kami = isset($params['kami']) ? $params['kami'] : null;
                if($goods_info['goods_type'] == 'fixed'){
                    if(isset($params['kami'])){
                        $kami = trim($params['kami'], ' ');
                        if(empty($kami)) throw new Exception("卡密不能为空");
                    }
                }else{ //普通卡密
                    $kami = explode("\r\n", $kami);
                }
                $timestamp = time();
                if($goods_info['goods_type'] != 'fixed'){
                    $kami = array_filter($kami); //去除空元素
                    $insert = [];
                    foreach($kami as $val){
                        $insert[] = [
                            'type' => $goods_info['goods_type'],
                            'goods_id' => $id,
                            'cdk' => $val,
                            'createtime' => $timestamp
                        ];
                    }
                    db::name('cdkey')->insertAll($insert);
                    $stock = db::name('cdkey')->where(['goods_id' => $id])->count();
                }else{ //重复卡密
                    $stock = 0;
                    if(isset($params['kami'])){
                        $insert = [
                            'type' => $goods_info['goods_type'],
                            'goods_id' => $id,
                            'cdk' => $kami,
                            'createtime' => $timestamp,
                            'num' => $params['stock']
                        ];
                        $res = db::name('cdkey')->where(['goods_id' => $id])->find();
                        if($res){
                            db::name('cdkey')->where(['id' => $res['id']])->update($insert);
                        }else{
                            db::name('cdkey')->insert($insert);
                        }
                        $stock = $params['stock'];
                    }
                }

                db::name('goods')->where(['id' => $id])->update(['stock' => $stock]);

                $result = true;
                Db::commit();
            }catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success();
            } else {
                $this->error(__('No rows were inserted'));
            }
        }
        $cdk = null;
        if($goods_info['goods_type'] == 'fixed'){
            $cdk = db::name('cdkey')->where(['goods_id' => $goods_info['id']])->find();
        }
        $this->assign([
            'goods_info' => $goods_info,
            'cdk' => $cdk,
        ]);
        return $this->view->fetch();
    }


    //删除库存
    public function stock_del(){
        $ids = $this->request->param('ids');
        Db::startTrans();
        try{
            db::name('cdkey')->whereIn('id', $ids)->delete();
            $goods_id = $this->request->param('goods_id');
            $stock = db::name('cdkey')->where(['goods_id' => $goods_id])->sum('num');
            db::name('goods')->where(['id' => $goods_id])->update(['stock' => $stock]);
            db::commit();
        } catch (\Exception $e) {
            db::rollback();
            $this->error($e->getMessage());
        }

        $this->success('删除成功');
    }

    /**
     * 清空商品所有的库存
     */
    public function ept(){
        $id = $this->request->param('id');
        $update = [
            'stock' => 0,
            'updatetime' => time()
        ];
        db::name('goods')->where(['id' => $id])->update($update);
        db::name('cdkey')->where(['goods_id' => $id])->delete();
        return  json(['code' => 200, 'msg' => '操作成功']);
    }

    /**
     * 导出商品下的库存
     */
    public function export(){
        $goods_id = $this->request->param('id');
        $stock = db::name('cdkey')->where(['goods_id' => $goods_id])->select();
        $goods = db::name('goods')->where(['id' => $goods_id])->find();
        $content = "";
        foreach($stock as $val){
            $content .= $val['cdk'] . "\r\n";
        }
        $filename = '商品ID_' .  $goods['id'];
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename = {$filename}.txt"); //文件命名
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        echo $content;
    }

    /**
     * 清除商品重复库存
     */
    public function repeat(){
        $id = $this->request->param('id');
        $where = [
            'goods_id' => $id
        ];
        db::startTrans();
        try {
            $goods = db::name('goods')->where(['id' => $id])->find();
            if(!$goods){
                throw new \Exception("商品不存在！");
            }

            $res = db::name('cdkey')->where($where)->field('id, cdk')->select();
            $cdk = [];
            foreach($res as $val){
                if(in_array($val['cdk'], $cdk)){
                    db::name('cdkey')->where(['id' => $val['id']])->delete();
                }else{
                    $cdk[] = $val['cdk'];
                }
            }

            $stock = count($cdk);
            if($goods['goods_type'] == '0'){ //卡密商品
                db::name('goods')->where(['id' => $id])->update(['stock' => $stock]);
            }


            db::commit();
        }catch (\Exception $e){
            db::rollback();
            return json(['code' => 400, 'msg' => '操作失败']);
        }

        return  json(['code' => 200, 'msg' => '操作成功']);
        print_r($res);die;
    }

    /**
     * 查看库存
     */
    public function stock(){

        $ids = $this->request->param('ids');

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $post = $this->request->param();
            if(empty($post['status']) || $post['status'] == 'ws'){
                $list = db::name('cdkey')->where(['goods_id' => $ids])->limit($post['offset'], $post['limit'])->select();
                $total = db::name('cdkey')->where(['goods_id' => $ids])->count();
            }else{
                $field = "s.content, s.create_time";
                $list = db::name('order')->alias('o')
                    ->join('sold s', 's.order_id=o.id')
                    ->field($field)
                    ->where(['o.goods_id' => $ids])
                    ->order('o.id desc')
                    ->limit($post['offset'], $post['limit'])
                    ->select();
                $total = db::name('order')->alias('o')
                    ->join('sold s', 's.order_id=o.id')
                    ->where(['o.goods_id' => $ids])
                    ->count();
            }


            $result = ["total" => $total, "rows" => $list];
            return json($result);
        }

        $this->assign([
            'id' => $ids
        ]);
        return $this->view->fetch();
    }


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
            $shelf = $this->request->param('shelf');
            $where_shelf = [];
            if($shelf != 'all' && $shelf != null){
                $where_shelf['shelf'] = $shelf;
            }
            $list = $this->model->with(['category', 'docksite'])
                ->where($where)->where($where_shelf)
                ->order($sort, $order)
                ->paginate($limit)->toArray();
            $rows = $list['data'];
            foreach($rows as &$val){
                if(empty($val['images'])) $val['images'] = "/assets/img/none.jpg";
            }
            $result = ["total" => $list['total'], "rows" => $rows];
            return json($result);
        }




        return $this->view->fetch();
    }













    //上架商品
    public function upGoods(){
        $post = $this->request->param();
        db::name('goods')->where(['id' => $post['id']])->update(['shelf' => $post['shelf']]);
        return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
    }

    //下架商品
    public function downGoods(){
        $post = $this->request->param();
        db::name('goods')->where(['id' => $post['id']])->update(['shelf' => $post['shelf']]);
        return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
    }


}
