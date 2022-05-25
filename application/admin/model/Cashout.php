<?php

namespace app\admin\model;

use think\Model;


class Cashout extends Model
{

    

    


    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'handletime_text'
    ];



    public function user(){
        return $this->belongsTo('user', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function getHandletimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['handletime']) ? $data['handletime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setHandletimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
