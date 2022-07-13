<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use fast\Http;
use think\Db;
use think\Cache;

/**
 * 系统更新类
 */
class Upgrade extends Backend {


    public function index() {

        //更新包检测地址
        $version = $this->options['version'];
        $upgrade_url = HMURL . "api/upgrade/download_upgrade/version/" . $version;
        try {
            //检测更新包
            $result = hmCurl($upgrade_url);
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
        $file_url = HMURL . $upgrade["file"]; //更新包下载地址
        $filename = basename($file_url); //更新包文件名称e2876e138e4d82e51774e9cbea8d9a10.zip

        $dir = ROOT_PATH . "runtime/upgrade/"; //更新包本地存储路径

        if (!file_exists($dir)) { //新建文件夹用来放置下载的更新包
            mkdir($dir, 0777, true);
        }

        //增加更新包下载次数
        if(!file_exists($dir . $filename)){
            $add_url = HMURL . "api/upgrade/add_upgrade_num/id/" . $upgrade['id'];
            hmCurl($add_url);
        }

        /**
         * 下载更新包到本地并赋值文件路径变量
         */
        $path = file_exists($dir . $filename) ? $dir . $filename : $this->download_file($file_url, $dir, $filename);

        if(!class_exists("\ZipArchive")) return json(['code' => 400, 'msg' => '您的PHP缺少ZipArchive扩展，你可以尝试安装编译版的PHP解决这个问题！']);

        $zip = new \ZipArchive();




        //打开压缩包
        if ($zip->open($path) === true) {
            $toPath = ROOT_PATH;
            try {
                //解压文件到toPath路径下，用于覆盖差异文件
                $zip->extractTo($toPath);
                rmdirs($path, false); //删除更新包
            } catch (\Exception $e) {
                return json(["msg" => "没有该目录[" . $toPath . "]的写入权限", "code" => 400]);
            }

            //文件差异覆盖完成，开始更新数据库
            if(file_exists(ROOT_PATH . "/sql.php")){
                include ROOT_PATH . "/sql.php";
                chmod(ROOT_PATH . "/sql.php",0777);
                unlink(ROOT_PATH . "/sql.php");
            }

            //更新后台静态文件版本
            db::name('config')->where(['name' => 'version'])->update(['value' => time()]);


            $this->options['version'] = $upgrade["version"]; //更新本次版本号准备检测下个版本

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



    public function download_file($url, $dir, $filename = '') {
        if (empty($url)) {
            return false;
        }
        $ext = strrchr($url, '.');
        /*if($ext != '.gif' && $ext != ".jpg" && $ext != ".bmp"){
            echo "格式不支持！";
            return false;
        }*/

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
     * 初始化版本
     */
    public function initVersion(){


        if(Cache::has('upgrade_result')){
            $result = Cache::get('upgrade_result');
            return json(['code' => 400, 'msg' => '缓存版本', 'data' => $this->options['version']]);
        }else{

            $url = HMURL . "api/upgrade/check_upgrade";

            $params = [
                "type" => "shop",
                "version" => $this->options['version']
            ];

            try {
                $result = json_decode(hmCurl($url, http_build_query($params), 0, false, 5), true);
            }catch (\Exception $e){
                $result = [];
            }
            Cache::set('upgrade_result', $result, 3600 * 12);
        }

        if (empty($result) || $result["code"] == 400) { //版本检测失败或没有新版本
            return json(['code' => 400, 'msg' => '版本检测失败或没有新版本', 'data' => $this->options['version']]);
        } else {
            return json(['code' => 200, 'msg' => '发现新版本', 'data' => $result['data'], 'version' => $this->options['version']]);
        }
    }

    /**
     * 检查更新
     */
    public function checkUpgrade(){
        $url = HMURL . "api/upgrade/check_upgrade";
        $params = [
            "type" => "shop",
            "version" => $this->options['version']
        ];
        try {
            $result = json_decode(hmCurl($url, http_build_query($params), 0, false, 5), true);
            if(empty($result)){
                throw new \Exception("检测失败，请点击重试");
            }
            if($result['code'] == 200){
                $result['content'] = "";
                foreach($result['list'] as $key => $val){
                    $upgrade_time = date('Y-m-d H:i:s', $val['createtime']);
                    $result['content'] .= "<style>#upgrade-content-box p{margin-bottom: 0;}</style><div id='upgrade-content-box'><h3>版本：v{$val['version']}</h3><p>更新时间：{$upgrade_time}</p>{$val['content']}</div>";
                }
            }
            Cache::set('upgrade_result', $result, 3600 * 12);
        }catch (\Exception $e){
            return json(['code' => 402, 'msg' => "检测失败，请点击重试"]);
        }
        return json($result);
    }

}
