<?php

namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Yuns extends Command {

    public $timestamp = null;
    public $prefix = null;
    public $dock_files = [];

    protected function configure() {
        $this->setName('yuns')->setDescription('红盟云卡系统定时任务');
    }

    protected function execute(Input $input, Output $output) {
        $this->timestamp = time();
        $output->writeln("开始执行任务, " . date('Y-m-d H:i:s', $this->timestamp));
        $this->prefix = \think\Db::getConnection()->getConfig('prefix');

        //同步对接商品库存
        $this->syncGoodsStock($output);
        //同步对接订单状态
        $this->syncOrderStatus($output);

        $s = time() - $this->timestamp;
        $output->writeln("任务执行完毕， 本次执行耗时：{$s}秒");
    }

    /**
     * 同步对接商品库存
     */
    public function syncGoodsStock($output){
        $timestamp = time();
        $sql = <<<sql
select 
    g.id, g.dock_id, g.remote_id, g.name, g.stock, d.type dock_type, d.info dock  
from {$this->prefix}goods g left join {$this->prefix}dock d on g.dock_id=d.id 
where g.goods_type='dock' and deletetime is null;
sql;
        $goods = Db::query($sql);

        $data = [];

        foreach($goods as $val){
            if(!in_array($val['dock_type'], $data)){
                $data[$val['dock_type']] = [];
                foreach($goods as $v){
                    if($val['dock_type'] == $v['dock_type']) $data[$val['dock_type']][] = $v;
                }
            }
        }

        foreach($data as $key => $val){
            $dock_file = ROOT_PATH . '/public/content/dock/' . $key . '/' . ucfirst($key) . '.php';
            if(!in_array($dock_file, $this->dock_files)){
                include $dock_file;
                $this->dock_files[] = $dock_file;
            }

            $objName = ucfirst($key) . 'Dock';
            $dockObj = new $objName();
            foreach($val as $v){
                $result = $dockObj->goodsInfo(json_decode($v['dock'], true), $v['remote_id']);
                if($result['code'] == 400) $output->writeln($result['msg']);
                db::name('goods')->where(['id' => $v['id']])->update(['stock' => $result['data']['stock']]);
                if($key == 'kakayun'){
                    sleep(3);
                }else{
                    sleep(1);
                }

            }
        }
        $s = time() - $timestamp;
        $output->writeln("商品库存同步完成，耗时：{$s}秒");
    }

    /**
     * 同步对接订单状态
     */
    public function syncOrderStatus($output){
        $timestamp = time();
        $sql = <<<sql
select 
    o.remote_order_no, o.goods_id, d.type dock_type, d.info dock, o.id order_id from {$this->prefix}order o 
    left join {$this->prefix}goods g on o.goods_id=g.id 
    left join {$this->prefix}dock d on g.dock_id=d.id 
where o.remote_order_no is not null and o.remote_order_no != '' and o.status != 'fail' and o.status != 'success';
sql;
        $order = Db::query($sql);
//        print_r($order);

        $data = [];
        foreach($order as $val){
            if(!in_array($val['dock_type'], $data)){
                $data[$val['dock_type']] = [];
                foreach($order as $v){
                    if($val['dock_type'] == $v['dock_type']) $data[$val['dock_type']][] = $v;
                }
            }
        }
//        print_r($data);

        foreach($data as $key => $val){
            $dock_file = ROOT_PATH . '/public/content/dock/' . $key . '/' . ucfirst($key) . '.php';
            if(!in_array($dock_file, $this->dock_files)){
                include $dock_file;
                $this->dock_files[] = $dock_file;
            }
            $objName = ucfirst($key) . 'Dock';
            $dockObj = new $objName();
            foreach($val as $v){
                $result = $dockObj->orderInfo(['remote_order_no' => $v['remote_order_no']], json_decode($v['dock'], true));
                //        print_r($result);die;
                if($result['code'] == 400){
                    return json(['code' => 400, 'msg' => $result['msg']]);
                }
                $remote_order = $result['data']['order'];
                db::name('order')->where(['id' => $v['order_id']])->update(['status' => $remote_order['status']]);
                if(!empty($remote_order['cdk'])){
                    $insert_sold = [];
                    foreach($remote_order['cdk'] as $vs){
                        $insert_sold[] = [
                            'order_id' => $v['order_id'],
                            'content' => $vs,
                            'create_time' => $this->timestamp
                        ];
                    }
                    db::name('sold')->insertAll($insert_sold);
                }
                if($key == 'kakayun'){
                    sleep(3);
                }else{
                    sleep(1);
                }

            }


        }

        $s = time() - $timestamp;
        $output->writeln("订单状态同步完成，耗时：{$s}秒");



    }




}














