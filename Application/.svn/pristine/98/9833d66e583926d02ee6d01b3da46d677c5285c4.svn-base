<?php
ini_set('memory_limit','256M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao;
use Wms\Dao\SkuDao;
use Common\Service\ExcelService;

$time = venus_script_begin("初始化Venus数据库的SPUSKU数据");

$files = "C:/Users/gfz_1/Desktop/spu/isku.xlsx";
//echo file_exists($files)?"yes":"no";exit();
$datas = ExcelService::GetInstance()->uploadByShell($files);
$dicts = array(
    "A" => "sku_code",//sku品类编号
    "B" => "spu_code",//spu品类编号
    "O" => "sku_norm",//spu的规格
    "P" => "sku_unit",//spu的单位
    "Y" => "sku_mark"//sku备注
);

$skuList = array();
foreach ($datas as $sheetName => $list) {
    unset($list[0]);
    $skuList = array_merge($skuList, $list);
}
venus_db_starttrans();//启动事务
$result = true;

foreach ($skuList as $index => $skuItem) {
    $skuData = array();
    foreach ($dicts as $col => $key) {
        $skuData[$key] = isset($skuItem[$col]) ? $skuItem[$col] : "";
    }

    $skuDatas = array(
        "sku_code" => $skuData["sku_code"],
        "sku_norm" => $skuData["sku_norm"],
        "sku_unit" => $skuData["sku_unit"],
        "sku_mark" => $skuData["sku_mark"],
        "spu_count" => 1,
        "spu_code" => $skuData["spu_code"],
        "sku_status" => 1,
        "war_code" => "WA000001"
    );
    $result = $result && SkuDao::GetInstance()->insert($skuDatas);
}

if ($result) {
    venus_db_commit();
    return true;
} else {
    venus_db_rollback();
    return false;
}







