<?php
ini_set('memory_limit', '256M');
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
$stime = "2018-11-01 00:00:00";
//$etime = date("Y-m-01 00:00:00", strtotime("+1 month"));
$etime = "2018-12-01 00:00:00";
$clause = array(
    'wstatus' => 3,
    'sctime' => $stime,
    'ectime' => $etime,
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
    $totalCprofit = bcmul($orderDatum['profit_price'], $orderDatum['goods_count'], 4);;//订单客户总利润额
    $orderDataSummary['totalSprice'] += $totalSprice;
    $orderDataSummary['totalTprice'] += $totalTprice;
    $orderDataSummary['totalCprofit'] += $totalCprofit;

    $orderDataSummary[$warname]['totalSprice'] += $totalSprice;
    $orderDataSummary[$warname]['totalTprice'] += $totalTprice;
    $orderDataSummary[$warname]['totalCprofit'] += $totalCprofit;

    if (!array_key_exists($orderDatum['sku_code'], $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']])) {
        $goodsData = array(
            "order_code" => $orderDatum['order_code'],
            "sku_code" => $orderDatum['sku_code'],
            "spu_name" => $orderDatum['spu_name'],
            "sup_name" => $orderDatum['sup_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'spu_bprice' => $orderDatum['spu_bprice'],
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }

    if (!array_key_exists($orderDatum['sku_code'], $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']])) {
        $goodsData = array(
            "order_code" => $orderDatum['order_code'],
            "sku_code" => $orderDatum['sku_code'],
            "spu_name" => $orderDatum['spu_name'],
            "sup_name" => $orderDatum['sup_name'],
            'spu_storetype' => venus_spu_storage_desc($orderDatum['spu_storetype']),
            'spu_type' => venus_spu_type_name($orderDatum['spu_type']),
            'spu_brand' => $orderDatum['spu_brand'],
            'sku_unit' => $orderDatum['sku_unit'],
            'sku_norm' => $orderDatum['sku_norm'],
            'sku_count' => $orderDatum['sku_count'],
            'spu_bprice' => $orderDatum['spu_bprice'],
            'totalSprice' => $totalSprice,
            'totalTprice' => $totalTprice,
            'totalCprofit' => $totalCprofit,
        );
        $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    $orderDataArr[$warname][$username]['totalSprice'] += $totalSprice;
    $orderDataArr[$warname][$username]['totalTprice'] += $totalTprice;
    $orderDataArr[$warname][$username]['totalCprofit'] += $totalCprofit;
}

$OrderExport = array();
$pushOrderSumTprice = array('订单汇总客户销售总金额', '', '', '', '', $orderDataSummary['totalTprice']);
$pushOrderSumSprice = array('订单汇总内部销售总金额', '', '', '', '', $orderDataSummary['totalSprice']);
$pushOrderSumCprofit = array('订单汇总客户总利润', '', '', '', '', $orderDataSummary['totalCprofit']);
unset($orderDataSummary['totalTprice']);
unset($orderDataSummary['totalSprice']);
unset($orderDataSummary['totalCprofit']);

foreach ($orderDataSummary as $warname => $orderData) {
    if ($warname != "totalTprice" || $warname != "totalSprice" || $warname != "totalCprofit") {
        $OrderExport["订单汇总"][] = array('项目名称', '日期', '货品类型', '客户销售金额', '内部销售金额', '客户利润');
        $keys = 0;
        $totalSpriceWarSum = $orderData['totalSprice'];
        $totalTpriceWarSum = $orderData['totalTprice'];
        $totalCprofitWarSum = $orderData['totalCprofit'];
        unset($orderData['totalSprice']);
        unset($orderData['totalTprice']);
        unset($orderData['totalCprofit']);
        foreach ($orderData as $time => $orderDatum) {
            foreach ($orderDatum as $goodsData) {
                foreach ($goodsData as $skucode => $goodsDatum) {
                    if(in_array($goodsDatum['spu_type'],$goodsDatum)){
                        $tTprice = $goodsDatum['totalTprice'];
                        $tSprice = $goodsDatum['totalSprice'];
                        $tCprofit = $goodsDatum['totalCprofit'];
                    }
                    $OrderExport["订单汇总"][] = array('', $time, $goodsDatum['spu_type'], $tTprice, $tSprice, $tCprofit);
                    $keys++;
                }
            }
        }
        $OrderExport["订单汇总"][] = array('', '', '总金额', $totalTpriceWarSum, $totalSpriceWarSum, $totalCprofitWarSum);
        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
    }
}

array_push($OrderExport["订单汇总"], $pushOrderSumTprice);
array_push($OrderExport["订单汇总"], $pushOrderSumSprice);
array_push($OrderExport["订单汇总"], $pushOrderSumCprofit);

$fileName = ExcelService::getInstance()->exportExcel($OrderExport, '', "002", 1);
if (!empty($fileName)) {
    $time = venus_current_datetime();
    $newFname = "orderSummary_xl.xlsx";
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










