<?php
/**
 * Created by PhpStorm.
 * User: li176
 * Date: 2019/1/1
 * Time: 21:31
 */
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
$time = venus_script_begin("开始检测出仓单");

use Wms\Dao\GoodsDao;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\WarehouseDao;

$warModel = WarehouseDao::getInstance("WA000001");
$goodsModel = GoodsDao::getInstance("WA000001");
$goodstoredModel = GoodstoredDao::getInstance("WA000001");
$goodsbatchModel = GoodsbatchDao::getInstance("WA000001");
$invModel = \Wms\Dao\InvoiceDao::getInstance("WA000001");
$iGoodsModel = IgoodsDao::getInstance("WA000001");
$iGoodsentModel = IgoodsentDao::getInstance("WA000001");
$orderModel = \Wms\Dao\OrderDao::getInstance("WA000001");
$ordergoodsModel = \Wms\Dao\OrdergoodsDao::getInstance("WA000001");
$ordertaskModel = \Wms\Dao\OrdertaskDao::getInstance("WA000001");
$receiptModel = \Wms\Dao\ReceiptDao::getInstance("WA000001");
$spuModel = \Wms\Dao\SpuDao::getInstance("WA000001");
$errorGoodsArr = array();
$errorSkuArr = array();
$errorOtCodeArr = array();
$otClause = array(
    "sctime" => "2018-12-29 00:20:00",
    "ownstatus" => 2
);
$otData = $ordertaskModel->queryListByCondition($otClause, 0, 100000);
$sentNumOgData = array();
$errGoodsDataArr = array();
foreach ($otData as $otDatum) {
    $otCode = $otDatum['ot_code'];
    $orderData = queryListByOrderTaskCode($otCode);
    foreach ($orderData as $orderDatum) {
        $oCode = $orderDatum['order_code'];
        $ogData = $ordergoodsModel->queryListByOrderCode($oCode, 0, 100000);
        $goodsData = array();
        foreach ($ogData as $ogDatum) {
            if ($ogDatum['supplier_code'] == "SU00000000000001" && $ogDatum['goods_count'] > 0) {
                $goodsData[$ogDatum['sku_code']] = $ogDatum;
                $goodsData[$ogDatum['sku_code']]['goods_code'] = $ogDatum['goods_code'];
                $goodsData[$ogDatum['sku_code']]['skuCount'] = $ogDatum['sku_count'];

            }
        }
        $invData = $invModel->queryByEcode($oCode);
        $sentGoodsData = array();
        foreach ($invData as $invDatum) {
            $invCode = $invDatum['inv_code'];
            $igoodsData = $iGoodsModel->queryListByInvCode($invCode, 0, 100000);
            foreach ($igoodsData as $igoodsDatum) {
                if ($igoodsDatum['sup_code'] == "SU00000000000001" && $igoodsDatum['igo_count'] > 0) {
                    $skuCode = $igoodsDatum['sku_code'];
                    $sentGoodsData[$skuCode]['igoCode'] = $igoodsDatum['igo_code'];
                    $sentGoodsData[$skuCode]['skuCount'] += $igoodsDatum['sku_count'];
                    $igoCode = $igoodsDatum['igo_code'];
                    $igsData = $iGoodsentModel->queryListByCondition(array("igocode" => $igoCode), 0, 100000);
                    if (empty($igsData)) {
                        $errorGoodsArr['emptyIgo'][$skuCode][$igoCode] = array("igocount" => $igoodsDatum['igo_count']);
                        if (!in_array($skuCode, $errorSkuArr)) {
                            $errorSkuArr[] = $skuCode;
                        }
                        if (!in_array($otCode, $errorOtCodeArr)) {
                            $errorOtCodeArr[] = $otCode;
                        }
                    } else {
                        $count = 0;
                        foreach ($igsData as $igsDatum) {
                            $count += $igsDatum['igs_count'];
                        }
                        $errGoodsDataArr[$skuCode] += $count;
                        if ($count != $igoodsDatum['igo_count']) {
                            $errorGoodsArr['igo'][$igoCode] = array("igocount" => $igoodsDatum['igo_count'], "igscount" => $count);
                            if (!in_array($skuCode, $errorSkuArr)) {
                                $errorSkuArr[] = $skuCode;
                            }
                            if (!in_array($otCode, $errorOtCodeArr)) {
                                $errorOtCodeArr[] = $otCode;
                            }
                        }
                    }
                }


            }
            foreach ($goodsData as $skuCode => $goodsDatum) {
                if ($goodsDatum['skuCount'] != $sentGoodsData[$skuCode]['skuCount']) {
                    $errorGoodsArr['og'][] = array("ogCode" => $goodsDatum['goods_code'],
                        "ogSkuCount" => $goodsDatum['skuCount'],
                        "igoCode" => $sentGoodsData[$skuCode]['igoCode'],
                        "igoSkuCount" => $sentGoodsData[$skuCode]['skuCount']);
                    if (!in_array($skuCode, $errorSkuArr)) {
                        $errorSkuArr[] = $skuCode;
                    }
                    if (!in_array($otCode, $errorOtCodeArr)) {
                        $errorOtCodeArr[] = $otCode;
                    }
                }


//                if(array_key_exists($skuCode,$sentNumOgData[$skuCode])){
//                    $sentNumOgData[$skuCode]=$goodsDatum['skuCount'];
//                }else{
//                    $sentNumOgData[$skuCode]+=$goodsDatum['skuCount'];
//                }
//                $gsData=$goodsModel->queryBySkuCode($skuCode);
//                if($gsData['sku_count']<$sentNumOgData[$skuCode]){
//                    $updateByOgcode = $ordergoodsModel->updateSupCodeByCode($goodsDatum['goods_code'], "SU00000000000002");
//                    $invInfo=$invModel->queryByInvEcodeAndSkuCode($goodsDatum['order_code'],$skuCode);
//                    if(empty($invCode)){
//                        $invInfo=$invModel->queryListByCondition(array("ecode"=>$goodsDatum['order_code'],"mark"=>":own"));
//                        $invCode=$invInfo[0]['inv_code'];
//                    }else{
//                        $invCode=$invInfo['inv_code'];
//                    }
//                    $igoodsAddData = array(
//                        "count" => $goodsDatum['count'],//spu总数量
//                        "spucode" => $goodsDatum['spCode'],//spu编号
//                        "sprice" => $goodsDatum,//spu当前销售价
//                        "pprice" => $goodsDatum,//spu当前利润
//                        "goodscode" => $gsData['goods_code'],//库存编号
//                        "percent" => $goodsDatum,//spu当前利润率
//                        "skucode" => $goodsDatum['skCode'],//sku编号
//                        "skucount" => $goodsDatum['skCount'],//sku数量
//                        "invcode" => $invCode,//所属出仓单单号
//                    );
//                }
            }
        }

    }
}
$errorGoodsDataArr = array();
$goodsDataArr = M("Goods")->select();
foreach ($goodsDataArr as $goodsData) {
    $skuCode = $goodsData['sku_code'];
    $countIgs = M("igoodsent")->query("select sum(sku_count) sku_count from `wms_igoodsent` where sku_code='{$skuCode}' group by sku_code");
    $countIgsSku = $countIgs[0]['sku_count'] - 0;
    if (!empty($countIgsSku) || $countIgsSku != null) {
        if ($countIgsSku != bcsub($goodsData['sku_init'], $goodsData['sku_count'], 2)) {
            $errorGoodsDataArr[] = $skuCode;
            $goodsDataList[$skuCode]['init'] = $goodsData['sku_init'];
            $goodsDataList[$skuCode]['count'] = $goodsData['sku_count'];
            $goodsDataList[$skuCode]['igscount'] = $countIgsSku;
        }
    } else {
        if ($goodsData['sku_init'] != $goodsData['sku_count']) {
            $errorGoodsDataArr[] = $skuCode;
            $goodsDataList[$skuCode]['init'] = $goodsData['sku_init'];
            $goodsDataList[$skuCode]['count'] = $goodsData['sku_count'];
            $goodsDataList[$skuCode]['igscount'] = $countIgsSku;
        }
    }
}
//echo count($errorGoodsDataArr);
//echo json_encode($errorGoodsDataArr);
//echo json_encode($errorGoodsDataArr);
echo json_encode($goodsDataList);
exit();
//echo count($errorGoodsArr['og']).PHP_EOL;
echo count($errorGoodsArr['og']) . PHP_EOL;
echo count($errorGoodsArr['emptyIgo']);
//echo json_encode($errorGoodsArr['og']);
//echo json_encode($errorSkuArr);
exit();
if (!empty($errorGoodsArr)) {

//    echo json_encode($errorGoodsArr['emptyIgo']);
    echo json_encode($errorGoodsArr['igo']);
    echo json_encode($errorSkuArr);
//    exit();
    //ordergoods数据没有supcode


    //有igo没有igs
    if (count($errorGoodsArr['emptyIgo']) != 0) {
        $data = $errorGoodsArr['emptyIgo'];
        $a = 0;
        $understockArr = array();
        $sentCountGoodsData = array();
        venus_db_starttrans();
        foreach ($data as $igoCode => $datum) {
            $igoData = $iGoodsModel->queryByCode($igoCode);
            if ($igoData['sup_code'] == "SU00000000000001") {
                $skuCode = $igoData['sku_code'];
                $invCode = $igoData['inv_code'];
                $goodsData = $goodsModel->queryBySkuCode($skuCode);
                if ($goodsData['goods_count'] >= $datum['igocount']) {
                    $goodstoredList = queryListBySkuCode($skuCode, 0, 100000);//指定商品的库存货品批次货位列表数据
                    $igoodsentData = branch_goodstored($goodstoredList, $datum['igocount'], $igoCode, $igoData['spu_code'], $invCode);//调用出仓批次方法
                    foreach ($igoodsentData as $igoodsentDatum) {
                        if (is_array($igoodsentDatum)) {
                            $goodsoredCount = $igoodsentDatum['remaining'];
                            $uptGsSpuCount = $goodstoredModel->updateByCode($igoodsentDatum['gscode'], $goodsoredCount);//修改发货库存批次剩余数量
                            $gsSkuCount = $goodstoredModel->queryByCode($igoodsentDatum['gscode'])['sku_count'];
                            if ($gsSkuCount < $igoodsentDatum['skucount']) {
                                $spName = $goodsData['spu_name'];
                                if (!array_key_exists($igoData['sku_code'], $understockArr)) {
                                    $understockArr[$igoData['sku_code']] = bcsub($igoData['sku_count'], $gsSkuCount, 2);
                                } else {
                                    $understockArr[$igoData['sku_code']] = bcadd($understockArr[$igoData['sku_code']], $igoData['sku_count']);
                                }
                            } else {
                                $uptGsSkuCount = $goodstoredModel->updateSkuCountByCode($igoodsentDatum['gscode'], $gsSkuCount - $igoodsentDatum['skucount']);//减少发货库存批次sku数量
                                $igoodsentCode = $iGoodsentModel->insert($igoodsentDatum);//创建发货批次
                                if (!$uptGsSpuCount || !$uptGsSkuCount) {
                                    $spName = $spuModel->queryByCode($igoodsDatum['spu_code'])['spu_name'];
                                    venus_db_rollback();
                                    venus_throw_exception(2, "修改" . $spName . "库存批次失败");
                                    return false;
                                }
                                if (!$igoodsentCode) {
                                    venus_db_rollback();
                                    venus_throw_exception(2, "创建发货批次失败");
                                    return false;
                                }
                            }
                            $newCountGoods = $goodsData['goods_count'] - $igoData['igo_count'];//新库存
                            $newSkuCountGoods = $goodsData['sku_count'] - $igoData['sku_count'];
                            $uptGoods = $goodsModel->updateCountByCode($goodsData['goods_code'], $goodsData['goods_count'], $newCountGoods, $newSkuCountGoods);//修改库存
                            if (!$uptGoods) {
                                venus_db_rollback();
                                venus_throw_exception(2, "修改库存失败");
                                return false;
                            }
                        }

                    }
                } else {
                    if (!array_key_exists($goodsData['sku_code'], $understockArr)) {
                        $understockArr[$goodsData['sku_code']] = $igoData['sku_count'];
                    } else {
                        $understockArr[$goodsData['sku_code']] = bcadd($understockArr[$goodsData['sku_code']], $igoData['sku_count']);
                    }
//                    $updateByOgcode = $ordergoodsModel->updateSupCodeByCode($goodsDatum['goods_code'], "SU00000000000002");
//                    $igoodsAddData = $igoData;
//                    $igoodsAddData['count'] = $igoData['igo_count'] - $goodsData['goods_count'];
//                    $igoodsAddData['skucount'] = $igoData['sku_count'] - $goodsData['sku_count'];
//                    $insertIgoRes = $iGoodsModel->insert($igoodsAddData);
                }

            }
        }
    }
}
//echo json_encode($understockArr) . PHP_EOL;
exit();
venus_db_commit();
venus_script_finish($time);

