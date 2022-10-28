<?php

namespace app\api\controller\v1;

use app\common\controller\Hm;
use think\Config;
use think\Db;
use think\Controller;

class Common extends Controller {

    public $timestamp = null;
    public $user = [];
    public $host = null;
    public $options = [];
    public $site = [];

    public function _initialize() {
        parent::_initialize();
        check_cors_request(); //跨域请求检测
        $this->timestamp = time();
        $this->host = getHostDomain();
        $this->user = Hm::getUser();

//        print_r($this->user);die;

        #########测试数据#########
        // $this->user['id'] = 1;
        // $this->user['login'] = true;
        // $this->user['email'] = '10220739@qq.com';
        // $this->user['money'] = 10;
        // $this->user['agent'] = 2;
        #########测试数据#########

        $options = db::name('options')->select();
        foreach($options as $val) $this->options[$val['option_name']] = $val['option_content'];
        $this->options['buy_data'] = json_decode($this->options['buy_data'], true);
        $this->site = Config::get("site");
        $this->options['goods_eject'] = empty(strip_tags($this->options['goods_eject'])) ? '' : $this->options['goods_eject'];
        $this->options['index_eject'] = empty(strip_tags($this->options['index_eject'])) ? '' : $this->options['index_eject'];
        includeAction();
    }



    /**
     * 数字换库存
     */
    public function handleStock($stock_number){
        $data = [];
        if($this->options != null && $this->options['stock_show_switch'] == 1 && is_numeric($stock_number)){
            $stock_show = json_decode($this->options['stock_show'], true);
            foreach($stock_show as $val){
                if($stock_number >= $val['less'] && $stock_number <= $val['greater']) $data['stock_text'] = $val['content'];
            }
        }else{
            $data['stock_text'] = $stock_number;
        }
        $data['stock_number'] = $stock_number;
        return $data;
    }


}
