<?php

namespace app\admin\controller\plugin;

use app\common\controller\Backend;
use fast\Http;
use think\addons\AddonException;
use think\addons\Service;
use think\Cache;
use think\Config;
use think\Db;
use think\Exception;

/**
 * 插件管理
 *
 * @icon   fa fa-cube
 * @remark 可在线安装、卸载、禁用、启用、配置、升级插件，插件升级前请做好备份。
 */
class Market extends Backend {
    protected $model = null;
    protected $noNeedRight = ['get_table_list'];

    public function _initialize() {
        parent::_initialize();

    }


    public function login() {
        $params = $this->request->param();

        return hmCurl(API . 'api/user/login', http_build_query($params), 1);
    }

    public function user() {
        $params = $this->request->param();
        return hmCurl(API . 'api/user/index', http_build_query($params), 1);
    }

    /**
     * 插件列表
     */
    public function index() {
        if ($this->request->isAjax()) {

            $user_id = $this->uid;

            $data = [
                'user_id' => $user_id
            ];

            $result = json_decode(hmCurl(API . 'api/plugin/index', http_build_query($data)), true);

            $pluginFiles = [];
            $pluginPath = ROOT_PATH . 'content';
            $pluginDir = @dir($pluginPath);

            if ($pluginDir) {
                while (($file = $pluginDir->read()) !== false) {
                    if (preg_match('|^\.+$|', $file)) {
                        continue;
                    }
                    if (is_dir($pluginPath . '/' . $file)) {
                        $pluginsSubDir = @ dir($pluginPath . '/' . $file);
                        if ($pluginsSubDir) {
                            while (($subFile = $pluginsSubDir->read()) !== false) {
                                if (preg_match('|^\.+$|', $subFile)) {
                                    continue;
                                }
                                if ($subFile == 'info.php') {
                                    $pluginFiles[] = $file;
                                }
                            }
                        }
                    }
                }
            }


            foreach ($result['rows'] as &$val) {
                $val['exist'] = in_array($val['english_name'], $pluginFiles) ? true : false;
            }


            return $result;
        }
        $this->assignconfig(['api_url' => API]);
        return $this->view->fetch();
    }

    /**
     * 安装
     */
    public function install() {


        $user_id = $this->uid;


        $uid = $this->request->post("uid");
        $token = $this->request->post("token");
        $plugin_id = $this->request->post('plugin_id');
        $upgrade = $this->request->post('upgrade');
        $version = db::name('options')->where(['name' => 'version'])->value('value');
        $data = [
            'uid' => $uid,
            'token' => $token,
            'plugin_id' => $plugin_id,
            'host' => $_SERVER['HTTP_HOST'],
            'version' => $version,
            'merchant_id' => $user_id
        ];


        $api = API . 'api/plugin/install';

        $result = hmCurl($api, $data, 1);


        $result = json_decode($result, true);

        if ($result['code'] != 200) {
            return json($result);
        }

        $data = $result['data'];
        if (empty($data['file'])) {
            return json(['code' => 400, 'msg' => '该插件正在维护, 请稍后下载']);
        }

        $dir = ROOT_PATH . "runtime/plugin/"; //插件本地临时存储路径
        if (!file_exists($dir)) { //新建文件夹用来放置下载的更新包
            mkdir($dir, 0777, true);
        }
        $filename = $data['english_name'] . '.zip';
        /**
         * 下载插件压缩包到本地并赋值文件路径变量
         */
        $file_url = API . ltrim($data['file'], '/');
        $path = file_exists($dir . $filename) ? $dir . $filename : $this->download_file($file_url, $dir, $filename);
        if ($path == 'default_socket_timeout') {
            return json(['code' => 400, 'msg' => '下载连接超时，网络不太好哦~']);
        }
        if (!class_exists("\ZipArchive")) return json(['code' => 400, 'msg' => '您的PHP缺少ZipArchive扩展，你可以尝试安装编译版的PHP解决这个问题！']);
        $zip = new \ZipArchive();

        //打开压缩包
        if ($zip->open($path) === true) {
            if ($upgrade) { //升级
                $toPath = ROOT_PATH . 'runtime/plugin/' . $data['english_name'];
                try {
                    //解压文件到toPath路径下
                    $zip->extractTo($toPath);
                    $zip->close();
                    unlink($path);
                } catch (\Exception $e) {
                    return json(['code' => 400, 'msg' => "没有该目录[" . $toPath . "]的写入权限"]);
                }
                if (file_exists($toPath . '/' . 'setting.php')) {
                    unlink($toPath . '/' . 'setting.php');
                }
                copydirs($toPath, ROOT_PATH . 'content/' . $data['english_name']);
                rmdirs($toPath);
                if(file_exists(ROOT_PATH . 'content/' . $data['english_name'] . '/template')){
                    copydirs(ROOT_PATH . 'content/' . $data['english_name'] . '/template', ROOT_PATH . 'public/template');
                }

                $this->success('升级成功');
            } else { //安装
                $toPath = ROOT_PATH . 'content/' . $data['english_name'];
                try {

                    //解压文件到toPath路径下
                    $zip->extractTo($toPath);
                    $zip->close();
                    unlink($path);
                } catch (\Exception $e) {
                    return json(['code' => 400, 'msg' => "没有该目录[" . $toPath . "]的写入权限"]);
                }

                if(file_exists(ROOT_PATH . 'content/' . $data['english_name'] . '/template')){
                    copydirs(ROOT_PATH . 'content/' . $data['english_name'] . '/template', ROOT_PATH . 'public/template');
                }


                $this->success('安装成功');
            }


        } else {
            unlink($path);
            return json(['code' => 400, 'msg' => "压缩包解压失败，请清空缓存后重试！"]);
        }
    }

