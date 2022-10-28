<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
set_time_limit(0);
include("Uploader.class.php");

/* 上传配置 */
$config = array(
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize" => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName" => "remote.png"
);
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = array();
if (isset($_POST[$fieldName])) {
    $source = $_POST[$fieldName];
} else {
    $source = $_GET[$fieldName];
}
foreach ($source as $imgUrl) {
    $imageInfo = getimagesize($imgUrl);
    $base64 = "" . chunk_split(base64_encode(file_get_contents($imgUrl)));
    $return='data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode(file_get_contents($imgUrl)));
    array_push($list, array(
        "state" => 'SUCCESS',
        "base64Data" => $return,
        "source" => $imgUrl
    ));
}

/* 返回抓取数据 */
return json_encode(array(
    'state'=> count($list) ? 'SUCCESS':'ERROR',
    'list'=> $list
));