<?php
namespace app\admin\controller;
use app\admin\model\permission as permissionModel;
use gmars\rbac\Rbac;
use think\facade\Request;
//use Request;
class Role extends Common
{
    function role(){
        return $this->fetch('role');
    }
    function roleAdd(){
        return $this->fetch('role_add');
    }
}