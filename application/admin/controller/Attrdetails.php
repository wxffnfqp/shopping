<?php
namespace app\admin\controller;
use think\facade\Request;
use Db;
//use Request;
class Attrdetails extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\attrdetails;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    function index(){
        $attr_id = Request::get('id');
        $name = Request::get('name');
        $this->assign('attr_id',$attr_id);
        $this->assign('attr_name',$name);
        return $this->fetch();
    }
    function show(){
        $attr_id = Request::get('id');
        $res = db('attr_details')->where('attr_id',$attr_id)->select();
        $count = Db::table('attr_details')->where('attr_id',$attr_id)->count('id');
        $arr=['code'=>'10001','status'=>'success','data'=>$res,'count'=>$count];
        return json($arr);
    }
    function add(){
        $this->add_validate();
        $id = Request::post('id');
        $data = Request::post('names');
        if (empty($data)){
            $arr=['code'=>'1','status'=>'error','message'=>'至少添加一条属性'];
            return json($arr);
        }
        unset($data['__token__']);
        foreach ($data as $v){
            $res = db('attr_details')->where('attr_id',$id)->where('name',$v)->find();
            if (empty($res)){
                Db::name('attr_details')->insert(['attr_id'=>$id,'name'=>$v]);
                $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
            }else{
                $arr=['code'=>'1','status'=>'error','message'=>'名字不能重复'];
            }
        }
        return json($arr);
    }
    function delete(){//单删和多删公用一个方法
        $data=Request::post();
        $validate = new \app\admin\validate\delete;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //如果接过来的是一个数组，是多删，就循环遍历删除
        if (is_array($data['id'])){
            $res=$data['id'];
            foreach ($res as $k => $v){
                db('attr_details')->where('id','in',$v)->delete();
            }
        }else{
            db('attr_details')->where('id',$data['id'])->delete();
        }
        $arr=['code'=>'10001','status'=>'success','message'=>'删除成功'];
        return json($arr);
    }
}
