<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Goodsimg extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\goodsimg;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    function index(){
        $goods_id = Request::get('id');
        $this->assign('goods_id',$goods_id);
        return $this->fetch('goodsimg/index');
    }
    function show(){
        $goods_id = Request::post('goods_id');
        $res= db('goods_img')->where('goods_id',$goods_id)->select();
        $arr=['code'=>'10001','status'=>'success','data'=>$res];
        return json($arr);
    }
    public function upload(){
        $this->add_validate();
        $goods_id = Request::post('goods_id');
        // 获取表单上传文件
        $files = request()->file('file');
        $time = date('Ymd');
        if ($files){
            foreach($files as $file){
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->validate(['size'=>1024*1024,'ext'=>'jpg,png,gif'])->move( './uploads/goodsimg');
                if($info){
                    $img = $info->getFilename();
                    $image = \think\Image::open("./uploads/goodsimg/$time/$img");
                    $original_img = $time . "/" . $img;
                    $big_img =$time . "/big_" . $img;
                    $middle_img = $time . "/middle_" . $img;
                    $small_img = $time . "/small_" . $img;
                    // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
                    $image->thumb(150, 150)->save("./uploads/goodsimg/". $big_img);
                    $image->thumb(100, 100)->save("./uploads/goodsimg/". $middle_img);
                    $image->thumb(50, 50)->save("./uploads/goodsimg/". $small_img);
                    $data = ['original_img'=>$original_img,'small_img'=>$small_img,'big_img'=>$big_img,'middle_img'=>$middle_img,'goods_id'=>$goods_id];
                    db('goods_img')->insert($data);
                    $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
                }else{
                    $arr=['code'=>'1','status'=>'success','message'=> $file->getError()];
                }
            }
            return json($arr);
        }else{
            $arr=['code'=>'1','status'=>'success','message'=>'请选择要添加的照片'];
            return json($arr);
        }
    }
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
                $res = db('goods_img')->where('id',$v)->find();
                if (!empty($res)){
                    $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$res['big_img'];
                    $url2=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$res['small_img'];
                    $url3=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$res['middle_img'];
                    $url4=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$res['original_img'];
                    unlink($url);
                    unlink($url2);
                    unlink($url3);
                    unlink($url4);
                }
                Db::query("delete from goods_img where id = '$v'");
            }
        }else{
            $res = db('goods_img')->where('id',$id)->find();
            if (!empty($res)) {
                $url = $_SERVER["DOCUMENT_ROOT"] . "/uploads/goodsimg/" . $res['big_img'];
                $url2 = $_SERVER["DOCUMENT_ROOT"] . "/uploads/goodsimg/" . $res['small_img'];
                $url3 = $_SERVER["DOCUMENT_ROOT"] . "/uploads/goodsimg/" . $res['middle_img'];
                $url4 = $_SERVER["DOCUMENT_ROOT"] . "/uploads/goodsimg/" . $res['original_img'];
                unlink($url);
                unlink($url2);
                unlink($url3);
                unlink($url4);
            }
            Db::query("delete from goods_img where id = '$id'");
        }
        $arr = ['status' => 'success', 'code' => '10001', 'message' => '删除成功'];
        return json($arr);
    }
}