/**
 * @param $goodstored array 库存批次货位数据
 * @param $igoCount string 需要发出的货品数量
 * @param $igoCode string 需要发出的igoods编号
 * @param $spuCode string 需要发出的spu编号
 * @param $invcode string 出仓单编号
 * @return mixed
 */
function branch_goodstored($goodstored, $igoCount, $igoCode, $spuCode, $invcode)
{
    $sentNum = 0;
    $igoodsentAddData = array();
    foreach ($goodstored as $item) {
        $skuCode = $item['sku_code'];
        if ($item['gs_count'] > 0) {
            if ($igoCount - $sentNum - $item['gs_count'] >= 0) {
                $sentNum += $item['gs_count'];
                $igoodsentAddData[] = array(
                    "count" => $item['gs_count'],
                    "bprice" => $item['gb_bprice'],
                    "spucode" => $spuCode,
                    "gscode" => $item['gs_code'],
                    "igocode" => $igoCode,
                    "skucode" => $skuCode,
                    "skucount" => floatval($item['sku_count']),
                    "invcode" => $invcode,
                    "remaining" => 0
                );
            } else {
                if ($igoCount - $sentNum != 0) {
                    $gscount = $item['gs_count'] - ($igoCount - $sentNum);
                    $igoodsentCount = $igoCount - $sentNum;
                    $sentNum += $igoodsentCount;
                    $igoodsentAddData[] = array(
                        "count" => $igoodsentCount,
                        "bprice" => $item['gb_bprice'],
                        "spucode" => $spuCode,
                        "gscode" => $item['gs_code'],
                        "igocode" => $igoCode,
                        "skucode" => $skuCode,
                        "skucount" => floatval($igoodsentCount / $item['spu_count']),
                        "invcode" => $invcode,
                        "remaining" => $gscount
                    );
                    break;
                }

            }
        } else {
            continue;
        }
    }
    $igoodsentAddData["sentNum"] += $sentNum;
    return $igoodsentAddData;
}

function queryListBySkuCode($code, $page = 0, $count = 100)
{
    return M("Goodstored")->alias('gs')->field('*,spu.spu_code,sku.sku_code,gs.sku_count')
        ->join("JOIN wms_sku sku ON sku.sku_code = gs.sku_code AND gs.sku_code = '{$code}'")
        ->join("JOIN wms_spu spu ON spu.spu_code = gs.spu_code")
        ->order('gs.gs_code asc')->limit("{$page},{$count}")->fetchSql(false)->select();
}

function queryListByOrderTaskCode($code)
{
    $condition = array("ot_code" => $code);
    return M("Order")->alias("o")->field('*,o.user_code,o.war_code')
        ->join("LEFT JOIN wms_user user ON user.user_code = o.user_code")
        ->join("LEFT JOIN wms_warehouse war ON war.war_code = o.war_code")
        ->where($condition)->order("order_code")
        ->limit(10000)->fetchSql(false)->select();
}
