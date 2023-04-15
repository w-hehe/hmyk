<?php

namespace addons\umeditor;

use think\Addons;

/**
 * 插件
 */
class Umeditor extends Addons
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

    /**
     * @param $params
     */
    public function configInit(&$params)
    {
        $config = $this->getConfig();
        $params['umeditor'] = [
            'classname'      => $config['classname'] ?? '.editor',
            'baidumapkey'    => $config['baidumapkey'] ?? '',
            'baidumapcenter' => $config['baidumapcenter'] ?? ''
        ];
    }

}
