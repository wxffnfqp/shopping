<?php
namespace app\admin\controller;
use think\Controller;
use Request;
use Db;
use think\facade\Session;
use gmars\rbac\Rbac;
class Login extends Controller
{
    public function index()
    {
        return $this->fetch('login');
    }
    function loginAction() {
        $rbac= new Rbac();
        $user=Request::post('user');
        $pwd=Request::post('pwd');
        $pwd=md5(md5($pwd));
        $where=['name'=>$user,'password'=>$pwd];
        $res=Db::table('admin')->where($where)->find();
        $code=Request::post('code');
        if(!captcha_check($code)){
            $arr=['status'=>'error','code'=>'1','message'=>'验证码错误'];
        }elseif($res['name']!=$user || $res['password']!=$pwd){
            $arr=['status'=>'error','code'=>'2','message'=>'用户名或密码错误'];
        }else{
            Session::set('admin',$res['name']);
            //service方式
            //service方式因为要用到session一般要依赖于cookie。在用户登录后要获取用户权限操作
            //传入参数为登录用户的user_id
            //该方法会返回该用户所有的权限列表
            $rbac->cachePermission($res['id']);
            $arr=['status'=>'seccuss','code'=>'0','message'=>'登录成功'];
        }
        $json=json_encode($arr);
        echo $json;
    }
    function out(){
        Session::delete('admin');
        $this->redirect('Login/index');
    }

}