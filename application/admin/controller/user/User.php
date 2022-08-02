<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\controller\Enum;
use app\common\library\Auth;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend {

    protected $relationSearch = true;
    protected $searchFields = 'id,mobile,email,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = model('User');
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
            $list = $this->model->where($where)->where("tourist is null")->order($sort, $order)->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }

            $rows = $list->items();

            $grade = db::name('user_grade')->select();

            foreach($rows as $val){
                if($val['agent'] > 0){
                    foreach($grade as $v){
                        if($val['agent'] == $v['id']){
                            $val['agent'] = $v['name'];
                        }
                    }
                }else{
                    $val['agent'] = '普通用户';
                }
            }

            $result = ["total" => $list->total(), "rows" => $rows];

            return json($result);
        }
        return $this->view->fetch();

    }

    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function money($ids = null) {

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $result = false;
                Db::startTrans();
                try {
                    if($params['type'] == 'plus'){
                        $result = db::name('user')->where(['id' => $ids])->setInc('money', $params['money']);
                    }
                    if($params['type'] == 'reduce'){
                        $result = db::name('user')->where(['id' => $ids])->setDec('money', $params['money']);
                    }
                    if($params['type'] == 'reset'){
                        $result = db::name('user')->where(['id' => $ids])->update(['money' => $params['money']]);
                    }
                    Db::commit();
                } catch (\Exception $e) {
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
                    if(empty($params['money'])){
                        unset($params['money']);
                    }else{
                        $user = Db::name('user')->where(['id' => $params['id']])->find();
                        if($user['money'] + $params['money'] < 0){
                            throw new Exception("用户余额不足, 无法扣除！");
                        }else{
                            Db::name('user')->where(['id' => $params['id']])->setInc('money', $params['money']);
                            $insert = [
                                'uid' => $params['id'],
                                'createtime' => time(),
                                'type' => 'system'
                            ];
                            if($params['money'] > 0){ //增加
                                $insert['description'] = '系统充值';
                                $insert['value'] = '+' . sprintf("%.2f", $params['money']);
                            }else if($params['money'] < 0){ //扣除
                                $insert['description'] = '系统扣除';
                                $insert['value'] = sprintf("%.2f", $params['money']);
                            }
                            db::name('money_bill')->insert($insert);
                            unset($params['money']);
                        }
                    }
                    if(empty($params['score'])){
                        unset($params['score']);
                    }else{
                        $user = Db::name('user')->where(['id' => $params['id']])->find();
                        if($user['score'] + $params['score'] < 0){
                            throw new Exception("用户积分不足, 无法扣除！");
                        }else{
                            Db::name('user')->where(['id' => $params['id']])->setInc('score', $params['score']);
                            unset($params['score']);
                        }
                    }
//                    print_r($params);die;
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
        $this->view->assign("row", $row);
        $grade = db::name('user_grade')->select();
        $this->assign([
            'grade' => $grade
        ]);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "") {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        db::name('user')->whereIn('id', explode(',', $ids))->delete();
        $this->success();
    }

}
