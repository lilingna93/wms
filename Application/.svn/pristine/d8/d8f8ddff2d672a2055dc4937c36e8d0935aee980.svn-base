<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/4/10
 * Time: 11:47
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
$receiptcodeArr = array("RE40404141346791");
$line = 0;
$skucount = 0;
$excelReader = PHPExcel_IOFactory::createReader('Excel2007');
$excelFile = new PHPExcel();
$excelFile->setActiveSheetIndex(0);
$excelSheet = $excelFile->getActiveSheet();
//    echo count($codes) . PHP_EOL;

//modifyGoodsCount($codes);exit;

//$errorcount = 0;
$line = 0;
foreach ($receiptcodeArr as $recCode) {
    $gbcodeArr = array();
    exportGoodbatchData($recCode, $excelSheet);
    exportGoodstoredData($excelSheet);
    $line++;
    $excelSheet->setCellValue("A{$line}", "------------------------------------------------------------------------------------------------------------");
    $excelSheet->setCellValue("D{$line}", "------------------------------------------------------------------------------------------------------------");
    $line++;
}
echo "SUM:{$line}" . PHP_EOL;
$excelWriter = PHPExcel_IOFactory::createWriter($excelFile, 'Excel2007');
$excelWriter->save("./tool/receipt/receiptdata.online.xlsx");
exit;
function exportGoodbatchData($code, $excelSheet)
{
    global $line;
    global $gbcodeArr;
    $line++;
    $excelSheet->setCellValue("A{$line}", "GoodsBatch");
    $line++;
    $list = M("goodsbatch")->where(array("rec_code" => $code, "war_code" => "WA000001"))->fetchSql(false)->select();
    foreach ($list as $data) {
        $excelSheet->setCellValue("A{$line}", $data["gb_code"]);
        $excelSheet->setCellValue("B{$line}", $data["sku_count"]);
        $excelSheet->setCellValue("C{$line}", $data["gb_count"]);
        $excelSheet->setCellValue("D{$line}", $data["sup_code"]);
        $excelSheet->setCellValue("E{$line}", $data["war_code"]);
        $excelSheet->setCellValue("F{$line}", $data["gb_ctime"]);
        $excelSheet->setCellValue("G{$line}", $data["rec_code"]);
        $excelSheet->setCellValue("H{$line}", M("receipt")->where(array("rec_code" => $data["rec_code"]))->getField("rec_mark"));
        $line++;
        $gbcodeArr[] = $data["gb_code"];
    }
}

function exportGoodstoredData($excelSheet)
{
    global $line;
    global $gbcodeArr;
    $line++;
    $excelSheet->setCellValue("A{$line}", "GoodsStored");
    $line++;
    $list = M("goodstored")->where(array("gb_code" => array("in", $gbcodeArr), "war_code" => "WA000001"))->fetchSql(false)->select();
    foreach ($list as $data) {
        $excelSheet->setCellValue("A{$line}", $data["gs_code"]);
        $excelSheet->setCellValue("B{$line}", $data["sku_init"]);
        $excelSheet->setCellValue("C{$line}", $data["gs_init"]);
        $excelSheet->setCellValue("D{$line}", $data["sku_count"]);
        $excelSheet->setCellValue("E{$line}", $data["gs_count"]);
        $excelSheet->setCellValue("F{$line}", $data["gb_code"]);
        $excelSheet->setCellValue("G{$line}", $data["war_code"]);
        $line++;
    }


}