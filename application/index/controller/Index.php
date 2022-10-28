<?php

namespace app\index\controller;

use think\Controller;

class Index extends Controller {

    public function password(){

        include ROOT_PATH . 'password.php';



    }
    
    public function index(){
        return 'hello';
    }

}
