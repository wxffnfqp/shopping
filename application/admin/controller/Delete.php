<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Delete extends Common
{
    function __construct()
    {
        parent::__construct();


    }
    //删除权限分组数据
    function permissioncetaDelete(){
        $id=Request::post('id');
        db('permission_category')->where('id',$id)->delete();
        $arr=['status'=>'success','code'=>'0','message'=>'删除成功'];
        $json=json_encode($arr);
        echo $json;
        die;
    }
    //多选删除
    function datadel(){
        $data=Request::post('id');
        $data=explode(',',$data);
        //前台传过来的值有个空，将开头的单元移出
        array_shift($data);
        $rbac = new Rbac();
        //rabc自带删除方法
        $rbac->delPermissionCategory($data);
        $arr=['status'=>'success','code'=>'0','message'=>'删除成功'];
        $json=json_encode($arr);
        echo $json;
        die;
    }

}