<?php

namespace app\admin\model;

use think\Model;


class Plugin extends Model
{

    

    

    // 表名
    protected $name = 'plugin';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
