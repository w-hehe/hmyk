<?php

namespace app\admin\controller\finance\order;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 商品订单管理
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend {

    protected $relationSearch = true;
    protected $searchFields = ['out_trade_no', 'goods_name', 'user.username'];

    /**
     * Goods模型对象
     * @var \app\admin\model\finance\order\Goods
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\finance\order\Goods;

        $options = [];
        $optionsResult = db::name('options')->select();
        foreach($optionsResult as $val){
            $options[$val['name']] = $val['value'];
        }

        $active_plugins = $options['active_plugin'];
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);
        if ($active_plugins && is_array($active_plugins)) {
            foreach($active_plugins as $plugin) {
                $info = include_once(ROOT_PATH . 'content/' . $plugin . '/info.php');
                if($info['type'] == 'basic'){
                    include_once(ROOT_PATH . 'content/' . $plugin . '/' . $plugin . '.php');
                }
            }
        }

    }


    public function supplement(){
        $order = db::name('goods_order')->where(['id' => $this->request->param('ids')])->find();
        $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
        db::startTrans();
        try {
            $result = $this->notifyGoodsSuccess($goods, $order['out_trade_no']);
            db::commit();
        }catch (\Exception $e){
            db::rollback();
            $this->error($e->getMessage() . '---' . $e->getLine());
            $this->error($e->getMessage());
        }
        if($result){
            $this->success('补单成功');
        }else{
            $this->error('补单失败');
        }
    }

    /**
     * 执行购买商品的回调操作
     * 1，写入发货表，更新库存表
     * 2，更新商品库存字段
     * 3，更新商品销量字段
     * 4，更新订单状态
     * 5，返佣给上级
     */
    protected function notifyGoodsSuccess($goods, $out_trade_no) {
        $order = db::name('goods_order')->where(['out_trade_no' => $out_trade_no])->find();
        db::name('goods_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp]);
        if ($goods['type'] == 'alone') { //更新库存表并写入发货表
            $stock = db::name('stock')->field('id, content')->where(['sku_id' => $order['sku_id']])->whereNull('sale_time')->limit($order['goods_num'])->select();
            $stock_ids = array_column($stock, 'id');
            db::name('stock')->whereIn('id', $stock_ids)->update(['sale_time' => $this->timestamp]); //更新库存表
            $deliver = [];
            foreach ($stock as $val) {
                $deliver[] = [
                    'content' => $val['content'],
                    'order_id' => $order['id'],
                    'create_time' => $this->timestamp
                ];
            }
            db::name('deliver')->insertAll($deliver);
        }
        if ($goods['type'] == 'fixed') { //更新库存表并写入发货表
            $stock = db::name('stock')->where(['sku_id' => $order['sku_id']])->limit($order['goods_num'])->find();
            //            print_r($stock);die;
            $deliver = [];
            for ($i = 0; $i < $order['goods_num']; $i++) {
                $deliver[] = [
                    'content' => $stock['content'],
                    'create_time' => $this->timestamp,
                    'order_id' => $order['id']
                ];
            }
            db::name('deliver')->insertAll($deliver); //写入发货表
            db::name('stock')->where(['id' => $stock['id']])->setDec('num', $order['goods_num']); //更新库存表
        }
        if ($goods['type'] == 'invented') {
            if ($goods['is_sku'] == 0) {
                $stock = db::name('stock')->where(['sku_id' => $goods['sku'][0]['id']])->find();
            }
            if ($goods['is_sku'] == 1) {
                $stock = db::name('stock')->where(['sku_id' => $order['sku_id']])->find();
            }
            db::name('stock')->where(['id' => $stock['id']])->setDec('num', $order['goods_num']); //更新库存表
        }
        db::name('goods')->where(['id' => $goods['id']])->setDec('stock', $order['goods_num']); //更新商品库存字段
        db::name('goods')->where(['id' => $goods['id']])->setInc('sales', $order['goods_num']); //更新商品销量字段
        db::name('goods_order')->where(['id' => $order['id']])->update(['pay_time' => $this->timestamp]); //更新订单状态
        db::name('sku')->where(['id' => $order['sku_id']])->setDec('stock', $order['goods_num']); //更新库存表

        // 计算该笔订单的利润
        $translate = $order['money'] - ($order['goods_cost'] * $order['goods_num']);
        if ($translate <= 0) {
            return true;
        }

        /**
         * 返佣给子站长
         */
        if (!$this->is_main) { //如果是子站下单
            // 获取返佣比例
            $rebate = db::name('merchant_grade')->where(['id' => $this->merchant['grade_id']])->value('rebate');
            // 计算佣金
            $commission = $translate * ($rebate / 100);
            // 记录分站长账单
            $merchant_user = db::name('user')->where(['id' => $this->merchant['user_id']])->find();
            $bill_insert = [
                'create_time' => $this->timestamp,
                'user_id' => $this->merchant['user_id'],
                'before' => $merchant_user['money'],
                'after' => $merchant_user['money'] + $commission,
                'value' => $commission,
                'content' => '子站用户购买商品返佣'
            ];
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $this->merchant['user_id']])->setInc('money', $commission);
        }

        /**
         * 返佣给上级
         * 1，获取返佣比例
         * 2，返佣给上级
         * 3，记录余额账单
         */
        $user = db::name('user')->where(['id' => $order['user_id']])->find();
        //给上级返佣、记录余额账单
        $bill_insert = [
            'create_time' => $this->timestamp,
        ];
        if ($user['p1'] > 0 && $this->options['rebeat_1'] > 0) {
            $commission = $translate * ($this->options['rebeat_1'] / 100);
            $puser = db::name('user')->where(['id' => $user['p1']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '一级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p1']])->setInc('money', $commission);
        }
        if ($user['p2'] > 0 && $this->options['rebeat_2'] > 0) {
            $commission = $translate * ($this->options['rebeat_2'] / 100); //佣金
            $puser = db::name('user')->where(['id' => $user['p2']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '二级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p2']])->setInc('money', $commission);
        }
        if ($user['p3'] > 0 && $this->options['rebeat_3'] > 0) {
            $commission = $translate * ($this->options['rebeat_3'] / 100); //佣金
            $puser = db::name('user')->where(['id' => $user['p3']])->find();
            $bill_insert['user_id'] = $puser['id'];
            $bill_insert['before'] = $puser['money'];
            $bill_insert['after'] = $puser['money'] + $commission;
            $bill_insert['value'] = $commission;
            $bill_insert['content'] = '三级推广好友购买商品返佣';
            db::name('bill')->insert($bill_insert);
            db::name('user')->where(['id' => $user['p3']])->setInc('money', $commission);
        }

        return true;
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
        $list = $this->model->with(['user'])->where($where)->order($sort, $order)->paginate($limit);
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function detail($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            
            $row['attach'] = json_decode($row['attach'], true);

            $deliver = db::name('deliver')->where(['order_id' => $ids])->select();

            $this->assign([
                'deliver' => $deliver
            ]);
            
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
        } catch (ValidateException | PDOException | Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function deliver($ids = null) {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $deliver = db::name('deliver')->where(['order_id' => $ids])->find();
            $this->view->assign('deliver', $deliver);
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
            $fd = db::name('deliver')->where(['order_id' => $ids])->find();
            $deliver = [];
            if($fd){
                $result = db::name('deliver')->where(['id' => $fd['id']])->update(['content' => $params['content']]);
                $deliver[] = ['content' => $params['content']];
            }else{
                $insert = [
                    'order_id' => $ids,
                    'content' => $params['content'],
                    'create_time' => time()
                ];
                $result = db::name('deliver')->insert($insert);
                $deliver[] = $insert;

            }
            doAction('send_goods', $row, $deliver);
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
