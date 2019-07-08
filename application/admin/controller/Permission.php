<?php
namespace app\admin\controller;
use app\admin\model\permission as permissionModel;
use think\facade\Request;
use gmars\rbac\Rbac;
use Db;
use Session;
class Permission extends Common
{
    //封装的添加和修改的验证，调用验证器验证是否符合条件
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\permission;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    //封装删除的验证，调用验证器验证是否符合条件
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
        $count=db('permission')->count('id');
        $this->assign('count',$count);
        return $this->fetch();
    }
    //查询数据库，把数组传到前台用表格遍历出来
    function show(){
        $count=db('permission')->count('id');
        $sql="select p.id,p.name,p.create_time,p.description,p.path,pc.id pc_id,pc.name pc_name from permission p inner join permission_category pc on p.category_id=pc.id";
        $res=Db::query($sql);
        $arr=['code'=>0,'stusta'=>'success','data'=>$res];
        echo json_encode($arr);
    }
    function option(){//查询数据库，转换为Json格式,前台利用ajax遍历
        $obj=db('permission_category')->select();
        $arr=['code'=>'1','status'=>'error','data'=>$obj];
        echo json_encode($arr);
    }
    //添加权限节点
    function add()
    {
        $this->add_validate();
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        $res=Db::table('permission')->where('name',$data['name'])->find();
        $res2=Db::table('permission')->where('path',$data['path'])->find();
        if (!empty($res2)){
            $arr=['status'=>'error','code'=>'1','message'=>'权限路径不能重复'];
             return json($arr);
        }
        //查看数据库，查看是否分组名是否重复，如果有重复不能重复添加
        if (!empty($res)){
            $arr=['status'=>'error','code'=>'1','message'=>'权限名称不能重复'];
            $json=json_encode($arr);
            echo $json;
            die;
        }else{
            //如果条件都符合，实例化rbac，调用rbac自带的添加方法
            $arr=Db::table('permission')->where('name',$data['name'])->find();
            $rbac = new Rbac();
            $rbac->createPermission([
                'name' => $data['name'],
                'description' => $data['description'],
                'status' => 1,
                'type' => 1,
                'category_id' => $data['pc_name'],
                'path' => $data['path'],
                'create_time' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $arr=['status'=>'success','code'=>'0','message'=>'添加成功'];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    //如果前台传过来的id是数组，就执行群删方法,否则就执行单删
    function delete(){
        $this->del_validate();
        $data=Request::post();
        $rbac = new Rbac();
        if (is_array($data['id'])){
            $rbac->delPermission($data['id']);
        }else{
            $rbac->delPermission([$data['id']]);
        }
        $arr=['status'=>'success','code'=>'0','message'=>'删除成功'];
        return json($arr);
    }
    //修改权限节点
    function update()
    {
        $this->add_validate();
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        //查看数据库，查看是否节点名是否重复，如果有重复不能重名
        $name=$data['name'];
        $path=$data['path'];
        $sql="select * from permission where name='$name' or path='$path'";
        $res = Db::query($sql);
        if (empty($res)) {
            unset($data['__token__']);
            Db::table('permission')->update($data);
            $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
            $json = json_encode($arr);
            echo $json;
            die;
        } else {
            //判断接过来的id是否等于查询出来库里的id，如果不等说明重名了，否则就是改自己的，不存在重名
            foreach ($res as $k => $v){
                if ($v['id']!=$data['id']){
                    $arr = ['status' => 'error', 'code' => '1', 'message' => '权限名称或权限路径以存在'];
                    $json = json_encode($arr);
                    echo $json;
                    die;
                }else{
                    unset($data['__token__']);
                    Db::table('permission')->update($data);
                    $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
                    $json = json_encode($arr);
                    echo $json;
                    die;
                }
            }
        }
    }
}