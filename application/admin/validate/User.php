<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'username' => 'require|regex:\w{1,30}|unique:user',
        'password' => 'regex:\S{6,30}',
        'email'    => 'require|email|unique:user',
        'mobile'   => 'unique:user'
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => ['username', 'password', 'email', 'mobile'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => __('账号'),
            'password' => __('Password'),
            'email'    => __('Email'),
            'mobile'   => __('Mobile')
        ];
        parent::__construct($rules, $message, $field);
    }

}
