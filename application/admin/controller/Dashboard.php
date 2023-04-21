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

        $tongji = $this->tongji('today');

        $this->assignconfig('column', $tongji['column']);
        $this->assignconfig('order_num', $tongji['order_num']);
        $this->assignconfig('order_money', $tongji['order_money']);


        $this->assign([
            'data' => $result['data'],
            'version' => $version,
            'new_version' => empty($result['data']['version']) ? 0 : 1,
            'tongji' => $tongji
        ]);
        return $this->view->fetch();
    }

    public function tongji($shijian = 'today'){
        $column = [];
        $order_num = [];
        $order_money = [];
        $add_user = 0;
        $_order_num = 0;
        $_order_money = 0;
        $_cost = 0;
        $_lirun = 0;

        if($shijian == '30tian'){
            $start_time = strtotime(date('Y-m-d 00:00:00', $this->timestamp)) - (86400 * 29);
            $add_user = db::name('user')->whereTime('jointime', '>', date('Y-m-d', $start_time))->count();
            $result = db::name('goods_order')
                ->field('id, out_trade_no, money, pay_time, goods_cost')
                ->whereNotNull('pay_time')
                ->whereTime('pay_time', '>', date('Y-m-d', $start_time))
                ->select();
            $_order_num = count($result);
            $c = 0;
            for($i = $start_time; $i < $start_time + (86400 * 30); $i += 86400){
                $xs = date('m/d', $i);
                $column[$c] = $xs;
                $order_num[$c] = 0;
                $order_money[$c] = 0;
                foreach($result as &$val){
                    if($val['pay_time'] >= $i && $val['pay_time'] < $i + 86400){
                        $order_num[$c] += 1;
                        $order_money[$c] += $val['money'];
                        $_order_money += $val['money'];
                        $_cost += $val['goods_cost'];
                        unset($val);
                    }
                }
                $c++;
            }
            $_lirun = $_order_money - $_cost;
        }

        if($shijian == '7tian'){
            $start_time = strtotime(date('Y-m-d 00:00:00', $this->timestamp)) - (86400 * 6);
            $add_user = db::name('user')->whereTime('jointime', '>', date('Y-m-d', $start_time))->count();
            $result = db::name('goods_order')
                ->field('id, out_trade_no, money, pay_time, goods_cost')
                ->whereNotNull('pay_time')
                ->whereTime('pay_time', '>', date('Y-m-d', $start_time))
                ->select();
            $_order_num = count($result);
            $c = 0;
            for($i = $start_time; $i < $start_time + (86400 * 7); $i += 86400){
                $xs = date('m/d', $i);
                $column[$c] = $xs;
                $order_num[$c] = 0;
                $order_money[$c] = 0;
                foreach($result as &$val){
                    if($val['pay_time'] >= $i && $val['pay_time'] < $i + 86400){
                        $order_num[$c] += 1;
                        $order_money[$c] += $val['money'];
                        $_order_money += $val['money'];
                        $_cost += $val['goods_cost'];
                        unset($val);
                    }
                }
                $c++;
            }
            $_lirun = $_order_money - $_cost;
        }

        if($shijian == 'zuotian'){
            $start_time = strtotime(date('Y-m-d 00:00:00', $this->timestamp)) - 86400;
            $add_user = db::name('user')->whereTime('jointime', 'yesterday')->count();
            $result = db::name('goods_order')
                ->field('id, out_trade_no, money, pay_time, goods_cost')
                ->whereNotNull('pay_time')
                ->whereTime('pay_time', 'yesterday')
                ->select();
            $_order_num = count($result);
            $c = 0;
            for($i = $start_time; $i < $start_time + 86400; $i += 3600){
                $xs = date('H:i', $i);
                $column[$c] = $xs;
                $order_num[$c] = 0;
                $order_money[$c] = 0;
                foreach($result as &$val){
                    if($val['pay_time'] >= $i && $val['pay_time'] < $i + 3600){
                        $order_num[$c] += 1;
                        $order_money[$c] += $val['money'];
                        $_order_money += $val['money'];
                        $_cost += $val['goods_cost'];
                        unset($val);
                    }
                }
                $c++;
            }
            $_lirun = $_order_money - $_cost;
        }

        if($shijian == 'today'){
            $start_time = strtotime(date('Y-m-d 00:00:00', $this->timestamp));
            $add_user = db::name('user')->whereTime('jointime', 'today')->count();
            $result = db::name('goods_order')
                ->field('id, out_trade_no, money, pay_time, goods_cost')
                ->whereNotNull('pay_time')
                ->whereTime('pay_time', 'today')
                ->select();
            $_order_num = count($result);
            $c = 0;
            for($i = $start_time; $i < $start_time + 86400; $i += 3600){
                $xs = date('H:i', $i);
                $column[$c] = $xs;
                $order_num[$c] = 0;
                $order_money[$c] = 0;
                foreach($result as &$val){
                    if($val['pay_time'] >= $i && $val['pay_time'] < $i + 3600){
                        $order_num[$c] += 1;
                        $order_money[$c] += $val['money'];
                        $_order_money += $val['money'];
                        $_cost += $val['goods_cost'];
                        unset($val);
                    }
                }
                $c++;
            }
            $_lirun = $_order_money - $_cost;
        }

        $data = [
            'column' => $column,
            'order_num' => $order_num,
            'order_money' => $order_money,
            'add_user' => $add_user,
            '_order_num' => $_order_num,
            '_order_money' => $_order_money,
            '_cost' => $_cost,
            '_lirun' => $_lirun
        ];

//        $this->success('', '', $data);
        return $data;
        return json_encode($data);

    }

}
