<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\facade\Request;
use Db;
//use Request;
class Goodsattr extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\goodsattr;
        //验证器验证前台传过来的数据是否符合要求，不符合要求吧数据返回给后台ajax给与提示
        if (!$validate->check($data)) {
            $arr=['status'=>'error','code'=>'9','message'=>$validate->getError()];
            $json=json_encode($arr);
            echo $json;
            die;
        }
    }
    function index(){
        $id = Request::get('id');
        $this->assign('goods_id',$id);
        return $this->fetch();
    }
    function attrshow(){
        $id = Request::post('goods_id');
        $attr_cate = db('goods_attr')->where('goods_id',$id)->find();
        $res = db('attr_category')->select();
        if (!empty($attr_cate)){
            $goods = db('goods')->where('id',$id)->find();
            $attrcate_id = $goods['attrcate_id'];
           $goods_attr = db('goods_attr')->field('attr_details_id')->where('goods_id',$id)->select();
            $arr=['status'=>'success','data'=>$res,'attrcate_id'=>$attrcate_id,'goods_attr'=>$goods_attr];
            return json($arr);
        }
        $arr=['code'=>'10001','status'=>'success','data'=>$res];
        return json($arr);
    }
    function show(){
        $attrcate_id = Request::post('attrcate_id');
        $goods_id = Request::post('goods_id');
        $goods_attr = Db::query("select attr_details_id from goods_attr where goods_id = '$goods_id'");
        $res=Db::table('attribute')
            ->field('a.id,a.name a_name,a.attrcate_id,ad.id ad_id,ad.name ad_name')
            ->alias('a')
            ->join('attr_details ad','a.id = ad.attr_id')
            ->where('attrcate_id',$attrcate_id)
            ->select();
        $my_res=[];
        foreach ($res as $k => $v){
            $my_res[$v['a_name']][]=$v;
        }
        if (!empty($goods_attr)){
            $arr = ['code'=>0,'status'=>'success','data'=>$my_res,'goods_attr'=>$goods_attr];
            return json($arr);
        }
        $arr = ['code'=>0,'status'=>'success','data'=>$my_res];
        return json($arr);
    }
    function add(){
        $this->add_validate();
        $data = Request::post();
        if (empty($data)){
            $arr=['code'=>'3','status'=>'error','message'=>'请选择属性值'];
            return json($arr);
        }
        $goods_id = $data['goods_id'];
        $attr_id = $data['attr_id'];
        $attrcate_id = $data['attrcate_id'];
        $attr_details_id = $data['attr_details_id'];
        $res_goods = db('goods_attr')->where('goods_id',$goods_id)->find();
        //查询属性值表，看有没有商品的id，如果有就删了重新添加
        if (!empty($res_goods)){
            db('goods_attr')->where('goods_id',$goods_id)->delete();
        }
        $res = array_combine($attr_details_id,$attr_id);//把两个数组合并，第一个值是变成健，第二个值就是对应的值
        foreach ($res as $k => $v){
            db('goods_attr')->insert(['goods_id'=>$goods_id,'attr_id'=>$v,'attr_details_id'=>$k]);
        }
        //修改商品表分类的id
        db('goods')->where('id',$goods_id)->update(['attrcate_id'=>$attrcate_id]);
        unset($data['__token__']);
        //给商品添加属性值
        unset($data['attrcate_id']);
        Db::name('goods_attr')->insert($data);
        $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
        return json($arr);
    }
}
