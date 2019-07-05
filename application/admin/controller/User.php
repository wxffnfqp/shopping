<?php
namespace app\admin\controller;
use app\admin\model\User as userModel;
use gmars\rbac\Rbac;
use think\facade\Request;
//use Request;
use Db;
class User extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\user;
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
        $count=db('user')->count('id');
        $this->assign('count',$count);
        return $this->fetch();
    }
    function show(){//查询数据库，转换为Json格式,前台利用ajax遍历
        $res=Db::query("select u.id,u.user_name,u.mobile,u.last_login_time,u.create_time,u.update_time,r.`name` from user u join user_role u_r on u.id=u_r.user_id join role r on r.id = u_r.role_id");
        $arr=['code'=>'1','status'=>'error','data'=>$res,];
        echo json_encode($arr);
    }
    function add(){//添加至数据库
        $this->add_validate();
        $rbac = new Rbac();
        $data=Request::post();
        $role_id=$data['role_id'];
        $res=db('user')->where(['user_name'=>$data['name']])->find();//查询验证是否有用户名重复
        $res_mobile=db('user')->where(['mobile'=>$data['phone']])->find();//查询验证是否有手机号重复
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
        $data2=[
            'user_name'  =>  $data['name'],
            'password' =>  $pwd,
            'create_time'  =>  $time,
            'update_time'  =>  $time,
            'mobile' =>  $data['phone'],
        ];
        $userId = Db::name('user')->insertGetId($data2);
        $rbac->assignUserRole($userId, [$role_id]);
        $arr=['code'=>'0','status'=>'ok','message'=>'添加成功'];
        echo json_encode($arr);
        die;
    }
    function update(){//修改用户信息
        $rbac = new Rbac();
        $data=Request::post();
        $time=strtotime(date('Y-m-d H:i:s'));//将当前时间转换为时间戳
        if (is_array($data['pwd'])){
            $arr_data=$data['pwd'];
            $validate = new \app\admin\validate\user_update;
            //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
            if (!$validate->check($arr_data)) {
                $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
                $json=json_encode($arr);
                echo $json;
                die;
            }
            $id=$arr_data['id'];
            $name = $arr_data['name'];
            $name_res=db('user')->where('user_name',$name)->find();
            if (empty($name_res)){
                db('user')->where('id',$id)->update(['user_name'=>$name,'update_time'=>$time]);
                $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
                return json($arr);
            }else{
                if ($name_res['id']!=$id){
                    $arr=['code'=>'1','status'=>'error','message'=>'用户名以存在'];
                    return json($arr);
                }else{
                    db('user')->where('id',$id)->update(['user_name'=>$name,'update_time'=>$time]);
                    $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
                    return json($arr);
                }
            }
        }
        if (is_array($data['role_id'])){
            $arr_phone=$data['role_id'];
            $validate = new \app\admin\validate\update_phone;
            //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
            if (!$validate->check($arr_phone)) {
                $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
                $json=json_encode($arr);
                echo $json;
                die;
            }
            $id=$arr_phone['id'];
            $phone= $arr_phone['phone'];
            $phone_res=db('user')->where('mobile',$phone)->find();
            if (empty($phone_res)){
                db('user')->where('id',$id)->update(['mobile'=>$phone,'update_time'=>$time]);
                $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
                return json($arr);
            }else{
                if ($phone_res['id']!=$id){
                    $arr=['code'=>'1','status'=>'error','message'=>'手机号以存在'];
                    return json($arr);
                }else{
                    db('user')->where('id',$id)->update(['mobile'=>$phone,'update_time'=>$time]);
                    $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
                    return json($arr);
                }
            }
        }
        $this->add_validate();
        $id=$data['id'];
        $role_id=$data['role_id'];
        $pwd=md5(md5($data['pwd']));
        $name=$data['name'];
        $phone=$data['phone'];
        $res_sql=Db::query("select id from `user` where `user_name` = '$name' or `mobile` = '$phone'");
        $data2=[
            'user_name'  =>  $name,
            'password' =>  $pwd,
            'update_time'  =>  $time,
            'mobile' =>  $phone,
        ];
        if (empty($res_sql)){
            db('user')->where('id',$id)->update($data2);
            Db::query("update user_role set role_id = '$role_id' where user_id = '$id'");
            $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
            return json($arr);
        }else{
            $name_res=db('user')->where('user_name',$name)->find();
            foreach ($res_sql as $k => $v){
                if ($v['id']!=$id){
                    if (!empty($name_res)){
                        $arr=['code'=>'1','status'=>'error','message'=>'用户名或手机号以存在'];
                        return json($arr);
                    }
                    $phone_res=db('user')->where('mobile',$phone)->find();
                    if(!empty($phone_res)){
                        $arr=['code'=>'2','status'=>'error','message'=>'此手机号已以存在'];
                        return json($arr);
                    }
                }
            }
            db('user')->where('id',$id)->update($data2);
            Db::query("update user_role set role_id = '$role_id' where user_id = '$id'");
            $arr=['code'=>'0','status'=>'ok','message'=>'修改成功'];
            return json($arr);
        }
    }
    function delete(){//删除用户，单删和多删公用一个方法
        $this->del_validate();
        $data=Request::post();
        //如果接过来的是一个数组，是多删，就循环遍历删除
        if (is_array($data['id'])){
            $res=$data['id'];
            foreach ($res as $k => $v){
                db('user')->where('id',$v)->delete();
                db('user_role')->where('user_id',$v)->delete();
            }
        }else{
            db('user')->where('id',$data['id'])->delete();
            db('user_role')->where('user_id',$data['id'])->delete();
        }
        $arr=['code'=>'0','status'=>'ok','message'=>'删除成功'];
        return json($arr);
    }
}