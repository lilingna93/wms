<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/5/5
 * Time: 10:49
 * 查看出库记录
 */

ini_set('memory_limit', '256M');
define('APP_DIR', dirname(__FILE__) . '/../../../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
vendor("PHPExcel");
echo venus_current_datetime() . PHP_EOL;
//$filePath = "spudata.20181226.xlsx";
//$exportFilePath = "spudata.20181226.result.xlsx";
//$excelReader = PHPExcel_IOFactory::createReader('Excel2007');
//$excelFile = $excelReader->load($filePath);
$excelReader = PHPExcel_IOFactory::createReader('Excel2007');
$excelFile = new PHPExcel();
$excelFile->setActiveSheetIndex(0);
$excelSheet = $excelFile->getActiveSheet();
//$list = M("sku")->limit(10000)->fetchSql(false)->select();
//$skucodes = array_column($list, "sku_code");
$skucodes = array("SK0000648", "SK0001003", "SK0000276");
$line = 0;
$skucount = 0;
foreach ($skucodes as $skucode) {
    $list = M("goods")->where(array("sku_code" => $skucode))->fetchSql(false)->find();
    if (empty($list)) continue;
    $name=M("spu")->where(array("spu_code"=>$list['spu_code']))->getField("spu_name");
    $line++;
    $excelSheet->setCellValue("A{$line}", $skucode);
    $excelSheet->setCellValue("B{$line}", $name);
    $skucount++;
    exportIgoodsData($skucode, $excelSheet);
    $line++;
    $excelSheet->setCellValue("A{$line}", "------------------------------------------------------------------------------------------------------------");
    $excelSheet->setCellValue("D{$line}", "------------------------------------------------------------------------------------------------------------");
}
echo "SUM:{$skucount}";
$excelWriter = PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007');
$excelWriter->save("./tool/invoice/invlog20190505.online.xlsx");
exit;

function exportIgoodsData($skucode, $excelSheet)
{
    global $errorcount;
    global $goodsSkuDict;
    global $line;
    $line++;
    $list = M("igoods")->where(array("sku_code" => $skucode, "igo_code" => array("GT", "GO40401000000000")))->select();//,"igo_code"=>array("GT","GO31229000000000")

    foreach ($list as $data) {
        $orderCode = M("invoice")->where(array("inv_code" => $data['inv_code']))->getField("inv_ecode");
        $receiver = M("invoice")->where(array("inv_code" => $data['inv_code']))->getField("inv_receiver");

        $excelSheet->setCellValue("A{$line}", $data["war_code"]);
        $excelSheet->setCellValue("B{$line}", $receiver);
        $excelSheet->setCellValue("C{$line}", $orderCode);
        $excelSheet->setCellValue("D{$line}", $data["sku_count"]);

        $line++;
    }

}