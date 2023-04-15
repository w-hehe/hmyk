<?php

namespace app\home\model;

use think\Model;


class GoodsOrder extends Model {


    public function goods(){
        return $this->belongsTo('Goods', 'goods_id');
    }


    public function deliver(){
        return $this->hasMany('Deliver', 'order_id');
    }


}
