<?php

namespace app\admin\controller;

use app\admin\model\User;
use app\admin\model\Goods;
use app\common\controller\Backend;
use think\Cache;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend {

    /**
     * 查看
     */
    public function index() {

        $today_register = db::name("user")->whereTime('createtime', 'today')->where("`tourist` IS NULL")->field('id')->count();
        //分类总数
        $category_total = db::name("category")->field("id")->where(['status' => 'normal'])->count();
        //商品总数
        $goods_total = db::name('goods')->field('id')->whereNull('deletetime')->count();
        //用户总数
        $user_total = db::name('user')->field('id')->count();
        //今日订单数量
        $today_order_result = db::name("order")->whereTime('create_time', 'today')->where("status != 'yiguoqi' and status != 'wait-pay'")->field("id, status, goods_money, money, buy_num, remote_money")->select();
        $today_order = count($today_order_result);
        //今日待处理订单
        $today_wait_order = 0;
        //今日成交金额
        $today_order_money = 0;
        //今日盈利金额
        $today_order_profit = 0;
        foreach ($today_order_result as $val) {
            if ($val["status"] == 'wait-send') {
                $today_wait_order++;
            }
            $today_order_money += $val["money"];
            $today_order_profit += $val["money"] - $val["remote_money"];
        }

        //商品销量top10
        $goods_list = db::name("goods")->where('sales > 0')->order(["sales" => "desc"])->limit(10)->select();
        foreach ($goods_list as $key => $val) {
            $images = explode(",", $val["images"]);
            $goods_list[$key]["cover"] = $images[0];
        }
//        echo '<pre>'; print_r($goods_list);die;

        //用户消费top10
        $user_list = User::withCount('order')->where("consume > 0")->order(['consume' => 'desc'])->limit(10)->select();

        $poster = false;
        if(Cache::has('poster_dashboard')){
            $poster = Cache::get('poster_dashboard')['data'];
        }
        $prefix = Config::get('database.prefix');
        $start_time = strtotime(date('Y-m-d 23:59:59', $this->timestamp)) - (3600*24*15);
//        echo date('Y-m-d H:i:s', $start_time);die;
        $sql = "select FROM_UNIXTIME(`pay_time`,'%Y-%m-%d') date, count(id) order_count, sum(money) sales_money from {$prefix}order where pay_time > {$start_time} group by date;";
        
        $result = db::query($sql);
        $sts_order = [];
        for($i = 0; $i < 15; $i++){
            $date = date('Y-m-d', ($start_time + 1) + (3600*24*$i));
            $sts_order['date'][$i] = $date;
            $sts_order['order_count'][$i] = 0;
            $sts_order['sales_money'][$i] = '0.00';
            foreach($result as $val){
                if($val['date'] == $date){
                    $sts_order['order_count'][$i] = $val['order_count'];
                    $sts_order['sales_money'][$i] = $val['sales_money'];
                }
            }
        }

        $this->view->assign([
            'sts_order' => $sts_order,
            'upgrade' => $this->upgrade(),
            'poster' => $poster,
            "options" => $this->options,
            "today_register" => $today_register,
            "category_total" => $category_total,
            "today_order" => $today_order,
            "today_wait_order" => $today_wait_order,
            "today_order_money" => $today_order_money,
            "today_order_profit" => $today_order_profit,
            "goods_list" => $goods_list,
            "user_list" => $user_list,
            'goods_total' => $goods_total,
            'user_total' => $user_total
        ]);

        return $this->view->fetch();
    }

    public function upgrade(){
        $url = HMURL . "api/upgrade/check_upgrade";
        $params = [
            "type" => "shop",
            "version" => $this->options['version']
        ];
        if(Cache::has('upgrade_result')){
            $result = Cache::get('upgrade_result');
        }else{
            try {
                $result = json_decode(hmCurl($url, http_build_query($params), 0, false, 5), true);
                if($result) Cache::set('upgrade_result', $result, 3600 * 12);
            }catch (\Exception $e){
                $result = false;
            }
        }

        if($result && $result['code'] == 200 && $this->options['version'] < $result['data']['version']){
            $upgrade = [
                'version' => $result['data']['version'],
            ];
        }else{
            $upgrade = [];
        }
        return $upgrade;
    }


    /**
     * 获取广告链接
     */
    public function poster(){
        $upgrade_url = HMURL . "api/hmyk/poster";

        if(Cache::has('poster_dashboard')){
            $result = Cache::get('poster_dashboard');
        }else{
            try {
                $result = json_decode(hmCurl($upgrade_url, false, 0, false, 5), true);

                Cache::set('poster_dashboard', $result, 3600*12);

            }catch (\Exception $e){
                $result = [];
            }
        }

        return json($result);

    }





}
