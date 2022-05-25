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
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++) {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
//        db::startTrans();


        $today_register = db::name("user")->whereTime('createtime', 'today')->where("`tourist` IS NULL")->field('id, createtime')->count();
        //分类总数
        $category_total = db::name("category")->field("id")->where(['status' => 'normal'])->count();
        //商品总数
        $goods_where = [
            'deletetime' => ['exp', db::raw('is null')]
        ];
        $goods_total = db::name('goods')->field('id')->where($goods_where)->count();
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
            if ($val["status"] == 'daifahuo') {
                $today_wait_order++;
            }
            $today_order_money += $val["money"];
            $today_order_profit += $val["money"] - $val["remote_money"];
        }

        //商品销量top10
        $goods_list = db::name("goods")->where('sales > 0')->order("sales desc")->limit(10)->select();
        foreach ($goods_list as &$val) {
            $images = explode(",", $val["images"]);
            $val["cover"] = $images[0];
        }

        //用户消费top10
        $user_list = User::withCount('order')->where("consume > 0")->order('consume desc')->limit(10)->select();

//        db::commit();

        $poster = false;
        if(Cache::has('poster_dashboard')){
            $poster = Cache::get('poster_dashboard')['data'];
        }

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

        if($result && $result['code'] == 200){
            $upgrade = [
                'version' => $result['data']['version'],
            ];
        }else{
            $upgrade = [];
        }


        $this->view->assign([
            'upgrade' => $upgrade,
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
        ]);

        return $this->view->fetch();
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
