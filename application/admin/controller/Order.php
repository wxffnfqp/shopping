<?php
namespace app\admin\controller;
use think\facade\Request;
use Db;
use Session;
use PHPExcel;
use Env;
use PHPExcel_IOFactory;
use \think\File;
class Order extends Common
{
    function index(){
        return $this->fetch();
    }
    function show(){
        $res = db('order')->select();
        $arr=['code'=>'10001','status'=>'success','data'=>$res];
        return json($arr);
    }
    function export(){
        $objPHPExcel = new PHPExcel();
//        $ids = input('ids');
//        if (!$ids) {
//            $this->error('数据异常');
//        }
//        $res = db('order')->where('id', 'in', $ids)->select();
        $res = db('order')->select();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
    // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'ID');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'NAME');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '支付状态');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '发货状态');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '时间');

    // Miscellaneous glyphs, UTF-8
        $objPHPExcel->setActiveSheetIndex(0);
         foreach ($res as $k => $v) {
             $i = $k + 2;
             $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $v['id']);
             $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $v['name']);
             $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $v['pay_status'] == 0 ? '未支付' : '已支付');
             $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $v['send_status'] == 0 ? '未发货' : '已发货');
             $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $v['pay_time']);
         }
    // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('订单列表');

    // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="订单'.date("Y-m-d").'.xlsx"');
        header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    function import(){
        //上传excel文件
        $file = request()->file('excel');
        //将文件保存到public/uploads目录下面
        $info = $file->validate(['size'=>1048576,'ext'=>'xls,xlsx'])->move( './uploads');
        if($info){
            //获取上传到后台的文件名
            $fileName = $info->getSaveName();
            //获取文件路径
            $filePath = Env::get('root_path').'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$fileName;
            //获取文件后缀
            $suffix = $info->getExtension();
            //判断哪种类型
            if($suffix=="xlsx"){
                $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            }else{
                $reader = PHPExcel_IOFactory::createReader('Excel5');
            }
        }else{
            $this->error('文件过大或格式不正确导致上传失败-_-!');
        }
        //载入excel文件
        $excel = $reader->load("$filePath",$encode = 'utf-8');
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num = $sheet->getHighestRow();
        //获取总列数
        $col_num = $sheet->getHighestColumn();
        $data = []; //数组形式获取表格数据
        for ($i = 2; $i <= $row_num; $i ++) {
            $data[$i]['name']  = $sheet->getCell("B".$i)->getValue();
            $data[$i]['pay_status']  = $sheet->getCell("C".$i)->getValue()=='已支付'?1:0;
            $data[$i]['send_status']  = $sheet->getCell("D".$i)->getValue()=='已发货'?1:0;
            $time = date('Y-m-d H:i',\PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell("E".$i)->getValue()));//将excel时间改成可读时间
            $data[$i]['pay_time'] = strtotime($time);
            //将数据保存到数据库
        }
        $res = Db::name('order')->insertAll($data);

        }

}