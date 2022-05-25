<?php
/*
Plugin Name: 小贴士
Version: 1.0.1
Plugin URL:
Description: 这是世界上第一个红盟云卡插件，它会在控制台页面送上一句温馨的小提示。
Author: 红盟云商
Author URL: http://www.hmy3.com/
*/

!defined('ROOT_PATH') && exit('access deined!');

global $array_tips;

$array_tips = [
    '请关注官网 <a href="https://www.ysxue.cc/" target="_blank">https://www.ysxue.cc/</a> 最新动态',
    '检查你的站点根目录下是否存在文件：sql.php，有的话请删除它',
    '及时更新程序到最新版本，更好的体验红盟云卡',
    '今天你备份数据了吗？',
    '从明天起，做一个幸福的人',
];

function tips() {
    global $array_tips;
    $i = mt_rand(0, count($array_tips) - 1);
    $tip = $array_tips[$i];
    echo "<div id=\"tip\"> $tip</div>";
}

addAction('admin_dashboard_upgrade', 'tips');

function tips_css() {
    echo "<style type='text/css'>
    #tip{
        background:url(/content/plugin/tips/icon_tips.gif) no-repeat left 3px;
        padding:3px 18px;
        margin:5px 0px;
        font-size:12px;
        color:#999999;
    }
    </style>\n";
}

addAction('admin_dashboard_head', 'tips_css');
