<?php

namespace app\admin\model;

use think\Model;


class Order extends Model
{





    // 表名

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
//        'paytime_text'
    ];



    public function user(){
        return $this->belongsTo('user', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function goods() {
        return $this->belongsTo('goods', 'goods_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
