<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Role extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\Role;
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
    function index(){
        return $this->fetch();
    }
    function show(){
        $count=Db::table('role')->count('id');
        $res=Db::query("select * from role");
        $arr=['code'=>0,'status'=>'success','data'=>$res,'count'=>$count];
        return json($arr);
    }
    function permissionId(){
        $id=Request::get('id');
        $res=Db::query("select permission_id from role_permission where role_id = '$id'");
        $arr=['code'=>0,'status'=>'success','data'=>$res];
        return json($arr);
    }
    function permissionShow(){
        $sql = "select p.id,p.name,p.category_id,p.path,p.description,pc.name as pc_name from permission p inner join permission_category pc on p.category_id=pc.id";
        $res=Db::query($sql);
        $my_res=[];
        foreach ($res as $k => $v){
            $my_res[$v['pc_name']][]=$v;
        }
        $arr = ['code'=>0,'status'=>'success','data'=>$my_res];
        return json($arr);
    }
    function add(){
        $rbac = new Rbac();
        $this->add_validate();
        $data =Request::post();
        $permission_id = explode(',',$data['permission_id']);
        array_shift($permission_id);
        $permission_id = implode(',',$permission_id);
        $res=db('role')->where('name',$data['name'])->find();
        if (empty($res)){
            unset($data['__token__']);
            $rbac->createRole([
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => 1
            ], $permission_id);
            $arr=['code'=>0,'status'=>'success','message'=>'添加成功'];
            return json($arr);
        }else{
            $arr=['code'=>0,'status'=>'success','message'=>'角色名称以存在'];
            return json($arr);
        }
    }
    function update()
    {
        $rbac = new Rbac();
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        //判断接过来的数据是不是数组，是数组就执行下面修改方法
        if (is_array($data['permission_id'])){
            $js_arr=$data['permission_id'];
            $id=$js_arr['id'];
            $name=$js_arr['name'];
            $description=$js_arr['description'];
            $res=db('role')->where('name',$name)->find();
            $validate = new \app\admin\validate\role_update;
            //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
            if (!$validate->check($js_arr)) {
                $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
                $json=json_encode($arr);
                echo $json;
                die;
            }
            //判断传过来的角色名是不是有重复的，如果没有重复的就直接添加
            if (empty($res) || !empty($res) && $id==$res['id']){
                Db::query("update role set `name`='$name' , `description` = '$description' where id = '$id' ");
                $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
                return json($arr);
            }else{//如果不是重复的就看看是不是自己的本条要修改的信息，如果不是就返回错误信息，否则就直接添加
                $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
                return json($arr);
            }
        }
        //自己封装的一个验证，$this指向方法名调用
        $this->add_validate();
        $id = $data['id'];
        $name=$data['name'];
        $description=$data['description'];
        $permission_id = explode(',',$data['permission_id']);
        array_shift($permission_id);
        //查看数据库，查看是否分组名是否重复，如果有重复不能重名
        $res = Db::table('role')->where('name', $data['name'])->find();
        //如果角色明不重复，就直接修改了，或者，如果是重名且id等于自己的id等于自己的id就可以直接修改，否则就提示错误信息
        if (empty($res) || !empty($res) && $res['id']==$data['id']) {
            Db::query("delete from role_permission where role_id = '$id' ");
            Db::query("update role set `name`='$name' , `description` = '$description' where id = '$id' ");
            foreach ($permission_id as $k => $v){
                Db::query("insert into role_permission (`role_id`,`permission_id`) values ('$id','$v')");
            }
            $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
            return json($arr);
        } else {
            $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
            return json($arr);
        }
    }
    //单删和多删
    function delete(){
        $this->del_validate();
        $rbac = new Rbac();
        $data=Request::post();
        //如果前台传过来的是数组的id是数组，就执行群删方法
        if (is_array($data['id'])){
            $rbac->delRole($data['id']);
        }else{
            $id=$data['id'];
            $rbac->delRole([$id]);
        }
        $arr = ['status' => 'success', 'code' => '0', 'message' => '删除成功'];
        return json($arr);
    }
}