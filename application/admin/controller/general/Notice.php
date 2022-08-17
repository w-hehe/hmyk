<?php

namespace app\admin\controller\general;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 *
 *
 * @icon fa fa-circle-o
 */
class Notice extends Backend {

    /**
     * Notice模型对象
     * @var \app\admin\model\general\Notice
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\general\Notice;

    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */






    public function edit($ids = null) {
        $className = $this->request->param('ids');
        $path = ROOT_PATH . 'extend/notice/' . lcfirst($className) . '/';
        if (false === $this->request->isPost()) {
            $data = include $path . 'setting.php';
            $this->assign([
                'data' => $data
            ]);
            return $this->view->fetch($path . 'setting.html');
        }
        $params = $this->request->post('row/a');

        $str = "<?php\nreturn [\n";
        foreach($params as $key => $val){
            $str .= '"' . $key . '"' . "\t=>\t" . '"' . $val . '",' . "\n";
        }
        $str .= "];";

        file_put_contents($path . 'setting.php', $str);

        $this->success();
    }

    public function _edit(){
        $post = $this->request->post('row/a');
        foreach($post as $key => $val){
            Db::name('options')->where(['option_name' => $key])->update(['option_content' => $val]);
        }
        $this->success();
    }

    public function edit_template(){
        $path = ROOT_PATH . 'application/extra/notice';
        $file = $this->request->param('ids');

        $file_path = $path . "/" . $file . ".tpl";

        if($this->request->isPost()){
            $content = $this->request->param('content');
            $res = file_put_contents($file_path, $content);
            if($res){
                $this->success();
            }else{
                $this->error();
            }
        }

        $content = file_get_contents($file_path);
        $this->assign([
            'content' => $content,
            'file' => $file
        ]);
        return view();
    }

    public function template() {

        $list = [
            [
                'name' => '订单投诉_站长',
                'file' => 'n_complain_ad'
            ],
            [
                'name' => '新订单_站长',
                'file' => 'n_order_ad'
            ],
            [
                'name' => '新订单_用户',
                'file' => 'n_order_us'
            ],
        ];

        $total = count($list);

        $result = ['total' => $total, 'rows' => $list];
        return json($result);
    }

    public function index() {
        $path = ROOT_PATH . 'extend/notice';
        $dir = @dir($path);

        if ($dir) {
            while (($file = $dir->read()) !== false) {
                if (preg_match('|^\.+$|', $file)) continue;
                if (is_dir($path . '/' . $file)) {
                    $pluginsSubDir = @ dir($path . '/' . $file);
                    if ($pluginsSubDir) {
                        while (($subFile = $pluginsSubDir->read()) !== false) {
                            if (preg_match('|^\.+$|', $subFile)) continue;
                            if ($subFile == 'config.php') $files[] = "$path/$file/$subFile";
                        }
                    }
                }
            }
        }
        $list = [];
        foreach ($files as $val) $list[] = include $val;
        $total = count($files);

        if (false === $this->request->isAjax()) {
            $this->assign([
                'list' => $list
            ]);
            return $this->view->fetch();
        }

        $result = ['total' => $total, 'rows' => $list];
        return json($result);
    }


}
