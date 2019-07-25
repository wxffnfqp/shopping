<?php
namespace app\admin\controller;
use gmars\rbac\Rbac;
use think\Console;
use think\facade\Request;
use Db;
//use Request;
class Onegoods extends Common
{
    function add_validate()
    {
        $data=Request::post();
        $validate = new \app\admin\validate\onegoods;
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
        var_dump($goods_id);
        $goods_name = Request::get('name');
        $this->assign('goods_id',$goods_id);
        $this->assign('goods_name',$goods_name);
        return $this->fetch();
    }
    //根据货品变里的id串查询它对应的属性值，速度太慢，所以改进了一下，把name直接存库里了
//    function show(){
//        $goods_id = Request::get('goods_id');
//        $count = Db::table('one_goods')->where('goods_id',$goods_id)->count('id');
//        //查询属性货品表，看这个商品对应着有几个货品
//        $res=Db::table('one_goods')
//            ->field('og.*')
//            ->alias('og')
//            ->where('og.goods_id',$goods_id)
//            ->select();
//        $new_arr = [];
//        //遍历结果，取货品对应的属性值，因为库里存在的是用逗号隔开的id,所以把它放到数组里面在进行遍历
//        //根据遍历出来的id,查询属性值表id对应的name,
//        foreach ($res as $v){
//            //循环里定义一个空数组，每次循环数组清空，最后遍历完就是所有的值
//            $attr_id = [];
//            //把查出来的id串变成数组
//            $a = explode(',',$v['goods_attr_id']);
//            //把查出来的货品表的值放到空数组里面，它的K定义一个goods
//            $attr_id['goods']=$v;
//            //定义一个空的变量字符串，下面准备把查出来的属性值用逗号进行拼接
//            $my_attr='';
//            //把查出来的id转换成的数组，去遍历查询属性值表的name
//            foreach ($a as $v2){
//                $res_attr=db('attr_details')->field('name')->where('id',$v2)->select();
//                //查出来后进行拼接
//                $my_attr .= $res_attr[0]['name']."，";
//            }
//            //把上面的数组拿下来，让它的货品对应它的属性，给属性定义k为attr,方便前台取值
//            $attr_id['goods']['attr']=$my_attr;
//            //把数组在放到一个新数组里面，因为上面数组它的第一层的K都是一样的，就变成了一个3维数组
//            $new_arr[]= $attr_id;
//        }
//        $arr=['code'=>'10001','status'=>'success','data'=>$new_arr,'count'=>$count];
//        return json($arr);
//    }
    //上面是根据id查的对应的属性值，闲太慢，又直接把它的name存进去了
    function show(){
        $goods_id = Request::post('goods_id');
        $res = db('one_goods')->where('goods_id',$goods_id)->select();
        $arr=['code'=>'10001','status'=>'success','data'=>$res];
        return json($arr);
    }
    //查询商品，属性，和属性值的中间表，查出这个商品对应的有哪些属性
    function attrShow(){
        $goods_id = Request::post('goods_id');
        $res=Db::table('goods_attr')
            ->field('ga.*,ad.name ad_name,ai.name ai_name')
            ->alias('ga')
            ->join('attribute ai','ga.attr_id = ai.id')
            ->join('attr_details ad','ga.attr_details_id = ad.id')
            ->where('ga.goods_id',$goods_id)
            ->select();
        $newarr=[];
        //遍历查出来的数组，取出属性的name，是为了前台显示一个属性，对应它的属性值
        //把属性变成k，这个K对应着它的多个属性值
        foreach ($res as $k => $v){
            $newarr[$v['ai_name']][]=$v;
        }
        $arr=['code'=>'10001','status'=>'success','data'=>$newarr,];
        return json($arr);
    }
    function add(){
        $this->add_validate();
        $data = Request::post();
        $attr_id = $data['attr_id'];
        $attr_name = $data['attr_name'];
        $attr_id = implode(',',$attr_id);
        $attr_name = implode(',',$attr_name);
        $stock =  $data['stock'];
        $insert=['stock'=>$stock,'goods_id'=>$data['goods_id'],'goods_attr_id'=>$attr_id,'attr_name'=>$attr_name,'price'=>$data['price'],'name'=>$data['name']];
        $zzid = Db::name('one_goods')->insertGetId($insert);
        $sn_number = "SN".$zzid;
        db('one_goods')->where('id',$zzid)->update(['sn_number'=>$sn_number]);
        $arr=['code'=>'10001','status'=>'success','message'=>'添加成功'];
        return json($arr);
    }
    function update(){
        $this->add_validate();
        $data = Request::post();
        $attr_id = $data['attr_id'];
        $attr_name = $data['attr_name'];
        $attr_id = implode(',',$attr_id);
        $attr_name = implode(',',$attr_name);
        $stock =  $data['stock'];
        $insert=['stock'=>$stock,'goods_id'=>$data['goods_id'],'goods_attr_id'=>$attr_id,'attr_name'=>$attr_name,'price'=>$data['price'],'name'=>$data['name']];
        db('one_goods')->where('id',$data['id'])->update($insert);
        $arr=['code'=>'10001','status'=>'success','message'=>'修改成功'];
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
                Db::query("delete from one_goods where id = '$v'");
            }
        }else{
            Db::query("delete from one_goods where id = '$id'");
        }
        $arr = ['status' => 'success', 'code' => '10001', 'message' => '删除成功'];
        return json($arr);
    }
}
