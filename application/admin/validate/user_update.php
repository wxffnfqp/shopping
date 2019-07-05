<?php

namespace app\admin\validate;

use think\Validate;

class user_update extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name'  =>  'require|min:1|max:25|token',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'name.require' => '名称不能为空',
        'name.max'     => '名称最多不能超过25个字符',
    ];
}
