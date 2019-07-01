<?php
namespace app\admin\controller;
use app\admin\model\User as userModel;
use gmars\rbac\Rbac;
use think\facade\Request;
//use Request;
class User extends Common
{
    function user(){
        $count=db('user')->count('id');
        $this->assign('count',$count);
        return $this->fetch('user');
    }
    function show(){//查询数据库，转换为Json格式,前台利用ajax遍历
        //实例化model层
        $user = new UserModel;
        $obj=$user->select();
        //1、json_decode对JSON格式的字符串进行编码
        //2、json_encode对变量进行 JSON 编码
        $res=json_decode($obj,true);
        $arr=['code'=>'1','status'=>'error','data'=>$res,];
        echo json_encode($arr);
    }
    function option(){//查询数据库，转换为Json格式,前台利用ajax遍历
        $obj=db('role')->select();
        $arr=['code'=>'1','status'=>'error','data'=>$obj];
        echo json_encode($arr);
    }
    function userAction(){//添加至数据库
        $user = new UserModel;
        $data=Request::post();
        $validate = new \app\admin\validate\user;
        if (!$validate->check($data)) {
            $arr=['code'=>'9','status'=>'no','message'=>$validate->getError()];
            echo json_encode($arr);
            die;
        }
        $res=$user->where(['user_name'=>$data['name']])->find();//查询验证是否有用户名重复
        $res_mobile=$user->where(['mobile'=>$data['phone']])->find();//查询验证是否有手机号重复
        if (!empty($res)){
            $arr=['code'=>'2','status'=>'error','message'=>'用户名不能重复'];
            echo json_encode($arr);
            die;
        }
        if (!empty($res_mobile)){
            $arr=['code'=>'4','status'=>'error','message'=>'手机号不能重复'];
            echo json_encode($arr);
            die;
        }
        //将当前时间转换为时间戳
        $time=strtotime(date('Y-m-d H:i:s'));
        $pwd=md5(md5($data['pwd']));
        $user->data([
            'user_name'  =>  $data['name'],
            'password' =>  $pwd,
            'create_time'  =>  $time,
            'update_time'  =>  $time,
            'mobile' =>  $data['phone'],
        ]);
        $res_add=$user->save();
        if ($res_add==true){
            $arr=['code'=>'0','status'=>'ok','message'=>'添加成功'];
            echo json_encode($arr);
            die;
        }
    }
    function userUpdate(){//修改用户信息
        $user = new UserModel;
        $data=Request::post();
        $validate = new \app\admin\validate\user;
        if (!$validate->check($data)) {
            $arr=['code'=>'9','status'=>'no','message'=>$validate->getError()];
            echo json_encode($arr);
            die;
        }
        $res=$user->where(['user_name'=>$data['name']])->find();//查询验证是否有用户名重复
        $res_mobile=$user->where(['mobile'=>$data['phone']])->find();//查询验证是否有手机号重复
        if (!empty($res)){
            $arr=['code'=>'2','status'=>'error','message'=>'用户名不能重复'];
            echo json_encode($arr);
            die;
        }
        if (!empty($res_mobile)){
            $arr=['code'=>'4','status'=>'error','message'=>'手机号不能重复'];
            echo json_encode($arr);
            die;
        }
        //将当前时间转换为时间戳
        $time=strtotime(date('Y-m-d H:i:s'));
        $pwd=md5(md5($data['pwd']));
        $res=[
            'user_name'  =>  $data['name'],
            'password' =>  $pwd,
            'update_time'  =>  $time,
            'mobile' =>  $data['phone'],
        ];
        $res_add=$user->where('id',$data['id'])->update($res);
        if ($res_add==true){
            $arr=['code'=>'0','status'=>'ok','message'=>'添加成功'];
            echo json_encode($arr);
            die;
        }

    }
    function userDelete(){//删除用户
        $id=Request::post('id');
        db('user')->where('id',$id)->delete();
    }
}