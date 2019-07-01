<?php
namespace app\admin\controller;
use app\admin\model\permission as permissionModel;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Permission extends Common
{    //查询数据库，把数组传到前台用表格遍历出来
    function permission(){
        $count=db('permission')->count('id');
        $this->assign('count',$count);
        return $this->fetch('Permission');
    }
    //查询数据库，把数组传到前台用表格遍历出来
    function show(){
        $count=db('permission')->count('id');
//        $this->assign('count',$count);
        $sql="select p.id,p.name,p.create_time,p.description,pc.id pc_id,pc.name pc_name from permission p inner join permission_category pc on p.category_id=pc.id";
        $res=Db::query($sql);
        $arr=['code'=>0,'stusta'=>'success','data'=>$res];
        echo json_encode($arr);
//        $this->assign('arr',$arr);
//        return $this->fetch('Permission');
    }
    function option(){//查询数据库，转换为Json格式,前台利用ajax遍历
        $obj=db('permission_category')->select();
        $arr=['code'=>'1','status'=>'error','data'=>$obj];
        echo json_encode($arr);
    }
    //添加权限节点
    function permissionAction()
    {
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        $res=Db::table('permission')->where('name',$data['name'])->find();
        //实例化验证器
        $validate = new \app\admin\validate\permission;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //查看数据库，查看是否分组名是否重复，如果有重复不能重复添加
        if (!empty($res)){
            $arr=['status'=>'error','code'=>'1','message'=>'分组名不能重复'];
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
                'path' => 'admin/permission/list',
                'create_time' => strtotime(date('Y-m-d H:i:s'))
            ]);
            $arr=['status'=>'success','code'=>'0','message'=>'添加成功'];
            $json=json_encode($arr);
            echo $json;
            die;

        }
    }
    //选中删除，调用rbac删除方法
    function datadel(){
        $id = Request::post('id');//前台接的值为字符串格式的
        $data = explode(',',$id);//把值拆成数组用逗号隔开
        array_shift($data);//去除数组首个单位，因为接过来的值第一个是空的
        $rbac = new Rbac();
        $rbac->delPermission($data);
    }
    //删除权限节点数据
    function permissionDelete(){
        $id=Request::post('id');
        $rbac = new Rbac();
        $rbac->delPermission([$id]);
    }
    //修改权限节点
    function permissionUpdate()
    {
        $data=Request::post();//接前台post传过来的数据，是数组形式的
        //实例化验证器
        $validate = new \app\admin\validate\permission;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //查看数据库，查看是否节点名是否重复，如果有重复不能重名
        $res = Db::table('permission')->where('name', $data['name'])->find();
        if (empty($res)) {
            Db::table('permission')->where('id',$data['id'])->update(['name'=>$data['name'],'category_id'=>$data['category_id'],'description'=>$data['description'],'status'=>1]);
            $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
            $json = json_encode($arr);
            echo $json;
            die;
        } else {
            //判断接过来的id是否等于查询出来库里的id，如果不等说明重名了，否则就是改自己的，不存在重名
            if ($res['id']!=$data['id']){
                $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
                $json = json_encode($arr);
                echo $json;
                die;
            }else{
                Db::table('permission')->where('id',$data['id'])->update(['name'=>$data['name'],'category_id'=>$data['category_id'],'description'=>$data['description'],'status'=>1]);
                $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
                $json = json_encode($arr);
                echo $json;
                die;
            }
        }
    }

}