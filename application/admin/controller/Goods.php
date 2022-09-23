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
        include ROOT_PATH . 'content/dock/' . $params['docksite']['type'] . '/' . ucfirst($params['docksite']['type']) . '.php';
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
        $row = $this->model->with(['price'])->where(['id' => $ids])->find()->toArray();
        $params = $this->request->param();
        $specification = db::name('specification')->select();
        if($this->request->isPost()) {
            db::startTrans();
            try {
                $sku = $params['sku'];

                foreach($sku['price'] as $val){
                    $sku_type = is_array($val) ? 'many' : 'one'; //单规格或多规格
                    break;
                }

//                echo $sku_type . "\n\n"; print_r($params);die;

                if($sku_type == 'many' && !empty($params['sku_value'])){
                    $sku_value = [];
                    foreach($params['sku_value'] as $key => $val){
                        foreach($val as $v){
                            if(empty($v)) continue;
                            $v_arr = explode(',', $v);
                            $sku_value[$key]['value'][] = [
                                'name' => $v_arr[0],
                                'id' => $v_arr[1]
                            ];
                        }
                        if(empty($sku_value[$key]['value'])) continue;
                        $sku_value[$key]['name'] = $key;
                    }
                }
                $params = $params['row'];
                if(empty($params['category_id'])) throw new \Exception("请选择商品分类");
                $params['name'] = empty($params['name']) ? '未命名商品' : $params['name'];
                if($params['goods_type'] == 'dock' && empty($params['remote_id'])) throw new \Exception("请选择对接商品");;;
                $params['price'] = empty($params['price']) || $params['price'] < 0 ? 0 : $params['price'];
                $update = [
                    "inputs" => $params["inputs"],
                    'dock_id' => $params['dock_id'],
                    'dock_data' => $params['dock_data'],
                    'remote_id' => $params['remote_id'],
                    'buy_default' => $params['buy_default'],
                    'category_id' => $params['category_id'], //商品分类ID
                    'attach_id' => $params['attach_id'], //附加选项ID
                    'name' => $params['name'], //商品名称
                    'buy_price' => empty($params['buy_price']) ? 0 : $params['buy_price'], //成本价格
                    'sales' => empty($params['sales']) ? 0 : $params['sales'], //销量
                    'images' => empty($params['images']) ? '' : $params['images'], //商品图片
                    'details' => $params['details'], //商品说明,
                    'eject' => $params['eject'], //商品弹窗内容
                    'goods_type' => $params['goods_type'], //商品类型
                    'sort' => empty($params['sort']) ? 0 : $params['sort'], //排序字段
                    'buy_msg' => $params['buy_msg'], //购买后的提示内容
                    'quota' => empty($params['quota']) ? 0 : $params['quota'], //单IP单日限购数量
                ];
                if($sku_type == 'one') $update['sku'] = null;
                if(isset($sku_value)) $update['sku'] = json_encode($sku_value);

                if($row['goods_type'] == 'dock' && $params['goods_type'] != 'dock'){
                    $update['remote_id'] = 0;
                    $update['inputs'] = '';
                    $update['dock_id'] = 0;
                    if($params['goods_type'] != 'manual') $update['stock'] = 0;
                }
                db::name('goods')->where(['id' => $ids])->update($update); //修改商品信息

//                print_r($specification);die;
                //处理代理的购买价格
                $price_insert_sku = [];
                foreach($sku['price'] as $key => $val){ //规格列表
                    $price_insert_sku[$key]['sku_ids'] = $key;
                    $price_insert_sku[$key]['sku'] = '';
                    $sku_ids = explode(',', $key);
                    foreach($sku_ids as $k => $v){
                        $sku_id = explode('-', $v);
                        foreach($specification as $sk => $sv){
                            if($sku_id[0] == $sv['id']){
                                $sv_value = json_decode($sv['value'], true);
                                foreach($sv_value as $vk => $vv){
                                    if($sku_id[1] == $vk){
                                        $vk_value = explode('|', $vv['value']);
                                        foreach($vk_value as $vk_k => $vk_v){
                                            if($sku_id[2] == $vk_k){
                                                $price_insert_sku[$key]['sku'] .= $vk_v . ',';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $price_insert_sku[$key]['sku'] = rtrim($price_insert_sku[$key]['sku'], ',');
                }
                $price_insert = [];
                $i = 0;
                foreach($sku['price'] as $key => $val){
                    if($sku_type == 'many'){
                        foreach($val as $k => $v){
//                            if($k != 'price' && $v === "") continue;
                            $price_insert[$i] = $price_insert_sku[$key];
                            $price_insert[$i]['goods_id'] = $ids;
                            if($k == 'price'){
                                $price_insert[$i]['grade_id'] = 0;
                            }else{
                                $k_arr = explode('_', $k);
                                $price_insert[$i]['grade_id'] = $k_arr[1];
                            }
                            $price_insert[$i]['price'] =  $v;
                            $i++;
                        }
                    }else{

                        if($key != 'price' && $val == "") continue;
                        $price_insert[$i]['price'] = $val;
                        $price_insert[$i]['goods_id'] = $ids;
                        if($key == 'price'){
                            $price_insert[$i]['grade_id'] = 0;
                        }else{
                            $key_arr = explode('_', $key);
                            $price_insert[$i]['grade_id'] = $key_arr[1];
                        }
                        $i++;
                    }

                }

                if($sku_type == 'many') {
                    db::name('price')->where([
                        'goods_id' => $ids,
                    ])->whereNull('sku_ids')->delete();
                }else{
                    db::name('price')->where([
                        'goods_id' => $ids,
                    ])->whereNotNull('sku_ids')->delete();
                }
//                print_r($price_insert);die;
                foreach ($price_insert as $val) {
                    $where = [
                        'goods_id' => $ids,
                        'grade_id' => $val['grade_id'],
                    ];
                    if($sku_type == 'many') $where['sku_ids'] = $val['sku_ids'];
                    if($val['grade_id'] == 0 && empty($val['price'])) $val['price'] = 0;
                    if (!isset($val['price']) || $val['price'] === "") {
                        $res = db::name('price')->where($where)->find();
                        if ($res) db::name('price')->where(['id' => $res['id']])->delete();
                    } else {
                        $res = db::name('price')->where($where)->find();
                        if ($res) {
                            $price_update = [
                                'price' => $val['price'],
                            ];
                            db::name('price')->where(['id' => $res['id']])->update($price_update);
                        } else {
                            $insert = [
                                'goods_id' => $ids,
                                'grade_id' => $val['grade_id'],
                                'price' => $val['price'],
                                'sku_ids' => empty($val['sku_ids']) ? null : $val['sku_ids'],
                                'sku' => empty($val['sku']) ? null : $val['sku'],
                            ];
                            db::name('price')->insert($insert);
                        }
                    }
                }
                db::commit();
            }catch (\Exception $e){
                db::rollback();
                $this->error($e->getMessage(). '-' . $e->getLine());
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
//                    $grade[$key]['price'] = $v['price'];
                }
            }
        }
        $grade = json_encode($grade);

        $dock = db::name('dock')->select();
        $increase = db::name('increase')->select();

        $sku_type = empty($row['sku']) ? 'one' : 'many';

        $price = [];
        if($sku_type == 'many'){
            foreach($row['price'] as $val){
                $price[$val['sku_ids']][$val['grade_id']] = $val['price'];
            }
        }else{
            foreach($row['price'] as $val){
                $price[$val['grade_id']] = $val['price'];
            }
        }



        $this->assign([
            'row' => $row,
            'grade' => $grade,
            'dock' => $dock,
            'increase' => $increase,
            'specification' => $specification,
            'price' => json_encode($price)
        ]);

        return $this->view->fetch();
    }


    /**
     * 添加商品
     */
    public function add() {
        $params = $this->request->param();

        $grade_result = db::name('user_grade')->order('id asc')->select();
        $grade = [];
        foreach($grade_result as $val){
            $grade[] = [
                'grade_id' => $val['id'],
                'name' => $val['name']
            ];
        }
        $specification = db::name('specification')->select();

        if($this->request->isPost()) {
            db::startTrans();
            try {
//                print_r($params);die;
                $sku = $params['sku'];
                $sku_type = empty($params['sku_value']) ? 'one' : 'many';
                if($sku_type == 'many'){
                    $sku_value = [];
                    foreach($params['sku_value'] as $key => $val){
                        foreach($val as $v){
                            if(empty($v)) continue;
                            $v_arr = explode(',', $v);
                            $sku_value[$key]['value'][] = [
                                'name' => $v_arr[0],
                                'id' => $v_arr[1]
                            ];
                        }
                        if(empty($sku_value[$key]['value'])) continue;
                        $sku_value[$key]['name'] = $key;
                    }
                }
                $params = $params['row'];

                if(empty($params['category_id'])) throw new \Exception('请选择商品分类');
                $params['name'] = empty($params['name']) ? '未命名商品' : $params['name'];
                if($params['goods_type'] == 'dock' && empty($params['remote_id'])) throw new \Exception('请选择对接商品');
                $params['price'] = empty($params['price']) || $params['price'] < 0 ? 0 : $params['price'];
                $insert = [
                    'sku' => $sku_type == 'many' ? json_encode($sku_value) : '',
                    "inputs" => $params["inputs"],
                    'dock_id' => $params['dock_id'],
                    'dock_data' => $params['dock_data'],
                    'remote_id' => $params['remote_id'],
                    'buy_default' => $params['buy_default'],
                    'category_id' => $params['category_id'], //商品分类ID
                    'attach_id' => $params['attach_id'], //附加选项ID
                    'name' => $params['name'], //商品名称
                    'sales' => empty($params['sales']) ? 0 : $params['sales'], //销量
                    'images' => empty($params['images']) ? '' : $params['images'], //商品图片
                    'details' => $params['details'], //商品说明,
                    'eject' => $params['eject'], //商品弹窗内容
                    'goods_type' => $params['goods_type'], //商品类型
                    'sort' => empty($params['sort']) ? 0 : $params['sort'], //排序字段
                    'buy_msg' => $params['buy_msg'], //购买后的提示内容
                    'quota' => empty($params['quota']) ? 0 : $params['quota'], //单IP单日限购数量
                ];

                $goods_id = db::name('goods')->insertGetId($insert);

                $price_insert_sku = [];
                foreach($sku['price'] as $key => $val){ //规格列表
                    $price_insert_sku[$key]['sku_ids'] = $key;
                    $price_insert_sku[$key]['sku'] = '';
                    $sku_ids = explode(',', $key);
                    foreach($sku_ids as $k => $v){
                        $sku_id = explode('-', $v);
                        foreach($specification as $sk => $sv){
                            if($sku_id[0] == $sv['id']){
                                $sv_value = json_decode($sv['value'], true);
                                foreach($sv_value as $vk => $vv){
                                    if($sku_id[1] == $vk){
                                        $vk_value = explode('|', $vv['value']);
                                        foreach($vk_value as $vk_k => $vk_v){
                                            if($sku_id[2] == $vk_k){
                                                $price_insert_sku[$key]['sku'] .= $vk_v . ',';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $price_insert_sku[$key]['sku'] = rtrim($price_insert_sku[$key]['sku'], ',');
                }
                $price_insert = [];
                $i = 0;
                foreach($sku['price'] as $key => $val){
                    if($sku_type == 'many'){
                        foreach($val as $k => $v){
                            if($k != 'price' && $v == "") continue;
                            $price_insert[$i] = $price_insert_sku[$key];
                            $price_insert[$i]['goods_id'] = $goods_id;
                            if($k == 'price'){
                                $price_insert[$i]['grade_id'] = 0;
                            }else{
                                $k_arr = explode('_', $k);
                                $price_insert[$i]['grade_id'] = $k_arr[1];
                            }
                            $price_insert[$i]['price'] = empty($v) ? 0 : $v;
                            $i++;
                        }
                    }else{
                        if($key != 'price' && $val === "") continue;
                        $price_insert[$i]['price'] = $val;
                        $price_insert[$i]['goods_id'] = $goods_id;
                        if($key == 'price'){
                            $price_insert[$i]['grade_id'] = 0;
                        }else{
                            $key_arr = explode('_', $key);
                            $price_insert[$i]['grade_id'] = $key_arr[1];
                        }
                        $price_insert[$i]['price'] = empty($val) ? 0 : $val;
                        $i++;
                    }

                }
//                print_r($sku); print_r($price_insert);die;
                db::name('price')->insertAll($price_insert);
                db::commit();
            }catch (\Exception $e){
                db::rollback();
                $this->error($e->getMessage() . '-' . $e->getLine());
            }
            $this->success();
        }

        $grade = json_encode($grade);

        $dock = db::name('dock')->select();
        $increase = db::name('increase')->select();


        $this->assign([
            'grade' => $grade,
            'dock' => $dock,
            'increase' => $increase,
            'specification' => $specification
        ]);
        return $this->view->fetch();
    }

    /**
     * 选择对接站商品
     */
    public function dockselectgoods(){
        $dock_id = $this->request->param('dock_id');
        $result = db::name('dock')->where(['id' => $dock_id])->find();
        $view = ROOT_PATH . 'content/dock/' . $result['type'] . '/select_goods.html';
        include ROOT_PATH . 'content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
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
        include ROOT_PATH . 'content/dock/' . $result['type'] . '/' . ucfirst($result['type']) . '.php';
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

            $params = $this->request->post();
            $result = false;
            Db::startTrans();

            try {

                if($goods_info['goods_type'] == 'fixed'){
                    if(isset($params['row']['kami'])){
                        $kami = trim($params['row']['kami'], ' ');
                        if(empty($kami)) throw new Exception("卡密不能为空");
                    }
                }elseif($goods_info['goods_type'] == 'alone'){ //独立卡密
                    $insert = [];
                    if(empty($goods_info['sku'])){
                        $stock = empty($params['row']['stock']) ? [] : $params['row']['stock'];
                        $stock_arr = array_filter(explode("\r\n", $stock));
                        foreach($stock_arr as $val){
                            $insert[] = [
                                'type' => $goods_info['goods_type'],
                                'goods_id' => $id,
                                'cdk' => $val,
                                'createtime' => $this->timestamp,
                            ];
                        }
                    }else{
                        $stock = isset($params['stock']) ? $params['stock'] : [];
                        foreach($stock as $key => $val){
                            $item = array_filter(explode("\r\n", $val));
                            foreach($item as $v){
                                $insert[] = [
                                    'type' => $goods_info['goods_type'],
                                    'goods_id' => $id,
                                    'cdk' => $v,
                                    'createtime' => $this->timestamp,
                                    'sku_ids' => $key
                                ];
                            }
                        }
                    }

                    db::name('cdkey')->insertAll($insert);
                    $stock = db::name('cdkey')->where(['goods_id' => $id])->count();
                }
                if($goods_info['goods_type'] == 'fixed'){ //固定卡密
                    $stock = 0;
                    if(isset($params['row']['kami'])){
                        $insert = [
                            'type' => $goods_info['goods_type'],
                            'goods_id' => $id,
                            'cdk' => $kami,
                            'createtime' => $this->timestamp,
                            'num' => $params['row']['stock']
                        ];
                        $res = db::name('cdkey')->where(['goods_id' => $id])->find();
                        if($res){
                            db::name('cdkey')->where(['id' => $res['id']])->update($insert);
                        }else{
                            db::name('cdkey')->insert($insert);
                        }
                    }
                }
                if($goods_info['goods_type'] == 'manual'){

                        $insert = [
                            'type' => $goods_info['goods_type'],
                            'goods_id' => $id,
                            'cdk' => '',
                            'createtime' => $this->timestamp,
                            'num' => empty($params['row']['stock']) ? 0 : $params['row']['stock']
                        ];
                        $res = db::name('cdkey')->where(['goods_id' => $id])->find();
                        if($res){
                            db::name('cdkey')->where(['id' => $res['id']])->update($insert);
                        }else{
                            db::name('cdkey')->insert($insert);
                        }
                }
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
        db::name('cdkey')->whereIn('id', $ids)->delete();
        $this->success('删除成功');
    }

    /**
     * 清空商品所有的库存
     */
    public function ept(){
        $id = $this->request->param('id');
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

            $list = $this->model->with(['category', 'docksite', 'price'])
                ->where($where)->where($where_shelf)
                ->order($sort, $order)
                ->paginate($limit)->toArray();
            $rows = $list['data'];
            foreach($rows as &$val){
                $val['images'] = empty($val['images']) ? "/assets/img/none.jpg" : $val['images'];
                $val['price'] = empty($val['price'][0]['price']) ? 0 : $val['price'][0]['price'];
                $val['stock'] = db::name('cdkey')->where(['goods_id' => $val['id']])->sum('num');
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
