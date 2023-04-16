<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Cache;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend {

    /**
     * 查看
     */
    public function index() {
        $version = db::name('options')->where(['name' => 'version'])->value('value');
        if(Cache::has('dashboard')){
            $result = Cache::get('dashboard');
        }else{
            $user_id = $this->uid;
            $data = [
                'user_id' => $user_id,
                'version' => $version
            ];
            $result = json_decode(hmCurl(API . 'api/dashboard/index', http_build_query($data)), true);
            if($result){
                Cache::set('dashboard', $result, 3600); //缓存一小时
            }
        }
        $this->assign([
            'data' => $result['data'],
            'version' => $version,
            'new_version' => empty($result['data']['version']) ? 0 : 1,
        ]);
        return $this->view->fetch();
    }

}
