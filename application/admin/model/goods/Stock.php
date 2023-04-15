<?php

namespace app\admin\model\goods;

use think\Model;


class Stock extends Model
{

    

    

    // 表名
    protected $name = 'stock';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'create_time_text',
        'sale_time_text',
        'status_text'
    ];

    public function sku(){
        return $this->hasOne('\\app\\admin\\model\\Sku', 'id', 'sku_id');
    }

    public function goods(){
        return $this->hasOne('Goods', 'id', 'goods_id');
    }


    public function getStatusList()
    {
        return ['10' => __('Status 10')];
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSaleTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sale_time']) ? $data['sale_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSaleTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
