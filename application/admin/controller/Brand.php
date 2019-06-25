<?php
namespace app\admin\controller;
use app\admin\model\Brand as BrandModel;
use gmars\rbac\Rbac;
use think\facade\Request;
//use Request;
class Brand extends Common
{
    function __construct()
    {
        parent::__construct();
        $rbac= new Rbac();
        $action = Request::action();//获取当前模块的方法名
        $action = 'admin/brand/'.$action;
        $res=$rbac->can($action);
        if ($res==false){
            $this->redirect('index/onControl');
            die;
//            $res=['code'=>'9','status'=>'error','message'=>'您没有访问权限'];
        }
//        $json=json_encode($res);
//        echo $json;
    }

    function list(){

    }

    function productBrand(){
        return $this->fetch('productBrand');
    }
    function productAction(){
        $name=Request::post('brand_name');
        $brand = new BrandModel();
        $brand->name = $name;
        $brand->save();
    }
    function show(){
        $brand = new BrandModel();
        $obj=$brand->select();
        $arr=json_decode($obj,true);
        $res=['code'=>'1','status'=>'success','data'=>$arr];
        $json=json_encode($res);
        echo $json;
    }
}
