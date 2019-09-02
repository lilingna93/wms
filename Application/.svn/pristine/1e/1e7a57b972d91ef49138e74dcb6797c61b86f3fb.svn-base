<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/1/10
 * Time: 15:59
 */
ini_set('memory_limit', '256M');
define('IS_MASTER', true);
define('APP_DIR', dirname(__FILE__) . '/../../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
vendor("PHPExcel");
echo venus_current_datetime().PHP_EOL;
$excelReader = PHPExcel_IOFactory::createReader('Excel2007');
$excelFile = new PHPExcel();
$excelFile->setActiveSheetIndex(0);
$excelSheet = $excelFile->getActiveSheet();

$list = M("sku")->limit(10000)->fetchSql(false)->select();
$skucodes = array_column($list,"sku_code");

$line = 0;
$skucount =0;
foreach ($skucodes as $skucode){
    $list = M("goods")->where(array("sku_code"=>$skucode))->fetchSql(false)->select();
    if(empty($list))continue;
    $skucount++;
    exportOrderGoodsData($skucode,$excelSheet);
    $line++;
    $excelSheet->setCellValue("A{$line}","------------------------------------------------------------------------------------------------------------");
    $excelSheet->setCellValue("D{$line}","------------------------------------------------------------------------------------------------------------");
}
echo "SUM:{$skucount}";
$excelWriter = PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007');
$excelWriter->save("goodsdata.online.xlsx");
exit;


function exportOrderGoodsData($skucode,$excelSheet){
    global $line;
    $line++;
    $excelSheet->setCellValue("A{$line}", "OrderGoods");
    $excelSheet->setCellValue("C{$line}", $skucode);
    $line++;
    $list = M("ordergoods")->where(array("sku_code"=>$skucode,"goods_code"=>array("GT","G31229000000000")))->select();
    foreach ($list as $data){
        $ocode = $data["order_code"];
        $order = M("order")->where(array("order_code"=>$ocode))->find();
        if(!empty($order)){
            if($order["order_status"]==3||($order["order_status"]==1&&$order["w_order_status"]==1)){
                continue;
            }
        }else{
            continue;
        }

        $excelSheet->setCellValue("A{$line}", $data["goods_code"]);
        $excelSheet->setCellValue("B{$line}", $data["sku_init"]);
        $excelSheet->setCellValue("C{$line}", $data["goods_count"]);
        $excelSheet->setCellValue("D{$line}", $data["sku_count"]);
        $excelSheet->setCellValue("E{$line}", $data["w_sku_count"]);
        $excelSheet->setCellValue("F{$line}", $data["supplier_code"]);
        $excelSheet->setCellValue("G{$line}", $data["order_code"]);
        $excelSheet->setCellValue("H{$line}", $data["ot_code"]);
        $excelSheet->setCellValue("I{$line}", $data["war_code"]);
        $line++;
    }
}