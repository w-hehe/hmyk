<?php

use think\Route;

/**
 * 红盟云卡
 */
Route::rule('/', "index/index/index"); //首页
Route::rule('/goodscate/:category_id', "index/index/index", 'get', [], ['category_id' => '\d+']); //分类页
Route::rule('/login', "user/user/login"); //登录页


Route::rule('/goods/:id', "index/index/goods", 'get', [], ['id' => '\d+']); //商品详情
Route::rule('/pay/goods', "index/pay/goods"); //购买商品


Route::rule('/user', "user/user/index"); //个人主页
Route::rule('/user/ajax/lang', "user/ajax/lang"); //加载语言包
Route::rule('/order', "user/user/order"); //订单列表
Route::rule('/balance', "user/balance/index"); //我的余额
Route::rule('/profile', "user/user/profile"); //我的资料
Route::rule('/changepwd', "user/user/changepwd"); //修改密码
Route::rule('/bill', "user/bill/index"); //账单记录
Route::rule('/find_order', "user/user/findOrder"); //查找订单
Route::rule('/agency', "user/user/agency"); //成为代理
Route::rule('/merchant', "user/merchant/index"); //开通分站
Route::rule('/spread', "user/user/spread"); //我要推广
Route::rule('/promotion_log', "user/user/promotionLog"); //推广记录
Route::rule('/invite', "user/invite/index"); //邀请进入
Route::rule('/branch', "user/branch/index"); //邀请进入


Route::rule('/scan', "index/scan/index"); //扫码页面
Route::rule('/submit', "index/submit/index");

Route::rule('/register', "user/user/register"); //注册页
Route::rule('/logout', "user/user/logout"); //退出登录

Route::rule('/blog/:id', "blog/detail/index", 'get', [], ['id' => '\d+']); //博客详情
Route::rule('/blog', "blog/index/index"); //博客列表


Route::rule('/custom/:action', "index/custom/index"); //自定义路由













