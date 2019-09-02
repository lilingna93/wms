<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/16
 * Time: 10:18
 */
include_once "start_generate_report.php";
include_once "common_report_function.php";
$receiptType = $REPORT_TYPE_RECEIPT_COLLECT;
$reportData = $repDataByType[$receiptType];

use Wms\Dao\ReceiptDao;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\SupplierDao;
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
    $dateArr = explode('年', $item['file']['name']);
    $month = explode('月', $dateArr[1])[0];
    $year = $dateArr[0];
    $days = get_days_by_year_and_month($year, $month);
    $supplierNameArr = array();
    foreach ($item['list'] as $key => $items) {

        $goodsbatchModel = GoodsbatchDao::getInstance($key);
        $supplierModel = SupplierDao::getInstance($key);
        $receiptModel = ReceiptDao::getInstance($key);

        $clauseRecGoods = array("in", $items);
        $recGoodsbatchDataList = $goodsbatchModel->queryListByRecCode($clauseRecGoods, 0, 100000);

        foreach ($recGoodsbatchDataList as $recGoodsbatchData) {
            $supplierName = $supplierModel->queryAllByCode($recGoodsbatchData['sup_code'])['sup_name'];
            if (!empty($supplierName)) {
                if (!in_array($supplierName, $supplierNameArr)) {
                    $supplierNameArr[] = $supplierName;
                }
                $recCtime = $receiptModel->queryByCode($recGoodsbatchData['rec_code'])['rec_ctime'];
                $date = date("m-d", strtotime($recCtime));
                $money = round(bcmul($recGoodsbatchData['gb_count'], $recGoodsbatchData['gb_bprice'], 6), 2);
                $recGoodsbatchDataListArr[$warName][$supplierName][$date] += $money;
                $recGoodsbatchDataListArr[$warName][$date] += $money;
                $key = $days + 1;
                $recGoodsbatchDataListArr[$warName][$supplierName][$month . "-" . $key] += $money;

                $recGoodsbatchDataListArr[$warName]['money'] += $money;
                unset($money);
            }
        }

        unset($recGoodsbatchDataList);
    }

    $data = array();
    $data[$item['file']['name']] = array(
        "C1" => $item['file']['name'],
    );
    $letters = array();
    for ($letter = 0; $letter < count($supplierNameArr) + 2; $letter++) {
        $letters[] = chr(65 + $letter);
    }
    for ($day = 1; $day <= $days; $day++) {
        $dayStr = str_pad($day, 2, 0, STR_PAD_LEFT);
        $dateToExcel = $month . "-" . $dayStr;
        $line[$dayStr - 0][] = $dateToExcel;
    }

    foreach ($recGoodsbatchDataListArr as $warNameKey => $goodArr) {

        foreach ($goodArr as $goodsKey => $goods) {
            if (is_array($goods)) {

                foreach ($goods as $goodKey => $good) {
                    $gk = explode("-", $goodKey)[1] - 0;

                    if ($gk == $days + 1 && !isset($line[$gk])) {
                        $line[$gk][] = "合计";
                    }

                    $line[$gk][$goodsKey] = $good;

                }
            } else {
                $gk = explode("-", $goodsKey)[1] - 0;
                $line[$gk]['money'] = $goodArr[$goodsKey];
            }
        }


    }

    for ($rows = 1; $rows < count($letters); $rows++) {
        $num = 2;
        $row = $rows - 1;

        if ($rows != count($letters) - 1) {
            $data[$item['file']['name']][$letters[$rows] . $num] = $supplierNameArr[$row];
        } else {
            $data[$item['file']['name']][$letters[$rows] . $num] = "合计";
        }
    }


    $countLineNum = count($line) + 2;

    for ($lineNum = 3; $lineNum < $countLineNum; $lineNum++) {
        for ($rows = 0; $rows < count($letters); $rows++) {
            $num = $letters[$rows] . $lineNum;
            if ($rows == count($letters) - 1 && $lineNum == $countLineNum - 1) {
                $data[$item['file']['name']][$num] = $line[0]['money'];
            } else {
                if ($rows == count($letters) - 1) {
                    $data[$item['file']['name']][$num] = $line[$lineNum - 2]['money'];
                } else {
                    if ($rows != 0) {
                        $data[$item['file']['name']][$num] = $line[$lineNum - 2][$data[$item['file']['name']][$letters[$rows] . 2]];
                    } else {
                        $data[$item['file']['name']][$num] = $line[$lineNum - 2][$rows];
                    }


                }
            }
        }

    }
    unset($line);

    if (empty($data)) {
        $uptReportFnameArr[] = report_upt_data_null($item['file'], $REPORT_STATUS_DATANULL);
    } else {
        $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "011");
        $uptReportFnameArr[] = report_upt_data($item['file'], $fileName, $REPORT_STATUS_FINISH);
    }


    unset($fileName);
    unset($data);
    unset($file);
    unset($warName);
}

unset($recGoodsbatchDataListArr);
unset($receiptType);
