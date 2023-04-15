<?php

namespace app\index\controller;

use app\common\controller\IndexCommon;
use hehe\Verify;
use think\Db;


class Submit extends IndexCommon {


    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];

    protected $layout = '';



    public function index() {

        $params = json_decode(base64_decode($this->request->param('data')), true);
        
        $gateway_url = $params['gateway_url'];
        unset($params['gateway_url']);
        $this->submitForm($gateway_url, $params);
        
    }





    protected function submitForm($url, $data){
        $sHtml = "<form id='form-box' action='" . $url . "' method='POST'>";
        foreach($data as $key => $val) {
            $val = str_replace("'", "&apos;", $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='提交支付中...'></form>";
        $sHtml = $sHtml . "<script>document.forms['form-box'].submit();</script>";
        echo $sHtml;
        die();
    }


}
