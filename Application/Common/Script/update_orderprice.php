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

$time = venus_script_begin("更新订单价格");
$oCode = "O40708090747836";
$goodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $page = 0, $count = 10000);//获取订单里的所有货品数据
$totalBprice = 0;//订单总内部采购价
$totalSprice = 0;//订单总内部销售价
$totalSprofit = 0;//订单总内部利润金额
$totalCprofit = 0;//订单客户总利润额
$totalTprice = 0;//订单总金额
foreach ($goodsList as $index => $goodsItem) {

    $bprice = bcmul($goodsItem['spu_bprice'], $goodsItem['goods_count'], 4);
    $sprice = bcmul($goodsItem['spu_sprice'], $goodsItem['goods_count'], 4);
    $totalBprice += $bprice;
    $totalSprice += $sprice;
    $totalSprofit = $totalSprice - $totalBprice;
    $totalCprofit += bcmul($goodsItem['profit_price'], $goodsItem['goods_count'], 4);
    $totalTprice += venus_calculate_sku_price_by_spu($goodsItem['spu_sprice'], $goodsItem['goods_count'], $goodsItem['profit_price']);
}
$updatePriceResult = OrderDao::getInstance()->updatePriceByCode($oCode, $totalBprice, $totalSprice, $totalSprofit, $totalCprofit, $totalTprice);
if ($updatePriceResult) {
    $success = true;
    $message = "订单价格修改成功";
} else {
    $success = false;
    $message = "订单价格修改失败";
}
return array($success, "", $message);










