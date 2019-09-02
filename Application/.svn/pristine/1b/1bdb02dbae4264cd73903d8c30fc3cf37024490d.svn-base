<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/24
 * Time: 13:56
 */
ini_set('memory_limit', '1000M');
define('IS_MASTER', true);
define('APP_DIR', dirname(__FILE__) . '/../../../../');
//define('APP_DIR', '/home/dev/venus/');
//define('APP_DIR', '/home/wms/app/');//正式站目录为/home/wms/app/
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';
$time = venus_script_begin("开始获取月报报表数据");

use Wms\Dao\ReportdownloadDao;

function getLettersCell($letter)
{
    $y = $letter / 26;
    if ($y >= 1) {
        $y = intval($y);
        return chr($y + 64) . chr($letter - $y * 26 + 65);
    } else {
        return chr($letter + 65);
    }
}

$stime = date("Y-m-01 00:00:00", strtotime("-1month"));
$etime = date("Y-m-01 00:00:00", time());
//$stime = "2019-06-01 00:00:00";
//$etime = "2019-06-23 00:00:00";

echo $stime . PHP_EOL;
echo $etime . PHP_EOL;

$fileArr = array();
//临时项目组上线后
include_once "accountMonthReportTableV3.php";//财务部月度财务报表
include_once "accountSupDataSummaryReportTable.php";//财务部供货商统计表
include_once "purchasingSupDataSummary.php";//采购部订单满足率
include_once "purchasingSkuDataSummary.php";//采购部周报及月报
include_once "warReturnSummaryTable.php";//仓配部退货统计表
include_once "projectDataSummaryTableV3.php";//市场部项目销售数据表
include_once "productControlReturnSummaryTableV2.php";//品控部追溯表

$year = date("Y", strtotime($stime));
$month = date("m", strtotime($stime));
//echo json_encode($fileArr);
//exit();
$departmentsArr = array(
    "0502" => "4",//财务部月度财务报表
    "055" => "4",//财务部供应商统计表
    "0501" => "2",//市场部项目销售数据表
    "056" => "1",//采购部订单满足率报表数据
    "057" => "1",//采购部周报及月报
    "058" => "3",//仓配部退货统计表
    "059" => "5",//品控部追溯表
);
//20190628更新为上传至数据平台
$isSuccess = true;
venus_db_starttrans();
foreach ($fileArr as $saveName => $fileInfoData) {
    foreach ($fileInfoData as $typeName => $fileName) {

        $saveName = $saveName . "月报" . "({$year}.{$month})";
        $item = array(
            "fname" => $saveName,//展示的文件名字
            "sfname" => $fileName,//真实名字
            "scatalogue" => $typeName,//文件存放目录
            "sdepartments" => $departmentsArr[$typeName],//所属部门：1.采购部 2.市场部 3.仓配部 4.财务部 5.品控部
        );
        $isSuccess = $isSuccess && ReportdownloadDao::getInstance()->insert($item);

    }
}
if ($isSuccess) {
    venus_db_commit();
    echo "写入成功";
} else {
    venus_db_rollback();
    echo "写入失败";
}

//以前为发送邮件
//$email = array(
//    "0502" => array(
//        "mail" => array(
//            "linghui.wang@shijijiaming.com",
//            "lingna.li@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//        ),
//    ),//财务部月度财务报表
//    "055" => array(
//        "mail" => array(
//            "linghui.wang@shijijiaming.com",
//            "lingna.li@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//        ),
//    ),//财务部供应商统计表
//    "0501" => array(
//        "mail" => array(
//            "xiaolong.hu@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//            "lingna.li@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//        ),
//    ),//市场部项目销售数据表
//    "056" => array(
//        "mail" => array(
//            "yingping.zheng@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//            "yu.gao@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//            "lingna.li@shijijiaming.com"
//        ),
//    ),//采购部订单满足率报表数据
//    "057" => array(
//        "mail" => array(
//            "yingping.zheng@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//            "yu.gao@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//            "lingna.li@shijijiaming.com"
//        ),
//    ),//采购部周报及月报
//    "058" => array(
//        "mail" => array(
//            "aihua.xing@shijijiaming.com",
//            "panjing@shijijiaming.com",
//            "wangyantao@shijijiaming.com",
//            "jinwei.cao@shijijiaming.com",
//            "lingna.li@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//        )
//    ),//仓配部退货统计表
//    "059" => array(
//        "mail" => array(
//            "weiwei@shijijiaming.com",
//            "jinwei.cao@shijijiaming.com",
//            "lingna.li@shijijiaming.com",
//            "hui.wang@shijijiaming.com",
//            "wenlong.yang@shijijiaming.com",
//        )
//    ),//品控部追溯表
//);
//foreach ($fileArr as $saveName => $fileInfoData) {
//    foreach ($fileInfoData as $typeName => $fileName) {
//        $email[$typeName]["saveName"] = $saveName;
//        $email[$typeName]["fileName"] = $fileName;
//    }
//}

//foreach ($email as $type => $emailData) {
//    $title = $emailData['saveName'] . "(月)";
//    if (empty($emailData['saveName']) || empty($emailData['fileName'])) continue;
//    $content = $emailData['saveName'] . "(月)";
////    $content = "现在统计月度报表数据为6.01-6.23的,如发现数据不准确请及时联系研发";
//    $address = $emailData['mail'];
//    $fileName = $emailData['fileName'];
//    $attachment = array(
//        $title . ".xlsx" => C("FILE_SAVE_PATH") . "$type/" . $fileName,
//    );
//
//    if (sendMailer($title, $content, $address, $attachment)) {
//        echo "{$emailData['saveName']}(发送成功)";
//    } else {
//        echo "{$emailData['saveName']}(发送失败)";
//    }
//    echo $type . PHP_EOL;
//}

venus_script_finish($time);
exit();
