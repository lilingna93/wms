<?php
ini_set('memory_limit', '2028M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

$time = venus_script_begin("开始查询每个货品近一个月的销售量数据");

use Wms\Dao\SpuDao;
use Wms\Dao\OrdergoodsDao;

$spuSalesVolume = array();
$newArr = array();
$sctime = date("Y-m-d 00:00:00", strtotime("-30 day"));
$ectime = date("Y-m-d 00:00:00", time());
$fileUrl = '/home/wms/app/Public/spufiles/spusalesvolume.txt';

$condition['skStatus'] = 'all';
$spuDataList = SpuDao::getInstance()->queryAllList($condition);

$newOgArr = array();
$cond = array(
    "sctime" => $sctime,//开始时间
    "ectime" => $ectime //结束时间
);
$orderGoodsList = OrdergoodsDao::getInstance()->queryListBySkuCode($cond);
foreach ($orderGoodsList as $ordergoods => $orderGoodsItem) {
    if (!array_key_exists($orderGoodsItem['sku_code'], $newOgArr)) {
        $newOgArr[$orderGoodsItem['sku_code']] = $orderGoodsItem['sku_count'];
    } else {
        $newOgArr[$orderGoodsItem['sku_code']] += $orderGoodsItem['sku_count'];
    }
}
foreach ($spuDataList as $index => $spuItem) {
    $skucode = $spuItem['sku_code'];
    if (array_key_exists($skucode, $newOgArr)) {
        $newArr[$skucode] = $newOgArr[$skucode];
    } else {
        $newArr[$skucode] = 0;
    }

}
//    foreach ($newArr as $index => $skItem){
//        $average = bcdiv($skItem,30,2);
//        $spuSalesVolume[$index] = $average;
//    }
$spuResult = json_encode($newArr);
file_put_contents($fileUrl, $spuResult);

venus_script_finish($time);
exit();