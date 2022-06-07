<?php

namespace app\admin\controller\docking;

use app\common\controller\Backend;
use app\common\controller\Enum;
use fast\Http;
use think\Cache;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use app\common\controller\dock\Dock;

/**
 * 对接站点管理
 *
 * @icon fa fa-circle-o
 */
class DockingSite extends Backend {

    /**
     * DockingSite模型对象
     * @var \app\admin\model\docking\DockingSite
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\docking\DockingSite;



        $where = [
            'type' => 'goods',
        ];
        $category = db::name('category')->where($where)->select();
        $attach = db::name('attach')->select();
        $this->assign([
            'category' => $category, 'attach' => $attach
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

    public function getDockType() {
        $dock = [];
        $dockFiles = [];
        $dockPath = ROOT_PATH . 'public/content/dock';
        $dockDir = @dir($dockPath);

        if ($dockDir) {
            while (($file = $dockDir->read()) !== false) {
                if (preg_match('|^\.+$|', $file)) continue;
                if (is_dir($dockPath . '/' . $file)) {
                    $docksSubDir = @ dir($dockPath . '/' . $file);
                    if ($docksSubDir) {
                        while (($subFile = $docksSubDir->read()) !== false) {
                            if (preg_match('|^\.+$|', $subFile)) continue;
                            if ($subFile == 'info.php') $dockFiles[] = "$file/$subFile";
                        }
                    }
                }
            }
        }
//        echo '<pre>'; print_r($dockFiles);die;
        foreach ($dockFiles as $key => $val) {
            $dockPath = ROOT_PATH . 'public/content/dock/' . $val;
            $dock[$key] = include_once($dockPath);
            $pluginName = substr($val, 0, -9);
            $dock[$key]['config'] = ROOT_PATH . 'public/content/dock/' . $pluginName . '/config.php';
        }

        return $dock;
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

            $list = $this->model->where($where)->order($sort, $order)->paginate($limit);

            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }

        $poster = $this->getPoster();
        $this->assign([
            'poster' => $poster,
        ]);

//        echo '<pre>'; print_r($poster);die;

        return $this->view->fetch();
    }

    /**
     * 获取对接站点区的广告位
     */
    public function getPoster() {
        if (Cache::has('docking_poster')) {
            $poster = Cache::get('docking_poster');
        } else {
            $poster_api = HMURL . "api/hmyk/docking_poster";
//            echo $poster_api;die;
            $poster = json_decode(hmCurl($poster_api, false, 0, false, 5), true);
            if ($poster && $poster['code'] == 200) {
                Cache::set('docking_poster', $poster['data'], 3600 * 12);
                $poster = $poster['data'];
            }

        }
        return $poster;
    }


    /**
     * 商品列表
     */
    public function goods_list() {


        $site = Dock::getSiteInfo($this->request->param('ids'));

        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {

            $list = Dock::get_goods_list($site);

            if (!$list) {
                return $this->error('目标站点请求超时');
            }

            $total = count($list);

            $result = ["total" => $total, "rows" => $list];
            return json($result);
        }

        $this->assign([
            'id' => $site['id'], 'docking_site' => $site,
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
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            // print_r($list);die;

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {

                    $where = [
                        'deletetime' => null, 'site_id' => $v->id,
                    ];

                    $goods = db::name('goods')->where($where)->field('id')->find();
                    if ($goods) {
                        throw new Exception("您必须先删除【{$v->domain}】站点下对接的商品");
                    }

                    $count += $v->delete();
                }

                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
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
                    $params = array_filter($params);
                    foreach($params as &$val){
                        $val = trim($val, ' ');
                    }
                    $dock_type = $params['dock_type'];
                    include ROOT_PATH . '/public/content/dock/' . $dock_type . '/' . ucfirst($dock_type) . '.php';
                    $objName = ucfirst($dock_type) . 'Dock';
                    $dockObj = new $objName();
                    $info = $dockObj->verify($params);

                    $insert = [
                        'type' => $dock_type,
                        'info' => $info,
                        'remark' => empty($params['remark']) ? '' : $params['remark']
                    ];
                    $result = $this->model->allowField(true)->save($insert);
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

//        echo '<pre>'; print_r($this->getDockType());die;
        $this->assign([
            'dockType' => $this->getDockType()
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

                    $params = array_filter($params);
                    foreach($params as &$val){
                        $val = trim($val, ' ');
                    }
                    $dock_type = $params['dock_type'];
                    include ROOT_PATH . '/public/content/dock/' . $dock_type . '/' . ucfirst($dock_type) . '.php';
                    $objName = ucfirst($dock_type) . 'Dock';
                    $dockObj = new $objName();
                    $info = $dockObj->verify($params);

                    $update = [
                        'type' => $dock_type,
                        'info' => $info,
                        'remark' => empty($params['remark']) ? '' : $params['remark']
                    ];


                    $result = $row->allowField(true)->save($update);
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
        $info = json_decode($row->info, true);

        $row->info = $info;

//         echo '<pre>'; print_r($row);die;

        $this->assign([
            'row' => $row,
            'info' => $info,
            'dockType' => $this->getDockType()
        ]);

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
