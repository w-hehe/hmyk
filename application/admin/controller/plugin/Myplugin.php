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
class Myplugin extends Backend
{
    protected $model = null;
    protected $noNeedRight = ['get_table_list'];

    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 插件配置
     */
    public function setting(){
        $params = $this->request->param();
        $plugin_name = $params['plugin_name'];
        if($this->request->isPost()){
            if(isset($params['dialog'])) unset($params['dialog']);
            if(isset($params['plugin_name'])) unset($params['plugin_name']);
            if(isset($params['ids'])) unset($params['ids']);

            if(isset($params['eq'])){ //开启设备
                $params['eq'] = array_filter($params['eq']);
                if(empty($params['eq'])) $this->error('您必须开启一个设备类型');
            }

            $data = "<?php \n" . "return " . var_export_short($params) . "\n" . "?>";

            $path = ROOT_PATH . 'content' . '/' . $plugin_name . '/' . 'setting.php';

            $res = file_put_contents($path, $data);

            if($res){
                $this->success('配置成功');
            }else{
                $this->error('配置失败');
            }
        }
        $pluginPath = ROOT_PATH . 'content' . '/' . $params['plugin_name'] . '/' . 'setting.html';
        $path = ROOT_PATH . 'content' . '/' . $plugin_name . '/' . 'setting.php';
        $row = file_exists($path) ? include_once $path : [];

//        echo '<pre>'; print_r($row);die;
        $this->assign([
            'row' => $row
        ]);
        return view($pluginPath);
//        include_once

    }

    /**
     * 插件列表
     */
    public function index() {

        $pluginFiles = [];
        $pluginPath = ROOT_PATH . 'content';
        $pluginDir = @dir($pluginPath);

        if ($pluginDir){
            while (($file = $pluginDir->read()) !== false) {
                if (preg_match('|^\.+$|', $file)){
                    continue;
                }
                if (is_dir($pluginPath . '/' . $file)){
                    $pluginsSubDir = @ dir($pluginPath . '/' . $file);
                    if ($pluginsSubDir){
                        while (($subFile = $pluginsSubDir->read()) !== false) {
                            if (preg_match('|^\.+$|', $subFile)){
                                continue;
                            }
                            if ($subFile == 'info.php'){
                                $pluginFiles[] = $file;
                            }
                        }
                    }
                }
            }
        }

        $active_plugins = Db::name('options')->where(['name' => 'active_plugin'])->value('value');
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);

        $list = [];
//        print_r()

        $market = json_decode(hmCurl(API . 'api/plugin/index'), true);
        $market = $market['rows'];

        foreach($pluginFiles as $key => $val) {
            $info = require_once $pluginPath . '/' . $val . '/' . 'info.php';
            $info['status'] = in_array($val, $active_plugins) ? 'enable' : 'disable';
            $info['setting'] = file_exists($pluginPath . '/' . $val . '/' . 'setting.html') ? true : false;
            $info['upgrade'] = false;
            foreach($market as $v){
                if($info['english_name'] == $v['english_name']){
                    $info['upgrade'] = $info['version'] < $v['version'] ? true : false;
                    $info['id'] = $v['id'];
                    $info['demo_url'] = $v['demo_url'];
                }
            }

            $list[] = $info;

            /*
            $pluginData = $this->getPluginData($pluginFile, $key + 1);

            if (empty($pluginData['name'])){
                continue;
            }

            $pluginData['status'] = in_array($pluginFile, $active_plugins) ? 'enable' : 'disable';
            $pluginData['install'] = true;
            $pluginData['upgrade'] = false;*/
        }


        $data = [
            'rows' => $list,
            'total' => 0
        ];
        return json($data);
    }

    /**
     * 卸载插件
     */
    public function delp(){
        $params = $this->request->param();
        $active_plugins = Db::name('options')->where(['name' => 'active_plugin'])->value('value');
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);
        $is_active = in_array($params['plugin_name'], $active_plugins);
        if($is_active){
            $this->error('您需要禁用插件后才可以卸载');
        }
        if(empty($params['plugin_name'])){
            $this->error('插件未找到');
        }
        $plugin_path = ROOT_PATH . 'content/' . $params['plugin_name'];
        rmdirs($plugin_path);
        $this->success();
    }


    /**
     * 改变插件状态
     */
    public function status(){

        $params = $this->request->param();
        $active_plugins = Db::name('options')->where(['name' => 'active_plugin'])->value('value');
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);
        $is_active = in_array($params['ids'], $active_plugins);

        if($params['cmd'] == 'disable'){ // 禁用状态改为启用
            if(!$is_active){
                $active_plugins[] = $params['ids'];
                db::name('options')->where(['name' => 'active_plugin'])->update(['value' => serialize($active_plugins)]);
            }
        }
        if($params['cmd'] == 'enable'){ //启用状态改为禁用
            foreach($active_plugins as $key => $val) {
                if ($params['ids'] == $val){
                    unset($active_plugins[$key]);
                    db::name('options')->where(['name' => 'active_plugin'])->update(['value' => serialize($active_plugins)]);
                }
            }
        }
        $this->success();
    }



}
