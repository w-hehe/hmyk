<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Http;
use think\Cache;
use think\Db;
use fast\QRcode;



/**
 *
 *
 * @icon fa fa-circle-o
 */
class Plugin extends Backend {

    /**
     * Plugin模型对象
     * @var \app\admin\model\Plugin
     */
    protected $model = null;

    public function _initialize(){
        parent::_initialize();
        $this->model = new \app\admin\model\Plugin;

    }

    public function import(){
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 登录官方站点
     */
    public function login(){
        $pm = $this->request->param();
        $result = hmCurl(YS . '/login', $pm, 1);
        if(!$result){
            return json(['code' => 400, 'msg' => '登录请求失败']);
        }
        echo $result; die;
    }

    /**
     * 插件设置
     */
    public function setting(){
        $plugin = $this->request->param('plugin_name');

        $plugin_path = ROOT_PATH . "public/content/plugin/{$plugin}/";

        if($this->request->isPost()){
            $post = $this->request->post("row/a");
            $post = json_encode($post);
            file_put_contents("{$plugin_path}{$plugin}_setting.json", $post);
            $this->success('操作成功');
        }

        $info = file_get_contents("{$plugin_path}{$plugin}_setting.json");
        $info = json_decode($info, true);
        $this->assign([
            'info' => $info
        ]);
        return view("{$plugin_path}{$plugin}_setting.php");
    }

    /**
     * 更新授权
     */
    public function update_auth(){
        $question = $this->request->param('question');
        $answer = trim($this->request->param('answer'), " ");
        $plugin_id = $this->request->param('plugin_id');
        $host = $this->request->param('host');
        $old_host = $this->request->param('old_host');
        $data = [
            'plugin_id' => $plugin_id,
            'host' => $host,
            'old_host' => $old_host,
            'question' => $question,
            'answer' => $answer
        ];
        $result = hmCurl(HMURL . 'api/plugin/update_auth', http_build_query($data), 1);
        $result = json_decode($result, true);
        if(empty($result)){
            return json(['code' => 400, 'msg' => '更新授权请求失败，请重试']);
        }
        if($result['code'] == 400){
            return json(['code' => 400, 'msg' => $result['msg']]);
        }
        if($result['code'] == 4001){
            return json(['code' => 4001, 'msg' => $result['msg']]);
        }
        return json(['code' => 200, 'msg' => $result['msg'], 'data' => $result['data']]);
    }

    /**
     * 绑定授权
     */
    public function bind_authorize(){
        $question = $this->request->param('question');
        $answer = trim($this->request->param('answer'), " ");
        $plugin_id = $this->request->param('plugin_id');
        $host = $this->request->param('host');
        $plugin_auth_id = $this->request->param('plugin_auth_id');
        $data = [
            'plugin_id' => $plugin_id,
            'host' => $host,
            'plugin_auth_id' => $plugin_auth_id,
            'question' => $question,
            'answer' => $answer
        ];
        $result = hmCurl(HMURL . 'api/plugin/bind', http_build_query($data), 1);
        $result = json_decode($result, true);
        if(empty($result)){
            return json(['code' => 400, 'msg' => '授权请求失败，请重试']);
        }
        if($result['code'] == 400){
            return json(['code' => 400, 'msg' => $result['msg']]);
        }
        if($result['code'] == 4001){
            return json(['code' => 4001, 'msg' => $result['msg']]);
        }
        return json(['code' => 200, 'msg' => $result['msg']]);
    }

    public function qrCode(){
        $qr_code = $this->request->param('qr_code');
        QRcode::png($qr_code,false, 'L', 7, 2);
        die;
    }


    /**
     * 安装插件
     */
    public function install(){
        $plugin_id = $this->request->param('plugin_id');

        //获取插件信息
        $result = json_decode(hmCurl(HMURL . 'api/plugin/detail/id/' . $plugin_id), true);

        $info = $result['data'];
        if($this->options['version'] != '开发版' && $this->options['version'] < $info['support']){
            return json(['code' => 400, 'msg' => '该插件最低要求程序版本：v' . $info['support']]);
        }
        if($info['price'] > 0){
            $plugin_id = $info['id'];
            $host = $_SERVER['HTTP_HOST'];
            $data = [
                'plugin_id' => $plugin_id,
                'host' => $host,
            ];
            $result = hmCurl(HMURL . 'api/plugin/check', http_build_query($data), 1);
//            echo HMURL . 'api/plugin/check';die;
//            echo $result;die;
            $result = json_decode($result, true);
            if(empty($result)){ //获取授权信息失败
                return json(['code' => 400, 'msg' => '返回为空']);
            }
            if($result['code'] == 400){ //未授权
                $data = [
                    'qr_code' => $result['data']['qr_code'],
                    'plugin_name' => $result['data']['plugin_name'],
                    'out_trade_no' => $result['data']['out_trade_no'],
                    'host' => $result['data']['host'],
                    'plugin_auth_id' => $result['data']['plugin_auth_id'],
                ];
                return json(['code' => 401, 'msg' => $result['msg'], 'data' => $data]);
            }
            if($result['code'] == 4000){ //未设置密保
                return json(['code' => 4000, 'msg' => $result['msg'], 'data' => $result['data']]);
            }
        }

//        print_r($info);die;
        $dir = ROOT_PATH . "runtime/plugin/"; //插件本地临时存储路径


        if (!file_exists($dir)) { //新建文件夹用来放置下载的更新包
            mkdir($dir, 0777, true);
        }
        $filename = $info['english_name'] . '.zip';
        /**
         * 下载插件压缩包到本地并赋值文件路径变量
         */
        $file_url = HMURL . ltrim($info['file'], '/');


        // echo $file_url;die;
        $path = file_exists($dir . $filename) ? $dir . $filename : $this->download_file($file_url, $dir, $filename);

        if($path == 'default_socket_timeout'){
            return json(['code' => 400, 'msg' => '下载插件连接超时，请重试！']);
        }

        if(!class_exists("\ZipArchive")) return json(['code' => 400, 'msg' => '您的PHP缺少ZipArchive扩展，你可以尝试安装编译版的PHP解决这个问题！']);

        $zip = new \ZipArchive();


        if($info['type'] == 'template'){
            $toPath = ROOT_PATH . 'public/content/template';
        }else{
            $toPath = ROOT_PATH . 'public/content/plugin';
        }

        //打开压缩包
        if ($zip->open($path) === true) {

            try {
                //解压文件到toPath路径下
                $zip->extractTo($toPath);
                $zip->close();
                unlink($path);
            } catch (\Exception $e) {
                return json(['code' => 400, 'msg' => "没有该目录[" . $toPath . "]的写入权限"]);
            }

            $plugin_path = $toPath . '/' . $info['english_name'];

            // return json(['code' => 400, 'msg' => $info]);

            if(file_exists($plugin_path . '/director')){
                copydirs($plugin_path . '/director', ROOT_PATH);
            }
            hmCurl(HMURL . '/api/plugin/download_num/id/' . $plugin_id);
            return json(['code' => 200, 'msg' => "安装成功"]);

        } else {
            unlink($path);
            return json(['code' => 400, 'msg' => "压缩包解压失败，请重试！"]);
        }
    }


    /**
     * 远程下载文件到本地
     */
    public function download_file($url, $dir, $filename = '') {
//        echo $url;die;
        if (empty($url)) {
            return false;
        }
        $ext = strrchr($url, '.');
        $dir = realpath($dir);
        //目录+文件
        $filename = (empty($filename) ? '/' . time() . '' . $ext : '/' . $filename);
        $filename = $dir . $filename;
        //开始捕捉
        ob_start();
        try {
            readfile($url);
        }catch (\Exception $e){

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
     * 卸载插件
     */
    public function del($ids = ''){
        Db::startTrans();
        try {
            $plugin = $this->request->param('plugin_name');
            if(empty($plugin) || $plugin == 'undefined'){
                throw new \Exception('插件不存在');
            }
            $value = $plugin . '/' . $plugin . '.php';
            $active_plugins = Db::name('options')->where(['option_name' => 'active_plugin'])->value('option_content');
            $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);

            foreach($active_plugins as $key => $val) {
                if ($value == $val){
                    unset($active_plugins[$key]);
                }
            }
            db::name('options')->where(['option_name' => 'active_plugin'])->update(['option_content' => serialize($active_plugins)]);
            if(file_exists(ROOT_PATH . 'public/content/plugin/' . $plugin . '/' . $plugin . '_del.php')){
                include_once ROOT_PATH . 'public/content/plugin/' . $plugin . '/' . $plugin . '_del.php';
            }
            rmdirs(ROOT_PATH . 'public/content/plugin/' . $plugin);
            if(strstr($plugin, '_template')){
                $template = explode('_template', $plugin);
                rmdirs(ROOT_PATH . 'public/content/template/' . $template[0]);
            }

            db::commit();
        }catch (\Exception $e){
            db::rollback();
            $this->error($e->getMessage());
        }

        $this->success('已卸载');

    }

    /**
     * 启用插件
     */
    public function enable(){
        $plugin = $this->request->param('plugin');
        $value = $plugin . '/' . $plugin . '.php';
        $active_plugins = Db::name('options')->where(['option_name' => 'active_plugin'])->value('option_content');
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);
        if (!in_array($value, $active_plugins)){
            $active_plugins[] = $value;
        }
        db::name('options')->where(['option_name' => 'active_plugin'])->update(['option_content' => serialize($active_plugins)]);
        return json(['code' => 200, 'msg' => '已启用']);
    }

    /**
     * 禁用插件
     */
    public function disable(){
        $plugin = $this->request->param('plugin');
        $value = $plugin . '/' . $plugin . '.php';
        $active_plugins = Db::name('options')->where(['option_name' => 'active_plugin'])->value('option_content');
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);

        foreach($active_plugins as $key => $val) {
            if ($value == $val){
                unset($active_plugins[$key]);
            }
        }

        db::name('options')->where(['option_name' => 'active_plugin'])->update(['option_content' => serialize($active_plugins)]);
        return json(['code' => 200, 'msg' => '已禁用']);
    }

