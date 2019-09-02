<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/4/10
 * Time: 14:04
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

$gbCode = "GB40304142912967";
$skuCount = 13;
$sql = array();

$gbData = getGbDataByCode($gbCode);
$gbCount = $gbData['gb_count'];
$gbSkuCount = $gbData['sku_count'];

$gsData = getGoodstoredDataByGbCode($gbCode);
$gsSkuInit = $gsData['sku_init'];
$gsSkuCount = $gsData['sku_count'];
$gsCode = $gsData['gs_code'];
$skuCode = $gsData['sku_code'];
$skuCountLess = bcsub($gbSkuCount, $skuCount, 2);

if ($skuCountLess>$gsSkuCount) {
    $title="此货品减少的数量小于剩余库存数量";
    echo "#################  {$title}  #################  " . PHP_EOL;
    exit();
}

$skuData = \Wms\Dao\SkuDao::getInstance("WA000001")->queryByCode($skuCode);
$spuCount = $skuData['spu_count'];

$updateGbCount = floatval(bcmul($spuCount, $skuCount, 4));

$updateGsSkuCount = bcsub($gsSkuCount, $skuCountLess, 2);
$updateGsCount = floatval(bcmul($updateGsSkuCount, $spuCount, 4));
$updateGsSkuInit = bcsub($gsSkuInit, $skuCountLess, 2);
$updateGsInit = floatval(bcmul($updateGsSkuInit, $spuCount, 4));
if ($skuCount == 0) {
    $sql[] = deleteGoodsbatchByCode($gbCode);
} else {
    $sql[] = updateGoodsbatchCountAndSkucountByCode($gbCode, $updateGbCount, $skuCount);
}

if ($updateGsSkuInit == 0) {
    $sql[] = deleteGoodstoredByCode($gsCode);
} else {
    $sql[] = updateGoodstoredInitAndCountAndSkuinitAndSkucountByCode($gsCode, $updateGsInit, $updateGsCount, $updateGsSkuInit, $updateGsSkuCount);
}
file_put_contents("./tool/receipt/goods_receipt.sql",implode(";".PHP_EOL,$sql));
exit();
function getGbDataByCode($gbCode)
{
    return M("Goodsbatch")->where(array("gb_code" => $gbCode))->find();
}

function getGoodstoredDataByGbCode($gbCode)
{
    return M("Goodstored")->where(array("gb_code" => $gbCode))->find();
}


function updateGoodsbatchCountAndSkucountByCode($gbCode, $count, $skuCount)
{
    return M("Goodsbatch")
        ->where(array("gb_code" => $gbCode))
        ->fetchSql(true)
        ->save(array("gb_count" => $count, "sku_count" => $skuCount));
}

function updateGoodstoredInitAndCountAndSkuinitAndSkucountByCode($gsCode, $init, $count, $skuInit, $skuCount)
{
    return M("Goodstored")
        ->where(array("gs_code" => $gsCode))
        ->fetchSql(true)
        ->save(array("gs_init" => $init, "gs_count" => $count, "sku_init" => $skuInit, "sku_count" => $skuCount));
}

function deleteGoodsbatchByCode($gbCode)
{
    return M("Goodsbatch")
        ->where(array("gb_code" => $gbCode))
        ->fetchSql(true)
        ->delete();
}

function deleteGoodstoredByCode($gsCode)
{
    return M("Goodstored")
        ->where(array("gs_code" => $gsCode))
        ->fetchSql(true)
        ->delete();
}

function queryIgoodsentByGsCode($gsCode)
{
    return M("Igoodsent")->where(array("gs_code" => $gsCode))->select();
}
