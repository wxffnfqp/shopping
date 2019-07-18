<?php

namespace app\admin\validate;

use think\Validate;

class goods extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'goods_name'  =>  'require|max:50|token',
        'goods_price' =>  'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'goods_name.require' => '名称不能为空',
        'goods_name.max'     => '名称最多不能超过50个字符',
        'goods_price.require' => '单价不能为空',
    ];
}
