<?php
namespace app\admin\controller;
use think\facade\Session;
use gmars\rbac\Rbac;
class Index extends Common
{
    public function index()
    {
        $a=Session::get('admin');
        $this->assign('a',$a);
        return $this->fetch();
    }
    public function onControl()
    {
        return $this->fetch('onControl');
    }

}
