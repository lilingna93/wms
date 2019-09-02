<?php
ini_set('memory_limit', '2028M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Common\Service\ExcelService;
use Wms\Dao\ReportdownloadDao;

$time = venus_script_begin("导出订单汇总数据");

//$supOrderGoods = array();
//$year = date("Y", time());
//$month = date("m", time());
//$days = get_days_by_year_and_month($year, $month);
//$stime = date("Y-m-01 00:00:00", time());
//$etime = date("Y-m-01 00:00:00", strtotime("+1 month"));

//$stime = "2019-08-12 00:00:00";
//$etime = "2019-08-19 00:00:00";

$stime = date('Y-m-d 00:00:00', strtotime('-7 days'));//上周周一
$etime = date('Y-m-d 00:00:00', time());//本周的周一

$type = "116";
$clause = array(
    'wstatus' => 3,
    'sctime' => $stime,
    'ectime' => $etime,
    'spuType' => array("NEQ", $type),
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

    $orderDataSummary[$warname]['totalSprice'] += $totalSprice;
    $orderDataSummary[$warname]['totalTprice'] += $totalTprice;
    $orderDataSummary[$warname]['totalCprofit'] += $totalCprofit;
    if (!array_key_exists($orderDatum['sku_code'], $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']])) {
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
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']] = $goodsData;
    } else {
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['sku_count'] += $orderDatum['sku_count'];
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalSprice'] += $totalSprice;
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalTprice'] += $totalTprice;
        $orderDataSummary[$warname][$time][$orderDatum['spu_subtype']][$orderDatum['sku_code']]['totalCprofit'] += $totalCprofit;
    }
    if (!array_key_exists($orderDatum['sku_code'], $orderDataArr[$warname][$username][$time][$orderDatum['spu_subtype']])) {
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
$pushOrderSumSprice = array('订单汇总内部销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalSprice']);
$pushOrderSumCprofit = array('订单汇总客户总利润', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalCprofit']);
$pushOrderSumTprice = array('订单汇总客户销售总金额', '', '', '', '', '', '', '', '', '', $orderDataSummary['totalTprice']);
unset($orderDataSummary['totalSprice']);
unset($orderDataSummary['totalTprice']);
unset($orderDataSummary['totalCprofit']);

$OrderExport["订单汇总"][] = array('项目名称', '订单编号', '日期', '序号', '货品编号', '货品品名', '仓储方式', '供货商', '一级分类', '二级分类', '品牌', '单位', '规格', '数量', '采购价', '内部销售金额', '客户销售金额', '客户利润', '毛利', '毛利率(%)', '客户利润占毛利比');
foreach ($orderDataSummary as $warname => $orderData) {
    if ($warname != "totalTprice" || $warname != "totalSprice" || $warname != "totalCprofit") {
//        $OrderExport["订单汇总"][] = array('项目名称', $warname);
//        $OrderExport["订单汇总"][] = array('订单编号', '日期', '序号', '货品编号', '货品品名', '仓储方式', '供货商', '一级分类', '二级分类', '品牌', '单位', '规格', '数量', '采购价', '内部销售金额', '客户销售金额', '客户利润', '毛利', '毛利率(%)', '客户利润占毛利比');
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
                    $skBprice = bcmul($goodsDatum['spu_bprice'], $goodsDatum['spu_count'], 4);
                    $finalSkBprice = bcmul($skBprice, $goodsDatum['sku_count'], 4);
                    $grossProfit = bcsub($goodsDatum['totalSprice'], $finalSkBprice, 4);//毛利
                    $grossProfitMargin = bcdiv($grossProfit, $goodsDatum['totalSprice'], 4);//毛利率
                    $grossProfitMargin = bcmul($grossProfitMargin,100,1);//毛利率
                    $cpgpr = bcdiv($goodsDatum['totalCprofit'], $grossProfit, 4);//客户利润占毛利比
                    $OrderExport["订单汇总"][] = array($warname, $goodsDatum['order_code'], $time, $keys + 1, $goodsDatum['sku_code'], $goodsDatum['spu_name'], $goodsDatum['spu_storetype'], $goodsDatum['sup_name'], $goodsDatum['spu_type'], $goodsDatum['spu_subtype'], $goodsDatum['spu_brand'], $goodsDatum['sku_unit'], $goodsDatum['sku_norm'], $goodsDatum['sku_count'], $finalSkBprice, $goodsDatum['totalSprice'], $goodsDatum['totalTprice'], $goodsDatum['totalCprofit'], $grossProfit, $grossProfitMargin, $cpgpr);
                    $keys++;
                }
            }
        }
//        $OrderExport["订单汇总"][] = array('内部销售总金额', '', '', '', '', '', '', '', '', '', $totalSpriceWarSum);
//        $OrderExport["订单汇总"][] = array('客户总利润', '', '', '', '', '', '', '', '', '', $totalCprofitWarSum);
//        $OrderExport["订单汇总"][] = array('客户销售总金额', '', '', '', '', '', '', '', '', '', $totalTpriceWarSum);
//        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
//        $OrderExport["订单汇总"][] = array('', '', '', '', '', '', '', '', '');
    }
}
array_push($OrderExport["订单汇总"], $pushOrderSumSprice);
array_push($OrderExport["订单汇总"], $pushOrderSumTprice);
array_push($OrderExport["订单汇总"], $pushOrderSumCprofit);

foreach ($orderDataArr as $warname => $orderData) {
    foreach ($orderData as $userName => $orderDatum) {
//        $OrderExport[$userName][] = array('项目名称', $warname);
        $OrderExport[$userName][] = array('项目名称', '订单编号', '日期', '序号', '货品编号', '货品品名', '仓储方式', '供货商', '一级分类', '二级分类', '品牌', '单位', '规格', '数量', '采购价', '内部销售金额', '客户销售金额', '客户利润', '毛利', '毛利率(%)', '客户利润占毛利比');
        $keys = 0;
        foreach ($orderDatum as $time => $goodsDataArr) {
            if ($time == "totalTprice") {
                $goodsMonthSumTprice = array('客户销售总金额', '', '', '', '', '', '', '', '', '', $goodsDataArr);
            } elseif ($time == "totalSprice") {
                $goodsMonthSumSprice = array('内部销售总金额', '', '', '', '', '', '', '', '', '', $goodsDataArr);
            } elseif ($time == "totalCprofit") {
                $goodsMonthSumCprofit = array('客户总利润', '', '', '', '', '', '', '', '', '', $goodsDataArr);
            } else {
                foreach ($goodsDataArr as $goodsData) {
                    foreach ($goodsData as $skuCode => $goodsDatum) {
                        $skBprice = bcmul($goodsDatum['spu_bprice'], $goodsDatum['spu_count'], 4);
                        $finalSkBprice = bcmul($skBprice, $goodsDatum['sku_count'], 4);
                        $grossProfit = bcsub($goodsDatum['totalSprice'], $finalSkBprice, 4);//毛利
                        $grossProfitMargin = bcdiv($grossProfit, $goodsDatum['totalSprice'], 4);//毛利率
                        $grossProfitMargin = bcmul($grossProfitMargin,100,1);//毛利率
                        $cpgpr = bcdiv($goodsDatum['totalCprofit'], $grossProfit, 4);//客户利润占毛利比
                        $OrderExport[$userName][] = array($warname, $goodsDatum['order_code'], $time, $keys + 1, $goodsDatum['sku_code'], $goodsDatum['spu_name'], $goodsDatum['spu_storetype'], $goodsDatum['sup_name'], $goodsDatum['spu_type'], $goodsDatum['spu_subtype'], $goodsDatum['spu_brand'], $goodsDatum['sku_unit'], $goodsDatum['sku_norm'], $goodsDatum['sku_count'], $finalSkBprice, $goodsDatum['totalSprice'], $goodsDatum['totalTprice'], $goodsDatum['totalCprofit'], $grossProfit, $grossProfitMargin, $cpgpr);
                        $keys++;
                    }
                }
            }

        }
        $OrderExport[$userName][] = $goodsMonthSumTprice;
        $OrderExport[$userName][] = $goodsMonthSumSprice;
        $OrderExport[$userName][] = $goodsMonthSumCprofit;
        $OrderExport[$userName][] = array('', '', '', '', '', '', '', '', '');
        $OrderExport[$userName][] = array('', '', '', '', '', '', '', '', '');
    }

}
$fileName = ExcelService::getInstance()->exportExcel($OrderExport, '', "002", 1);
if (!empty($fileName)) {

    $stime = date('Y.m.d', strtotime('-7 days'));//上周周一
    $etime = date('Y.m.d', strtotime('-1 days'));//当前时间的前一天

    $item = array(
        "fname" => '订单汇总('.$stime.'-'.$etime.')',
        "sfname" => $fileName,
        "scatalogue" => '002',//文件存放目录
        "sdepartments" => '4',//所属部门：1.采购部 2.市场部 3.仓配部 4.财务部 5.品控部
    );
    $insertFileslog = ReportdownloadDao::getInstance()->insert($item);
    if($insertFileslog){
        echo "写入成功";
    }else{
        echo "写入失败";
    }

    /*$time = venus_current_datetime();
    $newFname = "orderSummary.xlsx";
    $moveFileRes = rename("/home/wms/app/Public/files/002/$fileName", "/home/wms/app/Public/orderfiles/$newFname");
    if ($moveFileRes) {
        echo $newFname;
    } else {
        echo "移动失败" . "$newFname";
    }*/
} else {
    $success = false;
    $data = "";
    $message = "下载失败";
}
venus_script_finish($time);
exit();









