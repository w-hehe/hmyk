<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\common\library\Email;
use app\common\model\Config as ConfigModel;
use think\Cache;
use think\Db;
use think\Exception;
use think\Validate;
use think\Session;

/**
 * 系统配置
 *
 * @icon   fa fa-cogs
 * @remark 可以在此增改系统的变量和分组,也可以自定义分组和变量,如果需要删除请从数据库中删除
 */
class Config extends Backend
{

    /**
     * @var \app\common\model\Config
     */
    protected $model = null;
    protected $noNeedRight = ['check', 'rulelist', 'selectpage', 'get_fields_list'];

    public function _initialize()
    {
        parent::_initialize();

        $this->model = model('Config');

        ConfigModel::event('before_write', function ($row) {
            if (isset($row['name']) && $row['name'] == 'name' && preg_match("/fast" . "admin/i", $row['value'])) {
                throw new Exception(__("Site name incorrect"));
            }
        });

    }

    /**
     * 查看
     */
    public function index()
    {
        $siteList = [];

        $groupList = [
            'basic' => '基础配置',
//            'money' => '资金配置',
            'other' => '其他配置'
        ];

        foreach ($groupList as $k => $v) {
            $siteList[$k]['name'] = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list'] = [];
        }

//        $siteResult = $this->model->all();
        $siteResult = Db::name('config')->order('id asc')->select();

//        echo '<pre>'; print_r($siteResult);die;

        foreach ($siteResult as $k => $v) {
            if (!isset($siteList[$v['group']])) {
                continue;
            }
//            $value = $v->toArray();
            $v['title'] = __($v['title']);
            if (in_array($v['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $v['value'] = explode(',', $v['value']);
            }
            $v['content'] = json_decode($v['content'], true);
            $v['tip'] = htmlspecialchars($v['tip']);
            $siteList[$v['group']]['list'][] = $v;
        }
        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false;
            $index++;
        }


//        echo '<pre>'; print_r($siteList);die;


        $this->view->assign('siteList', $siteList);
        $this->view->assign('typeList', ConfigModel::getTypeList());

        $this->view->assign('ruleList', ConfigModel::getRegexList());

        $this->view->assign('groupList', $groupList);

        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a", [], 'trim');
            if ($params) {
                foreach ($params as $k => &$v) {
                    $v = is_array($v) && $k !== 'setting' ? implode(',', $v) : $v;
                }
                if (in_array($params['type'], ['select', 'selects', 'checkbox', 'radio', 'array'])) {
                    $params['content'] = json_encode(ConfigModel::decode($params['content']), JSON_UNESCAPED_UNICODE);
                } else {
                    $params['content'] = '';
                }
                try {
                    $result = $this->model->create($params);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    try {
                        $this->refreshFile();
                    } catch (Exception $e) {
                        $this->error($e->getMessage());
                    }
                    $this->success();
                } else {
                    $this->error($this->model->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     * @param null $ids
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
            $row = $this->request->post("row/a", [], 'trim');
            if ($row) {
                $configList = [];
                foreach ($this->model->all() as $v) {
                    if (isset($row[$v['name']])) {
                        $value = $row[$v['name']];
                        if (is_array($value) && isset($value['field'])) {
                            $value = json_encode(ConfigModel::getArrayData($value), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value'] = $value;
                        $configList[] = $v->toArray();
                    }
                }
                try {
                    $this->model->allowField(true)->saveAll($configList);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                try {
                    $this->refreshFile();
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
    }

    /**
     * 删除
     * @param string $ids
     */
    public function del($ids = "")
    {
        $name = $this->request->post('name');
        $config = ConfigModel::getByName($name);
        if ($name && $config) {
            try {
                $config->delete();
                $this->refreshFile();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            $this->success();
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 刷新配置文件
     */
    protected function refreshFile()
    {
        $config = [];
        foreach ($this->model->all() as $k => $v) {
            $value = $v->toArray();
            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files'])) {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array') {
                $value['value'] = (array)json_decode($value['value'], true);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(
            CONF_PATH . 'extra' . DS . 'site.php',
            '<?php' . "\n\nreturn " . var_export_short($config) . ";\n"
        );
    }

    /**
     * 检测配置项是否存在
     * @internal
     */
    public function check()
    {
        $params = $this->request->post("row/a");
        if ($params) {
            $config = $this->model->get($params);
            if (!$config) {
                $this->success();
            } else {
                $this->error(__('Name already exist'));
            }
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 规则列表
     * @internal
     */
    public function rulelist()
    {
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $keyValue = $this->request->request("keyValue", "");

        $keyValueArr = array_filter(explode(',', $keyValue));
        $regexList = \app\common\model\Config::getRegexList();
        $list = [];
        foreach ($regexList as $k => $v) {
            if ($keyValueArr) {
                if (in_array($k, $keyValueArr)) {
                    $list[] = ['id' => $k, 'name' => $v];
                }
            } else {
                $list[] = ['id' => $k, 'name' => $v];
            }
        }
        return json(['list' => $list]);
    }

    /**
     * 发送测试邮件
     * @internal
     */
    public function emailtest(){
        $row = $this->request->post('row/a');

//         print_r(session::get('admin'));die;
//        print_r($row);die;
        $admin = session::get('admin');

        $receiver = $admin['email'];
        if ($receiver) {
            if (!Validate::is($receiver, "email")) {
                $this->error(__('管理员邮箱错误'));
            }
            if(empty($row['mail_smtp_host'])){
                $this->error('请填写SMTP服务器');
            }

            if(empty($row['mail_smtp_pass'])){
                $this->error('请填写SMTP密码');
            }
            if(empty($row['mail_smtp_user'])){
                $this->error('请填写发件人名称');
            }
            if(empty($row['mail_from'])){
                $this->error('请填写发件人邮箱');
            }

            \think\Config::set('site', array_merge(\think\Config::get('site'), $row));

//            print_r($row);die;

            $email = new Email($row);
            $result = $email
                ->to($receiver)
                ->subject(__("红盟云卡测试邮件"))
                ->message('这是一封来自红盟云卡开源系统的校验邮件,用于校验邮件配置是否正常!')
                ->send();


            if ($result) {
                $this->success();
            } else {
                // SMTP Error: Could not authenticate
                if(strstr($email->getError(), "Could not authenticate")){
                    $this->error('SMTP密码错误：无法进行身份验证');
                }
                if(strstr($email->getError(), "Invalid address:  (From): root@localhost")){
                    $this->error("发件人邮件地址无效");
                }
                if(strstr($email->getError(), "SMTP connect() failed")){
                    $this->error("SMTP服务器连接失败");
                }
                if(strstr($email->getError(), "535 Login Fail. Please enter your authorization code to login")){
                    $this->error("SMTP授权码与发件人邮箱不匹配，请检查");
                }
                $this->error($email->getError());
            }

        } else {
            $this->error(__('当前管理员未绑定邮箱'));
        }
    }

    public function selectpage()
    {
        $id = $this->request->get("id/d");
        $config = \app\common\model\Config::get($id);
        if (!$config) {
            $this->error(__('Invalid parameters'));
        }
        $setting = $config['setting'];
        //自定义条件
        $custom = isset($setting['conditions']) ? (array)json_decode($setting['conditions'], true) : [];
        $custom = array_filter($custom);

        $this->request->request(['showField' => $setting['field'], 'keyField' => $setting['primarykey'], 'custom' => $custom, 'searchField' => [$setting['field'], $setting['primarykey']]]);
        $this->model = \think\Db::connect()->setTable($setting['table']);
        return parent::selectpage();
    }

    /**
     * 获取表列表
     * @internal
     */
    public function get_table_list()
    {
        $tableList = [];
        $dbname = \think\Config::get('database.database');
        $tableList = \think\Db::query("SELECT `TABLE_NAME` AS `name`,`TABLE_COMMENT` AS `title` FROM `information_schema`.`TABLES` where `TABLE_SCHEMA` = '{$dbname}';");
        $this->success('', null, ['tableList' => $tableList]);
    }

    /**
     * 获取表字段列表
     * @internal
     */
    public function get_fields_list()
    {
        $table = $this->request->request('table');
        $dbname = \think\Config::get('database.database');
        //从数据库中获取表字段信息
        $sql = "SELECT `COLUMN_NAME` AS `name`,`COLUMN_COMMENT` AS `title`,`DATA_TYPE` AS `type` FROM `information_schema`.`columns` WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION";
        //加载主表的列
        $fieldList = Db::query($sql, [$dbname, $table]);
        $this->success("", null, ['fieldList' => $fieldList]);
    }
}
