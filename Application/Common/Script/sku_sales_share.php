<?php
ini_set('memory_limit', '2028M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Common\Service\ExcelService;

$time = venus_script_begin("SKU销售额占比榜");
$stime = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
$etime = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));
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

$spuTypeTotalSales = array();
$spuSubtypeTotalSales = array();
$allTotalSales = 0;
foreach ($orderGoodsList as $index => $value) {
    $skSprice = bcmul($value['goods_count'], $value['spu_sprice'], 4);
    if (!in_array($value['sku_code'], $spuTypeTotalSales)) {
        $spuTypeTotalSales[$value['spu_type']] += $skSprice;
    }
    if (!in_array($value['sku_code'], $spuSubtypeTotalSales)) {
        $spuSubtypeTotalSales[$value['spu_subtype']] += $skSprice;
    }
    $allTotalSales += $skSprice;
}

$skuData = array();
$fname = "SKU销售额排行榜";
$header = array("货号", "名称", "单位", "销售额", "所属一级分类", "所属一级分类销售额占比", "所属二级分类", "所属二级分类销售额占比", "总销售额占比");
$orderGoodsSkuCodeList = array_unique(array_column($orderGoodsList, "sku_code"));
foreach ($spuList as $index => $spuItem) {
    $skCode = $spuItem['sku_code'];
    $spuType = $spuItem['spu_type'];
    $spuSubType = $spuItem['spu_subtype'];
    if (in_array($skCode, $orderGoodsSkuCodeList)) {
        foreach ($orderGoodsList as $ordergoods => $ordergoodsItem) {
            if ($ordergoodsItem['sku_code'] != $skCode) continue;
            $skSalesVolume = bcmul($ordergoodsItem['goods_count'], $ordergoodsItem['spu_sprice'], 4);
            $spuTypeSalesShare = bcdiv($skSalesVolume, $spuTypeTotalSales[$spuType], 3);
            $spuSubtypeSalesShare = bcdiv($skSalesVolume, $spuSubtypeTotalSales[$spuSubType], 3);
            $totalSalesShare = bcdiv($skSalesVolume, $allTotalSales, 3);
            $skuData[] = array(
                $ordergoodsItem['sku_code'], $ordergoodsItem['spu_name'], $ordergoodsItem['sku_unit'], $skSalesVolume,
                $ordergoodsItem['spu_type'], $spuTypeSalesShare, $ordergoodsItem['spu_subtype'],
                $spuSubtypeSalesShare, $totalSalesShare
            );
        }
    } else {
        $skuData[] = array(
            $spuItem['sku_code'], $spuItem['spu_name'], $spuItem['sku_unit'], 0,
            $spuItem['spu_type'], 0, $spuItem['spu_subtype'],
            0, 0
        );
    }
}

$excelData = array();
$skuSellingSalesPercentage = array();
foreach ($skuData as $index => $item) {
    $excelData[] = array(
        "skCode" => $item[0],
        "spName" => $item[1],
        "skUnit" => $item[2],
        "skSalesVolume" => $item[3],
        "spType" => $item[4],
        "spuTypeSalesShare" => $item[5],
        "spSubtype" => $item[6],
        "spuSubtypeSalesShare" => $item[7],
        "totalSalesShare" => $item[8],
    );
}
$totalSalesRanking = array_column($excelData, 'totalSalesShare');
array_multisort($totalSalesRanking, SORT_DESC, $excelData);
foreach ($excelData as $index => $val) {
    $skuSellingSalesPercentage[$fname][] = array(
        "0" => $val['skCode'],
        "1" => $val['spName'],
        "2" => $val['skUnit'],
        "3" => $val['skSalesVolume'],
        "4" => venus_spu_type_name($val['spType']),
        "5" => $val['spuTypeSalesShare'],
        "6" => venus_spu_catalog_name($val['spSubtype']),
        "7" => $val['spuSubtypeSalesShare'],
        "8" => $val['totalSalesShare'],
    );
}
$fileName = ExcelService::getInstance()->exportExcel($skuSellingSalesPercentage, $header, "001");

if ($fileName) {
    $title = "sku销售额占比榜";
    $content = "sku销售额占比榜";
    $address = array("wenlong.yang@shijijiaming.com","xiaolong.hu@shijijiaming.com","jinwei.cao@shijijiaming.com");
    $attachment = array(
        "sku销售额占比榜.xlsx" => C("FILE_SAVE_PATH") . "001/" . $fileName,
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




