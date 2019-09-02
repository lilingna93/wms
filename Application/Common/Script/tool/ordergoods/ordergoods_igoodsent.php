<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/6/4
 * Time: 10:25
 */
ini_set('memory_limit', '256M');
define('APP_DIR', dirname(__FILE__) . '/../../../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

$time = venus_script_begin("开始同步订单采购价数据");

//$date = date("Y-m-d", strtotime("-5days"));
//$stime = $date . " 00:00:00";
//$etime = $date . " 23:59:59";

$stime = "2019-06-14 00:00:00";
$etime = "2019-06-18 23:59:59";

echo $date . PHP_EOL;
echo $stime . PHP_EOL;
echo $etime . PHP_EOL;

$ordergoodsData = getOrdergoodsData($stime, $etime);
$updateData = getBpriceOrdergoodsData($ordergoodsData);

echo count($updateData) . PHP_EOL;

if (!empty($updateData)) {
    $isSuccess = true;
    venus_db_starttrans();
    foreach ($updateData as $goodsCode => $bprice) {
        $isSuccess = $isSuccess && M("ordergoods")->where(array("goods_code" => $goodsCode))->fetchSql(false)->save(array("spu_bprice" => $bprice, "timestamp" => venus_current_datetime()));
    }
    $orderCodeArr=array_keys($ordergoodsData);
    foreach ($orderCodeArr as $ocode) {
        $issetOrdergoodsList = \Wms\Dao\OrdergoodsDao::getInstance()->queryListByOrderCode($ocode, 0, 10000);
        $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);
        $uptOrderRes = \Wms\Dao\OrderDao::getInstance()->updatePriceByCode(
            $ocode, $uptOrderData['totalBprice'],
            $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
        $isSuccess = $isSuccess && $uptOrderRes;
    }
    if ($isSuccess) {
        venus_db_commit();
        echo $stime . "-" . $etime . "同步订单采购价数据成功";
    } else {
        venus_db_rollback();
        $title = "同步订单采购价数据";
        $content = "同步订单采购价数据失败";
        echo $title . ": " . $content;
        if (sendMailer($title, $content)) {
            echo "(发送成功)";
        } else {
            echo "(发送失败)";
        }
    }
} else {
    echo "data null" . PHP_EOL;
}

venus_script_finish($time);
exit();

function getOrdergoodsData($stime, $etime)
{
    $condition = array();
    $condition["order_ctime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND'
    );
    $orderCount = M("order")->where($condition)->count();
    $orderData = M("order")->where($condition)->field("order_code,order_ctime")->order("order_code desc")->limit(0, $orderCount)->fetchSql(false)->select();
    $orderCodeArr = array_column($orderData, "order_code");
    if (!empty($orderCodeArr)) {
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

        $data = array();
        foreach ($ordergoodsData as $ordergoodsDatum) {
            if (empty($ordergoodsDatum['ot_code'])) continue;
            $data[$ordergoodsDatum['order_code']][$ordergoodsDatum['goods_code']]['spucode'] = $ordergoodsDatum['spu_code'];
            $data[$ordergoodsDatum['order_code']][$ordergoodsDatum['goods_code']]['bprice'] = $ordergoodsDatum['bprice'];
        }
        return $data;
    } else {
        echo "无数据" . PHP_EOL;
        exit();
    }

}

function getBpriceOrdergoodsData($dataArr)
{
    $bpriceArr = array();
    foreach ($dataArr as $orderCode => $data) {
        foreach ($data as $goodsCode => $goodsData) {
            $spuCode = $goodsData['spucode'];
            $spuBprice = $goodsData['bprice'];
            $bprice = getBpriceData($orderCode, $spuCode);
            if ($spuBprice == $bprice) continue;

            $bpriceArr[$goodsCode] = $bprice;
        }
    }
    return $bpriceArr;
}

function getBpriceData($orderCode, $spuCode)
{
    $igsData = M("igoodsent")
        ->alias("igs")
        ->field("igs_count,igs_bprice")
        ->join("left join `wms_invoice` inv on inv.inv_code=igs.inv_code")
        ->where(array("inv.inv_ecode" => $orderCode, "igs.spu_code" => $spuCode))
        ->select();

    $sum = 0;
    $count = 0;
    foreach ($igsData as $igsDatum) {
        $sum = floatval(bcadd($sum, bcmul($igsDatum['igs_count'], $igsDatum['igs_bprice'], 4), 4));
        $count = floatval(bcadd($count, $igsDatum['igs_count'], 4));
    }
    return floatval(bcdiv($sum, $count, 4));

}


