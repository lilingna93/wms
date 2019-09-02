<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/10
 * Time: 14:14
 */
include_once "start_generate_report.php";
include_once "common_report_function.php";
if (array_key_exists($REPORT_TYPE_RECEIPT, $repDataByType)) {
    $receiptType = $REPORT_TYPE_RECEIPT;
} else {
    $receiptType = $REPORT_TYPE_PURCHUSE;
}
$reportData = $repDataByType[$receiptType];

use Wms\Dao\ReceiptDao;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\SupplierDao;
use Wms\Dao\ReportDao;
use Common\Service\ExcelService;

$reportDataList = array();
foreach ($reportData as $reportDatum) {
    $file = get_file_data($reportDatum);
    $clause = array();
    $clause['sftime'] = $reportDatum['stime'];
    $clause['eftime'] = $reportDatum['etime'];
    $clause['status'] = $RECEIPT_STATUS_FINISH;
    $receiptModel = ReceiptDao::getInstance($reportDatum['warCode']);
    $list = $receiptModel->queryListByCondition($clause, 0, 100000);

    if (!empty($list)) {
        foreach ($list as $v) {
            $recCodeList[$reportDatum['warCode']][] = $v['rec_code'];
        }
        if (!empty($recCodeList)) {
            $reportDataList[] = array(
                'list' => $recCodeList,
                'file' => $file
            );
        }
        unset($recCodeList);
    } else {
        $uptReportFnameArr[] = report_upt_data_null($file, $REPORT_STATUS_DATANULL);
    }

    unset($clause);
    unset($recCodeList);
    unset($file);
}
unset($reportData);

foreach ($reportDataList as $item) {
    $recGoodsbatchDataListArr = array();
    $warName = $item['file']['warName'];
    $sumMoney = 0;
    $supEmpty = array();
    $supEmptyToSpu = array();
    foreach ($item['list'] as $key => $items) {

        $goodsbatchModel = GoodsbatchDao::getInstance($key);
        $supplierModel = SupplierDao::getInstance($key);
        $recGoodsbatchDataList = array();

        $clauseRecGoods = array("in", $items);
        $recGoodsbatchDataList = array();
        $recGoodsbatchDataList = $goodsbatchModel->queryListByRecCode($clauseRecGoods, 0, 100000);

        $supMoney = 0;

        foreach ($recGoodsbatchDataList as $recGoodsbatchData) {
            if (!empty($recGoodsbatchData['sup_code'])) {
                $supplierName = $supplierModel->queryAllByCode($recGoodsbatchData['sup_code'])['sup_name'];
                if (!empty($supplierName)) {
                    $money = round(bcmul($recGoodsbatchData['gb_count'], $recGoodsbatchData['gb_bprice'], 6), 2);
                    $recGoodsbatchDataListArr[$warName][$supplierName] += $money;
                    $sumMoney += $money;
                    unset($money);
                } else {
                    if(!in_array($recGoodsbatchData['sup_code'],$supEmpty)){
                        $supEmpty[] = $recGoodsbatchData['sup_code'];
                    }
                }
            } else {
                if(!in_array($recGoodsbatchData['spu_code'],$supEmptyToSpu)){
                    $supEmptyToSpu[] = $recGoodsbatchData['spu_code'];
                }
            }

        }
        unset($recGoodsbatchDataList);
    }
    if (!empty($supEmpty) || !empty($supEmptyToSpu)) {
        if (!empty($supEmpty)) {
            $supEmptyStr = join(",", $supEmpty);
            $title = "WMS商品供应商不存在";
            $content = $supEmptyStr;
            echo $title . ": " . $content;
            if (sendMailer($title, $content)) {
                echo "(发送成功)";
            } else {
                echo "(发送失败)";
            }
        }
        if (!empty($supEmptyToSpuStr)) {
            $supEmptyToSpuStr = join(",", $supEmptyToSpu);
            $title = "WMS商品无供应商";
            $content = $supEmptyToSpuStr;
            echo $title . ": " . $content;
            if (sendMailer($title, $content)) {
                echo "(发送成功)";
            } else {
                echo "(发送失败)";
            }
        }
    } else {
        $data = array();
        $data[$item['file']['name']] = array(
            "C1" => $item['file']['name'],
            "B2" => "入库汇总",
            "C2" => date("Y年m月d日", time()),
            "F2" => $warName,
            "B13" => venus_money_amount_in_words($sumMoney),
            "E13" => $sumMoney,
        );
        $letters = array(
            "A", "E"
        );

        $line = array();
        foreach ($recGoodsbatchDataListArr as $warNameKey => $goods) {

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


        if (empty($line)) {
            $uptReportFnameArr[] = report_upt_data_null($item['file'], $REPORT_STATUS_DATANULL);

        } else {
            $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "010");
            $uptReportFnameArr[] = report_upt_data($item['file'], $fileName, $REPORT_STATUS_FINISH);
        }
        unset($line);
        unset($fileName);
        unset($data);
        unset($warName);
    }

}
unset($reportDataList);
unset($recGoodsbatchDataListArr);
unset($receiptType);



