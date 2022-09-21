<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Cache;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Pay extends Backend {

    /**
     * Pay模型对象
     * @var \app\admin\model\Pay
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Pay;
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 获取支付配置区的广告位
    */
    public function getPoster(){
        if(Cache::has('pay_poster')){
            $poster = Cache::get('pay_poster');
        }else{
            $poster_api = HMURL . "api/hmyk/pay_poster";
//            echo $poster_api;die;
            $poster = json_decode(hmCurl($poster_api, false, 0, false, 5), true);
            if($poster && $poster['code'] == 200){
                Cache::set('pay_poster', $poster['data'], 3600*12);
                $poster = $poster['data'];
            }

        }
        return $poster;
    }

    /**
     * 查看
     */
    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()){
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')){
                return $this->selectpage();
            }

            $hmPlugins = [];
            $pluginFiles = [];
            $pluginPath = ROOT_PATH . 'content/plugin';
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

                                if ($subFile == $file . '.php' && substr($file, -4) == '_pay'){
                                    $pluginFiles[] = "$file/$subFile";
                                }
                            }
                        }
                    }
                }
            }

            $active_pay = Db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
            $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

            foreach($pluginFiles as $key => $pluginFile) {
                $pluginData = $this->getPluginData($pluginFile, $key + 1);
                if (empty($pluginData['name'])){
                    continue;
                }
                $pluginData['status'] = isset($active_pay[$pluginData['plugin']]) ? 'enable' : 'disable';
                $pluginData['install'] = true;
                $hmPlugins[] = $pluginData;
            }
            $result = ["total" => count($hmPlugins), "rows" => $hmPlugins];
            return json($result);
        }



        return $this->view->fetch();
    }

    /**
     * 获取插件信息
     *
     * @param string $pluginFile
     * @return array
     */
    function getPluginData($pluginFile, $key){
        $pluginPath = ROOT_PATH . 'content/plugin/';
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


    /**
     * 编辑
     */
    public function edit($ids = null) {

        $plugin_path = ROOT_PATH . "content/plugin/{$ids}/";

        if($this->request->isPost()){
            $post = $this->request->post("row/a");

            $pay_type = empty($post['pay_type']) ? [] : $post['pay_type'];
            $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
            $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);
            if(isset($active_pay[$ids])){
                $active_pay[$ids]['pay_type'] = $pay_type;
                $active_pay = json_encode($active_pay);
                db::name('options')->where(['option_name' => 'active_pay'])->update(['option_content' => $active_pay]);
            }
            $post['pay_type'] = $pay_type;
            $post = json_encode($post);
            file_put_contents("{$plugin_path}{$ids}_setting.json", $post);
            $this->success('操作成功');
        }

        $pay_file = "{$ids}/{$ids}.php";
        $result = $this->getPluginData($pay_file, 1);

        $info = file_get_contents("{$plugin_path}{$ids}_setting.json");
        $info = json_decode($info, true);
//        echo '<pre>'; print_r($info);die;




        $this->assign([
            'row' => $result,
            'info' => $info
        ]);
        return view("{$plugin_path}{$ids}_setting.php");
    }


	//启用支付
	public function openStatus(){
		$post = $this->request->param();
        $plugin_path = ROOT_PATH . "content/plugin/{$post['id']}/";
        $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
        $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);
        $info = file_get_contents("{$plugin_path}{$post['id']}_setting.json");
        $info = json_decode($info, true);
        $active_pay[$post['id']] = [
            'pay_type' => $info['pay_type']
        ];
        $active_pay = json_encode($active_pay);
        db::name('options')->where(['option_name' => 'active_pay'])->update(['option_content' => $active_pay]);

		return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
	}

	//关闭支付
	public function closeStatus(){
		$post = $this->request->param();
        $active_pay = db::name('options')->where(['option_name' => 'active_pay'])->value('option_content');
        $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);
        unset($active_pay[$post['id']]);
        $active_pay = json_encode($active_pay);
        db::name('options')->where(['option_name' => 'active_pay'])->update(['option_content' => $active_pay]);
		return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
	}

}
