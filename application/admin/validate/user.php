<?php

namespace app\admin\validate;

use think\Validate;

class user extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name'  =>  'require|min:1|max:25',
        'phone' =>  'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
        'pwd'  =>  'require|min:4|max:20',
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
        'pwd.require' => '密码不能为空',
        'pwd.min' => '密码不能小于4位',
        'pwd.max' => '密码不能大于20位',
        'phone.require' => '手机号不能为空',
        'phone./^1[3-8]{1}[0-9]{9}$/' => '手机号格式不正确',
        'phone.max' => '手机号只能11位',

    ];
}
