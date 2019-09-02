<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/30
 * Time: 10:19
 */
ini_set('memory_limit', '2096M');
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
$time = venus_script_begin("开始检测库存");

use Wms\Dao\GoodsDao;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\IgoodsDao;

$wmsWarCode = "WA000001";
$errorGoodsArr = array();
$fileName = "goods_" . (date('Ym01', time())) . ".json";
$filePath = C("FILE_SAVE_PATH") . C("FILE_TYPE_NAME.COMMON") . "/" . $fileName;
if (file_exists($filePath)) {
    $fileData = json_decode(file_get_contents($filePath),true);
    $stime = $fileData[$wmsWarCode]['time'];
    $returnData = getReturnData($stime);
    if (!empty($returnData)) {
        foreach ($returnData as $returnDatum) {
            if ($returnDatum['supplier_code'] == "SU00000000000001") {
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsCount'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsCount'] - $returnDatum['actual_count'] * $returnDatum['spu_count'];
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuCount'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuCount'] - $returnDatum['actual_count'];
            } else {
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsInit'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsInit'] - $returnDatum['actual_count'] * $returnDatum['spu_count'];
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsCount'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['goodsCount'] - $returnDatum['actual_count'] * $returnDatum['spu_count'];
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuInit'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuInit'] - $returnDatum['actual_count'];
                $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuCount'] = $fileData[$wmsWarCode][$returnDatum["sku_code"]]['skuCount'] - $returnDatum['actual_count'];
            }
        }
    }

    //获取当前库存表数据
    $goodsData = getGoodsData();
    $goodsList=array();
    foreach ($goodsData as $goodsDatum) {
        $goodsList[$goodsDatum['sku_code']]=$goodsDatum;
    }
    //获取goodsbatch表数据
    $gbData = getGbData($stime);
    $gbCodeArr = array_column($gbData, "gb_code");
    //获取当月goodstored数据
    $gsData = getGsData($gbCodeArr);
    //获取goodstored按照goodsbatchcode整理的数据
    $gsToGbCodeData = array();
    $gsToSkuCodeData = array();
    $spuCodeArr = array();
    $skuCodeArr = array();
    foreach ($gsData as $gsDatum) {
        if (!isset($gsToGbCodeData[$gsDatum['gb_code']])) {
            $gsToGbCodeData[$gsDatum['gb_code']] = array(
                "init" => $gsDatum['gs_init'],
                "count" => $gsDatum['gs_count'],
                "skuInit" => $gsDatum['sku_init'],
                "skuCount" => $gsDatum['sku_count'],
            );
        } else {
            $gsToGbCodeData[$gsDatum['gb_code']]['init'] += $gsDatum['gs_init'];
            $gsToGbCodeData[$gsDatum['gb_code']]['skuInit'] += $gsDatum['sku_init'];
        }

        if (!in_array($gsDatum['sku_code'], $skuCodeArr)) {
            $skuCodeArr[] = $gsDatum['sku_code'];
        }
    }
    //对比goodstored按照goodsbatchcode整理的数据和goodsbatch表数据
    foreach ($gbData as $gbDatum) {
        if ($gbDatum['gb_count'] != $gsToGbCodeData[$gbDatum['gb_code']]['init'] || $gbDatum['sku_count'] != $gsToGbCodeData[$gbDatum['gb_code']]['skuInit']) {
            if (!in_array("gb_gs", $errorGoodsArr[$gbDatum['sku_code']])) {
                $errorGoodsArr[$gbDatum['sku_code']][$gbDatum['gb_code']] = "gb_gs";
            }
        }
        if (!array_key_exists($gbDatum['sku_code'], $gsToSkuCodeData)) {
            $gsToSkuCodeData[$gbDatum['sku_code']] = array(
                "init" => $gbDatum['gs_init'],
                "count" => $gbDatum['gs_count'],
                "skuInit" => $gbDatum['sku_init'],
                "skuCount" => $gbDatum['sku_count'],
            );
        } else {
            $gsToGbCodeData[$gbDatum['sku_code']]['init'] += $gbDatum['gs_init'];
            $gsToGbCodeData[$gbDatum['sku_code']]['count'] += $gbDatum['gs_count'];
            $gsToGbCodeData[$gbDatum['sku_code']]['skuInit'] += $gbDatum['sku_init'];
            $gsToGbCodeData[$gbDatum['sku_code']]['skuCount'] += $gbDatum['sku_count'];
        }
        $gsToGbCodeData[$gbDatum['sku_code']]['sentCount'] = bcsub($gsToGbCodeData[$gbDatum['spu_code']]['init'], $gsToGbCodeData[$gbDatum['spu_code']]['count'], 4);
        $gsToGbCodeData[$gbDatum['sku_code']]['sentSkuCount'] = bcsub($gsToGbCodeData[$gbDatum['spu_code']]['skuInit'], $gsToGbCodeData[$gbDatum['spu_code']]['skuCount'], 4);

        if (!in_array($gsDatum['sku_code'], $skuCodeArr)) {
            $skuCodeArr[] = $gsDatum['sku_code'];
        }
    }
    $invData = getInvData($stime);
    $invCodeArr = array_column($invData, "inv_code");
    $igoData = getIgoData($invCodeArr);
    $igsData = getIgsData($invCodeArr);
    $igsToIgoData = array();
    foreach ($igsData as $igsDatum) {
        if (!array_key_exists($igsDatum['igo_code'], $igsToIgoData)) {
            $igsToIgoData[$igsDatum['igo_code']] = array(
                "count" => $igsDatum['igo_count'],
                "skuCount" => $igsDatum['sku_count'],
            );
        } else {
            $igsToIgoData[$igsDatum['igo_code']]['count'] += $igsDatum['igo_count'];
            $igsToIgoData[$igsDatum['igo_code']]['skuCount'] += $igsDatum['sku_count'];

        }
        if (!in_array($igsDatum['sku_code'], $spuCodeArr)) {
            $skuCodeArr[] = $igsDatum['sku_code'];
        }
    }
    $igoToSkuCodeData = array();
    foreach ($igoData as $igoDatum) {
        if ($igoDatum['igo_count'] != $igsToIgoData[$igoDatum['igo_code']]['count'] || $igoDatum['sku_count'] != $igsToIgoData[$igoDatum['igo_code']]['skuCount']) {
            if (!in_array("igs_igo", $errorGoodsArr[$gbDatum['sku_code']])) {
                $errorGoodsArr[$igoDatum['sku_code']][$igoDatum['igo_code']] = "igs_igo";
            }
        }
        if (!array_key_exists($igoDatum['sku_code'], $igoToSkuCodeData)) {
            $igoToSkuCodeData[$igoDatum['sku_code']] = array(
                "count" => $igoDatum['igo_count'],
                "skuCount" => $igoDatum['sku_count'],
            );
        } else {
            $igoToSkuCodeData[$igoDatum['sku_code']]['count'] += $igoDatum['igo_count'];
            $igoToSkuCodeData[$igoDatum['sku_code']]['skuCount'] += $igoDatum['sku_count'];
        }
        if (!in_array($igoDatum['sku_code'], $skuCodeArr)) {
            $skuCodeArr[] = $igoDatum['sku_code'];
        }
    }
    foreach ($skuCodeArr as $skuCode) {
        if (array_key_exists($skuCode, $igoToSkuCodeData)) {
            $diffCount = $gsToGbCodeData[$skuCode]['sentCount'] - $igoToSkuCodeData[$skuCode]['count'];
            $diffSkuCount = $gsToGbCodeData[$skuCode]['sentSkuCount'] - $igoToSkuCodeData[$skuCode]['skuCount'];
        } else {
            $diffCount = $gsToGbCodeData[$skuCode]['sentCount'];
            $diffSkuCount = $gsToGbCodeData[$skuCode]['sentSkuCount'];
        }
        if ($diffCount != 0 || $diffSkuCount != 0) {
            if (!in_array("gs_igo", $errorGoodsArr[$skuCode])) {
                $errorGoodsArr[$skuCode] = "gs_igo";
            }
        }
        if(
            $gsToGbCodeData[$skuCode]['skuCount']+$fileData[$wmsWarCode][$skuCode]['skuCount']!=$goodsList[$skuCode]['sku_count']||
            $gsToGbCodeData[$skuCode]['count']+$fileData[$wmsWarCode][$skuCode]['goodsCount']!=$goodsList[$skuCode]['goods_count']||
            $gsToGbCodeData[$skuCode]['skuInit']+$fileData[$wmsWarCode][$skuCode]['skuInit']!=$goodsList[$skuCode]['sku_init']||
            $gsToGbCodeData[$skuCode]['init']+$fileData[$wmsWarCode][$skuCode]['goodsInit']!=$goodsList[$skuCode]['goods_init']
        ){
            if (!in_array("goods_gs", $errorGoodsArr[$skuCode])) {
                $errorGoodsArr[$skuCode] = "goods_gs";
            }
        }
    }

} else {
    echo "file empty" . PHP_EOL;
}
echo json_encode($errorGoodsArr) . PHP_EOL;
echo "1";
exit();
if (!empty($errorGoodsArr)) {
    $title = "[NOTICE]WMS库存错误货品提醒(测试站)";
//    $title = "[NOTICE]WMS库存错误货品提醒(正式站)";
    if (!empty($errorGoodsArr)) {
        $spuArr = array_keys($errorGoodsArr);
        if (!empty($spuArr)) {
            $content = "<div style='border: 1px solid #000000'>以下货品数据有问题：<br>";
            foreach ($spuArr as $spuCode) {
                $types = join(",<br>", $errorGoodsArr[$spuCode]);
                $content .= $spuCode . "发生以下异常:" . "{$types}" . "<br>";
            }
            $content .= "</div>";
            if (sendMailer($title, $content)) {
                echo "(发送成功)";
            } else {
                echo "(发送失败)";
            }
        }

    }
}


