<?php
namespace app\admin\controller;
use think\App;
use think\Controller;
use gmars\rbac\Rbac;
class Control extends Common
{

    function PermissionCategoryAdd(){
        return $this->fetch();
    }
    //创建权限分组
    function PermissionCategoryAction(){
        $rbac= new Rbac();
        $rbac->savePermissionCategory([
            'name' => '品牌理组',
            'description' => '网站品牌的管理',
            'status' => 1
        ]);
    }
    function permissionAdd(){
        return $this->fetch();
    }
    //创建权限节点
    function permissionAction(){
        $rbac= new Rbac();
        $rbac->createPermission([
            'name' => '品牌列表查询',
            'description' => '品牌类别',
            'status' => 1,
            'type' => 1,
            'category_id' => 1,
            'path' => 'admin/brand/list',
        ]);
    }
    function roleAdd(){
        return $this->fetch();
    }
    //创建角色&给角色分配权限
    function roleAction(){
        $rbac= new Rbac();
        $rbac->createRole([
            'name' => '内容管理员',
            'description' => '负责网站内容管理',
            'status' => 1
        ], '1');
    }
    //给用户分配角色
    function assignUserRole(){
        $rbac= new Rbac();
        $rbac->assignUserRole(1, [1]);
    }


}
