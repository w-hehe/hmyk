<?php

namespace app\api\controller;

use app\common\controller\Hm;
use think\Db;

class Goods extends Base {


    /**
     * 下单
     * 1, 判断商品库存
     * 2, 判断用户余额
     * 3, 写入订单记录
     * 4, 修改商品库存
     * 5, 扣除用户余额
     */
    public function buy(){
        $goods_id = $this->get['goods_id'];
        $buy_num = $this->get['buy_num'];
        $prefix = \think\Db::getConnection()->getConfig('prefix');
        $inputs = empty($this->get['inputs']) ? [] : $this->get['inputs'];

        //查询商品信息和商品价格
        $sql = <<<sql
        select g.id, g.category_id, g.name goods_name, g.buy_price, g.goods_type, 
               g.inputs, g.details, g.images, g.stock, g.dock_id, g.remote_id, g.buy_default, 
               coalesce(p.price, g.price) as price
        from {$prefix}goods as g 
        left join {$prefix}price as p on 
            p.goods_id=g.id and p.grade_id={$this->user['agent']} 
        where g.deletetime is null and g.id={$goods_id} limit 1;
sql;
        $result = db::query($sql);
        if(empty($result[0])) return json(['code' => 400, 'msg' => '商品不存在']);
        $goods = $result[0];
        if($buy_num > $goods['stock']) return json(['code' => 400, 'msg' => '商品库存不足']);
        if($this->user['money'] < $goods['price'] * $buy_num) return json(['code' => 400, 'msg' => '商户余额不足']);
        db::name('user')->where(['id' => $this->user['id']])->setDec('money', $goods['price'] * $buy_num);


        //写入订单表
        $insert_order = [
            'order_no' => $this->generateOrderNo(), //订单号
            'create_time' => $this->timestamp, //订单生成时间
            'pay_time' => $this->timestamp, //支付时间
            'pay_type' => 'balance', //支付方式
            'uid'         => $this->user["id"], //用户id
            'goods_id'    => $goods_id, //商品id
            'buy_num'   => $buy_num, //购买数量
            'goods_money' => $goods['price'], //商品单价
            'money'       => $goods['price'] * $buy_num, //订单金额
            'remote_money' => $goods['buy_price'] * $buy_num, //进货价
            'inputs' => json_encode($inputs), //对接订单参数
            // 'attach' => json_encode($attach),
            'ip' => getClientIp(),
        ];

        $order_id = db::name('order')->insertGetId($insert_order);
        $order = $insert_order;
        $order['id'] = $order_id;

        $result = Hm::handleOrder($goods, $order, $this->site);
        // var_dump($result);die;
        return json($result);
    }

    /**
     * 获取单个商品信息
     */
    public function info(){
        $goods_id = $this->get['goods_id'];
        $prefix = \think\Db::getConnection()->getConfig('prefix');
        $sql = <<<sql
        select g.id goods_id, g.category_id, g.name goods_name, 
               g.inputs, g.details, g.images, g.stock, 
               coalesce(p.price, g.price) as price
        from {$prefix}goods as g 
        left join {$prefix}price as p on 
            p.goods_id=g.id and p.grade_id={$this->user['agent']} 
        where g.id={$goods_id};
sql;
        $result = db::query($sql);
        if(!$result){
            return json(['code' => 200, 'msg' => '商品id不存在']);
        }
        $goods = $result[0];

        if(!empty($goods['images'])){
            $images_arr = explode(',', $goods['images']);
            $images = [];
            foreach($images_arr as $val){
                $images[] = getHostDomain() . $val;
            }
            $goods['images'] = implode(',', $images);
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $goods]);
    }


    /**
     * 获取全部商品
     * @param category_id
     */
    public function goods_list(){
        $prefix = \think\Db::getConnection()->getConfig('prefix');
        $where = "g.deletetime is null";
        if(!empty($this->get['category_id'])) $where .= " and g.category_id={$this->get['category_id']}";
        // echo $where;die;

        /*$field = "g.id goods_id, g.category_id, g.name goods_name, g.inputs, g.details, g.images, g.stock, coalesce(p.price, g.price) as price, g.attach_id";
        $goods = db::name('goods')->alias('g')->field($field)
                    ->join('price p', 'p.goods_id and p.grade_id=' . $this->user['agent'], 'left')
                    ->join('attach a', 'a.id=g.attach_id', 'left')
                    ->where($where)->whereNull('deletetime')->select();
        return json(['code' => 200, 'msg' => 'success', 'data' => $goods]);*/
        $sql = <<<sql
        select g.id goods_id, g.category_id, g.name goods_name, 
               g.inputs, g.details, g.images, g.stock, a.value_json attach,
               coalesce(p.price, g.price) as price
        from {$prefix}goods as g 
        left join {$prefix}price as p on 
            p.goods_id=g.id and p.grade_id={$this->user['agent']} 
        left join {$prefix}attach as a on a.id=g.attach_id where {$where};
sql;
        $goods = db::query($sql);

        foreach($goods as $key => $val){
            if(!empty($val['images'])){
                $images_arr = explode(',', $val['images']);
                $images = [];
                foreach($images_arr as $v){
                    $images[] = getHostDomain() . $v;
                }
                $goods[$key]['images'] = implode(',', $images);
            }
        }

        return json(['code' => 200, 'msg' => 'success', 'data' => $goods]);
    }


    /**
     * 获取商品分类
     * @param null
     */
    public function category(){
        $where = [
            'status' => 'normal'
        ];
        $field = "id, name";
        $category = db::name('category')->field($field)->where($where)->order('weigh desc')->select();
        return json(['code' => 200, 'msg' => 'success', 'data' => $category]);
    }



}
