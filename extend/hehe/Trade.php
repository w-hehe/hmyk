<?php

namespace hehe;

/**
 * 订单类
 */
class Trade {


    /**
     * 生成订单号
     */
    public static function generateTradeNo(){
        return date('YmdHis') . mt_rand(10000, 99999);
    }





}
