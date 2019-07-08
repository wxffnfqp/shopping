<?php
namespace app\admin\controller;
use think\App;
use think\Controller;
use think\facade\Session;
use think\facade\Request;
use gmars\rbac\Rbac;
class Common extends Controller
{
    function __construct()
    {

        parent::__construct();
        $admin = Session::get('admin');
        if (empty(Session::get('admin'))) {
            $this->redirect('Login/index');
        } else {
            $this->assign('token', uniqid());
            $this->assign('admin', $admin);
        }
        //获取模块名，控制器名，方法名，在转换为小写
        $module = Request::module();
        $controller = Request::controller();
        $action = Request::action();
        $my_constroller = ['User', 'Permission', 'Permissionceta', 'Role'];
        $my_action = ['add', 'update', 'delete', 'show'];
        if (in_array($controller, $my_constroller)) {
            if (in_array($action, $my_action)) {
                $rbac = new Rbac();
                $str="$module" . "/" . "$controller" . "/" . "$action";
                $str=strtolower($str);
                $permission = $rbac->can($str);
                if (!$permission) {
                    header("Content-Type:application/json");
                    $arr = ['code' => '10001', 'status' => 'error', 'message' => '没有此权限'];
                    echo json_encode($arr);
                    die;
                }
            }
        }
    }
    function commonToken(){
    $token = $this->request->token('__token__', 'sha1');
    $arr = ['token' =>$token ];
    echo json_encode($arr);
    }
}
