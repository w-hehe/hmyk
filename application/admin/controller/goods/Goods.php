<?php

namespace app\admin\controller\goods;

use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商品管理
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend {

    protected $searchFields = ['name'];
    /**
     * Goods模型对象
     * @var \app\admin\model\goods\Goods
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\goods\Goods;
        $this->category_model = new \app\admin\model\goods\Category;
        $tree = Tree::instance();
        $tree->init(collection($this->category_model->order('weigh desc,id desc')->select())->toArray(), 'pid');
        $categorylist = $tree->getTreeList($tree->getTreeArray(0), 'name');
		$categorydata = [0 => ['id' => null, 'name' => __('请选择商品类目')]];
        foreach ($categorylist as $k => $v) {
            $categorydata[$v['id']] = $v;
        }

        $user_agency = Db::name('user_agency')->field('id, name')->whereNull('deletetime')->order('weigh desc')->select();
        $this->assign([
			'user_agency' => $user_agency, //代理等级
            'category' => $categorydata, //商品分类
        ]);

    }

    /**
     * 处理附加选项
     */
    public function clAttach($attach){
        if (empty($attach)) {
            $attach = json_encode([]);
        } else {
            $attach = json_decode($attach, true);
            foreach ($attach as $key => $val) {
                if ((!empty($val['placeholder']) || !empty($val['checked'])) && empty($val['title'])) $this->error('附加选项标题不能为空');
                if (empty($val['title'])) unset($attach[$key]);
            }
            $attach = empty($attach) ? json_encode([]) : json_encode($attach);
        }
        return $attach;
    }

    /**
     * 处理批发优惠
     */
    public function clWholesale($wholesale){
        if (empty($wholesale)) {
            $wholesale = json_encode([]);
        } else {
            $wholesale = json_decode($wholesale, true);
            foreach ($wholesale as $key => $val) {
                $_empty = 0;
                if (empty($val['number'])) $_empty++;
                if (empty($val['offer'])) $_empty++;
                if ($_empty == 2) unset($wholesale[$key]);
                if ($_empty == 1) $this->error('批发优惠设置不完整');
            }
            $wholesale = empty($wholesale) ? json_encode([]) : json_encode($wholesale);
        }
        return $wholesale;
    }


    public function add() {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if (empty(trim($params['name']))) $this->error('请输入商品名称');
        if (empty($params['category_id'])) $this->error('请选择商品类目');

        if ($params['is_sku'] == 0) { //处理单规格
            $price = [
                'crossed_price' => isEmpty($params['crossed_price']) ? '' : sprintf('%.2f', $params['crossed_price']), //原价
                'cost_price' => isEmpty($params['cost_price']) ? '' : sprintf('%.2f', $params['cost_price']), //成本价
                'sale_price' => isEmpty($params['sale_price']) ? '' : sprintf('%.2f', $params['sale_price']), //销售价
            ];
            foreach ($params as $key => $val) {
                if (strstr($key, 'agency_price_')) $price[$key] = isEmpty($val) ? '' : sprintf('%.2f', $val);
            }
        }

        if ($params['is_sku'] == 1) { //处理多规格
            if(empty($params['sku'])) $this->error('请设置规格属性');
            $params['sku'] = json_decode($params['sku'], true);
            foreach ($params['sku'] as $key => $val) {
                if (empty($val['name'])) {
                    unset($params['sku'][$key]);
                } else {
                    foreach($val as $k => $v){
                        if($k != 'name'){
                            $params['sku'][$key][$k] = isEmpty($v) ? '' : sprintf('%.2f', $v);
                        }
                    }
                }
            }
            if (empty($params['sku'])) $this->error('请设置规格属性');
            $price = $params['sku'];
        }



        $goods_insert = [

            // 111111111111111111111111111111111111111111111
            'type' => $params['type'], //商品类型
            'name' => trim($params['name']), //商品名称
            'category_id' => $params['category_id'], //商品类目
            'cover' => empty($params['cover']) ? null : $params['cover'], //封面图
            'unit' => empty($params['unit']) ? null : $params['unit'], //商品单位

            // 2222222222222222222222222222222222222222222222
            'is_sku' => $params['is_sku'],  //规格类型
            'sku_name' => empty($params['sku_name']) ? null : $params['sku_name'], //规格名称

            // 33333333333333333333333333333333333333333333333
            'images' => empty($params['images']) ? null : $params['images'], //商品图册
            'detail' => empty($params['detail']) ? null : $params['detail'], //详细内容

            // 4444444444444444444444444444444444444444444444
            'invented_sales' => empty($params['invented_sales']) ? null : $params['invented_sales'], //虚拟销量
            'wholesale' => $this->clWholesale($params['wholesale']), //批发优惠

            // 5555555555555555555555555555555555555555555555
            'attach' => $this->clAttach($params['attach']), //附加选项

            // 6666666666666666666666666666666666666666666666
            'course' => empty($params['course']) ? null : $params['course'], //使用教程
            'pop_content' => empty($params['pop_content']) ? null : $params['pop_content'], //弹窗内容

            // 777777777777777777777777777777777777777777777777777
            'weigh' => empty($params['weigh']) ? 0 : $params['weigh'], //商品排序
            'start_number' => empty($params['start_number']) ? null : $params['start_number'], //起拍数量
            'quota' => empty($params['quota']) ? null : $params['quota'], //每日限购

        ];

//        print_r($price);
//        print_r($goods_insert);die;

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


            $weigh = db::name('goods')->order('weigh desc')->value('weigh');
            $goods_insert['weigh'] = ++$weigh;
            $goods_id = Db::name('goods')->insertGetId($goods_insert);

            db::name('sku')->where(['goods_id' => $goods_id])->delete();

            $sku_insert = [];
            if($params['is_sku'] == 0){ //单规格
                $sku_insert[] = [
                    'goods_id' => $goods_id,
                    'price' => json_encode($price),
                ];
            }
            if($params['is_sku'] == 1){ //多规格
                foreach($price as &$val){
                    $name = $val['name'];
                    unset($val['name']);
                    $sku_insert[] = [
                        'goods_id' => $goods_id,
                        'sku' => $name,
                        'price' => json_encode($val),
                    ];
                }
            }
//            print_r($sku_insert);die;
            $result = db::name('sku')->insertAll($sku_insert);

            Db::commit();
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    public function edit($ids = null) {
        $row = db::name('goods')->where(['id' => $ids])->find();
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            if($row['is_sku'] == 0){
                $row['sku'] = json_encode([['name' => '']]);
                $sku = db::name('sku')->where(['goods_id' => $row['id']])->find();
//                echo '<pre>'; print_r($sku);die;
                $row['price'] = array_merge(json_decode($sku['price'], true), ['id' => $sku['id']]);
            }
            if($row['is_sku'] == 1){
                $sku = db::name('sku')->where(['goods_id' => $row['id']])->select();
                $row['sku'] = [];
                foreach($sku as $val){
                    $price = json_decode($val['price'], true);
                    $price['name'] = $val['sku'];
                    $price['id'] = $val['id'];
                    $row['sku'][] = $price;
                }
                $row['sku'] = json_encode($row['sku']);
            }
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }

        if (empty(trim($params['name']))) $this->error('请输入商品名称');
        if (empty($params['category_id'])) $this->error('请选择商品类目');

        if ($params['is_sku'] == 0) { //处理单规格
            $price = [];
            $price[0] = [
                'id' => $params['sku_id'],
                'crossed_price' => isEmpty($params['crossed_price']) ? '' : sprintf('%.2f', $params['crossed_price']), //原价
                'cost_price' => isEmpty($params['cost_price']) ? '' : sprintf('%.2f', $params['cost_price']), //成本价
                'sale_price' => isEmpty($params['sale_price']) ? '' : sprintf('%.2f', $params['sale_price']), //销售价
            ];
            foreach ($params as $key => $val) {
                if (strstr($key, 'agency_price_')) $price[0][$key] = isEmpty($val) ? '' : sprintf('%.2f', $val);
            }
//            $params['price'] = json_encode($price);
        }

        if ($params['is_sku'] == 1) { //处理多规格
            if(empty($params['sku'])) $this->error('请设置规格属性');
            $params['sku'] = json_decode($params['sku'], true);
            foreach ($params['sku'] as $key => $val) {
                if (empty($val['name'])) {
                    unset($params['sku'][$key]);
                } else {
                    foreach($val as $k => $v){
                        if($k != 'name' && $k != 'id'){
                            $params['sku'][$key][$k] = isEmpty($v) ? '' : sprintf('%.2f', $v);
                        }
                    }
                }
            }
            if (empty($params['sku'])) $this->error('请设置规格属性');
            $price = $params['sku'];
        }
//        print_r($price);die;

//        print_r($params);die;
        $update = [

            // 111111111111111111111111111111111111111111111
            'type' => $params['type'], //商品类型
            'name' => trim($params['name']), //商品名称
            'category_id' => $params['category_id'], //商品类目
            'cover' => empty($params['cover']) ? null : $params['cover'], //封面图
            'unit' => empty($params['unit']) ? null : $params['unit'], //商品单位

            // 2222222222222222222222222222222222222222222222
            'is_sku' => $params['is_sku'],  //规格类型
            'sku_name' => empty($params['sku_name']) ? null : $params['sku_name'], //规格名称

            // 33333333333333333333333333333333333333333333333
            'images' => empty($params['images']) ? null : $params['images'], //商品图册
            'detail' => empty($params['detail']) ? null : $params['detail'], //详细内容

            // 4444444444444444444444444444444444444444444444
            'invented_sales' => empty($params['invented_sales']) ? null : $params['invented_sales'], //虚拟销量
            'wholesale' => $this->clWholesale($params['wholesale']), //批发优惠

            // 5555555555555555555555555555555555555555555555
            'attach' => $this->clAttach($params['attach']), //附加选项

            // 6666666666666666666666666666666666666666666666
            'course' => empty($params['course']) ? null : $params['course'], //使用教程
            'pop_content' => empty($params['pop_content']) ? null : $params['pop_content'], //弹窗内容

            // 777777777777777777777777777777777777777777777777777
            'weigh' => empty($params['weigh']) ? 0 : $params['weigh'], //商品排序
            'start_number' => empty($params['start_number']) ? null : $params['start_number'], //起拍数量
            'quota' => empty($params['quota']) ? null : $params['quota'], //每日限购

        ];
//        print_r($update);die;
//        $params = $this->preExcludeFields($params);

        if($row['is_sku'] != $params['is_sku']){
            if($row['stock'] > 0) $this->error("商品存在库存时无法切换规格类型");
        }
        if($row['type'] != $params['type']){
            if($row['stock'] > 0) $this->error("商品存在库存时无法切换商品类型");
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }

            if($params['is_sku'] == 1){

            }

            $sku = db::name('sku')->where(['goods_id' => $ids])->select();

            $old_sku_id = array_column($sku, 'id');
            $new_sku_id = array_column($price, 'id');
            $sku_diff = array_diff($old_sku_id, $new_sku_id);

//            print_r($sku_diff);die;



            if($sku_diff){
                db::name('sku')->whereIn('id', $sku_diff)->delete();
            }

//            print_r($sku_diff);die;
//            print_r($price); die;
//            print_r($sku);die;

            foreach($price as &$val){
                $sku_name = null;
                if(isset($val['name'])){
                    $sku_name = $val['name'];
                    unset($val['name']);
                }
                if(empty($val['id'])){
//                    echo 1;die;
                    unset($val['id']);
                    $sku_insert = [
                        'goods_id' => $ids,
                        'sku' => $sku_name,
                        'price' => json_encode($val),
                    ];
                    db::name('sku')->insert($sku_insert);
                }else{
                    $s = db::name('sku')->where(['id' => $val['id']])->find();
                    if($s){
                        $sku_id = $val['id'];
                        unset($val['id']);
                        $sku_update = [
                            'sku' => $sku_name,
                            'price' => json_encode($val),
                        ];
                        db::name('sku')->where(['id' => $sku_id])->update($sku_update);
                    }else{
                        $sku_insert = [
                            'goods_id' => $ids,
                            'price' => json_encode($val),
                        ];
                        db::name('sku')->insert($sku_insert);
                    }

                }
            }

            $result = db::name('goods')->where(['id' => $ids])->update($update);
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
        $list = $this->model->with(['sku'])
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit)->toArray();
        $rows = $list['data'];
//        print_r($rows);die;
        foreach($rows as &$val){
            $val['price'] = initPrice($val);
        }

        $result = ['total' => $list['total'], 'rows' => $rows];
        return json($result);
    }




    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}
