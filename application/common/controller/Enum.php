<?php

namespace app\common\controller;


use think\Cache;
use think\Db;
use think\Session;

/**
 * 枚举
 */
class Enum{


    //用户代理
    const AGENT = [
        0 => '普通用户',
        1 => '初级代理',
        2 => '高级代理',
        3 => '超级代理'
    ];
    



}
