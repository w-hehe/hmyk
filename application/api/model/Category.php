<?php

namespace app\api\model;

use think\Model;

class Category extends Model
{


    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
//    protected $deleteTime = 'deletetime';

    protected $resultSetType = 'collection';

    // 追加属性
    protected $append = [

    ];



    public function goods() {
        return $this->hasMany('goods', 'category_id', 'id');
    }






}
