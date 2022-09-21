<?php

namespace app\api\model;

use think\Model;

class Goods extends Model {



    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    
    protected $resultSetType = 'collection';

    // 追加属性
    protected $append = [

    ];


    public function cdkey() {
        return $this->hasMany('cdkey', 'goods_id', 'id');
    }

    public function price() {
        return $this->hasMany('price', 'goods_id', 'id');
    }

    public function category() {
        return $this->hasMany('category', 'id', 'category_id');
    }





}
