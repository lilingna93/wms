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

$time = venus_script_begin("更新goodsbatch最新采购价格");

$fileUrl = '../../../Public/orderfiles/updateGbprice.txt';
$SpuDao = SpuDao::getInstance();
$SkuDao = SkuDao::getInstance("WA000001");
$skuDataList = $SpuDao->queryAllList();
$updateGbPriceSqllist = array();

foreach ($skuDataList as $index => $spuItem) {
    $cond['skCode'] = $spuItem['sku_code'];
    $gbBprice = $SkuDao->querySpuListBySkuCode($cond);
    if ($spuItem['sku_code'] == $gbBprice['sku_code'] && $spuItem['spu_bprice'] !== $gbBprice['gb_bprice']) {
        $updateGbPrice = $SkuDao->updateSpupriceBySkuCode($gbBprice['spu_code'], $gbBprice['gb_bprice']);
        $updateGbPriceSqllist[] = $updateGbPrice;
    } else {
        continue;
    }
}
file_put_contents($fileUrl, $updateGbPriceSqllist);