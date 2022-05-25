<?php

namespace app\common\controller\dock;


use think\Cache;
use think\Db;
use think\Session;
// use app\common\controller\dock\Dock;
/**
 *
 */
class Kky{

    /**
     * 获取商户信息
    */
    static public function getUser($site_id){
        $site = Dock::getSiteInfo($site_id);
        $url = $site["domain"] . "/dockapi/index/userinfo.html";
        $params = [
            "userid" => $site['account'], //用户id
        ];
        $params["sign"] = self::getSign($params, $site["password"]);
        $result = json_decode(hmCurl($url, http_build_query($params), true), true);
        if($result['code'] == 1){
            $data = $result['data'];
        }
        return $data;
    }




}
