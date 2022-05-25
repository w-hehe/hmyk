<?php

namespace addons\summernote;

use think\Addons;

/**
 * Summernote富文本编辑器
 */
class Summernote extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

}
