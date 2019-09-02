<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/3/25
 * Time: 13:29
 * 财务月度报表
 */
ini_set('memory_limit', '1000M');
define('IS_MASTER', true);
define('APP_DIR', dirname(__FILE__) . '/../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
$time = venus_script_begin("开始获取月度数据");
//$stime = date("Y-m-01 00:00:00", time());
//$etime = date("Y-m-01 00:00:00", strtotime("+1month"));
//$etime = date("Y-m-01 00:00:00", time());
//$stime = date("Y-m-01 00:00:00", strtotime("-1month"));
$stime="2019-03-01 00:00:00";
$etime="2019-04-01 00:00:00";
echo $stime . PHP_EOL;
echo $etime . PHP_EOL;
$data = getOwnMonthData($stime, $etime);
$warData = $data["war"];
$timeData = $data["time"];
$spuTypeData = $data["type"];
$returnDataArr = $data["return"];
//echo json_encode($returnDataArr) . PHP_EOL;
$warExcelData = get_war_excel_data($warData, $stime, $etime);
$warFileData = export_report($warExcelData, "050");
echo $warFileData . PHP_EOL;
$timeExcelData = get_time_excel_data($timeData, $stime, $etime);
$timeFileData = export_report($timeExcelData, "051");
echo $timeFileData . PHP_EOL;
$spuTypeExcelData = get_sputype_excel_data($spuTypeData, $stime, $etime);
$spuTypeFileData = export_report($spuTypeExcelData, "052");
//echo md5(json_encode($spuTypeExcelData)) . PHP_EOL;
echo $spuTypeFileData . PHP_EOL;
//exit();
$supTypeData = getSupMonthData($stime, $etime);
$spuTypeSupData = $supTypeData["type"];
$spuTypeSupExcelData = get_sputype_excel_data($spuTypeSupData, $stime, $etime);
$spuTypeSupFileData = export_report($spuTypeSupExcelData, "052");
echo "sup:" . $spuTypeSupFileData . PHP_EOL;
//exit();
//$spuArr = array();
//$goodsData = get_goods_data($stime, $etime);
////echo 1;
//$goodsExcelData = get_goods_excel_data($goodsData);
//$goodsFileData = export_report($goodsExcelData, "053");
//echo $goodsFileData . PHP_EOL;
//echo venus_current_datetime();
//exit();
$fileDataArrList = array(
    "050" => array(
        "项目组" => $warFileData
    ),
    "051" => array(
        "时间" => $timeFileData
    ),
    "052" => array(
        "品类" => $spuTypeFileData,
        "直采品类" => $spuTypeSupFileData
    ),
    "053" => array(
        "库存" => $goodsFileData
    )
);
$saveName = "月度毛利统计表";
$a = output_zip_file_arr($fileDataArrList, $saveName);
$title = "月度毛利统计表";
$content = "月度毛利统计表：项目，时间，品类，库存(不局限于前三个表的数据，可能存在纸质订单之类的数据)";
//$address = array("lingna.li@shijijiaming.com");
//$address = array("linghui.wang@shijijiaming.com");
//$attachment = array(
//    "项目组.xlsx" => C("FILE_SAVE_PATH") . "050/" . $warFileData,
//    "时间.xlsx" => C("FILE_SAVE_PATH") . "051/" . $timeFileData,
//    "品类.xlsx" => C("FILE_SAVE_PATH") . "052/" . $spuTypeFileData,
//    "库存.xlsx" => C("FILE_SAVE_PATH") . "053/" . $goodsFileData,
//);
$attachment = array(
    "$saveName.zip" => $a,
);
if (sendMailer($title, $content, $address, $attachment)) {
    echo "(发送成功)";
} else {
    echo "(发送失败)";
}
echo venus_current_datetime();
exit();
/**
 * @param $stime
 * @param $etime
 * @return array
 * 获取订单中自营货品信息
 */
function getOwnMonthData($stime, $etime)
{
    $condition = array();
    $condition["order_ctime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND'
    );
    $condition["w_order_status"] = array('EQ', 3);
    $orderData = M("order")->where($condition)->field("order_code,order_ctime")->order("order_code desc")->limit(0, 1000000)->fetchSql(false)->select();
    $orderCodeArr = array_column($orderData, "order_code");
    $orderTimeArr = array();
    foreach ($orderData as $orderDatum) {
        $orderTimeArr[$orderDatum['order_code']] = $orderDatum['order_ctime'];
    }
    $ordergoodsCount = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice,goods.profit_price,goods.spu_bprice spu_bprice,goods.spu_count spu_count")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr), "goods.supplier_code" => "SU00000000000001"))
        ->count();
    $ordergoodsData = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice sprice,goods.profit_price,goods.spu_bprice bprice")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr), "goods.supplier_code" => "SU00000000000001"))
        ->order('goods.goods_code desc')->limit(0, $ordergoodsCount)->fetchSql(false)->select();

    $warData = array();
    $timeData = array();
    $spuTypeData = array();
    $returnDataArr = array();
    foreach ($ordergoodsData as $ordergoodsDatum) {
        $warCode = $ordergoodsDatum['war_code'];
        $dbName = C('WMS_CLIENT_DBNAME');
        $warName = M("$dbName.warehouse")->where(array("war_code" => $warCode))->getField("war_name");
        if (empty($warName)) {
            echo M("$dbName.warehouse")->where(array("war_code" => $warCode))->fetchSql(true)->getField("war_name");
            echo $warCode;
            exit();
        }
        $orderCode = $ordergoodsDatum['order_code'];
        $orderTime = date("m/d", strtotime($orderTimeArr[$orderCode]));
        $spuName = $ordergoodsDatum['spu_name'];
        $spuType = venus_spu_type_name($ordergoodsDatum['spu_type']);
        $spuBprice = $ordergoodsDatum['bprice'];
        $spuSprice = $ordergoodsDatum['sprice'];
        $spuPprice = $ordergoodsDatum["profit_price"];
        if ($spuType == "鲜鱼水菜") continue;

        $skuCount = floatval($ordergoodsDatum['sku_init']);
        $spuCount = $ordergoodsDatum['spu_count'];
        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));
        $skuBprice = floatval(bcmul($spuBprice, $spuCount, 8));
        $skuPprice = floatval(bcmul($spuPprice, $spuCount, 8));
        $sprice = floatval(bcmul($skuSprice, $skuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $pprice = floatval(bcmul($skuPprice, $skuCount, 8));

        $warData[$warName][$spuType]['money'] = floatval(bcadd($warData[$warName][$spuType]['money'], $sprice, 8));
        $warData[$warName][$spuType]['bprice'] = floatval(bcadd($warData[$warName][$spuType]['bprice'], $bprice, 8));
        $warData[$warName][$spuType]['count'] = floatval(bcadd($warData[$warName][$spuType]['count'], $skuCount, 8));

        $timeData[$orderTime][$spuType]['money'] = floatval(bcadd($timeData[$orderTime][$spuType]['money'], $sprice, 8));
        $timeData[$orderTime][$spuType]['bprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['bprice'], $bprice, 8));
        $timeData[$orderTime][$spuType]['count'] = floatval(bcadd($timeData[$orderTime][$spuType]['count'], $skuCount, 8));


        $spuTypeData[$spuType][$spuName]['money'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
        $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));
        $spuTypeData[$spuType][$spuName]['count'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['count'], $skuCount, 8));

        if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
            $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
        } else {
            $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], $pprice, 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
        }
    }
    $condition = array();
    $condition["rt_addtime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND',
    );

    $returnTaskData = M("returntask")->where($condition)->field("rt_code,rt_addtime")->order('rt_addtime desc')->limit(0, 1000000)->select();
    $returnTaskCodes = array_column($returnTaskData, "rt_code");
    $returnAddTimeArr = array();
    foreach ($returnTaskData as $returnTaskDatum) {
        $returnAddTimeArr[$returnTaskDatum['rt_code']] = $returnTaskDatum['rt_addtime'];
    }
    $returnData = M("ordergoodsreturn")->alias("ogr")->field("*,ogr.spu_code,ogr.spu_bprice,ogr.supplier_code")
        ->join("left join wms_spu spu on spu.spu_code=ogr.spu_code")
        ->where(array("rt_code" => array("in", $returnTaskCodes), "ogr.supplier_code" => "SU00000000000001", "ogr_status" => 2))
        ->limit(0, 1000000)->select();
    foreach ($returnData as $returnDatum) {
        $warName = $returnDatum["war_name"];
        $orderCode = $returnDatum["order_code"];
        $spuName = $returnDatum["spu_name"];
        $spuBprice = $returnDatum["spu_bprice"];
        $spuSprice = $returnDatum["spu_sprice"];
        $spuPprice = $returnDatum["profit_price"];
        $spuCount = $returnDatum["spu_count"];
        $goodsCode = $returnDatum["goods_code"];
        $rtCode = $returnDatum["rt_code"];
        $status = $returnDatum["ogr_status"];
        if ($status != 2) continue;
        $spuType = venus_spu_type_name($returnDatum['spu_type']);
        if ($spuType == "鲜鱼水菜") continue;
        $returnCount = floatval($returnDatum['actual_count']);


        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));
        $skuBprice = floatval(bcmul($spuBprice, $spuCount, 8));
        $skuPprice = floatval(bcmul($spuPprice, $spuCount, 8));
        $sprice = floatval(bcmul($skuSprice, $returnCount, 8));
        $bprice = floatval(bcmul($skuBprice, $returnCount, 8));
        $pprice = floatval(bcmul($skuPprice, $returnCount, 8));

        $returnDataArr[$warName][$returnAddTimeArr[$rtCode]][$orderCode][$goodsCode][$spuName][$skuBprice][$skuSprice]["returncount"] = $returnCount;
        $time = date("m/d", strtotime($returnAddTimeArr[$rtCode]));
        $warData[$warName][$spuType]['money'] = floatval(bcsub($warData[$warName][$spuType]['money'], $sprice, 8));
        $warData[$warName][$spuType]['bprice'] = floatval(bcsub($warData[$warName][$spuType]['bprice'], $bprice, 8));
        $warData[$warName][$spuType]['count'] = floatval(bcsub($warData[$warName][$spuType]['count'], $returnCount, 8));

        $timeData[$time][$spuType]['money'] = floatval(bcsub($timeData[$time][$spuType]['money'], $sprice, 8));
        $timeData[$time][$spuType]['bprice'] = floatval(bcsub($timeData[$time][$spuType]['bprice'], $bprice, 8));
        $timeData[$time][$spuType]['count'] = floatval(bcsub($timeData[$time][$spuType]['count'], $returnCount, 8));

        $spuTypeData[$spuType][$spuName]['money'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
        $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));
        $spuTypeData[$spuType][$spuName]['count'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['count'], $returnCount, 8));
        if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
            $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
        } else {
            $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], $pprice, 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
        }
    }
