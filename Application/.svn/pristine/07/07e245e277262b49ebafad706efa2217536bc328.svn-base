<?php

ini_set('memory_limit', '356M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao;
use Wms\Dao\SkuDao;
use Common\Service\ExcelService;

$time = venus_script_begin("SPU最新采购价格数据");

$SpuDao = SpuDao::getInstance();
$SkuDao = SkuDao::getInstance("WA000001");
$skuDataList = $SpuDao->queryAllList();
$spuBprice = array();
$fname = "SPU最新采购价";
$header = array( "品名", "SPU编号", "规格", "价格", "SKU编号", "规格", "价格");
foreach ($skuDataList as $index => $spuItem) {
    $condition = array(
        "skCode" => $spuItem['sku_code'],
        "supCode" => "SU00000000000001",
    );
    $gbBprice = $SkuDao->querySpuListBySkuCode($condition);//$spuItem['sku_code']
    if (!empty($gbBprice) && $gbBprice['gb_bprice'] != 0) {
        $gbBprices = $gbBprice['gb_bprice'];
    } else {
        $gbBprices = $spuItem['spu_bprice'];
    }
    $spuList = array(
        $skBprice = bcmul($spuItem['spu_count'],$gbBprices,2),
        "spCode" => $spuItem['spu_code'],
        "spName" => $spuItem['spu_name'],
        "spNorm" => $spuItem['spu_norm'],
        "spBprice" => $gbBprices,
        "skCode" => $spuItem['sku_code'],
        "skNorm" => $spuItem['sku_norm'],
        "skBprice" => $skBprice,
    );
    $spuBprice[$fname][] = array(
        $spuList['spName'], $spuList['spCode'], $spuList['spNorm'], $spuList['spBprice'],
        $spuList['skCode'], $spuList['skNorm'], $spuList['skBprice']
    );
}
$fileName = ExcelService::getInstance()->exportExcel($spuBprice, $header, "001");
if (!empty($fileName)) {
    $time = venus_current_datetime();
    $newFname = "spu_bprice.xlsx";
    $moveFileRes = rename("/home/wms/app/Public/files/001/$fileName", "/home/wms/app/Public/spufiles/$newFname");
    if ($moveFileRes) {
        echo $newFname;
    } else {
        echo "移动失败" . "$newFname";
    }

} else {
    echo "生成文件失败";
    exit;
}

