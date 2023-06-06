<?php

use think\Env;

return [
    'type'            => Env::get('database.type', 'mysql'), // 数据库类型
    'hostname'        => Env::get('database.hostname', '127.0.0.1'), // 服务器地址
    'database'        => Env::get('database.database', 'hmyk'), // 数据库名
    'username'        => Env::get('database.username', 'root'), // 用户名
    'password'        => Env::get('database.password', '123456'), // 密码
	'prefix'          => Env::get('database.prefix', 'hm_'), // 数据库表前缀
    'hostport'        => Env::get('database.hostport', ''), // 端口
	'charset'         => Env::get('database.charset', 'utf8mb4'), // 数据库编码默认采用 utf8mb4
    'dsn'             => '', // 连接dsn
    'params'          => [], // 数据库连接参数
    'debug'           => Env::get('database.debug', false), // 数据库调试模式
    'deploy'          => 0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'rw_separate'     => false, // 数据库读写是否分离 主从式有效
    'master_num'      => 1, // 读写分离后 主服务器数量
    'slave_no'        => '', // 指定从服务器序号
    'fields_strict'   => true, // 是否严格检查字段是否存在
    'resultset_type'  => 'array', // 数据集返回类型
    'auto_timestamp'  => false, // 自动写入时间戳字段
    'datetime_format' => false, // 时间字段取出后的默认时间格式,默认为Y-m-d H:i:s
    'sql_explain'     => false, // 是否需要进行SQL性能分析
];
