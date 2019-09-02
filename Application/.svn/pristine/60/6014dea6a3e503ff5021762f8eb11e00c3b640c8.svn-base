<?php
ini_set('memory_limit', '356M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Common\Service\ExcelService;

$time = venus_script_begin("导出订单汇总数据");

$supOrderGoods = array();
$year = date("Y", time());
$month = date("m", time());
$days = get_days_by_year_and_month($year, $month);
//$stime = date("Y-m-01 00:00:00", time());
$stime = "2019-03-01 00:00:00";
//$etime = date("Y-m-01 00:00:00", strtotime("+1 month"));
$etime = "2019-04-10 00:00:00";
$type = "116";
$clause = array(
    'wstatus' => 3,
    'sctime' => $stime,
    'ectime' => $etime,
    'spuType' => array("EQ", $type),
);
$orderData = OrderDao::getInstance()->queryListToOrdergoodsByTime($clause);//获取所有订单信息
/* $totalBprice 订单总内部采购价
 * $totalSprice 订单内部销售金额
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
    $totalBprice = bcmul($orderDatum['spu_bprice'], $orderDatum['goods_count'], 4);//内部采购金额
    $totalSprice = bcmul($orderDatum['spu_sprice'], $orderDatum['goods_count'], 4);//内部销售金额
    $totalTprice = venus_calculate_sku_price_by_spu($orderDatum['spu_sprice'], $orderDatum['goods_count'], $orderDatum['profit_price']);//订单总金额
    $totalCprofit = bcmul($orderDatum['profit_price'], $orderDatum['goods_count'], 4);;//订单客户总利润额

    $orderDataSummary['totalBprice'] += $totalBprice;
    $orderDataSummary['totalSprice'] += $totalSprice;
    $orderDataSummary['totalTprice'] += $totalTprice;
    $orderDataSummary['totalCprofit'] += $totalCprofit;

    if (!array_key_exists($orderDatum['sku_code'], $orderDataSummary[$orderDatum['spu_subtype']])) {
        $goodsData = array(
            "sku_code" => $orderDatum['sku_code'],
            "spu_name" => $orderDatum['spu_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'totalBprice' => $totalBprice,
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalBprice'] += $totalBprice;
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataSummary[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    if (!array_key_exists($orderDatum['sku_code'], $orderDataArr[$orderDatum['spu_subtype']])) {
        $goodsData = array(
            "sku_code" => $orderDatum['sku_code'],
            "spu_name" => $orderDatum['spu_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'totalBprice' => $totalBprice,
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalBprice'] += $totalBprice;
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataArr[$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    $orderDataArr['totalBprice'] += $totalBprice;
    $orderDataArr['totalSprice'] += $totalSprice;
    $orderDataArr['totalTprice'] += $totalTprice;
    $orderDataArr['totalCprofit'] += $totalCprofit;
}

$OrderExport = array();
$pushOrderSumBprice = array('订单汇总内部采购总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalBprice']);
$pushOrderSumSprice = array('订单汇总内部销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalSprice']);
$pushOrderSumCprofit = array('订单汇总客户总利润', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalCprofit']);
$pushOrderSumTprice = array('订单汇总客户销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalTprice']);
unset($orderDataSummary['totalSprice']);
unset($orderDataSummary['totalTprice']);
unset($orderDataSummary['totalCprofit']);

$OrderExport["订单汇总"][] = array('序号', '货品编号', '货品品名', '仓储方式', '货品类型', '品牌', '单位', '规格', '数量', '内部采购金额', '内部销售金额', '客户销售金额', '客户利润');
$keys = 0;
foreach ($orderDataSummary as $orderData) {
        $totalBpriceWarSum = $orderData['totalBprice'];
        $totalSpriceWarSum = $orderData['totalSprice'];
        $totalTpriceWarSum = $orderData['totalTprice'];
        $totalCprofitWarSum = $orderData['totalCprofit'];
        unset($orderData['totalSprice']);
        unset($orderData['totalTprice']);
        unset($orderData['totalCprofit']);
//        foreach ($orderData as $orderDatum) {
//            foreach ($orderDatum as $goodsData) {
            foreach ($orderData as $goodsData) {
                $OrderExport["订单汇总"][] = array($keys + 1, $goodsData['sku_code'], $goodsData['spu_name'], $goodsData['spu_storetype'], $goodsData['spu_type'], $goodsData['spu_brand'], $goodsData['sku_unit'], $goodsData['sku_norm'], $goodsData['sku_count'], $goodsData['totalBprice'], $goodsData['totalSprice'], $goodsData['totalTprice'], $goodsData['totalCprofit']);
                $keys++;
            }
//        }
//        $OrderExport["订单汇总"][] = array('内部销售总金额', '', '', '', '', '', '', '', '', '', $totalSpriceWarSum);
//        $OrderExport["订单汇总"][] = array('客户总利润', '', '', '', '', '', '', '', '', '', $totalCprofitWarSum);
//        $OrderExport["订单汇总"][] = array('客户销售总金额', '', '', '', '', '', '', '', '', '', $totalTpriceWarSum);
//        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
//        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
}
array_push($OrderExport["订单汇总"], $pushOrderSumBprice);
array_push($OrderExport["订单汇总"], $pushOrderSumSprice);
array_push($OrderExport["订单汇总"], $pushOrderSumTprice);
array_push($OrderExport["订单汇总"], $pushOrderSumCprofit);

$fileName = ExcelService::getInstance()->exportExcel($OrderExport, '', "002", 1);
if (!empty($fileName)) {
    $time = venus_current_datetime();
    $newFname = "orderSummary_all.xlsx";
    $moveFileRes = rename("/home/wms/app/Public/files/002/$fileName", "/home/wms/app/Public/orderfiles/$newFname");
    if ($moveFileRes) {
        echo $newFname;
    } else {
        echo "移动失败" . "$newFname";
    }

} else {
    echo "生成文件失败";
    exit;
}










