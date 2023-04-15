<?php

namespace hehe;

/**
 * 网络
 */
class Network {




    /**
     * 获取主域名
     *
     * @return string
     */
    public static function getHostDomain($http = true) {
        if($http){
            return self::getHttpType() . $_SERVER['HTTP_HOST'];
        }else{
            return $_SERVER['HTTP_HOST'];
        }

    }

    /**
     * 获取 HTTPS协议类型
     *
     * @return string
     */
    public static function getHttpType() {
        return $type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    }




}
