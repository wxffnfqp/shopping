<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Goods extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\goods;
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
        $res=Db::table('goods')
            ->field('g.id,g.brand_id,g.goods_unit_id,g.category_id,g.goods_name,g.goods_price,g_u.goods_unit,brand.name')
            ->alias('g')
            ->join('goods_unit g_u','g.goods_unit_id = g_u.id')
            ->join('brand','g.brand_id = brand.id')
            ->select();
        $count = Db::table('goods')->count('id');
        $arr=['code'=>'10001','status'=>'success','data'=>$res,'count'=>$count];
        return json($arr);
    }
    function goodsUnit(){
        $res=db('goods_unit')->select();
        $arr=['code'=>'10001','status'=>'success','data'=>$res];
        return json($arr);
    }
    function add(){
        $this->add_validate();
        $data = Request::post();
        unset($data['__token__']);
        Db::name('goods')->insert($data);
        $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
        return json($arr);
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
            //传过来的Id是个数组，遍历查询要群删的数据的ID
            foreach ($id as $k => $v){
                $res = db('goods')->where('id',$v)->find();
                //查出来的商品的ID,在根据ID查询商品里面的图片，循环里面套循环
                $res_img = db('goods_img')->where('goods_id',$res['id'])->select();
                foreach ($res_img as $v_img){
                    $img_id = $v_img['id'];
                    $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v_img['big_img'];//商品大图的位置
                    $url2=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v_img['small_img'];//商品小图的位置
                    $url3=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v_img['middle_img'];//商品中图的位置
                    $url4=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v_img['original_img'];//商品原图的位置
                    unlink($url);//删除商品对应的图片
                    unlink($url2);
                    unlink($url3);
                    unlink($url4);
                    Db::query("delete from goods_img where goods_id = '$img_id'");
                }
                Db::query("delete from goods where id = '$v'");
            }
        }else{
            $res = db('goods')->where('id',$id)->find();
            $res_img = db('goods_img')->where('goods_id',$res['id'])->select();
            foreach ($res_img as $v){
                $img_id = $v['id'];
                $url=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v['big_img'];
                $url2=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v['small_img'];
                $url3=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v['middle_img'];
                $url4=$_SERVER["DOCUMENT_ROOT"]."/uploads/goodsimg/".$v['original_img'];
                unlink($url);
                unlink($url2);
                unlink($url3);
                unlink($url4);
                Db::query("delete from goods_img where goods_id = '$img_id'");
            }
            Db::query("delete from goods where id = '$id'");
        }
        $arr = ['status' => 'success', 'code' => '10001', 'message' => '删除成功'];
        return json($arr);
    }
    function update(){
        $this->add_validate();
        $data = Request::post();
        unset($data['__token__']);
        db('goods')->update($data);
        $arr = ['status' => 'success', 'code' => '10001', 'message' => '修改成功'];
        return json($arr);
    }
}