//    echo md5(json_encode($spuTypeData));
//    exit();
    ksort($timeData);
    $data = array(
        "war" => $warData,
        "time" => $timeData,
        "type" => $spuTypeData,
        "return" => $returnDataArr
    );

    return $data;
}

/**
 * @param $stime
 * @param $etime
 * @return array
 * 获取订单中直采货品信息
 */
function getSupMonthData($stime, $etime)
{
    $condition = array();
    $condition["order_ctime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND'
    );
    $condition["w_order_status"] = array('EQ', 3);
    $orderData = M("order")->where($condition)->field("order_code,order_ctime")->order("order_code desc")->limit(0, 1000000)->fetchSql(false)->select();
    $orderCodeArr = array_column($orderData, "order_code");
    $orderTimeArr = array();
    foreach ($orderData as $orderDatum) {
        $orderTimeArr[$orderDatum['order_code']] = $orderDatum['order_ctime'];
    }
    $ordergoodsCount = M("ordergoods")->alias("goods")->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,goods.spu_sprice,goods.profit_price,goods.spu_bprice spu_bprice,goods.spu_count spu_count")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array(
            "goods.order_code" => array("in", $orderCodeArr),
            "goods.supplier_code" => array("neq", "SU00000000000001")
        ))
        ->count();
    $ordergoodsData = M("ordergoods")->alias("goods")->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,goods.spu_sprice sprice,goods.profit_price,goods.spu_bprice bprice")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array(
            "goods.order_code" => array("in", $orderCodeArr),
            "goods.supplier_code" => array("neq", "SU00000000000001")
        ))
        ->order('goods.goods_code desc')->limit(0, $ordergoodsCount)->fetchSql(false)->select();

    $warData = array();
    $timeData = array();
    $spuTypeData = array();
    $returnDataArr = array();
    foreach ($ordergoodsData as $ordergoodsDatum) {
        $warCode = $ordergoodsDatum['war_code'];
        $dbName = C('WMS_CLIENT_DBNAME');
        $warName = M("$dbName.warehouse")->where(array("war_code" => $warCode))->getField("war_name");
        if (empty($warName)) {
            echo M("$dbName.warehouse")->where(array("war_code" => $warCode))->fetchSql(true)->getField("war_name");
            echo $warCode;
            exit();
        }
        $orderCode = $ordergoodsDatum['order_code'];
        $orderTime = date("m/d", strtotime($orderTimeArr[$orderCode]));
        $spuName = $ordergoodsDatum['spu_name'];
        $spuType = venus_spu_type_name($ordergoodsDatum['spu_type']);
        $spuBprice = $ordergoodsDatum['bprice'];
        $spuSprice = $ordergoodsDatum['sprice'];
        $spuPprice = $ordergoodsDatum["profit_price"];
        if ($spuType == "鲜鱼水菜") continue;

        $skuCount = floatval($ordergoodsDatum['sku_init']);
        $spuCount = $ordergoodsDatum['spu_count'];
        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));
        $skuBprice = floatval(bcmul($spuBprice, $spuCount, 8));
        $skuPprice = floatval(bcmul($spuPprice, $spuCount, 8));
        $sprice = floatval(bcmul($skuSprice, $skuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $pprice = floatval(bcmul($skuPprice, $skuCount, 8));

        $warData[$warName][$spuType]['money'] = floatval(bcadd($warData[$warName][$spuType]['money'], $sprice, 8));
        $warData[$warName][$spuType]['bprice'] = floatval(bcadd($warData[$warName][$spuType]['bprice'], $bprice, 8));
        $warData[$warName][$spuType]['count'] = floatval(bcadd($warData[$warName][$spuType]['count'], $skuCount, 8));

        $timeData[$orderTime][$spuType]['money'] = floatval(bcadd($timeData[$orderTime][$spuType]['money'], $sprice, 8));
        $timeData[$orderTime][$spuType]['bprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['bprice'], $bprice, 8));
        $timeData[$orderTime][$spuType]['count'] = floatval(bcadd($timeData[$orderTime][$spuType]['count'], $skuCount, 8));

        $spuTypeData[$spuType][$spuName]['money'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
        $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));
        $spuTypeData[$spuType][$spuName]['count'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['count'], $skuCount, 8));

        if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
            $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
        } else {
            $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], $pprice, 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
        }
    }
    $condition = array();
    $condition["rt_addtime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND',
    );

    $returnTaskData = M("returntask")->where($condition)->field("rt_code,rt_addtime")->order('rt_addtime desc')->limit(0, 1000000)->select();
    $returnTaskCodes = array_column($returnTaskData, "rt_code");
    $returnAddTimeArr = array();
    foreach ($returnTaskData as $returnTaskDatum) {
        $returnAddTimeArr[$returnTaskDatum['rt_code']] = $returnTaskDatum['rt_addtime'];
    }
    $returnData = M("ordergoodsreturn")->alias("ogr")->field("*,ogr.spu_code,ogr.spu_bprice,ogr.supplier_code")
        ->join("left join wms_spu spu on spu.spu_code=ogr.spu_code")
        ->where(array(
            "rt_code" => array("in", $returnTaskCodes),
            "ogr.supplier_code" => array("neq", "SU00000000000001"),
            "ogr_status" => 2
        ))
        ->limit(0, 1000000)->select();
    foreach ($returnData as $returnDatum) {
        $orderTime = date("m/d", strtotime($orderTimeArr[$orderCode]));
        $warName = $returnDatum["war_name"];
        $orderCode = $returnDatum["order_code"];
        $spuName = $returnDatum["spu_name"];
        $spuBprice = $returnDatum["spu_bprice"];
        $spuSprice = $returnDatum["spu_sprice"];
        $spuPprice = $returnDatum["profit_price"];
        $spuCount = $returnDatum["spu_count"];
        $goodsCode = $returnDatum["goods_code"];
        $rtCode = $returnDatum["rt_code"];
        $status = $returnDatum["ogr_status"];
        if ($status != 2) continue;
        $spuType = venus_spu_type_name($returnDatum['spu_type']);
        if ($spuType == "鲜鱼水菜") continue;
        $returnCount = floatval($returnDatum['actual_count']);


        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));
        $skuBprice = floatval(bcmul($spuBprice, $spuCount, 8));
        $skuPprice = floatval(bcmul($spuPprice, $spuCount, 8));
        $sprice = floatval(bcmul($skuSprice, $returnCount, 8));
        $bprice = floatval(bcmul($skuBprice, $returnCount, 8));
        $pprice = floatval(bcmul($skuPprice, $returnCount, 8));

        $returnDataArr[$warName][$returnAddTimeArr[$rtCode]][$orderCode][$goodsCode][$spuName][$skuBprice][$skuSprice]["returncount"] = $returnCount;
        $time = date("m/d", strtotime($returnAddTimeArr[$rtCode]));

        $warData[$warName][$spuType]['money'] = floatval(bcsub($warData[$warName][$spuType]['money'], $sprice, 8));
        $warData[$warName][$spuType]['bprice'] = floatval(bcsub($warData[$warName][$spuType]['bprice'], $bprice, 8));
        $warData[$warName][$spuType]['count'] = floatval(bcsub($warData[$warName][$spuType]['count'], $returnCount, 8));

        $timeData[$time][$spuType]['money'] = floatval(bcsub($timeData[$time][$spuType]['money'], $sprice, 8));
        $timeData[$time][$spuType]['bprice'] = floatval(bcsub($timeData[$time][$spuType]['bprice'], $bprice, 8));
        $timeData[$time][$spuType]['count'] = floatval(bcsub($timeData[$time][$spuType]['count'], $returnCount, 8));

        $spuTypeData[$spuType][$spuName]['money'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
        $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));
        $spuTypeData[$spuType][$spuName]['count'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['count'], $returnCount, 8));
        if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
            $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
        } else {
            $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], $pprice, 8));
            $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
            $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
        }
    }
