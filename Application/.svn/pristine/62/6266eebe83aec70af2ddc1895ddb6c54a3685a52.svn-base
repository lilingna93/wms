<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/17
 * Time: 11:25
 */
include_once "start_generate_report.php";
include_once "common_report_function.php";
$invoiceType = $REPORT_TYPE_INVOICE_COLLECT;
$reportData = $repDataByType[$invoiceType];

use Wms\Dao\InvoiceDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\IgoodsentDao;
use Common\Service\ExcelService;

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

    unset($invCodeList);
    unset($reportData);
}


foreach ($reportDataList as $item) {
    $invGoodsentDataListArr = array();
    $line = array();
    $warName = $item['file']['warName'];
    $dateArr = explode('年', $item['file']['name']);
    $month = explode('月', $dateArr[1])[0];
    $year = $dateArr[0];
    $days = get_days_by_year_and_month($year, $month);
    $spuTypeNameArr = array();
    foreach ($item['list'] as $key => $items) {

        $invoiceModel = InvoiceDao::getInstance($key);
        $igoodsentModel = IgoodsentDao::getInstance($key);

        $clauseInvGoods = array("in", $items);
        $invGoodsentDataList = $igoodsentModel->queryListByInvCode($clauseInvGoods,0,100000);

        foreach ($invGoodsentDataList as $invGoodsentData) {
            $spuTypeName = venus_spu_type_name($invGoodsentData['spu_type']);
            if (!empty($spuTypeName)) {
                if (!in_array($spuTypeName, $spuTypeNameArr)) {
                    $spuTypeNameArr[] = $spuTypeName;
                }
                $invCtime = $invoiceModel->queryByCode($invGoodsentData['inv_code'])['inv_ctime'];
                $date = date("m-d", strtotime($invCtime));
                $money = round(bcmul($invGoodsentData['igs_count'], $invGoodsentData['igs_bprice'], 6), 2);

                $invGoodsentDataListArr[$warName][$spuTypeName][$date] += $money;

                $invGoodsentDataListArr[$warName][$date] += $money;

                $key = $days + 1;
                $invGoodsentDataListArr[$warName][$spuTypeName][$month . "-" . $key] += $money;

                $invGoodsentDataListArr[$warName]['money'] += $money;
                unset($money);
            }
        }
        unset($invGoodsentDataList);
    }
    $data=array();
    $data[$item['file']['name']] = array(
        "C1" => $item['file']['name'],
    );
    $letters = array();
    for ($letter = 0; $letter < count($spuTypeNameArr) + 2; $letter++) {
        $letters[] = chr(65 + $letter);
    }
    for ($day = 1; $day <= $days; $day++) {
        $dayStr = str_pad($day, 2, 0, STR_PAD_LEFT);
        $dateToExcel = $month . "-" . $dayStr;
        $line[$dayStr - 0][] = $dateToExcel;
    }

    foreach ($invGoodsentDataListArr as $warNameKey => $goodArr) {

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

        if ($rows == count($letters) - 1) {
            $data[$item['file']['name']][$letters[$rows] . $num] = "合计";
        } else {
            $data[$item['file']['name']][$letters[$rows] . $num] = $spuTypeNameArr[$row];
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
        $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "021");
        $uptReportFnameArr[] = report_upt_data($item['file'], $fileName, $REPORT_STATUS_FINISH);

    }

    unset($fileName);
    unset($data);
    unset($file);
    unset($warName);
}

unset($invGoodsentDataList);
unset($invoiceType);