/*************************************************************************************************************/
/*检测当日缺货数据并邮件通报*/
$oosDate = date("Y-m-d", strtotime(" - 1 day"));
$oosFilePath = C("FILE_SAVE_PATH") . C("FILE_TYPE_NAME.WAREHOUSE_OUT_OF_STOCK") . "/{$oosDate}.log";
$oosFileData = file_get_contents($oosFilePath);
if (!empty($oosFileData)) {
    sendMailer("[NOTICE]昨日缺货提醒_{$oosDate}(测试站)",
        "<div style = 'border:1px solid #000000;background-color: #cccccc;font-size: 12px'> $oosFileData</div> ");
}
/*************************************************************************************************************/


venus_script_finish($time);
exit();

function getGoodsData()
{
    $count = M("goods")->count();
    return M("goods")->limit(0, $count)->select();
}

function getGbData($stime)
{
    $count = M("goodsbatch")->where(array("gb_ctime" => array("EGT", $stime)))->count();
    return M("goodsbatch")->where(array("gb_ctime" => array("EGT", $stime), "gb_status" => array("neq", 1)))->limit(0, $count)->select();
}

function getGsData($gbCodeArr)
{
    $count = M("goodstored")
        ->where(array("gb_code" => array("in", $gbCodeArr)))
        ->count();

    return M("goodstored")
        ->field("gs_init,gs_count,sku_init,sku_count,gb_code")
        ->where(array("gb_code" => array("in", $gbCodeArr)))
        ->limit(0, $count)
        ->select();
}