//    echo md5(json_encode($spuTypeData));
//    exit();
    ksort($timeData);
    $data = array(
        "war" => $warData,
        "time" => $timeData,
        "type" => $spuTypeData,
        "return" => $returnDataArr
    );

    return $data;
}

/**
 * @param $warData项目维度数据
 * @param $stime开始时间
 * @param $etime结束时间
 * @return array
 */
function get_war_excel_data($warData, $stime, $etime)
{
    $excelData = array();
    $timeCell = "D2";
    $excelData["月度毛利统计表-项目组"][$timeCell] = "制表期间:" . $stime . "-" . $etime;
    $line = 6;
    foreach ($warData as $warName => $warDatum) {
        $numCell = 'A' . $line;
        $excelData["月度毛利统计表-项目组"][$numCell] = $line - 5;
        $warCell = 'B' . $line;
        $excelData["月度毛利统计表-项目组"][$warCell] = $warName;
        foreach ($warDatum as $spuType => $warItem) {
            if ($spuType == "鸡鸭禽蛋") {
                $spriceCell = 'D' . $line;//销售额
                $mpriceCell = 'E' . $line;//管理费
                $bpriceCell = 'F' . $line;//采购成本
                $ppriceCell = 'G' . $line;//毛利
            } elseif ($spuType == "酒水饮料") {
                $spriceCell = 'H' . $line;//销售额
                $mpriceCell = 'I' . $line;//管理费
                $bpriceCell = 'J' . $line;//采购成本
                $ppriceCell = 'K' . $line;//毛利
            } elseif ($spuType == "调味干货") {
                $spriceCell = 'L' . $line;//销售额
                $mpriceCell = 'M' . $line;//管理费
                $bpriceCell = 'N' . $line;//采购成本
                $ppriceCell = 'O' . $line;//毛利
            } elseif ($spuType == "米面粮油") {
                $spriceCell = 'P' . $line;//销售额
                $mpriceCell = 'Q' . $line;//管理费
                $bpriceCell = 'R' . $line;//采购成本
                $ppriceCell = 'S' . $line;//毛利
            } elseif ($spuType == "水产冻货") {
                $spriceCell = 'T' . $line;//销售额
                $mpriceCell = 'U' . $line;//管理费
                $bpriceCell = 'V' . $line;//采购成本
                $ppriceCell = 'W' . $line;//毛利
            } elseif ($spuType == "休闲食品") {
                $spriceCell = 'X' . $line;//销售额
                $mpriceCell = 'Y' . $line;//管理费
                $bpriceCell = 'Z' . $line;//采购成本
                $ppriceCell = 'AA' . $line;//毛利
            } elseif ($spuType == "猪牛羊肉") {
                $spriceCell = 'AB' . $line;//销售额
                $mpriceCell = 'AC' . $line;//管理费
                $bpriceCell = 'AD' . $line;//采购成本
                $ppriceCell = 'AE' . $line;//毛利
            } else {
                echo "war" . PHP_EOL;
                echo $warName . PHP_EOL;
                echo $spuType . PHP_EOL;
                echo "此一级分类不存在" . PHP_EOL;
                exit();
            }
            $excelData["月度毛利统计表-项目组"][$spriceCell] = $warItem['money'];
            $excelData["月度毛利统计表-项目组"][$bpriceCell] = $warItem['bprice'];
            $excelData["月度毛利统计表-项目组"][$mpriceCell] = $warItem['pprice'];
            $excelData["月度毛利统计表-项目组"][$ppriceCell] = "=$spriceCell-$mpriceCell-$bpriceCell";
        }
        $totalSpriceCell = 'AF' . $line;//销售额
        $totalMpriceCell = 'AG' . $line;//管理费
        $totalBpriceCell = 'AH' . $line;//采购成本
        $totalPpriceCell = 'AI' . $line;//毛利
        $excelData["月度毛利统计表-项目组"][$totalSpriceCell] = "=AB$line+X$line+T$line+P$line+L$line+H$line+D$line";
        $excelData["月度毛利统计表-项目组"][$totalMpriceCell] = "=AC$line+Y$line+U$line+Q$line+M$line+I$line+E$line";
        $excelData["月度毛利统计表-项目组"][$totalBpriceCell] = "=AD$line+Z$line+V$line+R$line+N$line+J$line+F$line";
        $excelData["月度毛利统计表-项目组"][$totalPpriceCell] = "=AF$line-AG$line-AH$line";
        $line++;
    }
    $excelData["月度毛利统计表-项目组"]["line"] = $line - 6;
    return $excelData;
}

