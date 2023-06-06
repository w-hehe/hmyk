<?php

namespace app\common\controller;

use app\common\library\Auth;
use hehe\Network;
use hehe\Verify;
use think\Config;
use think\Controller;
use think\Db;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Validate;

/**
 * 前台控制器基类
 */
class Frontend extends Controller
{

    /**
     * 布局模板
     * @var string
     */
    protected $layout = '';

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    public $user = null;

	public $timestamp = null;

    public $options = [];

    public $is_main = false; //是否是主站

    public $host = null;

    public $merchant = []; //分站信息

    public $plugin = []; // 启用的插件列表信息




    public function _initialize()
    {
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
        $modulename = $this->request->module();
        $controllername = Loader::parseName($this->request->controller());
        $actionname = strtolower($this->request->action());

        // 检测IP是否允许
        check_ip_allowed();

		$this->timestamp = time();


        $this->auth = Auth::instance();

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->request('token', \think\Cookie::get('token')));

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            $this->auth->init($token);

            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->redirect(url('/login'));die;
                $this->error(__('Please login first'), 'index/user/login');
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error(__('You have no permission'));
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }
        $this->user = $this->auth->getUser();
        $this->view->assign('user', $this->user);
        $options = db::name('options')->select();
        foreach($options as $val){
            $this->options[$val['name']] = $val['value'];
        }
        $this->options['buy_input'] = empty($this->options['buy_input']) ? [] : unserialize($this->options['buy_input']);

        $host = Network::getHostDomain(false);
        $this->merchant = db::name('merchant')->where(['domain' => $host])->find();
        $this->is_main = $this->merchant ? false : true;
        $this->assign([
            'is_main' => $this->is_main,
            'merchant' => $this->merchant,
            'options' => $this->options
        ]);

        // 语言检测
        $lang = $this->request->langset();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';

        $site = Config::get("site");

        $upload = \app\common\model\Config::upload();

        // 上传信息配置后
        Hook::listen("upload_config_init", $upload);

        // 配置信息
        $config = [
            'site'           => array_intersect_key($site, array_flip(['name', 'cdnurl', 'version', 'timezone', 'languages'])),
            'upload'         => $upload,
            'modulename'     => $modulename,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'jsname'         => 'frontend/' . str_replace('.', '/', $controllername),
            'moduleurl'      => rtrim(url("/{$modulename}", '', false), '/'),
            'language'       => $lang
        ];
        $config = array_merge($config, Config::get("view_replace_str"));

        Config::set('upload', array_merge(Config::get('upload'), $upload));

        // 配置信息后
        Hook::listen("config_init", $config);
        // 加载当前控制器语言包
        $this->loadlang($controllername);
        $this->assign('site', $site);
        $this->assign('config', $config);

        $active_plugins = $this->options['active_plugin'];
        $active_plugins = empty($active_plugins) ? [] : unserialize($active_plugins);
        if ($active_plugins && is_array($active_plugins)) {
            foreach($active_plugins as $plugin) {
                $info = include_once(ROOT_PATH . 'content/' . $plugin . '/info.php');
                $this->plugin[] = $info;
                if($info['type'] == 'basic'){
                    include_once(ROOT_PATH . 'content/' . $plugin . '/' . $plugin . '.php');
                }

            }
        }

        // 如果有使用模板布局
        if ($this->layout) {
            $this->view->engine->layout('layout/' . $this->layout);
        }


    }





	protected function userAgency(){
		$agency = Db::name('user_agency')->select();
		$data = [];
		foreach($agency as $val){
			$data[$val['id']] = $val['discount'];
		}
		return $data;
	}

    /**
     * 获取商品库库存
     */
    function getGoodsStock($sku_id){
        return db::name('sku')->where(['id' => $sku_id])->value('stock');
    }

	/**
	 * 获取商品的购买价格
	 */
	function getGoodsMoney($goods, $agency, $options = []){
		$price = -1;
		if($goods['is_sku'] == 0){
//            echo '<pre>'; print_r($goods);die;
			$price = $this->clPrice(json_decode($goods['sku'][0]['price'], true), $agency);
		}
		if($goods['is_sku'] == 1){
//            print_r($goods['sku']);
//            print_r($options);die;
			foreach($goods['sku'] as $val){

				if($options['sku_id'] == $val['id']){
					$price = $this->clPrice($val['price'], $agency);
					break;
				}
			}
		}
		return $price;
	}

    /**
     * 获取商品的成本价
     */
    function getGoodsCost($goods, $options = []){
        $cost = -1;
        if($goods['is_sku'] == 0){
            $cost = json_decode($goods['sku'][0]['price'], true)['cost_price'];
        }
        if($goods['is_sku'] == 1){
            foreach($goods['sku'] as $val){
                if($options['sku_id'] == $val['id']){
                    $cost = $val['price']['cost_price'];
                    break;
                }
            }
        }
        return empty($cost) ? 0 : $cost;
    }



	protected function clPrice($val, $agency){

        if(empty($this->user) || $this->user['agency_id'] == 0){
            $init_price = empty($val['sale_price']) ? sprintf('%.2f', 0) : $val['sale_price'];
        }else{
//            print_r($val);
            $val['sale_price'] = sprintf('%.2f', $val['sale_price']);
            if(Verify::isEmpty($val['agency_price_' . $this->user['agency_id']])){
                if(isset($agency[$this->user['agency_id']])){
                    $init_price = sprintf('%.2f', $val['sale_price'] * ($agency[$this->user['agency_id']] / 10));
                }else{
                    $init_price = $val['sale_price'];
                }
            }else{
                $init_price = $val['agency_price_' . $this->user['agency_id']];
            }
        }

		return $init_price;
	}
	/**
	 * 完全处理详情
	 */
	protected function goodsDetail($goods, $agency){

//		$goods['sku'] = json_decode($goods['sku'], true);
//		$goods['price'] = json_decode($goods['price'], true);
		$goods['attach'] = json_decode($goods['attach'], true);

		$goods['wholesale'] = json_decode($goods['wholesale'], true);
		foreach($goods['wholesale'] as &$val){
			$val['offer'] = sprintf('%.2f', $val['offer']);
		}
//        echo '<pre>'; print_r($goods);die;
        $sku = json_decode($goods['sku'][0]['price'], true);
		if($goods['is_sku'] == 0){
			$goods['init_stock'] = $goods['stock'];
			$goods['crossed_price'] = sprintf('%.2f', $sku['crossed_price']);
			$goods['init_price'] = $this->clPrice($sku, $agency);
		}

		if($goods['is_sku'] == 1){
			$active = false;
			foreach($goods['sku'] as $key => &$val){

                $val['stock'] = Db::name('sku')->field('id')->where(['goods_id' => $goods['id'], 'id' => $val['id']])->value('stock');

                $val['price'] = json_decode($val['price'], true);
                $val['price']['crossed_price'] = sprintf('%.2f', $val['price']['crossed_price']);

				$val['init_price'] = $this->clPrice($val['price'], $agency);
				if($key == 0){
					$goods['init_stock'] = $val['stock'];
					$goods['init_price'] = $val['init_price'];
					$goods['crossed_price'] = sprintf('%.2f', $val['price']['crossed_price']);
					$goods['jiesheng'] = sprintf('%.2f', $goods['crossed_price'] - $val['init_price']);
				}
				if($active == false && $val['stock'] > 0) {
					$active = true;
					$val['active'] = true;
					$goods['init_stock'] = $val['stock'];
					$goods['init_price'] = $val['init_price'];
					$goods['crossed_price'] = sprintf('%.2f', $val['price']['crossed_price']);
					$goods['jiesheng'] = sprintf('%.2f', $goods['crossed_price'] - $val['init_price']);
				}
			}
		}

		$goods['sku_name'] = empty($goods['sku_name']) ? '类型' : $goods['sku_name'];
		$goods['jiesheng'] = empty($goods['crossed_price']) ? false : sprintf('%.2f', $goods['crossed_price'] - $goods['init_price']);
//        echo '<pre>'; print_r($goods);die;
		return $goods;
	}









    /**
     * 加载语言文件
     * @param string $name
     */
    protected function loadlang($name)
    {
        $name = Loader::parseName($name);
        $name = preg_match("/^([a-zA-Z0-9_\.\/]+)\$/i", $name) ? $name : 'index';
        $lang = $this->request->langset();
        $lang = preg_match("/^([a-zA-Z\-_]{2,10})\$/i", $lang) ? $lang : 'zh-cn';
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $lang . '/' . str_replace('.', '/', $name) . '.php');
    }

    /**
     * 渲染配置信息
     * @param mixed $name  键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }

    /**
     * 刷新Token
     */
    protected function token()
    {
        $token = $this->request->param('__token__');

        //验证Token
        if (!Validate::make()->check(['__token__' => $token], ['__token__' => 'require|token'])) {
            $this->error(__('Token verification error'), '', ['__token__' => $this->request->token()]);
        }

        //刷新Token
        $this->request->token();
    }
}
