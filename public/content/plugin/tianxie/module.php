<?php

use think\Db;
use think\Cache;
use think\Session;
use app\common\controller\Hm;
use think\Request;


/**
 * 获取模板配置
*/
function get_config(){
    $template_config = file_get_contents(ROOT_PATH . "public/content/template/tianxie/setting.json");
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

function _order_list(){
    $params = empty($params) ? input() : $params;
    $search_type = empty($params['search_type']) ? false : $params['search_type'];

    if($search_type == 'voucher'){
        if(empty($params['account'])){
            return 'voucher';
        }
        $where = [
            'o.account' => $params['account'],
            'o.password' => $params['password']
        ];
        if(isset($params['search_password'])){
            unset($where['password']);
        }
    }
    if($search_type == 'orderno') {

        if(empty($params['orderno'])){
            return 'orderno';
        }
        $where = [
            'o.order_no' => $params['orderno']
        ];
    }
    if(empty($search_type)){
        $user = Hm::getUser();
        $where = [
            'o.uid' => $user['id'],
        ];
    }

    $result = db::name('order')->alias('o')
        ->join('goods g', 'g.id=o.goods_id')
        ->field('o.*, g.name goods_name')
        ->where($where)->order('o.id desc')->paginate(10, false, [
            'query' => Request::instance()->param(),//不丢失已存在的url参数
        ]);

    $timestamp = time();
    $list = $result->items();
    $page = $result->render();

    foreach($list as &$val){

        if($val['status'] == 'yiguoqi'){
            $val['s'] = '订单已失效';
        }elseif($val['status'] == 'weizhifu' && $timestamp - $val['create_time'] >= 600){
            $val['s'] = '订单已失效';
            db::name('order')->where(['id' => $val['id']])->update(['status' => 'yiguoqi']);
            $val['status'] = 'yiguoqi';
        }elseif($val['status'] == 'weizhifu'){
            $val['s'] = '待付款';
        }elseif($val['status'] == 'daifahuo'){
            $val['s'] = '待发货';
        }elseif($val['status'] == 'yifahuo'){
            $val['s'] = '待收货';
        }elseif($val['status'] == 'success'){
            $val['s'] = '交易完成';
            $val['s_color'] = '#52c41a';
        }else{
            $val['s'] = '订单状态错误';
            $val['s_color'] = '#d20707';
        }
        $val['timestamp'] = date('Y-m-d H:i:s', $val['create_time']);
    }

    return [
        'data' => true,
        'list' => $list,
        'page' => $page
    ];

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
	];

	$active_pay = db::name('options')->where($where)->value('option_content');
    $active_pay = empty($active_pay) ? [] : json_decode($active_pay, true);

//    echo '<pre>'; print_r($active_pay);die;
	foreach($active_pay as $val){
		if(in_array('alipay', $val['pay_type'])) $list['alipay'] = true;
        if(in_array('wxpay', $val['pay_type'])) $list['wxpay'] = true;
        if(in_array('qqpay', $val['pay_type'])) $list['qqpay'] = true;
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
 * 最新商品
 */
function new_goods($options){


    $user = Hm::getUser();

    $model = new \app\admin\model\Goods;

    $where = [
        'shelf' => 0,
        'deletetime' => ['exp', Db::raw('is null')],
    ];

    $goods = $model->with(['category', 'cdkey'])->where($where)->order('id desc')->limit(10)->select();

    $goods = $goods->toArray();

    foreach($goods as $key => $val){
        $goods[$key] = Hm::handle_goods($val, $user, $options);
    }
    return $goods;


}

/**
 * 首页分类和商品
*/
function goods_list($options){
    $category = db::name('category')->where(['status' => 'normal'])->order('weigh desc')->select();

    $list = [];

    $user = Hm::getUser();

    $model = new \app\admin\model\Goods;
//echo '<pre>';
    foreach($category as $key => $val){

        $where = [
            'shelf' => 0,
            'deletetime' => ['exp', Db::raw('is null')],
            'category_id' => $val['id'],
        ];
		if($val['goods_sort'] == 0 || $val['goods_sort'] == 1){ //id降序
            $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, id desc')->select();
		}else if($val['goods_sort'] == 2){ //id升序
		    $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, id asc')->select();
		}else if($val['goods_sort'] == 3){ //价格降序
		    $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, price desc')->select();
		}else if($val['goods_sort'] == 4){ //价格升序
		    $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, price asc')->select();
		}else if($val['goods_sort'] == 5){ //销量降序
		    $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, sales desc')->select();
		}else if($val['goods_sort'] == 6){ //销量升序
		    $goods = $model->with(['category', 'cdkey', 'gradePrice'])->where($where)->order('sort desc, sales asc')->select();
		}
        $goods = $goods->toArray();

        $list[$key] = $val;
        $list[$key]['goods'] = [];
        $list[$key]['goods_num'] = 0;

//        echo '<pre>'; print_r($user);die;

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
