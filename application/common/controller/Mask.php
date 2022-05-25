<?php

namespace app\common\controller;


use think\Cache;
use think\Db;
use think\Session;

/**
 *
 */
class Mask {


    public function show() {

//        phpinfo();die;

        $siteurl='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $html = '<html>
<head>
    <meta charset="UTF-8">
    <title>使用浏览器打开</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta name="format-detection" content="telephone=no">
    <meta content="false" name="twcClient" id="twcClient">
    <meta name="aplus-touch" content="1">
    <style>
        body,html{width:100%;height:100%}
        *{margin:0;padding:0}
        body{background-color:#fff}
        #browser img{
            width:50px;
        }
        #browser{
            margin: 0px 10px;
            text-align:center;
        }
        #contens{
            font-weight: bold;
            color: #2466f4;
            margin:-285px 0px 10px;
            text-align:center;
            font-size:20px;
            margin-bottom: 125px;
        }
        .top-bar-guidance{font-size:15px;color:#fff;height:70%;line-height:1.8;padding-left:20px;padding-top:20px;background:url(/assets/img/banner.png) center top/contain no-repeat}
        .top-bar-guidance .icon-safari{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
        .app-download-tip{margin:0 auto;width:290px;text-align:center;font-size:15px;color:#2466f4;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcAQMAAACak0ePAAAABlBMVEUAAAAdYfh+GakkAAAAAXRSTlMAQObYZgAAAA5JREFUCNdjwA8acEkAAAy4AIE4hQq/AAAAAElFTkSuQmCC) left center/auto 15px repeat-x}
        .app-download-tip .guidance-desc{background-color:#fff;padding:0 5px}
        .app-download-tip .icon-sgd{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
        .app-download-btn{display:block;width:214px;height:40px;line-height:40px;margin:18px auto 0 auto;text-align:center;font-size:18px;color:#2466f4;border-radius:20px;border:.5px #2466f4 solid;text-decoration:none}
    </style>
    <link rel="stylesheet" href="/assets/shop/dist/bui/css/bui.css" />
    <link rel="stylesheet" href="/assets/shop/css/base.css" />
</head>
<body>

<div class="top-bar-guidance">
    <p>点击右上角<img src="/assets/img/3dian.png" class="icon-safari">在 浏览器 打开</p>
    <p>苹果设备<img src="/assets/img/iphone.png" class="icon-safari">安卓设备<img src="/assets/img/android.png" class="icon-safari">↗↗↗</p>
</div>

<div id="contens">
<p><br/><br/></p>
<p>1.本站不支持 微信或QQ 内访问</p>
<p><br/></p>
<p>2.请按提示在手机 浏览器 打开</p>
</div>

<div class="app-download-tip">
    <span class="guidance-desc" id="url">'.$siteurl.'</span>
</div>
<p><br/></p>
<div class="app-download-tip">
    <span class="guidance-desc">点击右上角<img src="/assets/img/3dian.png" class="icon-sgd"> or 复制网址自行打开</span>
</div>

<script src="/assets/libs/jquery/dist/jquery.min.js"></script>
<script src="/assets/shop/dist/clipboard.min.js"></script>
<a class="app-download-btn" id="copy">点此复制本站网址</a>
<script src="/assets/shop/dist/bui/js/bui.js"></script>
<script src="/assets/shop/js/base.js"></script>
<script>

      
    var kami = $(\'#url\').html();
    var clipboard = new Clipboard(\'#copy\', {
        text: function() {
            return kami;
        }
    });

    clipboard.on(\'success\', function(e) {
        hint("复制成功");
    });
    
    clipboard.on(\'error\', function(e) {
        hint("复制失败");
    });
      
</script>

<body>
</html>';

        echo $html;
        die;


    }


}
