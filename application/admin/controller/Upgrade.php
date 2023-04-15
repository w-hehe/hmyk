<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;
use think\Cache;


class Upgrade extends Backend {

    public function jiance(){
        $user_id = $this->uid;


        $version = db::name('options')->where(['name' => 'version'])->value('value');

        $data = [
            'user_id' => $user_id,
            'version' => $version
        ];

        $result = json_decode(hmCurl(API . 'api/dashboard/index', http_build_query($data)), true);

        if(empty($result)){
            return json(['code' => 400, 'msg' => '检测失败']);
        }

        $new_version = empty($result['data']['version']) ? 0 : 1;

        return json(['code' => 200, 'msg' => '检测完成', 'data' => $new_version]);

        print_r($result);die;

    }

    /**
     * 更新
     */
    public function index() {

        //更新包检测地址
        $version = db::name('options')->where(['name' => 'version'])->value('value');
        
        $upgrade_url = API . "api/upgrade/index";
        try {
            //检测更新包
            $result = hmCurl($upgrade_url, http_build_query(['version' => $version]));
        } catch (\Exception $e) {
            return json(["msg" => "更新包获取失败，请重试！", "code" => 400]);
        }
        $result = json_decode($result, true);
        if(empty($result)){
            return json(["msg" => "更新包获取失败，请重试！", "code" => 400]);
        }
        if($result["code"] == 400){ //更新完毕
            return json(["msg" => "更新完成！请刷新页面", "code" => 201]);
        }
        
        
        
        //更新包信息
        $upgrade = $result["data"];
        $file_url = API . $upgrade["file"]; //更新包下载地址
        $filename = basename($file_url); //更新包文件名称e2876e138e4d82e51774e9cbea8d9a10.zip

        $dir = ROOT_PATH . "runtime/upgrade/"; //更新包本地存储路径

        if (!file_exists($dir)) { //新建文件夹用来放置下载的更新包
            mkdir($dir, 0777, true);
        }



        /**
         * 下载更新包到本地并赋值文件路径变量
         */
        $path = file_exists($dir . $filename) ? $dir . $filename : $this->download_file($file_url, $dir, $filename);
        
        

        if(!class_exists("\ZipArchive")) return json(['code' => 400, 'msg' => '您的PHP缺少ZipArchive扩展，你可以尝试安装编译版的PHP解决这个问题！']);

        $zip = new \ZipArchive();

        // print_r($result);die;


        //打开压缩包
        if ($zip->open($path) === true) {
            $toPath = ROOT_PATH . "runtime/upgrade/up";
            try {
                //解压文件到toPath路径下
                $zip->extractTo($toPath);
                rmdirs($path, false); //删除更新包
            } catch (\Exception $e) {
                return json(["msg" => "没有该目录[" . $toPath . "]的写入权限", "code" => 400]);
            }

            //文件解压成，开始更新数据库
            if(file_exists(ROOT_PATH . "runtime/upgrade/up/sql.php")){
                include ROOT_PATH . "runtime/upgrade/up/sql.php";
                chmod(ROOT_PATH . "runtime/upgrade/up/sql.php",0777);
                unlink(ROOT_PATH . "runtime/upgrade/up/sql.php");
            }
            
            copydirs($toPath, ROOT_PATH);
            rmdirs($toPath);

            //更新后台静态文件版本
            db::name('config')->where(['name' => 'version'])->update(['value' => time()]);


            //更新完成后刷新配置文件
            $this->refreshFile();
            //清除站点缓存
            rmdirs(CACHE_PATH, false);
            Cache::clear();
            return json(["msg" => "更新版本段完成，继续更新", "code" => 200]);

        } else {
            rmdirs($path, false); //删除更新包
            return json(["msg" => "更新包解压失败，请重试！", "code" => 400]);
        }
        
    }
    
    
    
    
    
    protected function download_file($url, $dir, $filename = '') {
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
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
        $size = strlen($img);
        $fp2 = fopen($filename, "a");
        fwrite($fp2, $img);
        fclose($fp2);
        return $filename;
    }
    
    
    /**
     * 刷新配置文件
     */
    protected function refreshFile(){
        $config = [];
        $list = db::name('config')->select();
        foreach ($list as $k => $v) {
            $value = $v;
            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files'])) {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array') {
                $value['value'] = (array)json_decode($value['value'], true);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(
            CONF_PATH . 'extra' . DS . 'site.php',
            '<?php' . "\n\nreturn " . var_export_short($config) . ";\n"
        );
    }

}