    /**
     * 远程下载文件到本地
     */
    public function download_file($url, $dir, $filename = '') {
        if (empty($url)) {
            return false;
        }
        $ext = strrchr($url, '.');
        $dir = realpath($dir);
        //目录+文件
        $filename = (empty($filename) ? '/' . time() . $ext : '/' . $filename);
        $filename = $dir . $filename;
        //开始捕捉
        ob_start();
        try {
            readfile($url);
        } catch (\Exception $e) {
            return 'default_socket_timeout';
        }
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        $fp2 = fopen($filename, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return $filename;
    }

    /**
     * 检测
     */
    public function isbuy() {
        $plugin_id = $this->request->post("plugin_id");
        $uid = $this->request->post("uid");
        $token = $this->request->post("token");
        $data = [
            'uid' => $uid,
            'token' => $token,
            'plugin_id' => $plugin_id,
            'host' => $_SERVER['HTTP_HOST'],
            'out_trade_no' => $this->request->post('out_trade_no')
        ];
        $result = json_decode(hmCurl(API . 'api/plugin/isbuy', $data, true), true);
        return json($result);
    }

    /**
     * 配置
     */
    public function config($name = null) {
        $name = $name ? $name : $this->request->get("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        $info = get_addon_info($name);
        $config = get_addon_fullconfig($name);
        if (!$info) {
            $this->error(__('Addon not exists'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$v['name']])) {
                        if ($v['type'] == 'array') {
                            $params[$v['name']] = is_array($params[$v['name']]) ? $params[$v['name']] : (array)json_decode($params[$v['name']], true);
                            $value = $params[$v['name']];
                        } else {
                            $value = is_array($params[$v['name']]) ? implode(',', $params[$v['name']]) : $params[$v['name']];
                        }
                        $v['value'] = $value;
                    }
                }
                try {
                    $addon = get_addon_instance($name);
                    //插件自定义配置实现逻辑
                    if (method_exists($addon, 'config')) {
                        $addon->config($name, $config);
                    } else {
                        //更新配置文件
                        set_addon_fullconfig($name, $config);
                        Service::refresh();
                    }
                } catch (Exception $e) {
                    $this->error(__($e->getMessage()));
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $tips = [];
        $groupList = [];
        foreach ($config as $index => &$item) {
            //如果有设置分组
            if (isset($item['group']) && $item['group']) {
                if (!in_array($item['group'], $groupList)) {
                    $groupList["custom" . (count($groupList) + 1)] = $item['group'];
                }
            }
            if ($item['name'] == '__tips__') {
                $tips = $item;
                unset($config[$index]);
            }
        }
        $groupList['other'] = '其它';
        $this->view->assign("groupList", $groupList);
        $this->view->assign("addon", ['info' => $info, 'config' => $config, 'tips' => $tips]);
        $configFile = ADDON_PATH . $name . DS . 'config.html';
        $viewFile = is_file($configFile) ? $configFile : '';
        return $this->view->fetch($viewFile);
    }


    /**
     * 卸载
     */
    public function uninstall() {
        $name = $this->request->post("name");
        $force = (int)$this->request->post("force");
        $droptables = (int)$this->request->post("droptables");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        //只有开启调试且为超级管理员才允许删除相关数据库
        $tables = [];
        if ($droptables && Config::get("app_debug") && $this->auth->isSuperAdmin()) {
            $tables = get_addon_tables($name);
        }
        try {
            Service::uninstall($name, $force);
            if ($tables) {
                $prefix = Config::get('database.prefix');
                //删除插件关联表
                foreach ($tables as $index => $table) {
                    //忽略非插件标识的表名
                    if (!preg_match("/^{$prefix}{$name}/", $table)) {
                        continue;
                    }
                    Db::execute("DROP TABLE IF EXISTS `{$table}`");
                }
            }
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Uninstall successful'));
    }

    /**
     * 禁用启用
     */
    public function state() {
        $name = $this->request->post("name");
        $action = $this->request->post("action");
        $force = (int)$this->request->post("force");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        try {
            $action = $action == 'enable' ? $action : 'disable';
            //调用启用、禁用的方法
            Service::$action($name, $force);
            Cache::rm('__menu__');
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'));
    }

    /**
     * 本地上传
     */
    public function local() {
        Config::set('default_return_type', 'json');

        $info = [];
        $file = $this->request->file('file');
        try {
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $faversion = $this->request->post("faversion");
            if (!$uid || !$token) {
                throw new Exception(__('Please login and try to install'));
            }
            $extend = [
                'uid' => $uid,
                'token' => $token,
                'faversion' => $faversion
            ];
            $info = Service::local($file, $extend);
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Offline installed tips'), '', ['addon' => $info]);
    }

    /**
     * 更新插件
     */
    public function upgrade() {
        $name = $this->request->post("name");
        $addonTmpDir = RUNTIME_PATH . 'addons' . DS;
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }

        $info = [];
        try {
            $info = get_addon_info($name);
            $uid = $this->request->post("uid");
            $token = $this->request->post("token");
            $version = $this->request->post("version");
            $faversion = $this->request->post("faversion");
            $extend = [
                'uid' => $uid,
                'token' => $token,
                'version' => $version,
                'oldversion' => $info['version'] ?? '',
                'faversion' => $faversion
            ];
            //调用更新的方法
            $info = Service::upgrade($name, $extend);
            Cache::rm('__menu__');
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'), '', ['addon' => $info]);
    }

    /**
     * 测试数据
     */
    public function testdata() {
        $name = $this->request->post("name");
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }

        try {
            Service::importsql($name, 'testdata.sql');
        } catch (AddonException $e) {
            $this->result($e->getData(), $e->getCode(), __($e->getMessage()));
        } catch (Exception $e) {
            $this->error(__($e->getMessage()), $e->getCode());
        }
        $this->success(__('Import successful'), '');
    }

    /**
     * 已装插件
     */
    public function downloaded() {
        $offset = (int)$this->request->get("offset");
        $limit = (int)$this->request->get("limit");
        $filter = $this->request->get("filter");
        $search = $this->request->get("search");
        $search = htmlspecialchars(strip_tags($search));
        $onlineaddons = $this->getAddonList();
        $filter = (array)json_decode($filter, true);
        $addons = get_addon_list();
        $list = [];
        foreach ($addons as $k => $v) {
            if ($search && stripos($v['name'], $search) === false && stripos($v['title'], $search) === false && stripos($v['intro'], $search) === false) {
                continue;
            }

            if (isset($onlineaddons[$v['name']])) {
                $v = array_merge($v, $onlineaddons[$v['name']]);
                $v['price'] = '-';
            } else {
                $v['category_id'] = 0;
                $v['flag'] = '';
                $v['banner'] = '';
                $v['image'] = '';
                $v['demourl'] = '';
                $v['price'] = __('None');
                $v['screenshots'] = [];
                $v['releaselist'] = [];
                $v['url'] = addon_url($v['name']);
                $v['url'] = str_replace($this->request->server('SCRIPT_NAME'), '', $v['url']);
            }
            $v['createtime'] = filemtime(ADDON_PATH . $v['name']);
            if ($filter && isset($filter['category_id']) && is_numeric($filter['category_id']) && $filter['category_id'] != $v['category_id']) {
                continue;
            }
            $list[] = $v;
        }
        $total = count($list);
        if ($limit) {
            $list = array_slice($list, $offset, $limit);
        }
        $result = array("total" => $total, "rows" => $list);

        $callback = $this->request->get('callback') ? "jsonp" : "json";
        return $callback($result);
    }


    /**
     * 刷新授权
     */
    public function authorization() {
        $params = [
            'uid' => $this->request->post('uid'),
            'token' => $this->request->post('token'),
            'faversion' => $this->request->post('faversion'),
        ];
        try {
            Service::authorization($params);
        } catch (Exception $e) {
            $this->error(__($e->getMessage()));
        }
        $this->success(__('Operate successful'));
    }

    /**
     * 获取插件相关表
     */
    public function get_table_list() {
        $name = $this->request->post("name");
        if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
            $this->error(__('Addon name incorrect'));
        }
        $tables = get_addon_tables($name);
        $prefix = Config::get('database.prefix');
        foreach ($tables as $index => $table) {
            //忽略非插件标识的表名
            if (!preg_match("/^{$prefix}{$name}/", $table)) {
                unset($tables[$index]);
            }
        }
        $tables = array_values($tables);
        $this->success('', null, ['tables' => $tables]);
    }

    protected function getAddonList() {
        $onlineaddons = Cache::get("onlineaddons");
        if (!is_array($onlineaddons) && config('fastadmin.api_url')) {
            $onlineaddons = [];
            $params = [
                'uid' => $this->request->post('uid'),
                'token' => $this->request->post('token'),
                'version' => config('fastadmin.version'),
                'faversion' => config('fastadmin.version'),
            ];
            $json = [];
            try {
                $json = Service::addons($params);
            } catch (\Exception $e) {

            }
            $rows = isset($json['rows']) ? $json['rows'] : [];
            foreach ($rows as $index => $row) {
                $onlineaddons[$row['name']] = $row;
            }
            Cache::set("onlineaddons", $onlineaddons, 600);
        }
        return $onlineaddons;
    }

}
