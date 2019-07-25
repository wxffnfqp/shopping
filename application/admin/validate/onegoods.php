<?php

namespace app\admin\validate;

use think\Validate;

class onegoods extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name'  =>  'require|token',
        'price' =>  'require',
        'stock' =>  'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message  =   [
        'name.require' => '货品名不能为空',
        'price.require'     => '单价不能为空',
        'stock.require' =>  '库存不能为空',
    ];
}