function getInvData($stime)
{
    $count = M("invoice")->where(array("inv_ctime" => array("EGT", $stime)))->count();
    return M("invoice")->where(array("inv_ctime" => array("EGT", $stime), "inv_status" => 5))->limit(0, $count)->select();

}

function getIgoData($invCodeArr)
{
    $count = M("igoods")
        ->where(array("inv_code" => array("in", $invCodeArr)))
        ->count();
    return M("igoods")
        ->where(array("inv_code" => array("in", $invCodeArr)))
        ->limit(0, $count)
        ->select();

}

function getIgsData($invCodeArr)
{
    $count = M("igoodsent")
        ->where(array("inv_code" => array("in", $invCodeArr)))
        ->count();
    return M("igoodsent")
        ->where(array("inv_code" => array("in", $invCodeArr)))
        ->limit(0, $count)
        ->select();
}

function getReturnData($time)
{
    $count = M("ordergoodsreturn")->where(array(
        "ogr_node" => 1,
        "ogr_status" => 2,
        "apply_time" => array("LT", $time),
        "timestamp" => array("EGT", "$time")

    ))->count();

    return M("ordergoodsreturn")
        ->where(
            array(
                "ogr_node" => 1,
                "ogr_status" => 2,
                "apply_time" => array("LT", $time),
                "timestamp" => array("EGT", "$time 00:00:00")

            ))
        ->limit(0, $count)->select();

}
