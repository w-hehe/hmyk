<?php

use think\Route;


//页面
Route::rule('user/recharge','user/balance/recharge'); //账户充值
Route::get('complain-list','shop/complain/complainList'); //投诉列表
Route::rule('complain','shop/complain/index'); //投诉页面
Route::get('upgrade','index/index/upgrade'); //升级
Route::get('password', 'index/index/password'); //重置密码
Route::get('/','shop/index/index'); //首页
Route::get('category/:category_id','shop/index/index'); //分类商品列表
Route::get('category','shop/category/index'); //分类
Route::get('service','shop/service/index'); //客服
Route::get('login','shop/login/index'); //登录
Route::get('register','shop/register/index'); //注册
Route::get('notice','shop/notice/index'); //站内消息
Route::get('balance','shop/wallet/balance'); //余额
Route::get('setting','shop/user/setting'); //设置
Route::get('logout','shop/user/logout'); //退出登录
Route::get('cashout','shop/wallet/cashout'); //提现
Route::get('recharge/[:money]','shop/wallet/recharge'); //充值
Route::get('bill','shop/wallet/bill'); //账单
Route::get('goods/:id','shop/goods/detail'); //商品详情
Route::rule('avatar/[:id]/[:name]/[:type]/[:lastModifiedDate]/[:size]/[:file]','shop/user/avatar'); //上传头像
Route::rule('nickname','shop/user/nickname'); //修改昵称
Route::rule('gender','shop/user/gender'); //修改性别
Route::rule('email','shop/user/email'); //绑定邮箱
Route::rule('alipay','shop/user/alipay'); //绑定支付宝
Route::rule('search','shop/search/index'); //搜索页
Route::get('list/:category','shop/goods/lists'); //商品列表
Route::rule('aliprecreate/:out_trade_no/:table/[:img]','shop/pay.pay/aliprecreate'); //支付宝当面付
Route::rule('aliprecreate','shop/pay.pay/aliprecreate'); //支付宝当面付
Route::rule('getorderstatus/:out_trade_no','shop/order/getorderstatus'); //获取订单支付状态
Route::rule('get_recharge_status/:out_trade_no','shop/order/get_recharge_status'); //获取订单支付状态
Route::rule('tourist_key','shop/index/get_tourist_key'); //获取游客标识
Route::rule('tourist_login','shop/index/tourist_login'); //游客登录
Route::rule('post_order', 'shop/order/postOrder'); //提交订单
Route::rule('confirm','shop/pay.pay/confirm'); //确认订单页面
Route::rule('pay','shop/pay.pay/pay'); //提交支付
// Route::rule('notify/:receive_type/:notice_type/:pay_type','shop/notify/index'); //回调通知地址
// Route::rule('recharge_notify/:receive_type/:notice_type/:pay_type','shop/notify/recharge_notify'); //回调通知地址
Route::rule('order','shop/order/index');
Route::rule('user_info','user/index/info');
Route::rule('dock_info','user/index/dockInfo');
Route::rule('reset_secret','user/index/resetSecret');

//接口
Route::post('login','shop/login/index'); //登录
Route::post('register','shop/register/index'); //注册

Route::rule('/page/:page', "shop/page/index"); //自定义页面

Route::rule("module/:func", "shop/module/index"); //分配方法


return [
    //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
    '__alias__'   => [
    ],
    //变量规则
    '__pattern__' => [
    ],
    //        域名绑定到模块
    //        '__domain__'  => [
    //            'admin' => 'admin',
    //            'api'   => 'api',
    //        ],
];
