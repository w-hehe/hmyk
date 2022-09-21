<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Cache;
use think\Db;
/**
 * 模板管理
 *
 * @icon fa fa-circle-o
 */
class Template extends Backend
{

    /**
     * Template模型对象
     * @var \app\admin\model\Template
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Template;

    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function index(){
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()){
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')){
                return $this->selectpage();
            }

            $hmTemplates = [];
            $templateFiles = [];
            $templatePath = ROOT_PATH . '/content/template';
            $templateDir = @dir($templatePath);

            // print_r($templateDir);die;

            if ($templateDir){
                while (($file = $templateDir->read()) !== false) {
                    if (preg_match('|^\.+$|', $file)){
                        continue;
                    }

                    // echo $file;

                    // echo $templatePath . '/' . $file;die;

                    if (is_dir($templatePath . '/' . $file)){
                        $templatesSubDir = @ dir($templatePath . '/' . $file);
                        if ($templatesSubDir){
                            while (($subFile = $templatesSubDir->read()) !== false) {

                                // echo $file;

                                if (preg_match('|^\.+$|', $subFile)){
                                    continue;
                                }
                                if ($subFile == 'info.php'){
                                    $templateFiles[] = "$file/$subFile";
                                }
                            }
                        }
                    }
                }
            }

            $active_templates = $this->options['active_template'];
            $active_templates = empty($active_templates) ? [] : unserialize($active_templates);

            $active_templates['pc'] = empty($active_templates['pc']) ? 'default' : $active_templates['pc'];
            $active_templates['mobile'] = empty($active_templates['mobile']) ? 'default' : $active_templates['mobile'];

            foreach($templateFiles as $key => $templateFile) {

                // $templateData = $this->getTemplateData($templateFile, $key + 1);

                $templatePath = ROOT_PATH . '/content/template/' . $templateFile;

                $templateData = include_once($templatePath);

                if($active_templates['pc'] . '/info.php' == $templateFile && $active_templates['mobile'] . '/info.php' == $templateFile){
                    $templateData['apply'] = 'pc_mobile';
                }elseif($active_templates['pc'] . '/info.php' == $templateFile && $active_templates['mobile'] . '/info.php' != $templateFile){
                    $templateData['apply'] = 'pc';
                }elseif($active_templates['pc'] . '/info.php' != $templateFile && $active_templates['mobile'] . '/info.php' == $templateFile){
                    $templateData['apply'] = 'mobile';
                }else{
                    $templateData['apply'] = 'none';
                }

                $templateData['folder'] = explode('/', $templateFile)[0];


                if (empty($templateData['name'])){

                    continue;
                }
                // $templateData['status'] = in_array($templateFile, $active_templates) ? 'enable' : 'disable';
                // $templateData['install'] = true;
                $hmTemplates[] = $templateData;
            }

            $result = ["total" => count($hmTemplates), "rows" => $hmTemplates];

            return json($result);
        }

        return $this->view->fetch();
    }





    public function setting(){

        $folder = $this->request->param('ids');

        $active_templates = $this->options['active_template'];

        $active_templates = empty($active_templates) ? [] : unserialize($active_templates);



        if($this->request->isPost()){
            $post = $this->request->post("row/a");

            $apply = $post['apply'];
            unset($post['apply']);

            $post = json_encode($post);
            file_put_contents(ROOT_PATH . 'content/template/' . $folder . '/setting.json', $post);

            foreach($apply as $val){
                $active_templates[$val] = $folder;
            }
            db::name('options')->where(['option_name' => 'active_template'])->update(['option_content' => serialize($active_templates)]);

            Cache::rm($folder . '_template');
            $this->success('操作成功');
        }



        $info = file_get_contents(ROOT_PATH . 'content/template/' . $folder . '/setting.json');
        $info = json_decode($info, true);

        $active_templates['pc'] = empty($active_templates['pc']) ? 'default' : $active_templates['pc'];
        $active_templates['mobile'] = empty($active_templates['mobile']) ? 'default' : $active_templates['mobile'];

        $this->assign([
            'info' => $info,
            'active_template' => $active_templates
        ]);


        return view(ROOT_PATH . 'content/template/' . $folder . '/setting.php');
    }

    /**
     * 删除
     */
    public function del($ids = ""){
        $this->error("删除功能暂未开放");
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $info = Db::name('template')->where(['id' => $v->id])->find();
                    if(!$info){
                        continue;
                    }
                    $directory = $info['directory'];
                    $dir = ROOT_PATH . 'template' . DS . $directory;
                    $res = rmdirs($dir);
                    if($res){
                        $count += $v->delete();
                    }
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    public function set_all(){
        $id = $this->request->param('id');
        Db::startTrans();
        try{
            db::name('template')->where(['pc|mobile' => 1])->update(['pc' => 0, 'mobile' => 0]);
            db::name('template')->where(['id' => $id])->update(['pc' => 1, 'mobile' => 1]);
            Db::commit();
            return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['msg' => '操作失败, 请重试！', 'code' => 400]);
        }

    }

    public function set_pc(){
        $id = $this->request->param('id');
        Db::startTrans();
        try{
            db::name('template')->where(['pc' => 1])->update(['pc' => 0]);
            db::name('template')->where(['id' => $id])->update(['pc' => 1]);
            Db::commit();
            return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['msg' => '操作失败, 请重试！', 'code' => 400]);
        }

    }

    public function set_mobile(){
        $id = $this->request->param('id');
        Db::startTrans();
        try{
            db::name('template')->where(['mobile' => 1])->update(['mobile' => 0]);
            db::name('template')->where(['id' => $id])->update(['mobile' => 1]);
            Db::commit();
            return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['msg' => '操作失败, 请重试！', 'code' => 400]);
        }

    }




    public function set_default(){
        $id = $this->request->param('id');
        Db::startTrans();
        try{
            db::name('template')->where(['default' => 1])->update(['default' => 0]);
            db::name('template')->where(['id' => $id])->update(['default' => 1]);
            Db::commit();
            return json(['data' => '', 'msg' => '操作成功', 'code' => 200]);
        }catch (\Exception $e){
            Db::rollback();
            return json(['msg' => '操作失败, 请重试！', 'code' => 400]);
        }

    }


}
