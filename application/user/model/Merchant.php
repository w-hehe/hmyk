<?php

namespace app\user\model;

use think\Model;


class Merchant extends Model {


    public function grade(){
        return $this->hasOne('app\user\model\MerchantGrade', 'id', 'grade_id');
    }

}
