<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\dock\Dock;
use app\common\controller\Email;
use app\common\controller\Hm;
use app\shop\controller\Notify;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Config;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend {

    protected $searchFields = ['order_no', 'goods.name', 'order.email', 'user.mobile', 'user.email', 'user.nickname'];

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;

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
     * 同步订单状态
     */
    public function sync_order_status(){
        $params = $this->request->param();
        $dock = db::name('dock')->where(['id' => $params['goods']['dock_id']])->find();
        include ROOT_PATH . '/public/content/dock/' . $dock['type'] . '/' . ucfirst($dock['type']) . '.php';
        $objName = ucfirst($dock['type']) . 'Dock';
        $dockObj = new $objName();
        $result = $dockObj->orderInfo($params, json_decode($dock['info'], true));
//        print_r($result);die;
        if($result['code'] == 400){
            return json(['code' => 400, 'msg' => $result['msg']]);
        }
        $remote_order = $result['data']['order'];
        db::name('order')->where(['id' => $params['id']])->update(['status' => $remote_order['status']]);
        if(!empty($remote_order['cdk'])){
            $insert_sold = [];
            foreach($remote_order['cdk'] as $val){
                $insert_sold[] = [
                    'order_id' => $params['id'],
                    'content' => $val,
                    'create_time' => $this->timestamp
                ];
            }
            db::name('sold')->insertAll($insert_sold);
        }

        return json(['code' => 200, 'msg' => '操作成功']);

    }


    /**
     * 发货
     */
    public function sendgoods(){

        if($this->request->isPost()){
            $params = $this->request->post("row/a");
            $order_id = $this->request->param('ids');
            $insert = [
                'order_id' => $order_id,
                'content' => $params['content'],
                'create_time' => $this->timestamp
            ];
            $where = [
                'order_id' => $order_id
            ];
            $sold = db::name('sold')->where($where)->find();

            if($sold){
                db::name('sold')->where(['id' => $sold['id']])->update($insert);
            }else{
                db::name('sold')->insert($insert);
            }
            $update = [
                'status' => 'success',
            ];
            db::name('order')->where(['id' => $order_id])->update($update);
            $this->success();

        }

        return view();
    }


    /**
     * 补单
     */
    public function supplement(){
        $order_id = $this->request->param('ids');
        $order = db::name('order')->where(['id' => $order_id])->find();
        $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
        $result = Hm::handleOrder($goods, $order, $this->options);
        if($result['code'] == 200){
            doAction('order_notify', $order, $goods);
            $this->success('补单成功');
        }else{
            $this->error($result['msg']);
        }

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

            $status = $this->request->param('status');
            $where_status = [];
            if(!empty($status) && $status != 'all'){
                $where_status['order.status'] = $status;
            }


            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model->with(['user', 'goods'])->where($where)->where($where_status)->order($sort, $order)->paginate($limit);

            $total = $list->total();
            $list = $list->items();

            foreach($list as &$val){
                $val->create_time = date('Y-m-d H:i:s', $val->create_time);
                $val->pay_time = empty($val->pay_time) ? '未支付' : date('Y-m-d H:i:s', $val->pay_time);
                $val->buy_info = json_decode($val->buy_info, true);
            }

            $result = ["total" => $total, "rows" => $list];

            return json($result);
        }
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
                $result = false;
                Db::startTrans();
                try {
                    $kami = $params['kami'];
                    $kami = explode("\r\n", $kami);
                    $timestamp = time();
                    foreach($kami as $val){
                        $temp = trim($val, " ");
                        if(!empty($temp)){
                            $sold = db::name('sold')->where(['order_id' => $this->request->param('ids'), 'content' => $temp])->find();
                            if(!$sold){
                                $insert = [
                                    'order_id' => $this->request->param('ids'),
                                    'content' => $temp,
                                    'create_time' => $timestamp
                                ];
                                db::name('sold')->insert($insert);
                            }
                        }
                    }
                    // print_r($params);die;

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

        $goods = db::name('goods')->where(['id' => $row->goods_id])->find();
        $sold = db::name('sold')->where(['order_id' => $row->id])->select();
        $kami = "";
        foreach($sold as $val){
            $kami .= $val['content'] . "\r\n";
        }
        $row->kami = $kami;
        $this->assign([
            'goods' => $goods,
            'row' => $row,
        ]);

        // echo '<pre>'; print_r($row);die;
        return $this->view->fetch();
    }

}
