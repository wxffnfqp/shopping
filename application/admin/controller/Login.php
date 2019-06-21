<?php
namespace app\admin\controller;
use think\Controller;
use Request;
use Db;
class Login extends Controller
{
    public function index()
    {
        return $this->fetch('login');
    }
    function loginAction() {
        $res=Db::table('admin')->find();
        $user=Request::post('user');
        $pwd=Request::post('pwd');
        $code=Request::post('code');
        if(!captcha_check($code)){
            $arr=['status'=>'error','code'=>'1','message'=>'验证码错误'];
        }elseif($res['name']!=$user || $res['passwrod']!=$pwd){
            $arr=['status'=>'error','code'=>'2','message'=>'用户名或密码错误'];
        }else{
            $arr=['status'=>'seccuss','code'=>'0','message'=>'登录成功'];
        }
        $json=json_encode($arr);
        echo $json;
    }

}