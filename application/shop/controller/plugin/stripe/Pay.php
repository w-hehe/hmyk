<?php

namespace app\shop\controller\plugin\stripe;

use app\common\controller\Fun;
use app\common\controller\Hm;
use app\shop\controller\Base;
use think\Db;
use think\Session;

class Pay extends Base {

    private static $key;

    public function initialize() {
        parent::initialize();
    }



    public function pay() {

        $plugin_path = ROOT_PATH . "public/content/plugin/stripe_pay/";

        $info = file_get_contents("{$plugin_path}stripe_pay_setting.json");
        $info = json_decode($info, true);

        $out_trade_no = $this->request->param('out_trade_no');
        $pay_type = $this->request->param('pay_type');
        $cmd = $this->request->param('cmd');

        $order = db::name('order')->where(['order_no' => $out_trade_no])->find();
        $goods = db::name('goods')->where(['id' => $order['goods_id']])->find();
        $host = getHostDomain();

        if(empty($info['image'])){
            if(empty($goods['images'])){
                $goods_cover = $host . '/assets/img/none.jpg';
            }else{
                $arr = explode(',', $goods['images']);
                $goods_cover = $host . $arr[0];
            }
        }else{
            $goods_cover = $host . $info['image'];
        }
        header('Content-Type: application/json');

        \Stripe\Stripe::setApiKey($info['private_key']);

        if($cmd == 'order'){
            $success_url = $host . '/order.html?order_no=' . $order['order_no'];
            $cancel_url = $host . '/goods/' . $goods['id'] . '.html';
        }else{
            $success_url = $host . '/user.html';
            $cancel_url = $host . '/user.html';
        }


        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => [$pay_type], //支付类型
            'payment_method_options' => ['wechat_pay' => ['client' => 'web']],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'cny', //货币类型
                        'unit_amount' => $order['money'] * 100, //支付金额
                        'product_data' => [
                            'name' => $out_trade_no, //商品名称
                            'images' => [$goods_cover], //商品图片
                        ]
                    ],
                    'quantity' => 1
                ]
            ],
            'mode' => 'payment',
            'success_url' => $success_url,
            'cancel_url' => $cancel_url
        ]);

        db::name('order')->where(['id' => $order['id']])->update(['remote_order_no' => $checkout_session->payment_intent]);

        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
        die;

    }

    // 支付成功通知
    public function successNotify($private_key, $content) {
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($private_key);

        //        $payload = file_get_contents('php://input');


        $event = null;

        try {
            $event = \Stripe\Event::constructFrom($content);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        // db::name('test')->insert(['content' => json_encode($content), 'createtime' => $this->timestamp]);

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':

                $succeeded = $event->data->object;

                if ($succeeded->status == 'succeeded') {

                    $payment_intent = $succeeded->id;
                    // var_dump($succeeded);die;

                    // 查询订单详情
                    $order_info = Db::name('order')->where('remote_order_no', $payment_intent)->find();

                    if(empty($order_info['pay_time']) || $order_info['pay_time'] == 0){
                        return $order_info['order_no'];
                    }else{

                        return false;
                    }


                }
                break;
            default:
                echo 'Received unknown event type ' . $event->type;
        }
        http_response_code(200);
    }

}
