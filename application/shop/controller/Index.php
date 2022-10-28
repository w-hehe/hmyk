<?php

namespace app\shop\controller;



class Index extends Base {
    public function index() {


        // var_dump($this->request->param());die;

        echo '<link rel="icon" id="icon" href="' . $this->site['ico'] . '">';

        echo $this->site['statistics'];
        $path = ROOT_PATH . "content/template/{$this->template_name}/setting.json";
        $template_info = json_decode(file_get_contents($path), true);
        echo <<<style
        <style>
        .el-drawer.left-menu{
            background-color: #fff!important;
        }
        </style>
style;
        if(!empty($template_info['bg_image']) && $template_info['transparent'] < 1){
            echo <<<style
<style>
#app{

background-image: url("{$template_info['bg_image']}");
    background-size: cover;
    background-position: center;
    
}
#app > .container{
    
    background-color: rgba(255, 255, 255, {$template_info['transparent']})!important;
}
.el-card{
    background-color: rgba(255, 255, 255, 0.5)!important;
}
.el-input__wrapper{
    background-color: rgba(255, 255, 255, 0.5)!important;
}
</style>
style;
        }

        if(!empty($template_info['transparent']) && $template_info['transparent'] == 1){
            echo <<<style
<style>
#app > .container{
    background-color: #f8f8f8!important;
}
.el-card{
    background-color: rgba(255, 255, 255, 1)!important;
}
</style>
style;
        }




        return view($this->template_path . "index.html");
    }
}