/**
 * @param $timeData时间维度数据
 * @param $stime开始时间
 * @param $etime结束时间
 * @return array
 */
function get_time_excel_data($timeData, $stime, $etime)
{
    $excelData = array();
    $timeCell = "C2";
    $excelData["月度毛利统计表-时间"][$timeCell] = "制表期间:" . $stime . "-" . $etime;
    $line = 6;
    foreach ($timeData as $time => $timeDatum) {
        $numCell = 'A' . $line;
        $excelData["月度毛利统计表-时间"][$numCell] = $line - 5;
        $timeCell = 'B' . $line;
        $excelData["月度毛利统计表-时间"][$timeCell] = $time;
        foreach ($timeDatum as $spuType => $warItem) {
            if ($spuType == "鸡鸭禽蛋") {
                $spriceCell = 'C' . $line;//销售额
                $mpriceCell = 'D' . $line;//管理费
                $bpriceCell = 'E' . $line;//采购成本
                $ppriceCell = 'F' . $line;//毛利
            } elseif ($spuType == "酒水饮料") {
                $spriceCell = 'G' . $line;//销售额
                $mpriceCell = 'H' . $line;//管理费
                $bpriceCell = 'I' . $line;//采购成本
                $ppriceCell = 'J' . $line;//毛利
            } elseif ($spuType == "调味干货") {
                $spriceCell = 'K' . $line;//销售额
                $mpriceCell = 'L' . $line;//管理费
                $bpriceCell = 'M' . $line;//采购成本
                $ppriceCell = 'N' . $line;//毛利
            } elseif ($spuType == "米面粮油") {
                $spriceCell = 'O' . $line;//销售额
                $mpriceCell = 'P' . $line;//管理费
                $bpriceCell = 'Q' . $line;//采购成本
                $ppriceCell = 'R' . $line;//毛利
            } elseif ($spuType == "水产冻货") {
                $spriceCell = 'S' . $line;//销售额
                $mpriceCell = 'T' . $line;//管理费
                $bpriceCell = 'U' . $line;//采购成本
                $ppriceCell = 'V' . $line;//毛利
            } elseif ($spuType == "休闲食品") {
                $spriceCell = 'W' . $line;//销售额
                $mpriceCell = 'X' . $line;//管理费
                $bpriceCell = 'Y' . $line;//采购成本
                $ppriceCell = 'Z' . $line;//毛利
            } elseif ($spuType == "猪牛羊肉") {
                $spriceCell = 'AA' . $line;//销售额
                $mpriceCell = 'AB' . $line;//管理费
                $bpriceCell = 'AC' . $line;//采购成本
                $ppriceCell = 'AD' . $line;//毛利
            } else {
                echo "time" . PHP_EOL;
                echo $time . PHP_EOL;
                echo $spuType . PHP_EOL;
                echo "此一级分类不存在" . PHP_EOL;
                exit();
            }
            $excelData["月度毛利统计表-时间"][$spriceCell] = $warItem['money'];
            $excelData["月度毛利统计表-时间"][$bpriceCell] = $warItem['bprice'];
            $excelData["月度毛利统计表-时间"][$mpriceCell] = $warItem['pprice'];
            $excelData["月度毛利统计表-时间"][$ppriceCell] = "=$spriceCell-$mpriceCell-$bpriceCell";
        }
        $totalSpriceCell = 'AE' . $line;//销售额
        $totalMpriceCell = 'AF' . $line;//管理费
        $totalBpriceCell = 'AG' . $line;//采购成本
        $totalPpriceCell = 'AH' . $line;//管理费
        $excelData["月度毛利统计表-时间"][$totalSpriceCell] = "=AA$line+W$line+S$line+O$line+K$line+G$line+C$line";
        $excelData["月度毛利统计表-时间"][$totalMpriceCell] = "=AB$line+X$line+T$line+P$line+L$line+H$line+D$line";
        $excelData["月度毛利统计表-时间"][$totalBpriceCell] = "=AC$line+Y$line+U$line+Q$line+M$line+I$line+E$line";
        $excelData["月度毛利统计表-时间"][$totalPpriceCell] = "=AE$line-AF$line-AG$line";
        $line++;
    }
    $excelData["月度毛利统计表-时间"]["line"] = $line - 6;
    return $excelData;
}

