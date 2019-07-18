<?php
namespace app\admin\controller;
use app\admin\model\Brand as BrandModel;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
use \think\File;
//use Request;
class Brand extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\brand;
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
    function add(){
//        header("Content-type:application/json");
        $this->add_validate();
            // 获取表单上传文件 例如上传了001.jpg
        // 获取表单上传文件 例如上传了001.jpg
        $data=Request::post();
        $name=$data['name'];
        $description=$data['description'];
        $file = request()->file('logo');
        if (empty($file)){
            $arr=['code'=>'3','status'=>'error','message'=>'LOGO不能为空'];
            return json($arr);
        }
        $info = $file->validate(['size'=>1024*1024,'ext'=>'jpg,png,gif'])->move('./uploads/');
        $res = db('brand')->where('name',$name)->find();
        if (!empty($res)){
            $arr=['code'=>'2','status'=>'error','message'=>'品牌名不能重复'];
            return json($arr);
        }
        if($info){
            // 成功上传后 获取上传信息
           $logo =str_replace("\\","/",$info->getSaveName());
            $data = ['name' => $name, 'description' => $description,'logo' => $logo];
            Db::name('brand')->insert($data);
            $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
            return json($arr);
        }else{
            $arr=['code'=>'1','status'=>'error','message'=>'图片格式之支持jpg,png,gif，大小为1M'];
            return json($arr);
        }
    }
    function show(){
        $count = db('brand')->count('id');
        $res=Db::query("select id,`name`,logo,description from brand");
        $arr=['code'=>'1','status'=>'success','data'=>$res,'count'=>$count];
        $json=json_encode($arr);
        echo $json;
    }
    //单删和多删
    function delete(){
        $data=Request::post();
        $id = $data['id'];
        $validate = new \app\admin\validate\delete;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
        //如果前台传过来的是数组的id是数组，就执行群删方法
        if (is_array($id)){
            foreach ($id as $k => $v){
                $res = db('brand')->where('id',$v)->find();
                $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/".$res['logo'];
                unlink($url);
                Db::query("delete from brand where id = '$v'");
            }
        }else{
            $res = db('brand')->where('id',$id)->find();
            $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/".$res['logo'];
            unlink($url);
            Db::query("delete from brand where id = '$id'");
        }
        $arr = ['status' => 'success', 'code' => '0', 'message' => '删除成功'];
        return json($arr);
    }
    function update()
    {
        $data=Request::post();
        if (is_array($data['id'])){
            $id=$data['id'];
            $validate = new \app\admin\validate\brand;
            //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
            if (!$validate->check($data)) {
                $arr = ['status' => 'error', 'code' => '9', 'message' => $validate->getError()];
                $json = json_encode($arr);
                echo $json;
                die;
            }
            $res = Db::table('brand')->where('name', $data['name'])->find();
            if (empty($res) || !empty($res) && $res['id']==$id['id']) {
                Db::table('brand')->where('id',$id['id'])->update(['name'=>$data['name'],'description'=>$data['description']]);
                $arr = ['status' => 'success', 'code' => '0', 'message' => '修改成功'];
                echo  json_encode($arr);
                die;
            } else {
                $arr = ['status' => 'error', 'code' => '1', 'message' => '分组名不能重复'];
                echo  json_encode($arr);
                die;
            }
        }else{
            $file = request()->file('logo');
            if (empty($file)){
                $arr=['code'=>'3','status'=>'error','message'=>'LOGO不能为空'];
                return json($arr);
            }
            $info = $file->validate(['size'=>1024*1024,'ext'=>'jpg,png,gif'])->move('./uploads/');
            if($info){
                // 查询库里的logo路径，删除原图片，修改成新的图片
                $id=$data['id'];
                $res = db('brand')->where('id',$id)->find();
                $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/".$res['logo'];
                unlink($url);//删除图片
                // 双\\的意思就是\，把\斜杠，换成/斜杠
                $logo =str_replace("\\","/",$info->getSaveName());
                $data2 = ['logo' => $logo];
                db('brand')->where('id',$id)->update($data2);
                $arr=['code'=>'10001','status'=>'success','message'=>'修改成功'];
                return json($arr);
            }else{
                $arr=['code'=>'1','status'=>'error','message'=>'图片格式之支持jpg,png,gif，大小为1M'];
                return json($arr);
            }
        }
    }
}
