<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/9/12
 * Time: 16:14
 */
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
$time = venus_script_begin("开始删除冗余报表");

use Wms\Dao\ReportDao;
use Wms\Dao\WarehouseDao;

static $REPORT_STATUS_CREATE = "1";//报表状态已创建
static $REPORT_STATUS_UNDERWAY = "2";//报表状态处理中
static $REPORT_STATUS_FINISH = "3";//报表状态已生成
static $REPORT_STATUS_DATANULL = "4";//报表状态无数据
static $REPORT_STATUS_INVUNUAUAL = "5";//报表状态异常

$reportModel = ReportDao::getInstance("WA000001");
$warehouseModel = WarehouseDao::getInstance("WA000001");
$clause = array(
    "status" => $REPORT_STATUS_FINISH
);
$reportData = $reportModel->queryListByConditionWithoutWarehouse($clause, 0, 100000);

$fileList = array();
$dirArr = array();
foreach ($reportData as $v) {
    $fileList[report_type_to_dir($v['rep_type'])][] = $v["rep_fname"] . ".xlsx";
    if (!in_array(report_type_to_dir($v['rep_type']), $dirArr)) {
        $dirArr[] = report_type_to_dir($v['rep_type']);
    }

}
unset($reportData);
$fileDirList = array();
foreach ($dirArr as $dir) {
    $openDir = C("FILE_SAVE_PATH") . $dir; // 文件夹的名称
    $handler = opendir($openDir);//当前目录中的文件夹下的文件夹
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") {
            $fileDirList[$dir][] = $filename;
        }
    }
    closedir($handler);

}
$fileMore = array();
foreach ($dirArr as $dir) {
    $fileMore[$dir] = array_diff($fileDirList[$dir], $fileList[$dir]);
}
unset($fileDirList);
unset($fileList);
foreach ($fileMore as $dir => $fileArr) {
    $openDir = C("FILE_SAVE_PATH") . $dir;
    foreach ($fileArr as $file) {
        unlink($openDir . "/" . $file);
    }
}
venus_script_finish($time);