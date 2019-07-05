<?php

namespace app\admin\validate;

use think\Validate;

class permission extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require|min:1|max:25|token',
        'description' => 'require|max:100',
        'path' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.max' => '名称最多不能超过25个字符',
        'name.require' => '名称不能为空',
        'description.require' => '描述不能为空',
        'description.max' => '描述不能超过100个字符',
        'path.require' => '权限路径不能为空',

    ];
}