/**
 * @param $spuTypeData品类维度数据
 * @param $stime开始时间
 * @param $etime结束时间
 * @return array
 */
function get_sputype_excel_data($spuTypeData, $stime, $etime)
{
    $excelData = array();
    foreach ($spuTypeData as $spuType => $spuTypeDatum) {
        $timeCell = "C2";
        $excelData["月度毛利统计表-品类{$spuType}"][$timeCell] = "制表期间:" . $stime . "-" . $etime;
        $typeCell = 'C4';
        $excelData["月度毛利统计表-品类{$spuType}"][$typeCell] = $spuType;
        $line = 6;
        foreach ($spuTypeDatum as $spuName => $spuItem) {
            $numCell = 'A' . $line;
            $excelData["月度毛利统计表-品类{$spuType}"][$numCell] = $line - 5;
            $spuNameCell = 'B' . $line;
            $excelData["月度毛利统计表-品类{$spuType}"][$spuNameCell] = $spuName;
            $spriceCell = 'C' . $line;//销售额
            $mpriceCell = 'D' . $line;//管理费
            $bpriceCell = 'E' . $line;//采购成本
            $ppriceCell = 'F' . $line;//毛利
            $excelData["月度毛利统计表-品类{$spuType}"][$spriceCell] = $spuItem['money'];
            $excelData["月度毛利统计表-品类{$spuType}"][$bpriceCell] = $spuItem['bprice'];
            $excelData["月度毛利统计表-品类{$spuType}"][$mpriceCell] = $spuItem['pprice'];
            $excelData["月度毛利统计表-品类{$spuType}"][$ppriceCell] = "=$spriceCell-$mpriceCell-$bpriceCell";
            $line++;
        }
        $excelData["月度毛利统计表-品类{$spuType}"]['line'] = $line - 6;
    }
    return $excelData;
}

function get_goods_data($stime, $etime)
{
    global $spuArr;
    $prevData = get_prev_data($stime);
    $currentData = get_current_data($stime, $etime);
    $data = array();
    foreach ($spuArr as $spuType => $spuData) {
        foreach ($spuData as $spuName) {
            if (array_key_exists($spuName, $prevData[$spuType])) {
                $data[$spuType][$spuName]['prev']['money'] = $prevData[$spuType][$spuName]['prev']['money'];
                $data[$spuType][$spuName]['prev']['count'] = $prevData[$spuType][$spuName]['prev']['count'];
                $data[$spuType][$spuName]['prev']['price'] = $prevData[$spuType][$spuName]['prev']['price'];
            }
            if (array_key_exists($spuName, $currentData[$spuType])) {
                if (array_key_exists("rec", $currentData[$spuType][$spuName])) {
                    $data[$spuType][$spuName]['rec']['money'] = $currentData[$spuType][$spuName]['rec']['money'];
                    $data[$spuType][$spuName]['rec']['count'] = $currentData[$spuType][$spuName]['rec']['count'];
                    $data[$spuType][$spuName]['rec']['price'] = $currentData[$spuType][$spuName]['rec']['price'];
                }
                if (array_key_exists("4", $currentData[$spuType][$spuName])) {
                    $data[$spuType][$spuName]['4']['money'] = $currentData[$spuType][$spuName]['4']['money'];
                    $data[$spuType][$spuName]['4']['count'] = $currentData[$spuType][$spuName]['4']['count'];
                    $data[$spuType][$spuName]['4']['price'] = $currentData[$spuType][$spuName]['4']['price'];
                }
                if (array_key_exists("5", $currentData[$spuType][$spuName])) {
                    $data[$spuType][$spuName]['5']['money'] = $currentData[$spuType][$spuName]['5']['money'];
                    $data[$spuType][$spuName]['5']['count'] = $currentData[$spuType][$spuName]['5']['count'];
                    $data[$spuType][$spuName]['5']['price'] = $currentData[$spuType][$spuName]['5']['price'];
                }
                if (array_key_exists("6", $currentData[$spuType][$spuName])) {
                    $data[$spuType][$spuName]['6']['money'] = $currentData[$spuType][$spuName]['6']['money'];
                    $data[$spuType][$spuName]['6']['count'] = $currentData[$spuType][$spuName]['6']['count'];
                    $data[$spuType][$spuName]['6']['price'] = $currentData[$spuType][$spuName]['6']['price'];
                }
                if (array_key_exists("7", $currentData[$spuType][$spuName])) {
                    $data[$spuType][$spuName]['7']['money'] = $currentData[$spuType][$spuName]['7']['money'];
                    $data[$spuType][$spuName]['7']['count'] = $currentData[$spuType][$spuName]['7']['count'];
                    $data[$spuType][$spuName]['7']['price'] = $currentData[$spuType][$spuName]['7']['price'];
                }
            }
        }
    }

    return $data;
}

/**
 * @param $stime
 * @return array
 * 获取开始时间之前的数据(期初库存)
 */
