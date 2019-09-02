<?php
ini_set('memory_limit', '2028M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
//define('APP_DIR', '/home/dev/venus/');//测试站运行脚本路径
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Common\Service\ExcelService;
use Wms\Dao\ReportdownloadDao;

$time = venus_script_begin("SKU每日销售量（周报）");

$stime = date('Y-m-d 00:00:00', strtotime('-7 days'));//上周周一
$etime = date('Y-m-d 00:00:00', time());//本周的周一

$type = "116";
$clause = array(
    'wstatus' => 3,
    'sctime' => $stime,
    'ectime' => $etime,
//    'spuType' => array("NEQ", $type),
//    'isExternal' => 1,
);
$orderData = OrderDao::getInstance()->queryListToOrdergoodsByTime($clause);//获取所有订单信息
/*$totalSprice 内部销售金额
 * $totalTprice 订单总金额
 * $totalCprofit 订单客户总利润额
 * */
$orderDataArr = array();
$orderDataSummary = array();
foreach ($orderData as $orderKey => $orderDatum) {
    $orderTprice = $orderDatum['order_tprice'];//订单总金额
    $warname = $orderDatum["war_name"];//仓库名称
    $username = $warname . "|" . explode("[", $orderDatum["user_name"])[0];
    $time = date("Y-m-d", strtotime($orderDatum['order_ctime']));
    $goodsData = array();
    $totalSprice = bcmul($orderDatum['spu_sprice'], $orderDatum['goods_count'], 4);//内部销售金额
    $totalTprice = venus_calculate_sku_price_by_spu($orderDatum['spu_sprice'], $orderDatum['goods_count'], $orderDatum['profit_price']);//订单总金额
    $totalCprofit = bcmul($orderDatum['profit_price'], $orderDatum['goods_count'], 4);//订单客户总利润额
    $orderDataSummary['totalSprice'] += $totalSprice;
    $orderDataSummary['totalTprice'] += $totalTprice;
    $orderDataSummary['totalCprofit'] += $totalCprofit;

    $orderDataSummary['totalSprice'] += $totalSprice;
    $orderDataSummary['totalTprice'] += $totalTprice;
    $orderDataSummary['totalCprofit'] += $totalCprofit;
    if (!array_key_exists($orderDatum['sku_code'], $orderDataSummary[$time][$orderDatum['spu_subtype']])) {
        $goodsData = array(
            'order_code' => $orderDatum['order_code'],
            'sku_code' => $orderDatum['sku_code'],
            'spu_name' => $orderDatum['spu_name'],
            'sup_name' => $orderDatum['sup_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_subtype' => venus_spu_catalog_name($orderDatum['spu_subtype']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'spu_bprice' => $orderDatum['spu_bprice'],
            'spu_count' => $orderDatum['spu_count'],
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataSummary[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataSummary[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataSummary[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataSummary[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataSummary[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    if (!array_key_exists($orderDatum['sku_code'], $orderDataArr[$time][$orderDatum['spu_subtype']])) {
        $goodsData = array(
            'order_code' => $orderDatum['order_code'],
            'sku_code' => $orderDatum['sku_code'],
            'spu_name' => $orderDatum['spu_name'],
            'sup_name' => $orderDatum['sup_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_subtype' => venus_spu_catalog_name($orderDatum['spu_subtype']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'spu_bprice' => $orderDatum['spu_bprice'],
            'spu_count' => $orderDatum['spu_count'],
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataArr[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataArr[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataArr[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataArr[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataArr[$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    $orderDataArr['totalSprice'] += $totalSprice;
    $orderDataArr['totalTprice'] += $totalTprice;
    $orderDataArr['totalCprofit'] += $totalCprofit;
}

$OrderExport = array();
$pushOrderSumSprice = array('订单汇总内部销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalSprice']);
$pushOrderSumCprofit = array('订单汇总客户总利润', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalCprofit']);
$pushOrderSumTprice = array('订单汇总客户销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalTprice']);
unset($orderDataSummary['totalSprice']);
unset($orderDataSummary['totalTprice']);
unset($orderDataSummary['totalCprofit']);

$OrderExport["订单汇总"][] = array('日期', '货品编号', '货品品名', '销售数量');
foreach ($orderDataSummary as $time => $orderData) {
    if ($time != "totalTprice" || $time != "totalSprice" || $time != "totalCprofit") {
//        $OrderExport["订单汇总"][] = array('项目名称', $warname);
//        $OrderExport["订单汇总"][] = array('订单编号', '日期', '货品编号', '货品品名', '销售数量');
//        $keys = 0;
        $totalSpriceWarSum = $orderData['totalSprice'];
        $totalTpriceWarSum = $orderData['totalTprice'];
        $totalCprofitWarSum = $orderData['totalCprofit'];
        unset($orderData['totalSprice']);
        unset($orderData['totalTprice']);
        unset($orderData['totalCprofit']);
//        foreach ($orderData as $time => $orderDatum) {
            foreach ($orderData as $goodsData) {
                foreach ($goodsData as $skucode => $goodsDatum) {
                    $OrderExport["订单汇总"][] = array($time, $goodsDatum['sku_code'], $goodsDatum['spu_name'], $goodsDatum['sku_count']);
                }
//            }
        }
    }
}

$fileName = ExcelService::getInstance()->exportExcel($OrderExport, '', "002", 1);
if (!empty($fileName)) {

    $stime = date('Y.m.d', strtotime('-7 days'));//上周周一
    $etime = date('Y.m.d', strtotime('-1 days'));//当前时间的前一天

    $item = array(
        "fname" => 'SKU每日销售量周报('.$stime.'-'.$etime.')',
        "sfname" => $fileName,
        "scatalogue" => '002',//文件存放目录
        "sdepartments" => '1',//所属部门：1.采购部 2.市场部 3.仓配部 4.财务部 5.品控部
    );
    $insertFileslog = ReportdownloadDao::getInstance()->insert($item);
    if($insertFileslog){
        echo "写入成功";
    }else{
        echo "写入失败";
    }

} else {
    $success = false;
    $data = "";
    $message = "下载失败";
}
venus_script_finish($time);
exit();









