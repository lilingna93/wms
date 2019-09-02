<?php
ini_set('memory_limit', '2028M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Common\Service\ExcelService;

$time = venus_script_begin("SKU库存数量周转率");
$stime = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
$etime = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));
$days = date('t', strtotime($stime));
$dbName = "zwdb_wms";
$spuSql = "SELECT * FROM $dbName.wms_spu spu LEFT JOIN $dbName.wms_sku sku ON sku.spu_code = spu.spu_code";
$spuSql .= " WHERE spu.is_selfsupport = 1";
$spuList = M()->query($spuSql);

$goodsSql = "SELECT SUM(goods_count) as goods_count, goods.spu_sprice, sku.sku_code, sku.sku_unit, spu.spu_name, spu.spu_type, spu.spu_subtype FROM $dbName.wms_order o LEFT JOIN $dbName.wms_ordergoods goods ON goods.order_code = o.order_code";
$goodsSql .= " LEFT JOIN $dbName.wms_sku sku ON sku.sku_code = goods.sku_code";
$goodsSql .= " LEFT JOIN $dbName.wms_spu spu ON spu.spu_code = goods.spu_code";
$goodsSql .= " LEFT JOIN $dbName.wms_supplier sup ON sup.sup_code = goods.supplier_code";
$goodsSql .= " WHERE o.order_ctime >= '$stime' AND o.order_ctime <= '$etime'";
$goodsSql .= " AND o.w_order_status = 3 AND goods.supplier_code = 'SU00000000000001' group by sku.sku_code";
$orderGoodsList = M()->query($goodsSql);

$skuData = array();
$fname = "SKU库存数量周转率";
$header = array("货号", "名称", "单位", "总出库数量", "平均库存数量", "库存数量周转率");
$orderGoodsSkuCodeList = array_unique(array_column($orderGoodsList, "sku_code"));
foreach ($spuList as $index => $spuItem) {
    $skCode = $spuItem['sku_code'];
    $spuType = $spuItem['spu_type'];
    $spuSubType = $spuItem['spu_subtype'];
    if (in_array($skCode, $orderGoodsSkuCodeList)) {
        foreach ($orderGoodsList as $ordergoods => $ordergoodsItem) {
            if ($ordergoodsItem['sku_code'] != $skCode) continue;
            $goodsCount = $ordergoodsItem['goods_count'];
            $averageInventoryAmount = bcdiv($goodsCount,$days,3);
            $skuInventoryTurnover = bcdiv($goodsCount,$averageInventoryAmount,3);//库存金额周转率
            $skuData[] = array(
                $ordergoodsItem['sku_code'], $ordergoodsItem['spu_name'], $ordergoodsItem['sku_unit'], $goodsCount,
                $averageInventoryAmount, $skuInventoryTurnover,
            );
        }
    } else {
        $skuData[] = array(
            $spuItem['sku_code'], $spuItem['spu_name'], $spuItem['sku_unit'], 0, 0, 0
        );
    }
}

$excelData = array();
$skuInventoryAmountTurnover = array();
foreach ($skuData as $index => $item) {
    $excelData[] = array(
        "skCode" => $item[0],
        "spName" => $item[1],
        "skUnit" => $item[2],
        "goodsCount" => $item[3],
        "averageInventoryAmount" => $item[4],
        "skuInventoryTurnover" => $item[5],
    );
}
$totalSalesRanking = array_column($excelData, 'skuInventoryTurnover');
array_multisort($totalSalesRanking, SORT_DESC, $excelData);
foreach ($excelData as $index => $val) {
    $skuInventoryAmountTurnover[$fname][] = array(
        "0" => $val['skCode'],
        "1" => $val['spName'],
        "2" => $val['skUnit'],
        "3" => $val['goodsCount'],
        "4" => $val['averageInventoryAmount'],
        "5" => empty($val['skuInventoryTurnover']) ? 0 : $val['skuInventoryTurnover'],
    );
}
$fileName = ExcelService::getInstance()->exportExcel($skuInventoryAmountTurnover, $header, "001");

if ($fileName) {
    $title = "SKU库存数量周转率";
    $content = "SKU库存数量周转率";
    $address = array("wenlong.yang@shijijiaming.com","xiaolong.hu@shijijiaming.com","jinwei.cao@shijijiaming.com");
    $attachment = array(
        "SKU库存数量周转率.xlsx" => C("FILE_SAVE_PATH") . "001/" . $fileName,
    );
    if (sendMailer($title, $content, $address, $attachment)) {
        echo "(发送成功)";
    } else {
        echo "(发送失败)";
    }
} else {
    $success = false;
    $data = "";
    $message = "下载失败";
}
venus_script_finish($time);
exit();




