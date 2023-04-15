<?php

return [
    [
        'name'    => 'classname',
        'title'   => '渲染文本框元素',
        'type'    => 'string',
        'content' => [],
        'value'   => '.editor',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '用于对指定的元素渲染，一般情况下无需修改',
        'ok'      => '',
        'extend'  => '',
    ],
    [
        'name'    => 'baidumapkey',
        'title'   => '百度地图API密钥',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => '',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'baidumapcenter',
        'title'   => '百度地图中心点经纬度',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => '116.404413,39.903536',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => '__tips__',
        'title'   => '温馨提示',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => '百度地图API密钥申请地址：http://lbsyun.baidu.com/apiconsole/key<br>百度地图经纬度坐标获取：https://api.map.baidu.com/lbsapi/getpoint/index.html',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
];
