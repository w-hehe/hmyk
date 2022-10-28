<?php

namespace addons\ueditor\controller;

use think\addons\Controller;

/**
 * ueditor接口控制器
 */
class Api extends Controller
{

    public function index()
    {
        //header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
        header("Content-Type: text/html; charset=utf-8");

        $phppath = ADDON_PATH . 'ueditor/library/php/';
        $configpath = $phppath . 'config.json';

        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($configpath)), true);
        $action = $this->request->get('action');
        $callback = $this->request->get('callback');

        switch ($action)
        {
            case 'config':
                $result = json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $result = include($phppath . "action_upload.php");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = include($phppath . "action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include($phppath . "action_list.php");
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                $result = include($phppath . "action_crawler.php");
                break;
            default:
                $result = json_encode(array(
                    'state' => '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if ($callback)
        {
            if (preg_match("/^[\w_]+$/", $callback))
            {
                echo htmlspecialchars($callback) . '(' . $result . ')';
            }
            else
            {
                echo json_encode(array(
                    'state' => 'callback参数不合法'
                ));
            }
        }
        else
        {
            echo $result;
        }
        return;
    }

}
