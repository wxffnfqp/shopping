<?php
namespace app\admin\controller;
use app\admin\model\permissioncate as permissioncateModel;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Permissionceta extends Common
{
    function add_validate()
    {
        //获取是get,还是post传值
//        $request = Request::instance();
//        $method = $request->method();//获取上传方式
//        if ($method == 'POST'){
//
//        }
        $data=Request::post();
        $validate = new \app\admin\validate\permissionceta;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    function del_validate(){
        $data=Request::post();
        $validate = new \app\admin\validate\delete;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    //查询数据库，把数组传到前台用表格遍历出来
    function index(){
        $count=db('permission_category')->count('id');
        $this->assign('count',$count);
        $sql="select * from permission_category";
        $arr=Db::query($sql);
        $this->assign('arr',$arr);
        return $this->fetch();
    }
    //添加权限分组
    function add()
    {
        $this->add_validate();
        $data=Request::post();
        $arr=Db::table('permission_category')->where('name',$data['name'])->find();
        //实例化验证器
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
    //修改权限分组,即点即改是一个方法
    function update()
    {
        //自己封装的一个验证，$this指向方法名调用
        $this->add_validate();
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        //查看数据库，查看是否分组名是否重复，如果有重复不能重名
        $res = Db::table('permission_category')->where('name', $data['name'])->find();
        if (empty($res) || !empty($res) && $res['id']==$data['id']) {
            Db::table('permission_category')->where('id',$data['id'])->update(['name'=>$data['name'],'description'=>$data['description'],'status'=>1]);
            $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
            $json = json_encode($arr);
            echo $json;
            die;
        } else {
            $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
            $json = json_encode($arr);
            echo $json;
            die;
        }
    }
    //删除权限分组数据,如果接过来的id为数组就是多删，否则就是单删
    function delete(){
        $this->del_validate();
        $rbac = new Rbac();
        $data=Request::post();
        if (is_array($data['id'])){
            //rabc自带删除方法
            $rbac->delPermissionCategory($data['id']);
        }else{
            $rbac->delPermissionCategory([$data['id']]);
        }
        $arr=['status'=>'success','code'=>'0','message'=>'删除成功'];
        $json=json_encode($arr);
        echo $json;
        die;
    }
}