<?php

namespace app\admin\validate;

use think\Validate;

class update_phone extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'phone' =>  'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'phone.require' => '手机号不能为空',
        'phone./^1[3-8]{1}[0-9]{9}$/' => '手机号格式不正确',
        'phone.max' => '手机号只能11位',

    ];
}
