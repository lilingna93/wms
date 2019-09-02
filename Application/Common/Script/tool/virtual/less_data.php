<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/4/15
 * Time: 10:05
 */
define('IS_MASTER', true);
define('APP_DIR', dirname(__FILE__) . '/../../../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
$time = venus_script_begin("修复分单缺少数据");

$igsCodeArr = array(
    "GS40415154300750",
    "GS40415154300834",
    "GS40415154300594",
    "GS40415154300136",
    "GS40415154300105",
    "GS40415154300554",
    "GS40415154300719",
    "GS40415154300597",
    "GS40415154300142",
    "GS40415154300701",
    "GS40415154300838",
    "GS40415154300506",
    "GS40415154300307",
    "GS40415154300365",
    "GS40415154300999",
    "GS40415154300323",
    "GS40415154300626",
    "GS40415154300772",
    "GS40415154300420",
    "GS40415154300663",
    "GS40415154300205",
    "GS40415154300532",
    "GS40415154300291",
    "GS40415154300462",
    "GS40415154300536",
    "GS40415154300988",
    "GS40415154300351",
    "GS40415154300394",
    "GS40415154300495",
    "GS40415154300115",
    "GS40415154300376",
    "GS40415154300235",
    "GS40415154300755",
    "GS40415154300388",
    "GS40415154300141",
    "GS40415154300659",
    "GS40415154300194",
    "GS40415154300302",
    "GS40415154300598",
    "GS40415154300591",
    "GS40415154300967",
    "GS40415154300808",
    "GS40415154300968",
    "GS40415154300249",
    "GS40415154300574",
    "GS40415154300463",
    "GS40415154300483",
    "GS40415154300322",
    "GS40415154300341",
    "GS40415154300200",
    "GS40415154300992",
    "GS40415154300520",
    "GS40415154300270",
    "GS40415154300690",
    "GS40415154300959",
    "GS40415154300569",
    "GS40415154300895",
    "GS40415154300469",
    "GS40415154300160",
    "GS40415154300247",
    "GS40415154300190",
    "GS40415154300213",
    "GS40415154300170",
    "GS40415154300226",
    "GS40415154300632",
    "GS40415154300345",
    "GS40415154300698",
    "GS40415154300211",
    "GS40415154300328",
    "GS40415154300666",
    "GS40415154300589",
    "GS40415154300782",
    "GS40415154300584",
    "GS40415154300100",
    "GS40415154300334",
    "GS40415154300377",
    "GS40415154300163",
    "GS40415154300631",
    "GS40415154301441",
    "GS40415154301250",
    "GS40415154301688",
    "GS40415154301332",
    "GS40415154301878",
    "GS40415154301622",
    "GS40415154301532",
    "GS40415154301419",
);

$uptData = array();
$arr = array();
$ogrData = getOgrData();
$skuCodeArr=array_column($ogrData,"sku_code");
echo json_encode($skuCodeArr);
exit();
foreach ($ogrData as $ogrDatum) {
    $orderCode = $ogrDatum['order_code'];
    $ordergoodsCode = $ogrDatum['goods_code'];
    $skuCode = $ogrDatum['sku_code'];
    $aCount = $ogrDatum['actual_count'];
    $skuCount = $ogrDatum['sku_count'];
    $otCode = $ogrDatum['ot_code'];
    $recCode = getSupReceiptCode($otCode);
    $invCode = getSupInvoiceCode($orderCode);
    $igsCode = getIgsCode($invCode, $skuCode);
    $oldIgsData = getOldIgsData($igsCode);
    $igsData = getIgsData($igsCode);
    $oldGsCode = $oldIgsData['gs_code'];
    $gsCode = $igsData['gs_code'];
    $oldGsData = getOldGsData($oldGsCode);
    $gsData = getGsData($gsCode);
    $oldGbCode = $oldGsData['gb_code'];
    $gbCode = $gsData['gb_code'];
    $skuData = getSkuData($skuCode);
    $spuCount = $skuData['spu_count'];
    $count = bcmul($spuCount, $aCount, 2);
    $sql[] = update_goodsbatch_count_sql($gbCode, $oldGsData['sku_init'], $oldGsData['sku_init']);
    $arr[] = array(
        "newGsCode" => $gsCode,
        "oldGsCode" => $oldGsCode,
        "newGbCode" => $gbCode,
        "oldGbCode" => $oldGbCode,
        "skuCount" => $aCount,
        "count" => $count,
        "orderCode" => $orderCode,
        "skuCode" => $skuCode,
        "old" => $oldGsData,
        "new" => $gsData,
    );
}

foreach ($arr as $item) {
    $oldGsCode = $item['oldGsCode'];
    $oldGbCode = $item['oldGbCode'];
    $gsCode = $item['newGsCode'];
    $gbCode = $item['newGbCode'];
    $skuCount = $item['skuCount'];
    $count = $item['count'];
    $newSkuCount = 0 - $item['skuCount'];
    $newCount = 0 - $item['count'];
    $sql[] = update_goodsbatch_sql($gbCode, $skuCount, $count);
    $sql[] = update_goodstored_sql($gbCode, $skuCount, $count);
    $sql[] = update_goodsbatch_sql($oldGbCode, $skuCount, $count);
    $sql[] = update_goodstored_sql($oldGbCode, $skuCount, $count);
}
echo json_encode($arr);
exit();
function getOgrData()
{
    return M("venus_wms.ordergoodsreturn")->where(array("order_code" => "O40412174421220"))->fetchSql(false)->select();
}

function getIgsCode($invCode, $skuCode)
{
    return M("igoodsent")->where(array("inv_code" => $invCode, "sku_code" => $skuCode))->getField("igs_code");
}

function getOldIgsData($igsCode)
{
    return M("igoodsent")->where(array("igs_code" => $igsCode))->find();
}

function getIgsData($igsCode)
{
    return M("venus_wms.igoodsent")->where(array("igs_code" => $igsCode))->find();
}

function getOldGsData($gsCode)
{
    return M("goodstored")->where(array("gs_code" => $gsCode))->find();
}

function getGsData($gsCode)
{
    return M("venus_wms.goodstored")->where(array("gs_code" => $gsCode))->find();
}

exit();
$sql = array();
foreach ($igsCodeArr as $igsCode) {
    $orderData = getData($igsCode);
    $skuCode = $orderData['sku_code'];
    $orderCode = $orderData['order_code'];
    $otCode = $orderData['ot_code'];
    $recCode = getSupReceiptCode($otCode);
    $gsCode = getGsCode($recCode, $skuCode);
    $sql[] = update_igs_gs_code($igsCode, $gsCode);
}
file_put_contents("./tool/virtual/igsSql.sql", implode(";" . PHP_EOL, $sql));
exit();

function getData($igsCode)
{
    return M("igoodsent")->alias("igs")
        ->join("left join `wms_invoice` inv on inv.inv_code=igs.inv_code")
        ->join("left join `wms_order` o on o.order_code=inv.inv_ecode")
        ->where(array("igs_code" => $igsCode))->find();
}

function update_igs_gs_code($igsCode, $gsCode)
{
    return M("igoodsent")->where(array("igs_code" => $igsCode))->fetchSql(true)->setField("gs_code", $gsCode);
}

$ordergoodsDataList = getOrderGoodsData();
$ownData = array();
$supData = array();
foreach ($ordergoodsDataList as $ordergoodsData) {
    $orderCode = $ordergoodsData['order_code'];
    $goodsCode = $ordergoodsData['goods_code'];
    $skuInit = $ordergoodsData['sku_init'];
    $skuCount = $ordergoodsData['sku_count'];
    $supCode = $ordergoodsData['supplier_code'];
    $skuCode = $ordergoodsData['sku_code'];
    $otCode = $ordergoodsData['ot_code'];
    $oStatus = getOrderStatus($orderCode);
    if ($oStatus == 3 || $oStatus == 1) {
        continue;
    } else {
        if ($supCode == "SU00000000000001") {
            $ownData[$orderCode][$goodsCode] = array(
                "code" => $skuCode,
                "init" => $skuInit,
                "count" => $skuCount,
            );
        } else {
            $supData[$otCode][$orderCode][$goodsCode] = array(
                "code" => $skuCode,
                "init" => $skuInit,
                "count" => $skuCount,
                "supCode" => $supCode,
            );
        }
    }

}


$orderCodeArr = array(
    "O40412152559446", "O40412152559282"
);
foreach ($orderCodeArr as $orderCode) {
    $orderData = getOrderData($orderCode);
    $receiver = $orderData['user_name'];
    $worCode = "WO000001";
    $phone = $orderData['user_phone'];
    $postal = $orderData['war_postal'];
    $ecode = $orderData['order_code'];
    $invAddData = array(
        "status" => 5,//出仓单状态已出仓
        "receiver" => $receiver,//客户名称
        "type" => 4,//出仓单类型
        "mark" => "小程序单(直采)",//出仓单备注
        "worcode" => $worCode,//人员编号
        "phone" => $phone,
        "address" => $address,
        "postal" => $postal,
        "ecode" => $ecode,
    );//出仓单新增数据
    $addSql[] = insertInvoiceData($invAddData);
}
$addInvoiceData = array();
foreach ($ownData as $orderCode => $ownDatum) {
    $invCode = getOwnInvoiceCode($orderCode);
    if (empty($invCode)) {
        echo $orderCode . "此订单无出仓单";
        exit();
    }
    foreach ($ownDatum as $goodsCode => $item) {
        $skuCode = $item['code'];
        $skuCount = $item['count'];
        $skuInit = $item['init'];
        $igoData = getIgoData($invCode, $skuCode);
        if (empty($igoData)) {
            if ($skuCount != $skuInit) {
                $addInvoiceData[$invCode][] = array(
                    "skuCode" => $skuCode,
                    "skuCount" => $skuInit,
                );
            } else {
                $addInvoiceData[$invCode][] = array(
                    "skuCode" => $skuCode,
                    "skuCount" => $skuCount,
                );
            }
        }

    }
}
echo json_encode($addInvoiceData) . PHP_EOL;
//exit();
$addRecData = array();
$addInvData = array();
foreach ($supData as $otCode => $supDatum) {
    $recCode = getSupReceiptCode($otCode);
    if (empty($recCode)) {
        echo $orderCode . "此订单无入仓单";
        exit();
    }
    foreach ($supDatum as $orderCode => $skuData) {
        $invCode = getSupInvoiceCode($orderCode);
        if (empty($invCode)) {
            echo $orderCode . "此订单无直采出仓单";
            exit();
        }
        foreach ($skuData as $goodsCode => $skuDatum) {
            $skuCode = $skuDatum['code'];
            $skuCount = $skuDatum['count'];
            $skuInit = $skuDatum['init'];
            $supCode = $skuDatum['supCode'];
            $igoData = getIgoData($invCode, $skuCode);
            if (empty($igoData)) {
                $addRecData[$recCode][$skuCode][$supCode] = bcadd($addRecData[$recCode][$skuCode][$supCode], $skuInit, 2);
                $addInvData[$invCode][] = array(
                    "skuCode" => $skuCode,
                    "skuCount" => $skuInit,
                    "recCode" => $recCode
                );
            }
        }
    }
}
echo json_encode($addRecData) . PHP_EOL;
echo json_encode($addInvData) . PHP_EOL;
exit();
$addGbGsSql = array();
$uptGbSql = array();
foreach ($addRecData as $recCode => $addRecDatum) {
    foreach ($addRecDatum as $skuCode => $skuSupCount) {
        foreach ($skuSupCount as $supCode => $skuCount) {
            $issetRecSku = issetRecSkuData($recCode, $skuCode, $supCode);
            $skuData = getSkuData($skuCode);
            $spuCount = $skuData['spu_count'];
            $count = bcmul($spuCount, $skuCount, 2);
            $bprice = $skuData['spu_bprice'];
            $spuCode = $skuData['spu_code'];
            $sprice = $skuData['spu_sprice'];
            $pprice = $skuData['profit_price'];
            $percent = 0;
            if (!empty($issetRecSku)) {
                $gbCode = $issetRecSku['gb_code'];
                $uptGbSql[] = update_goodsbatch_sql($gbCode, $skuCount, $count);
                $uptGbSql[] = update_goodstored_sql($gbCode, $skuCount, $count);
            } else {
                $status = 3;
                $gbCode = venus_unique_code("GB");
                $addGbData = array(
                    "code" => $gbCode,
                    "status" => $status,
                    "count" => $count,
                    "bprice" => $bprice,
                    "spucode" => $spuCode,
                    "supcode" => $supCode,
                    "skucode" => $skuCode,
                    "skucount" => $skuCount,
                    "reccode" => $recCode,
                );
                $addGbGsSql[] = addGoodsbatchData($addGbData);
                $gsCode = venus_unique_code("GW");
                $addGsData = array(
                    "code" => $gsCode,
                    "init" => $count,
                    "count" => 0,
                    "bprice" => $bprice,
                    "gbcode" => $gbCode,
                    "spucode" => $spuCode,
                    "skucode" => $skuCode,
                    "skuinit" => $skuCount,
                    "skucount" => 0
                );
                $addGbGsSql[] = addGsData($addGsData);
            }
            $goodsCode = getGoodsCode($skuCode);
            $uptGbSql[] = update_goods_sql($skuCode, $skuCount, $count);
        }

    }
}
$addIgoIgsSql = array();
foreach ($addInvData as $invCode => $addInvDatum) {

    foreach ($addInvDatum as $igoData) {
        $skuCode = $igoData['skuCode'];
        $recCode = $igoData['recCode'];
        $skuCount = $igoData['skuCount'];
        $skuData = getSkuData($skuCode);
        $spuCount = $skuData['spu_count'];
        $count = bcmul($spuCount, $skuCount, 2);
        $bprice = $skuData['spu_bprice'];
        $spuCode = $skuData['spu_code'];
        $sprice = $skuData['spu_sprice'];
        $pprice = $skuData['profit_price'];
        $percent = 0;
        $igoCode = venus_unique_code("GO");
        $addIgoData = array(
            "code" => $igoCode,
            "count" => $count,
            "spucode" => $spuCode,
            "sprice" => $sprice,
            "pprice" => $pprice,
            "percent" => $percent,
            "goodscode" => $goodsCode,
            "skucode" => $skuCode,
            "skucount" => $skuCount,
            "invcode" => $invCode,
        );
        $addIgoIgsSql[] = addIgoData($addIgoData);
        $gsCode = getGsCode($recCode, $skuCode);
        $igsCode = venus_unique_code("GS");
        $addIgsData = array(
            "code" => $igsCode,
            "count" => $count,
            "bprice" => $bprice,
            "spucode" => $spuCode,
            "gscode" => $gsCode,
            "igocode" => $igoCode,
            "skucode" => $skuCode,
            "skucount" => $skuCount,
            "invcode" => $invCode,
        );
        $addIgoIgsSql[] = addIgsData($addIgsData);
    }

}

file_put_contents("./tool/virtual/addRecData.json", json_encode($addRecData));
file_put_contents("./tool/virtual/addInvData.json", json_encode($addInvData));
//echo json_encode($addRecData) . PHP_EOL;
//echo json_encode($addInvData) . PHP_EOL;
//file_put_contents("./tool/virtual/create_invoice.sql",implode(";".PHP_EOL,$addSql));
//file_put_contents("./tool/virtual/addGbGsSql.sql", implode(";" . PHP_EOL, $addGbGsSql));
//file_put_contents("./tool/virtual/uptGbSql.sql", implode(";" . PHP_EOL, $uptGbSql));
file_put_contents("./tool/virtual/addIgoIgsSql.sql", implode(";" . PHP_EOL, $addIgoIgsSql));
exit();
function getOrderStatus($orderCode)
{
    return M("order")->where(array("order_code" => $orderCode))->getField("order_status");
}

function getOrderData($orderCode)
{
    return M("order")->alias("o")->field('*,o.user_code,o.war_code')
        ->join("LEFT JOIN wms_user user ON user.user_code = o.user_code")
        ->join("LEFT JOIN zwdb_iwms.wms_warehouse war ON war.war_code = o.war_code")
        ->where(array("order_code" => $orderCode))
        ->fetchSql(false)
        ->find();
}


function getOrderGoodsData()
{
    return M("ordergoods")->where(array("goods_code" => array("GT", "G40411003035733")))->limit(1000000)->select();
}

function getOwnInvoiceCode($orderCode)
{//小程序单(直采)/小程序单(自营)
    return M("invoice")->where(array("inv_ecode" => $orderCode, "inv_mark" => "小程序单(自营)"))->getField("inv_code");
}

function getSupReceiptCode($otCode)
{
    return M("receipt")->where(array("rec_mark" => $otCode))->getField("rec_code");
}

function getSupInvoiceCode($orderCode)
{//小程序单(直采)/小程序单(自营)
    return M("invoice")->where(array("inv_ecode" => $orderCode, "inv_mark" => "小程序单(直采)"))->getField("inv_code");
}

function getIgoData($invCode, $skuCode)
{
    return M("igoods")->where(array("inv_code" => $invCode, "sku_code" => $skuCode))->find();
}

function insertInvoiceData($item)
{
    $code = venus_unique_code("IN");
    $data = array(
        "inv_code" => $code,
        "inv_ctime" => venus_current_datetime(),
        "inv_status" => $item["status"],
        "inv_ecode" => $item["ecode"],
        "inv_receiver" => $item["receiver"],
        "inv_phone" => $item["phone"],
        "inv_address" => $item["address"],
        "inv_postal" => $item["postal"],
        "inv_type" => $item["type"],
        "inv_mark" => $item["mark"],
        "trace_code" => $item["tracecode"],
        "wor_code" => $item["worcode"],
        "war_code" => "WA000001",
        "timestamp" => venus_current_datetime(),
    );
    return M("Invoice")->fetchSql(true)->add($data);
}

function addGoodsbatchData($item)
{
    $data = array(
        "gb_code" => $item["code"],
        "gb_ctime" => venus_current_datetime(),
        "gb_status" => $item["status"],
        "gb_count" => $item["count"],  //spu的数量，该货品的实际数量，比如多少瓶
        "gb_bprice" => $item["bprice"], //spu的采购价格
        "spu_code" => $item["spucode"],//spu编码
        "sup_code" => $item["supcode"],//spu编码
        "sku_code" => $item["skucode"],//sku编码，该商品采购时的规格信息
        "sku_count" => $item["skucount"],//sku的数量，该商品采购时的采购数量，比如多少箱
        "rec_code" => $item["reccode"],//所属入仓单编码
        "war_code" => "WA000001",
    );
    if (isset($item["skuProCount"])) {
        $data['promote_skucount'] = $item["skuProCount"];
    }
    return M("Goodsbatch")->fetchSql(true)->add($data);
}

function addGsData($item)
{

    $data = array(
        "gs_code" => $item["code"],
        "gs_init" => $item["init"],     //初次写入的货品数量，即spu的数量
        "gs_count" => $item["count"],   //当前货品数量，即spu的实际数量
        "gb_bprice" => $item["bprice"], //货品的采购价格，即spu的采购价格
        "gb_code" => $item["gbcode"],   //所属入仓货品批次表激励编号
        "spu_code" => $item["spucode"], //spu编号
        "sku_code" => $item["skucode"], //sku编号，货品采购和上架时的规格数据信息
        "sku_init" => $item["skuinit"],//sku采购数量，即按货品采购时规格的采购数量
        "sku_count" => $item["skucount"],//sku的实际数量
        "war_code" => "WA000001",//所属仓库
    );
    return M("Goodstored")->fetchSql(true)->add($data);
}

function getSkuData($skuCode)
{
    return M("sku")->alias('sku')->field('*,sku.sku_code,spu.spu_code')
        ->join("JOIN wms_spu spu ON spu.spu_code = sku.spu_code")
        ->where(array("sku_code" => $skuCode))->order('spu.spu_code desc')->fetchSql(false)->find();
}

function issetRecSkuData($recCode, $skuCode, $supCode)
{
    return M("goodsbatch")->where(array("rec_code" => $recCode, "sku_code" => $skuCode, "sup_code" => $supCode))->find();
}

function update_goodsbatch_sql($gbCode, $skuCount, $count)
{
    return "UPDATE `wms_goodsbatch` SET `gb_count`=`gb_count`+$count,`sku_count`=`sku_count`+$skuCount WHERE `gb_code`='{$gbCode}'";
}

function update_goodstored_sql($gbCode, $skuCount, $count)
{
    return "UPDATE `wms_goodstored` SET `gs_init`=`gs_init`+$count,`sku_init`=`sku_init`+$skuCount WHERE `gb_code`='{$gbCode}'";
}

function addIgoData($item)
{

    $data = array(
        "igo_code" => $item['code'],
        "igo_count" => $item["count"],  //要出仓的spu数量
        "spu_code" => $item["spucode"], //要出仓的sku编号
        "spu_sprice" => $item["sprice"],//货品的销售价
        "spu_pprice" => $item["pprice"],//货品的利润价
        "spu_percent" => $item["percent"],//货品的利润点
        "goods_code" => $item["goodscode"],//所属库存编号
        "sku_code" => $item["skucode"],//规格上所属的sku编号
        "sku_count" => $item["skucount"],//规格上对应的sku数量
        "inv_code" => $item["invcode"],//所属的出仓单编号
        "war_code" => "WA000001",//所属仓库编号
    );
    return M("Igoods")->fetchSql(true)->add($data);
}

function addIgsData($item)
{

    $data = array(
        "igs_code" => $item['code'],
        "igs_count" => $item["count"],  //不通批次货架货品的出仓货品spu数量
        "igs_bprice" => $item["bprice"], //不通批次货架货品的出仓货品spu采购价格，即成本价
        "igs_ctime" => venus_current_datetime(),//产生时间
        "spu_code" => $item["spucode"],//spu编号
        "gs_code" => $item["gscode"], //所属货品批次货架中货品的货品编号
        "igo_code" => $item["igocode"],//所属出仓货品清单中的货品编号
        "sku_code" => $item["skucode"],//sku编号，货品出仓实际规格数据信息
        "sku_count" => $item["skucount"],//sku数量，即货品出仓按规格计算的货品数量
        "inv_code" => $item["invcode"], //所属出仓编号
        "war_code" => "WA000001",//所属仓库编号
    );
    return M("Igoodsent")->fetchSql(true)->add($data);
}

function getGoodsCode($skuCode)
{
    return M("goods")->where(array("sku_code" => $skuCode))->getField("goods_code");
}

function update_goods_sql($goodsCode, $skuCount, $count)
{
    return "UPDATE `wms_goods` SET `goods_init`=`goods_init`+$count,`sku_init`=`sku_init`+$skuCount WHERE `goods_code`='{$goodsCode}'";
}

function getGsCode($recCode, $skuCode)
{
    $gbCode = M("goodsbatch")->where(array("sku_code" => $skuCode, "rec_code" => $recCode))->getField("gb_code");
    return M("goodstored")->where(array("gb_code" => $gbCode))->getField("gs_code");
}

function update_goodsbatch_count_sql($gbCode, $skuCount, $count)
{
    return "UPDATE `wms_goodsbatch` SET `gb_count`=$count,`sku_count`=$skuCount where `gb_code`='{$gbCode}'";
}
