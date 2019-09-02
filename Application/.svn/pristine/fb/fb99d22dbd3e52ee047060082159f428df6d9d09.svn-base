<?php
ini_set('memory_limit', '356M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
//define('APP_DIR', '/home/dev/venus/');//测试站运行脚本路径
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao;
use Wms\Dao\SkuDao;
use Wms\Dao\SupplierDao;
use Common\Service\ExcelService;

$time = venus_script_begin("插入最新新发地SKU数据");

$dbName = "zwdb_wms";
$sql = "select * from $dbName.wms_primaryxfdsku where 1 GROUP BY sku_name,sku_norm,timestamp";
$result = M()->query($sql);

$totalCount = queryCountByCondition();
if ($totalCount > 0) {
    $i = $totalCount;
} else {
    $i = 0;
}

foreach ($result as $index => $val) {
    $i++;
    $skCode = "XD" . str_pad($i, 6, "0", STR_PAD_LEFT);
    $skName = $val['sku_name'];
    $mPrice = $val['minimum_price'];
    $aPrice = $val['average_price'];
    $maPrice = $val['maximum_price'];
    $skNorm = $val['sku_norm'];
    $skUnit = $val['sku_unit'];
    $reTime = $val['timestamp'];
    $timestamp = date("Y-m-d H:i:s");

    $cond = array(
        "sku_name" => $skName,
        "sku_norm" => $skNorm,
    );
    $selectXfdsku = queryAllList($cond);

    if (empty($selectXfdsku)) {
        $data = array(
            "sku_code" => $skCode,
            "sku_name" => $skName,
            "spu_code" => '',
            "spu_name" => '',
            "minimum_price" => $mPrice,
            "average_price" => $aPrice,
            "maximum_price" => $maPrice,
            "pro_percent" => '0.00',
            "sku_norm" => $skNorm,
            "sku_unit" => $skUnit,
            "release_time" => $reTime,
            "timestamp" => $timestamp,
        );
        $insertXfdsku = insert($data);
    } else {//更新

        foreach ($selectXfdsku as $index => $item) {

            $cond = array(
                "sku_name" => $item['sku_name'],
                "sku_norm" => $item['sku_norm'],
                "release_time" => $item['release_time'],
            );
            $data = queryBySkNameAndSkNormAndRtime($cond);

            $skname = $data[0]['sku_name'];
            $sknorm = $data[0]['sku_norm'];
            $mprice = $data[0]['minimum_price'];
            $aprice = $data[0]['average_price'];
            $maprice = $data[0]['maximum_price'];
            $rtime = $data[0]['timestamp'];
            $updateXfdPrice = updatePriceByCode($skname, $sknorm, $mprice, $aprice, $maprice, $rtime);
        }

    }

}

function queryCountByCondition()
{
    return M("xinfadisku")->fetchSql(false)->count();
}

function insert($data)
{
    return M("xinfadisku")->add($data) ? true : false;
}

function updatePriceByCode($skname, $sknorm, $mprice, $aprice, $maprice, $rtime)
{
    $condition = array("sku_name" => $skname, "sku_norm" => $sknorm);
    return M("xinfadisku")->where($condition)->fetchSql(false)
        ->save(array("timestamp" => venus_current_datetime(), "minimum_price" => $mprice,
            "average_price" => $aprice, "maximum_price" => $maprice, "release_time" => $rtime));
}

function queryAllList($cond)
{//查询
    if (isset($cond['sku_name'])) {
        $condition['sku_name'] = $cond['sku_name'];
    }
    if (isset($cond['sku_norm'])) {
        $condition['sku_norm'] = $cond['sku_norm'];
    }
    return M("xinfadisku")
        ->where($condition)->fetchSql(false)->select();
}

function queryBySkNameAndSkNormAndRtime($cond)
{
    if (isset($cond['sku_name'])) {
        $condition['sku_name'] = $cond['sku_name'];
    }
    if (isset($cond['sku_norm'])) {
        $condition['sku_norm'] = $cond['sku_norm'];
    }
    if(isset($cond['release_time'])){
        $condition['timestamp'] = array('EGT',$cond['release_time']);
    }
    return M("primaryxfdsku")->where($condition)->order('timestamp desc')->limit(1)->fetchSql(false)->select();
}

venus_script_finish($time);
exit();