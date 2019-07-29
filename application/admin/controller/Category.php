<?php
namespace app\admin\controller;
use app\admin\model\Brand as BrandModel;
use Db;
use think\facade\Request;
use Cache;
//use Request;
class Category extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\category;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    function index(){
        return $this->fetch();
    }
    function show(){
        $res = Cache::get('name');
<<<<<<< Updated upstream
        if(!$res){
=======
        if (!$res){
>>>>>>> Stashed changes
            $res=db('goods_category')->select();
            Cache::set('name',$res,36000);
        }
        $arr=['code'=>'0','status'=>'success','data'=>$res];
        $json=json_encode($arr);
        echo $json;
    }
    function add(){
        $this->add_validate();
        $data = Request::post();
        $name = $data['name'];
        $res=db('goods_category')->where('name',$name)->find();
        $level=$data['level'];
        if (!empty($res)){
            $arr=['code'=>'1','status'=>'error','message'=>'分类名不能重复'];
            return json($arr);
        }
        if ($data['category']==''){
            $data2 = ['name'=>$name,'pid'=>'0','level'=>'0'];
            Db::name('goods_category')->insert($data2);
            $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
            return json($arr);
        }
        $res2=db('goods_category')->where('name',$data['category'])->find();
        $data2 = ['name'=>$name,'pid'=>$res2['id'],'level'=>$level];
        Db::name('goods_category')->insert($data2);
        $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
        return json($arr);
    }
    function delete(){
        $data=Request::post();
        $id = Request::post('id');
        $validate = new \app\admin\validate\delete;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        $array=Db::table('goods_category')->select();
        //调用递归方法，吧接过来的id和查出来的数组放到里面
        //$arr是递归出来的结果，就是分类下面所有的子类
        $arr=$this->digui($id,$array);
        if (empty($arr)){
            db('goods_category')->where('id',$id)->delete();
            $json = ['code'=>'10001','status'=>'success','message'=>'删除成功'];
        }else{
            foreach ($arr as $k => $v){
                db('goods_category')->where('id',$id)->delete();
                db('goods_category')->where('id',$v['id'])->delete();
            }
            $json = ['code'=>'10001','status'=>'success','message'=>'删除成功'];
        }
        return json($json);
    }
    //定义一个递归的方法,$id为前台接过来的id,$array等于查询要操作的表的数据
    function digui($id,$array=array(),$level=1){
        static $list;//声明静态数组,避免递归调用时,多次声明导致数组覆盖
        foreach ($array as $k => $v)
        {
            //如果数组里面的pid等于接过来的id,就吧值放到数组里面
            if($v['pid'] == $id)
            {
                $list[] = $v;//存放数组中
                unset($array[$k]);// 删除查询过的数组
                //自己调自己，$v里面的id,就是遍历出来的，放里面继续判断
                //然后就继续循环，如果pid等于id,就会查出数据的id
                //然后在根据这条数据的pid有没有等于父级的id,实现无限循环，直到没有子分类
                $this->digui($v['id'],$array,$level+1);
            }
        }
        //循环完后，所有的子级都会在list这个数组里面，返回这个数组，给方法调用取值
        return $list;
    }

    function update(){
        $this->add_validate();
        $data = Request::post();
        $res = Db::table('goods_category')->where('name', $data['name'])->find();
        if (empty($res) || !empty($res) && $res['id']==$data['id']) {
            Db::table('goods_category')->where('id',$data['id'])->update(['name'=>$data['name']]);
            $arr = ['status' => 'success', 'code' => '10001', 'message' => '修改成功'];
            echo  json_encode($arr);
            die;
        } else {
            $arr = ['status' => 'error', 'code' => '1', 'message' => '商品分类名字不能重复'];
            echo  json_encode($arr);
            die;
        }
    }
    function getTree($array, $pid =0, $level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];


        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点

            if ($value['pid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $flg = str_repeat('|--',$level);
                // 更新 名称值
                $value['name'] = $flg.$value['name'];
                // 输出 名称
                $id = $value['id'];
                echo "<option value='$id'>";
                echo $value['name'];
                echo "</option>";
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                $this->getTree($array, $value['id'], $level+1);
            }

        }

        return $list;
    }
    function tree(){
        $res = Cache::get('name');
        if(!$res){
            $res=db('goods_category')->select();
            Cache::set('name',$res,3600);
        }
    $this->getTree($res);
    }

}
