<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/13
 * Time: 11:21
 */

include_once "start_generate_report.php";
include_once "common_report_function.php";

if(array_key_exists($REPORT_TYPE_INVOICE,$repDataByType)){
    $invoiceType = $REPORT_TYPE_INVOICE;
}else{
    $invoiceType = $REPORT_TYPE_APPLY;
}
$reportData = $repDataByType[$invoiceType];

use Wms\Dao\InvoiceDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\ReportDao;
use Common\Service\ExcelService;
use Wms\Dao\IgoodsentDao;

$reportDataList = array();
foreach ($reportData as $reportDatum) {
    $file = get_file_data($reportDatum);

    $clause = array();
    $clause['sctime'] = $reportDatum['stime'];
    $clause['ectime'] = $reportDatum['etime'];
    $clause['status'] = $INVOICE_STATUS_FINISH;
    $invoiceModel = InvoiceDao::getInstance($reportDatum['warCode']);
    $list = $invoiceModel->queryListByCondition($clause, 0, 100000);
    if (!empty($list)) {
        foreach ($list as $v) {
            $invCodeList[$reportDatum['warCode']][] = $v['inv_code'];
        }

        if (!empty($invCodeList)) {
            $reportDataList[] = array(
                'list' => $invCodeList,
                'file' => $file
            );

        }
    } else {
        $uptReportFnameArr[] = report_upt_data_null($file, $REPORT_STATUS_DATANULL);
    }
    unset($clause);
    unset($list);
    unset($invCodeList);
    unset($file);
}
unset($reportData);

foreach ($reportDataList as $item) {
    $invIgoodsDataListArr = array();
    $warName = $item['file']['warName'];
    $sumMoney=0;
    foreach ($item['list'] as $key => $items) {

        $igoodsModel = IgoodsDao::getInstance($key);
        $igoodsentModel = IgoodsentDao::getInstance($key);


        $clauseInvGoods = array("in", $items);
        $invGoodsentDataList = $igoodsentModel->queryListByInvCode($clauseInvGoods,0,100000);

        foreach ($invGoodsentDataList as $invGoodsentData) {

            $typeName = venus_spu_type_name($invGoodsentData['spu_type']);
            if (!empty($typeName)) {
                $money = round(bcmul($invGoodsentData['igs_count'], $invGoodsentData['igs_bprice'], 6), 2);
                $invIgoodsDataListArr[$warName][$typeName] += $money;
                $sumMoney+=$money;
                unset($money);
            }
        }
        unset($invIgoodsDataList);
    }
    $data=array();
    $data[$item['file']['name']] = array(
        "C1" => $item['file']['name'],
        "B2" => "出库汇总",
        "C2" => date("Y年m月d日", time()),
        "F2" => $warName,
        "B13" => venus_money_amount_in_words($sumMoney),
        "E13" => $sumMoney,
    );
    $letters = array(
        "A", "E"
    );

    $line = array();
    foreach ($invIgoodsDataListArr as $warNameKey => $goods) {

        foreach ($goods as $goodKey => $good) {
            $line[] = array($goodKey, $good);
        }
    }
    $countLineNum = count($line) + 4;
    for ($lineNum = 4; $lineNum < $countLineNum; $lineNum++) {
        for ($rows = 0; $rows < count($letters); $rows++) {
            $num = $letters[$rows] . $lineNum;
            $data[$item['file']['name']][$num] = $line[$lineNum - 4][$rows];
        }
    }

    if (empty($data)) {
        $uptReportFnameArr[] = report_upt_data_null($item['file'], $REPORT_STATUS_DATANULL);
    } else {
        $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "020");
        $uptReportFnameArr[] = report_upt_data($item['file'], $fileName, $REPORT_STATUS_FINISH);
    }
    unset($line);
    unset($fileName);
    unset($data);
    unset($file);
    unset($invIgoodsDataListArr);
    unset($fileName);
}
unset($invoiceType);
unset($reportData);
