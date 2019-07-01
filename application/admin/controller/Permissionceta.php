<?php
namespace app\admin\controller;
use app\admin\model\permissioncate as permissioncateModel;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Permissionceta extends Common
{
    //查询数据库，把数组传到前台用表格遍历出来
    function Permissionceta(){
        $count=db('permission_category')->count('id');
        $this->assign('count',$count);
        $sql="select * from permission_category";
        $arr=Db::query($sql);
        $this->assign('arr',$arr);
        return $this->fetch('Permissionceta');
    }
    //添加权限分组
    function permissioncetaAction()
    {
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        $arr=Db::table('permission_category')->where('name',$data['name'])->find();
        //实例化验证器
        $validate = new \app\admin\validate\permissionceta;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //查看数据库，查看是否分组名是否重复，如果有重复不能重复添加
        if (!empty($arr)){
            $arr=['status'=>'error','code'=>'1','message'=>'分组名不能重复'];
            $json=json_encode($arr);
            echo $json;
            die;
        }else{
            //如果条件都符合，实例化rbac，调用rbac自带的添加方法
            $rbac = new Rbac();
            $rbac->savePermissionCategory([
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => 1 ,
                'create_time' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $arr=['status'=>'success','code'=>'0','message'=>'添加成功'];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    //删除权限分组数据
    function permissioncetaDelete(){
        $id=Request::post('id');
        $a=Db::table('permission_category')->where('id',$id)->delete();
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
    //修改权限分组,即点即改是一个方法
    function permissioncetaUpdateAction()
    {
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        //实例化验证器
        $validate = new \app\admin\validate\permissionceta;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //查看数据库，查看是否分组名是否重复，如果有重复不能重名
        $res = Db::table('permission_category')->where('name', $data['name'])->find();
        if (empty($res)) {
            Db::table('permission_category')->where('id',$data['id'])->update(['name'=>$data['name'],'description'=>$data['description'],'status'=>1]);
            $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
            $json = json_encode($arr);
            echo $json;
            die;
        } else {
            if ($res['id']!=$data['id']){
                $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
                $json = json_encode($arr);
                echo $json;
                die;
            }else{
                //如果条件都符合，就执行修改语句
                Db::table('permission_category')->where('id',$data['id'])->update(['name'=>$data['name'],'description'=>$data['description'],'status'=>1]);
                $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
                $json = json_encode($arr);
                echo $json;
                die;
            }
        }
    }

}