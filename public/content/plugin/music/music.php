<?php
/*
Plugin Name: 音乐播放器
Version: 1.0.0
Plugin URL:
Description: 在网页中添加音乐播放器
Author: 云商学院
Author URL: https://www.ysxue.cc/
*/


use app\common\controller\Hm;
use think\Db;

!defined('ROOT_PATH') && exit('access deined!');


function music() {

    $plugin_path = ROOT_PATH . "public/content/plugin/music/";

    $info = file_get_contents("{$plugin_path}music_setting.json");
    $info = json_decode($info, true);

    echo empty($info['html']) ? '' : $info['html'];

}

addAction('home_foot', 'music');