    /**
     * 插件市场
     */
    public function cjsc(){
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()){
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')){
                return $this->selectpage();
            }
            $type = $this->request->has('type') ? $this->request->param('type') : 'all';
            $url = HMURL . "/api/plugin/list/type/{$type}";
            $result = json_decode(hmCurl($url), true);

//            print_r($result);die;

            $hmPlugins = [];
            $pluginFiles = [];
            $pluginPath = ROOT_PATH . 'public/content/plugin';
            //            echo $pluginPath;die;
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
                                if ($subFile == $file . '.php'){
                                    $pluginFiles[] = "$file/$subFile";
                                }
                            }
                        }
                    }
                }
            }

            sort($pluginFiles);

            //            print_r($pluginFiles);die;
            $active_plugins = Db::name('options')->where(['option_name' => 'active_plugin'])->value('option_content');
            $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);


            foreach($pluginFiles as $key => $pluginFile) {
                $pluginData = $this->getPluginData($pluginFile, $key + 1);
                if (empty($pluginData['name'])){
                    continue;
                }

                $pluginData['status'] = in_array($pluginFile, $active_plugins) ? 'enable' : 'disable';
                $hmPlugins[] = $pluginData;
            }


//            print_r($result['data']);
//            print_r($hmPlugins);die;

            foreach($result['data'] as &$val){
                $val['author'] = '<a href="' . $val['author_url'] . '" target="_blank">' . $val['author'] . '</a>';
                if(!empty($val['plugin_page'])){
                    $val['name'] = '<a href="' . $val['plugin_page'] . '" target="_blank">' . $val['name'] . '</a>';
                }
                $val['install'] = false;
                foreach($hmPlugins as $v){
                    $val['upgrade'] = false;
                    if($val['english_name'] == $v['plugin'] || ($val['type'] == 'template' && $val['english_name'] . '_template' == $v['plugin'])){
                        $val['install'] = true;
                        $val['setting'] = $v['setting'];
                        $val['plugin'] = $v['plugin'];
                        $val['status'] = $v['status'];
                        $val['upgrade'] = $val['version'] > $v['version'] ? true : false;
                        continue 2;
                    }
                }
            }

            //            print_r($result['data']);die;

            $result = ["total" => $result['total'], "rows" => $result['data']];

            return json($result);


            $result = ["total" => count($hmPlugins), "rows" => $hmPlugins];

            return json($result);
        }

        return $this->view->fetch();
    }



    /**
     * 查看已安装插件列表
     */
    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()){
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')){
                return $this->selectpage();
            }

            $hmPlugins = [];
            $pluginFiles = [];
            $pluginPath = ROOT_PATH . 'public/content/plugin';
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
                                if ($subFile == $file . '.php'){
                                    $pluginFiles[] = "$file/$subFile";
                                }
                            }
                        }
                    }
                }
            }

            $active_plugins = Db::name('options')->where(['option_name' => 'active_plugin'])->value('option_content');
            $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);


            foreach($pluginFiles as $key => $pluginFile) {
                $pluginData = $this->getPluginData($pluginFile, $key + 1);

                if (empty($pluginData['name'])){
                    continue;
                }

                $pluginData['status'] = in_array($pluginFile, $active_plugins) ? 'enable' : 'disable';
                $pluginData['install'] = true;
                $pluginData['upgrade'] = false;
                $hmPlugins[] = $pluginData;
            }

            $result = ["total" => count($hmPlugins), "rows" => $hmPlugins];

            return json($result);
        }

        $this->assign([
            'ys' => YS,
            'host' => empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'],
        ]);

        return $this->view->fetch();
    }


    /**
     * 获取插件信息
     *
     * @param string $pluginFile
     * @return array
     */
    function getPluginData($pluginFile, $key){
        $pluginPath = ROOT_PATH . 'public/content/plugin/';
        $pluginData = implode('', file($pluginPath . $pluginFile));
        preg_match("/Plugin Name:(.*)/i", $pluginData, $plugin_name);
        preg_match("/Version:(.*)/i", $pluginData, $version);
        preg_match("/Plugin URL:(.*)/i", $pluginData, $plugin_url);
        preg_match("/Description:(.*)/i", $pluginData, $description);
        preg_match("/ForEmlog:(.*)/i", $pluginData, $foremlog);
        preg_match("/Author:(.*)/i", $pluginData, $author_name);
        preg_match("/Author URL:(.*)/i", $pluginData, $author_url);


        $ret = explode('/', $pluginFile);
        $plugin = $ret[0];
        $setting = file_exists($pluginPath . $plugin . '/' . $plugin . '_setting.php') ? true : false;
        $pay = substr($plugin, -4);
        if($pay == '_pay') $setting = false;

        $plugin_name = isset($plugin_name[1]) ? strip_tags(trim($plugin_name[1])) : '';




        $version = isset($version[1]) ? strip_tags(trim($version[1])) : '';
        $description = isset($description[1]) ? strip_tags(trim($description[1])) : '';
        $plugin_url = isset($plugin_url[1]) ? strip_tags(trim($plugin_url[1])) : '';
        $author = isset($author_name[1]) ? strip_tags(trim($author_name[1])) : '';
        $foremlog = isset($foremlog[1]) ? strip_tags(trim($foremlog[1])) : '';
        $author_url = isset($author_url[1]) ? strip_tags(trim($author_url[1])) : '';

        return [
            'id' => $key,
            'name' => $plugin_name,
            'version' => $version,
            'description' => $description,
            'url' => $plugin_url,
            'author' => '<a href="' . $author_url . '" target="_blank">' . $author . '</a>',
            'forEmlog' => $foremlog,
            'authorUrl' => $author_url,
            'setting' => $setting,
            'plugin' => $plugin
        ];
    }

}
