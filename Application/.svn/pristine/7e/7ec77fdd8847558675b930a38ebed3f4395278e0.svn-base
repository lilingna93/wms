<?php
/**
 * Created by PhpStorm.
 * User: li176
 * Date: 2019/1/3
 * Time: 0:07
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
$time = venus_script_begin("开始检测工单批量操作");

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
venus_db_starttrans();
$otService = new \Wms\Service\OrdertaskService();
$otClause = array(
    "sctime" => "2018-12-29 00:20:00",
    "ectime" => "2019-01-02 00:00:01",
);
$otData = $ordertaskModel->queryListByCondition($otClause, 0, 100000);
$isSuccess = true;
foreach ($otData as $otDatum) {
    $otCode = $otDatum['ot_code'];
    $oCodes = json_decode($otDatum['or_mark'], true);
    if ($otDatum['ot_ownstatus'] == 1 && $isSuccess = true) {
        $paramOwn = array(
            "data" => array(
                "oCodes" => $oCodes,
                "otCode" => $otCode,
                "isAllow" => 1
            )
        );
        $ownHandle = $otService->own_inv_create($paramOwn);
        $isSuccess = $isSuccess && (empty($ownHandle['success']) ? $ownHandle[0] : $ownHandle['success']);
    }
    echo $isSuccess;
    exit();
    if ($otDatum['ot_supstatus'] == 1 && $isSuccess = true) {
        $paramSup = array(
            "data" => array(
                "oCodes" => $oCodes,
                "otCode" => $otCode,
            )
        );
        $supHandle = $otService->sup_inv_create($paramSup);
        $isSuccess = $isSuccess && (empty($supHandle['success']) ? $supHandle[0] : $supHandle['success']);
    }
}
if ($isSuccess) {
    venus_db_commit();
    echo "1";
    exit();
} else {
    venus_db_rollback();
    echo "2";
    exit();
}
