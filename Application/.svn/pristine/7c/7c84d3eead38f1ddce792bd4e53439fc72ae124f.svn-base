<?php
define('IS_MASTER', true);
define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
$time = venus_script_begin("开始生成报表");

use Wms\Dao\ReportDao;

include_once "start_generate_report.php";

venus_db_starttrans();
$uptReportFnameArr = array();
include_once "start_receipt_report.php";
include_once "start_invoice_report.php";
include_once "start_receipt_collect_report.php";
include_once "start_invoice_collect_report.php";
include_once "start_goodstroed_account_report.php";
include_once "start_goodstroed_collect_report.php";
if (!empty($uptReportFnameArr)) {
    foreach ($uptReportFnameArr as $uptReportFname) {
        $reportModel = ReportDao::getInstance($uptReportFname["warCode"]);
        if (isset($uptReportFname['fName'])) {
            $repFname = $reportModel->queryByCodeAndFname($uptReportFname['repCode'], $uptReportFname['fName']);
            if (!$repFname) {
                $uptRepFname = $reportModel->updateFnameByCode($uptReportFname['repCode'], $uptReportFname['fName']);
                if (!$uptRepFname) {
                    venus_db_rollback();
                    $title = "测试站WMS报表";
//                    $title = "正式站WMS报表";
                    $content = "创建报表失败";
                    echo $title . ": " . $content;
                    if (sendMailer($title, $content)) {
                        echo "(发送成功)";
                    } else {
                        echo "(发送失败)";
                    }
                }
            }
        }
        $uptRepStatus = $reportModel->updateStatusAndFinishTimeByCode($uptReportFname['repCode'], $uptReportFname['status']);
        if (!$uptRepStatus) {
            venus_db_rollback();
            $title = "测试站WMS报表";
//            $title = "正式站WMS报表";
            $content = "创建报表失败";
            echo $title . ": " . $content;
            if (sendMailer($title, $content)) {
                echo "(发送成功)";
            } else {
                echo "(发送失败)";
            }
        }

    }
    venus_db_commit();
    echo json_encode($uptReportFnameArr) . PHP_EOL;
}


venus_script_finish($time);
exit();