<?php

use think\Db;
use think\Cache;
use think\Session;
use app\common\controller\Hm;



/**
 * 获取模板配置
*/
function get_config(){
    $template_config = file_get_contents(ROOT_PATH . "public/content/template/default/setting.json");
    $template_config = json_decode($template_config, true);
    return $template_config;
}


/**
 * 获取订单列表
*/
function order_list(){


    $order = Hm::orderList(input());

    echo $order;die;

}

/**
 * 获取可支付列表
 */
function pay_list(){
	$where = [
		'option_name' => 'active_pay',
	];
	$list = [
		'alipay' => false,
		'wxpay' => false,
		'qqpay' => false,
        'apple_pay' => false,
        'card' => false,
	];

	$active_pay = db::name('options')->where($where)->value('option_content');
    $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

//    echo '<pre>'; print_r($active_pay);die;
	foreach($active_pay as $val){
		if(in_array('alipay', $val['pay_type'])) $list['alipay'] = true;
        if(in_array('wxpay', $val['pay_type'])) $list['wxpay'] = true;
        if(in_array('qqpay', $val['pay_type'])) $list['qqpay'] = true;
        if(in_array('apple_pay', $val['pay_type'])) $list['apple_pay'] = true;
        if(in_array('card', $val['pay_type'])) $list['card'] = true;
	}

	return $list;

}


/**
 * 获取单个商品信息
*/
function goods_info($id){



    $goods = Hm::getGoodsInfo($id);

    $images = empty($goods["images"]) ? '' : explode(',', $goods['images']);
    $goods['cover'] = empty($images[0]) ? '' : $images[0];
    $attach = [];
    if($goods['attach_id'] > 0 && $goods['deliver'] == 1){
        $attach = db::name('attach')->where(['id' => $goods['attach_id']])->find();
        if($attach){
            $attach = json_decode($attach['value_json'], true);
        }
    }
    $goods['attach'] = $attach;

//    echo '<pre>'; print_r($goods);die;

    return $goods;
}

/**
 * 首页分类和商品
*/
function goods_list($options){
    $category = db::name('category')->where(['status' => 'normal'])->order('weigh desc')->select();

    $list = [];

    $user = Hm::getUser();

//    echo '<pre>'; print_r($user);die;

    $model = new \app\admin\model\Goods;

    foreach($category as $key => $val){

        $where = [
            'shelf' => 0,
            'deletetime' => ['exp', Db::raw('is null')],
            'category_id' => $val['id'],
        ];
		if($val['goods_sort'] == 0 || $val['goods_sort'] == 1){ //id降序
            $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, id desc')->select();
		}else if($val['goods_sort'] == 2){ //id升序
		    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, id asc')->select();
		}else if($val['goods_sort'] == 3){ //价格降序
		    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, price desc')->select();
		}else if($val['goods_sort'] == 4){ //价格升序
		    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, price asc')->select();
		}else if($val['goods_sort'] == 5){ //销量降序
		    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, sales desc')->select();
		}else if($val['goods_sort'] == 6){ //销量升序
		    $goods = $model->with(['category', 'gradePrice'])->where($where)->order('sort desc, sales asc')->select();
		}
        $goods = $goods->toArray();

//        echo '<pre>'; print_r($goods);die;

        $list[$key] = $val;
        $list[$key]['goods'] = [];
        $list[$key]['goods_num'] = 0;

        foreach($goods as $k => $v){

            if($val['id'] == $v['category_id']){
                $list[$key]['goods'][] = Hm::handle_goods($goods[$k], $user, $options);
                unset($goods[$k]);
                $list[$key]['goods_num']++;
            }
        }
    }
    return $list;
}
