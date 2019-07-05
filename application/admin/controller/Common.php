<?php
namespace app\admin\controller;
use think\App;
use think\Controller;
use think\facade\Session;
class Common extends Controller
{
    function __construct()
    {
        parent::__construct();
        $admin=Session::get('admin');
        if (empty(Session::get('admin'))){
            $this->redirect('Login/index');
        }else{
            $this->assign('token',uniqid());
            $this->assign('admin',$admin);
        }
    }
    function commonToken(){
    $token = $this->request->token('__token__', 'sha1');
    $arr = ['token' =>$token ];
    echo json_encode($arr);
    }
}