function get_prev_data($stime)
{
    global $spuArr;
    $clauseRec = array(
        "rec_ctime" => array("ELT", $stime),
    );
    $receiptData = get_list_receipt_data($clauseRec);
    $recCodeData = array_column($receiptData, "rec_code");
    $clauseGoodsbatch = array(
        "gb.rec_code" => array("in", $recCodeData),
    );
    $recData = array();
    foreach ($recCodeData as $recCodeDatum) {
        $recData[$recCodeDatum['rec_code']] = $recCodeDatum['rec_type'];
    }
    $goodsbatchData = get_list_goodsbatch_data($clauseGoodsbatch);
    $data = array();
    foreach ($goodsbatchData as $goodsbatchDatum) {
        $spuType = $goodsbatchDatum['spu_type'];
        $spuName = $goodsbatchDatum['spu_name'];
        $skuCount = $goodsbatchDatum['sku_count'];
        $spuCount = $goodsbatchDatum['spu_count'];
        $skuBprice = floatval(bcmul($goodsbatchDatum['gb_bprice'], $spuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $recCode = $goodsbatchDatum['rec_code'];
        if (venus_spu_type_name($spuType) == "鲜鱼水菜") continue;
        if (!in_array($spuName, $spuArr[$spuType])) {
            $spuArr[$spuType][] = $spuName;
        }
//        if ($recData[$recCode] == 2) {
//            $data[$spuType][$spuName]['prev'] = floatval(bcsub($data[$spuType][$spuName]['prev'], $bprice, 8));
//        } else {
        $data[$spuType][$spuName]['prev']['money'] = floatval(bcadd($data[$spuType][$spuName]['prev']['money'], $bprice, 8));
        $data[$spuType][$spuName]['prev']['count'] = floatval(bcadd($data[$spuType][$spuName]['prev']['count'], $skuCount, 8));
        $data[$spuType][$spuName]['prev']['price'] = floatval(bcdiv($data[$spuType][$spuName]['prev']['money'], $data[$spuType][$spuName]['prev']['count'], 2));
//        }
    }
    $clauseInv = array(
        "inv_ctime" => array("ELT", $stime),
    );
    $invoiceData = get_list_invoice_data($clauseInv);
    $invCodeData = array_column($invoiceData, "inv_code");
    $invData = array();
    foreach ($invCodeData as $invCodeDatum) {
        $invData[$invCodeDatum['inv_code']] = $invCodeDatum['inv_type'];
    }
    $clauseIgoodsent = array(
        "inv_code" => array("in", $invCodeData),
    );
    $igoodsentData = get_list_igoodsent_data($clauseIgoodsent);
    foreach ($igoodsentData as $igoodsentDatum) {
        $spuType = $igoodsentDatum['spu_type'];
        $spuName = $igoodsentDatum['spu_name'];
        $skuCount = $igoodsentDatum['sku_count'];
        $spuCount = $igoodsentDatum['spu_count'];
        $skuBprice = floatval(bcmul($igoodsentDatum['igs_bprice'], $spuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $invCode = $igoodsentDatum['inv_code'];
        if (venus_spu_type_name($spuType) == "鲜鱼水菜") continue;
//        if($invData[$invCode]==6){
//            $data[$spuType][$spuName]['prev'] = floatval(bcadd($data[$spuType][$spuName]['prev'], $bprice, 8));
//        }else{
        $data[$spuType][$spuName]['prev']['money'] = floatval(bcsub($data[$spuType][$spuName]['prev']['money'], $bprice, 2));
        $data[$spuType][$spuName]['prev']['count'] = floatval(bcsub($data[$spuType][$spuName]['prev']['count'], $skuCount, 2));
        $data[$spuType][$spuName]['prev']['price'] = floatval(bcdiv($data[$spuType][$spuName]['prev']['money'], $data[$spuType][$spuName]['prev']['count'], 2));
//        }
        if (!in_array($spuName, $spuArr[$spuType])) {
            $spuArr[$spuType][] = $spuName;
        }
    }
    return $data;
}

/**
 * @param $stime
 * @param $etime
 * @return array
 * 本期数据
 */
function get_current_data($stime, $etime)
{
    global $spuArr;
    $clauseRec = array(
        "rec_ctime" => array(
            array('EGT', $stime), array('ELT', $etime), 'AND'
        ),
        "rec_mark" => array("notlike", "OT%")
    );
    $receiptData = get_list_receipt_data($clauseRec);
    $recCodeData = array_column($receiptData, "rec_code");
    $clauseGoodsbatch = array(
        "gb.rec_code" => array("in", $recCodeData),
    );
    $recData = array();
    foreach ($recCodeData as $recCodeDatum) {
        $recData[$recCodeDatum['rec_code']] = $recCodeDatum['rec_type'];
    }

    $goodsbatchData = get_list_goodsbatch_data($clauseGoodsbatch);
    $data = array();
    foreach ($goodsbatchData as $goodsbatchDatum) {
        $spuType = $goodsbatchDatum['spu_type'];
        $spuName = $goodsbatchDatum['spu_name'];
        $skuCount = $goodsbatchDatum['sku_count'];
        $spuCount = $goodsbatchDatum['spu_count'];
        $skuBprice = floatval(bcmul($goodsbatchDatum['gb_bprice'], $spuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $recCode = $goodsbatchDatum['rec_code'];
        if (venus_spu_type_name($spuType) == "鲜鱼水菜") continue;
//        if ($recData[$recCode] == 2) {
//            $data[$spuType][$spuName]['rec'] = floatval(bcsub($data[$spuType][$spuName]['rec'], $bprice, 8));
//        }else{
        $data[$spuType][$spuName]['rec']['money'] = floatval(bcadd($data[$spuType][$spuName]['rec']['money'], $bprice, 2));
        $data[$spuType][$spuName]['rec']['count'] = floatval(bcadd($data[$spuType][$spuName]['rec']['count'], $skuCount, 2));
        $data[$spuType][$spuName]['rec']['price'] = floatval(bcdiv($data[$spuType][$spuName]['rec']['money'], $data[$spuType][$spuName]['rec']['count'], 2));

//        }
        if (!in_array($spuName, $spuArr[$spuType])) {
            $spuArr[$spuType][] = $spuName;
        }

    }

    $clauseInv = array(
        "inv_ctime" => array(
            array('EGT', $stime), array('ELT', $etime), 'AND'
        ),
//        "inv_mark" => "小程序单(自营)"
        "inv_mark" => array("notlike", "小程序单(直采)")
    );
    $invoiceData = get_list_invoice_data($clauseInv);
    $invTypeData = array();
    foreach ($invoiceData as $invoiceDatum) {
        $invCode = $invoiceDatum['inv_code'];
        $invType = $invoiceDatum['inv_type'];
        $invTypeData[$invCode] = $invType;
    }
    $invCodeData = array_column($invoiceData, "inv_code");
    $clauseIgoodsent = array(
        "inv_code" => array("in", $invCodeData),
    );
    $igoodsentData = get_list_igoodsent_data($clauseIgoodsent);
    foreach ($igoodsentData as $igoodsentDatum) {
        $invCode = $igoodsentDatum['inv_code'];
        $spuType = $igoodsentDatum['spu_type'];
        $spuName = $igoodsentDatum['spu_name'];
        $skuCount = $igoodsentDatum['sku_count'];
        $spuCount = $igoodsentDatum['spu_count'];
        $skuBprice = floatval(bcmul($igoodsentDatum['igs_bprice'], $spuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        if (venus_spu_type_name($spuType) == "鲜鱼水菜") continue;
//        if ($invTypeData[$invCode] == 6) continue;
//{
//            $data[$spuType][$spuName][$invTypeData[$invCode]] = floatval(bcsub($data[$spuType][$spuName][$invTypeData[$invCode]], $bprice, 8));
//        }else{
        $data[$spuType][$spuName][$invTypeData[$invCode]]['money'] = floatval(bcadd($data[$spuType][$spuName][$invTypeData[$invCode]]['money'], $bprice, 2));
        $data[$spuType][$spuName][$invTypeData[$invCode]]['count'] = floatval(bcadd($data[$spuType][$spuName][$invTypeData[$invCode]]['count'], $skuCount, 2));
        $data[$spuType][$spuName][$invTypeData[$invCode]]['price'] = floatval(bcdiv($data[$spuType][$spuName][$invTypeData[$invCode]]['money'], $data[$spuType][$spuName][$invTypeData[$invCode]]['count'], 2));
//        }
        if (!in_array($spuName, $spuArr[$spuType])) {
            $spuArr[$spuType][] = $spuName;
        }
    }

    return $data;
}

function get_goods_excel_data($data)
{
    $excelData = array();
    //出仓单类型
    //"4" => "销售出仓",//20190118增加
    //"5" => "领用出仓",//20190118增加
    //"6" => "退货出仓",//20190118增加
    //"7" => "损耗出仓"//20190118增加
    $line = 4;
    $lineArr = array();
    foreach ($data as $spuType => $currentDatum) {
        $startLine = $line;
        $typeCell = "A" . $startLine;
        $excelData["库存表"][$typeCell] = venus_spu_type_name($spuType);
//        echo $typeCell.PHP_EOL;
        $spuTypeLine = 1;
        foreach ($currentDatum as $spuName => $current) {
            $numCell = "B" . $line;
            $excelData["库存表"][$numCell] = $spuTypeLine;
            $spuNameCell = "C" . $line;
            $excelData["库存表"][$spuNameCell] = $spuName;

            foreach ($current as $type => $moneyData) {
                $count = $moneyData['count'];
                $bprice = $moneyData['price'];
                $money = $moneyData['money'];
                if ($type == "prev") {
                    $countCell = "D" . $line;
                    $bpriceCell = "E" . $line;
                    $moneyCell = "F" . $line;
                    $excelData["库存表"][$countCell] = $count;
                    $excelData["库存表"][$bpriceCell] = $bprice;
                    $excelData["库存表"][$moneyCell] = $money;
                }
                if ($type == "rec") {
                    $countCell = "G" . $line;
                    $bpriceCell = "H" . $line;
                    $moneyCell = "I" . $line;
                    $excelData["库存表"][$countCell] = $count;
                    $excelData["库存表"][$bpriceCell] = $bprice;
                    $excelData["库存表"][$moneyCell] = $money;
                }

                if ($type == 4) {
                    $countCell = "J" . $line;
                    $bpriceCell = "K" . $line;
                    $moneyCell = "L" . $line;
                    $excelData["库存表"][$countCell] = $count;
                    $excelData["库存表"][$bpriceCell] = $bprice;
                    $excelData["库存表"][$moneyCell] = $money;
                }
                if ($type == 5) {
                    $moneyCell = "M" . $line;
                    $excelData["库存表"][$moneyCell] = $money;
                }
                if ($type == 6) {
                    $moneyCell = "N" . $line;
                    $excelData["库存表"][$moneyCell] = $money;
                }
                if ($type == 7) {
                    $moneyCell = "O" . $line;
                    $excelData["库存表"][$moneyCell] = $money;
                }
            }

            $invTotalCell = "P" . $line;
            $excelData["库存表"][$invTotalCell] = "=L$line+M$line+N$line+O$line";
            $goodsTotalCell = "Q" . $line;
            $excelData["库存表"][$goodsTotalCell] = "=F$line+I$line-P$line";
            $spuTypeLine++;
            $line++;
        }
        $endLine = $line;
//        echo $line . PHP_EOL;
//        echo $spuTypeLine . PHP_EOL;
//        echo "A" . $startLine . ":" . "A" . $endLine . PHP_EOL;

        $line++;
        $stopLine = $endLine - 1;
        $excelData["库存表"]["mell"][] = "A" . $startLine . ":" . "A" . $endLine;
        $excelData["库存表"]["mell"][] = "B" . $endLine . ":" . "C" . $endLine;
        $totalSpuTypeCell = "B" . $endLine;
        $excelData["库存表"][$totalSpuTypeCell] = "小计";
        $recTotalCell = "F" . $endLine;
        $excelData["库存表"][$recTotalCell] = "=SUM(F$startLine:F$stopLine)";
        $recCurTotalCell = "I" . $endLine;
        $excelData["库存表"][$recCurTotalCell] = "=SUM(I$startLine:I$stopLine)";
        $sellTotalCell = "L" . $endLine;
        $excelData["库存表"][$sellTotalCell] = "=SUM(L$startLine:L$stopLine)";
        $applyTotalCell = "M" . $endLine;
        $excelData["库存表"][$applyTotalCell] = "=SUM(M$startLine:M$stopLine)";
        $lessTotalCell = "N" . $endLine;
        $excelData["库存表"][$lessTotalCell] = "=SUM(N$startLine:N$stopLine)";
        $returnTotalCell = "O" . $endLine;
        $excelData["库存表"][$returnTotalCell] = "=SUM(O$startLine:O$stopLine)";
        $invTotalCell = "P" . $endLine;
        $excelData["库存表"][$invTotalCell] = "=L$endLine+M$endLine+N$endLine+O$endLine";
        $goodsTotalCell = "Q" . $endLine;
        $excelData["库存表"][$goodsTotalCell] = "=F$endLine+I$endLine-P$endLine";
        $lineArr[] = $endLine;
    }
    $totleLine = $line;
    $excelData["库存表"]['insert'][4] = $endLine - 24;
    $prevTotalStr = "=";
    $recTotalStr = "=";
    $sellTotalStr = "=";
    $applyTotalStr = "=";
    $lessTotalStr = "=";
    $returnTotalStr = "=";
    foreach ($lineArr as $key => $lineData) {
        if ($key != count($lineArr) - 1) {
            $prevTotalStr = $prevTotalStr . "F" . $lineData . "+";
            $recTotalStr = $recTotalStr . "I" . $lineData . "+";
            $sellTotalStr = $sellTotalStr . "L" . $lineData . "+";
            $applyTotalStr = $applyTotalStr . "M" . $lineData . "+";
            $lessTotalStr = $lessTotalStr . "N" . $lineData . "+";
            $returnTotalStr = $returnTotalStr . "O" . $lineData . "+";
        } else {
            $prevTotalStr = $prevTotalStr . "F" . $lineData;
            $recTotalStr = $recTotalStr . "I" . $lineData;
            $sellTotalStr = $sellTotalStr . "L" . $lineData;
            $applyTotalStr = $applyTotalStr . "M" . $lineData;
            $lessTotalStr = $lessTotalStr . "N" . $lineData;
            $returnTotalStr = $returnTotalStr . "O" . $lineData;
        }

    }
    $prevCurTotalCell = "F" . $totleLine;
    $excelData["库存表"][$prevCurTotalCell] = $prevTotalStr;
    $recCurTotalCell = "I" . $totleLine;
    $excelData["库存表"][$recCurTotalCell] = $recTotalStr;
    $sellTotalCell = "L" . $totleLine;
    $excelData["库存表"][$sellTotalCell] = $sellTotalStr;
    $applyTotalCell = "M" . $totleLine;
    $excelData["库存表"][$applyTotalCell] = $applyTotalStr;
    $lessTotalCell = "N" . $totleLine;
    $excelData["库存表"][$lessTotalCell] = $lessTotalStr;
    $returnTotalCell = "O" . $totleLine;
    $excelData["库存表"][$returnTotalCell] = $returnTotalStr;
    $invTotalCell = "P" . $totleLine;
    $excelData["库存表"][$invTotalCell] = "=L$totleLine+M$totleLine+N$totleLine+O$totleLine";
    $goodsTotalCell = "Q" . $totleLine;
    $excelData["库存表"][$goodsTotalCell] = "=F$totleLine+I$totleLine-P$totleLine";
//    exit();
    return $excelData;
//    echo  json_encode($excelData),PHP_EOL;
//    exit();
}

/**
 * @param $clause
 * @return mixed
 * 获取入仓单信息
 */
function get_list_receipt_data($clause)
{

    return M("receipt")->where($clause)->fetchSql(false)->limit(0, 1000000)->select();
}

/**
 * @param $clause
 * @return mixed
 * 获取入仓清单信息
 */
function get_list_goodsbatch_data($clause)
{
    return M("goodsbatch")->alias("gb")
        ->join("left join wms_sku sku on sku.sku_code=gb.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where($clause)->limit(0, 1000000)->select();
}

/**
 * @param $clause
 * @return mixed
 * 获取批次库存信息
 */
function get_list_goodstored_data($clause)
{
    return M("goodstored")->alias("gs")
        ->join("left join wms_sku sku on sku.sku_code=gs.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where($clause)->limit(0, 1000000)->select();
}

/**
 * @param $clause
 * @return mixed
 * 获取出仓单信息
 */
function get_list_invoice_data($clause)
{
    return M("invoice")->where($clause)->limit(0, 1000000)->select();
}

/**
 * @param $clause
 * @return mixed
 * 获取发货清单
 */
function get_list_igoods_data($clause)
{
    return M("igoods")->alias("igo")
        ->join("left join wms_sku sku on sku.sku_code=igo.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=igo.spu_code")
        ->where($clause)->limit(0, 1000000)->select();
}

/**
 * @param $clause
 * @return mixed
 * 获取发货批次清单
 */
function get_list_igoodsent_data($clause)
{
    return M("igoodsent")->alias("igs")
        ->join("left join wms_sku sku on sku.sku_code=igs.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where($clause)->limit(0, 1000000)->select();
}

/**
 * @param $data
 * @param $typeName
 * @return string
 */
function export_report($data, $typeName)
{
    $template = C("FILE_TPLS") . $typeName . ".xlsx";
    $saveDir = C("FILE_SAVE_PATH") . $typeName;

    $fileName = md5(json_encode($data)) . ".xlsx";
    if (file_exists($fileName)) {
        return $fileName;
    }
    vendor('PHPExcel.class');
    vendor('PHPExcel.IOFactory');
    vendor('PHPExcel.Writer.Excel2007');
    vendor("PHPExcel.Reader.Excel2007");
    $objReader = new \PHPExcel_Reader_Excel2007();
    $objPHPExcel = $objReader->load($template);    //加载excel文件,设置模板

    $templateSheet = $objPHPExcel->getSheet(0);


    foreach ($data as $sheetName => $list) {
        $line = $list['line'];
        unset($list['line']);

        $excelSheet = $templateSheet->copy();

        $excelSheet->setTitle($sheetName);
        //创建新的工作表
        $sheet = $objPHPExcel->addSheet($excelSheet);
        if ($typeName != "053" && $line > 11) {
            $addLine = $line - 11;
            $sheet->insertNewRowBefore(11, $addLine);   //在行3前添加n行
        }
        if ($typeName == "053") {

            if (isset($list['mell'])) {
                $mellList = $list['mell'];
                unset($list['mell']);
            }
            if (isset($list['insert'])) {
                foreach ($list['insert'] as $line => $addLine) {
                    $sheet->insertNewRowBefore($line, $addLine);   //在行3前添加n行
                }
                unset($list['insert']);
            }
        }
//        exit();

        foreach ($list as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        if (isset($mellList)) {
            foreach ($mellList as $mell) {
                $sheet->mergeCells($mell);
            }
        }

    }
    //移除多余的工作表
    $objPHPExcel->removeSheetByIndex(0);
    //设置保存文件名字

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    if (!file_exists($saveDir)) {
        mkdir("$saveDir");
    }
    $objWriter->save($saveDir . "/" . $fileName);
    return $fileName;
}

/**
 * @param $fileDataArrList文件数组 [$typeDir->$saveFile->$fileName]
 * @param $saveNamezip包名称
 * 从多种type文件夹下载不同的表格放到同一个zip包
 */
function output_zip_file_arr($fileDataArrList, $saveName)
{
    $fileDataArr = array();
    foreach ($fileDataArrList as $typeDir => $fileData) {
        foreach ($fileData as $saveFile => $fileName) {
            $fileDataArr[$typeDir][$saveFile] = $fileName;
        }
    }
    unset($fileDataArrList);

    $zip = new \ZipArchive();
    $zipName = md5($saveName) . ".zip";
//    $fileZip = C("FILE_ZIP_SAVE_PATH") . $zipName;
    $fileZip = C("FILE_SAVE_PATH") . "000/" . $zipName;
    if (file_exists($fileZip)) {
        unlink($fileZip);
    }
    if (!file_exists($fileZip)) {
        touch($fileZip);
        chmod($fileZip, 0777);
        if ($zip->open($fileZip, \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($fileDataArr as $typeDir => $fileData) {
                foreach ($fileData as $saveFile => $fileName) {
                    if (!empty($fileName)) {
                        $file = C("FILE_SAVE_PATH") . $typeDir . "/" . $fileName;
//                        echo $file . PHP_EOL;
                        if (file_exists($file)) {
                            $zip->addFile($file, $saveFile . ".xlsx");
                        }
                    } else {
                        continue;
                    }

                }
            }
        }
        $zip->close(); //关闭处理的zip文件
        return $fileZip;
    } else {
        return "文件创建失败，请检查对应的目录的写权限";
    }

}