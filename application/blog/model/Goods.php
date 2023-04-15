<?php

namespace app\index\model;

use think\Model;


class Goods extends Model {


    public function sku(){
        return $this->hasMany("app\index\model\Sku", 'goods_id', 'id');
    }

}
