<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/17
 * Time: 14:55
 */
include_once "start_generate_report.php";
include_once "common_report_function.php";
$goodstoredCollectType = $REPORT_TYPE_GOODSTROED_COLLECT;
$reportData = $repDataByType[$goodstoredCollectType];

use Wms\Dao\GoodsbatchDao;
use Wms\Dao\InvoiceDao;
use Wms\Dao\IgoodsentDao;
use Common\Service\ExcelService;

$prevMonthDataList = array();
foreach ($reportData as $reportDatum) {
    $file = get_file_data($reportDatum);

    $clause = array();
    $clause['ectime'] = $reportDatum['etime'];
    $clause['recstatus'] = array("not in", array($RECEIPT_STATUS_CREATE, $RECEIPT_STATUS_INSPECTION));;

    $goodsbatchModel = GoodsbatchDao::getInstance($reportDatum['warCode']);
    $invoiceModel = InvoiceDao::getInstance($reportDatum['warCode']);
    $igoodsentModel = IgoodsentDao::getInstance($reportDatum['warCode']);

    $prevMonthGbData = $goodsbatchModel->queryPrevMonth($clause, 0, 100000);
    if (!empty($prevMonthGbData)) {
        $prevMonthDataList['gb'][] = $prevMonthGbData;
    }
    $goodsDataList[$reportDatum['warCode']]['count'] = 0;
    $goodstroedList = array();
    unset($clause);
    $clause = array(
        "ectime" => $reportDatum['etime']
    );

    $igsData = $igoodsentModel->queryPrevMonth($clause, 0, 100000);
    if (!empty($igsData)) {
        $prevMonthDataList['igs'][] = $igsData;
    }

    $gbCount = array();
    $gbPrice = array();
    $igsCount = array();
    $igsPrice = array();
    $count = array();
    $price = array();
    $bprice = array();
    $spuCodeArr = array();
    if (!empty($prevMonthDataList['gb'])) {
        foreach ($prevMonthDataList['gb'] as $prevMonthData) {
            foreach ($prevMonthData as $prevMonthDatum) {
                $gbCount[$prevMonthDatum['spu_code']] += $prevMonthDatum['gb_count'];
                $gbPrice[$prevMonthDatum['spu_code']] += round(bcmul($prevMonthDatum['gb_count'], $prevMonthDatum['gb_bprice'], 6), 2);
                if (!in_array($prevMonthDatum['spu_code'], $spuCodeArr)) {
                    $spuCodeArr[] = $prevMonthDatum['spu_code'];
                }
            }
        }

    }

    if (!empty($prevMonthDataList['igs'])) {
        foreach ($prevMonthDataList['igs'] as $prevMonthData) {
            foreach ($prevMonthData as $prevMonthDatum) {
                $igsCount[$prevMonthDatum['spu_code']] += $prevMonthDatum['igs_count'];
                $igsPrice[$prevMonthDatum['spu_code']] += round(bcmul($prevMonthDatum['igs_count'], $prevMonthDatum['igs_bprice'], 6), 2);
                if (!in_array($prevMonthDatum['spu_code'], $spuCodeArr)) {
                    $spuCodeArr[] = $prevMonthDatum['spu_code'];
                }
            }
        }

    }

    foreach ($spuCodeArr as $spuCode) {
        $count[$spuCode] = $gbCount[$spuCode] - $igsCount[$spuCode];
        $price[$spuCode] = $gbPrice[$spuCode] - $igsPrice[$spuCode];
        $bprice[$spuCode] = round(bcdiv($price[$spuCode], $count[$spuCode], 6), 2);
    }

    foreach ($prevMonthDataList['gb'] as $prevMonthData) {
        foreach ($prevMonthData as $prevMonthDatum) {
            if (!array_key_exists($prevMonthDatum['spu_code'], $goodsDataList[$reportDatum['warCode']][$prevMonthDatum['spu_type']])) {
                $goodsDataList[$reportDatum['warCode']][$prevMonthDatum['spu_type']][$prevMonthDatum['spu_code']] = array($prevMonthDatum['spu_name'], $prevMonthDatum['spu_unit'], $count[$prevMonthDatum['spu_code']], $bprice[$prevMonthDatum['spu_code']], $price[$prevMonthDatum['spu_code']]);
            }

        }
    }

    if (!empty($goodsDataList)) {
        $reportDataList[] = array(
            'list' => $goodsDataList,
            'file' => $file
        );

    } else {
        $uptReportFnameArr[] = array(
            "warCode" => $file['warCode'],
            "repCode" => $file['repCode'],
            "status" => $REPORT_STATUS_DATANULL
        );
    }

    unset($clause);
    unset($list);
    unset($invCodeList);
    unset($file);
    unset($goodsbatchModel);

}
unset($reportData);

$letters = array();
for ($letter = 0; $letter < 13; $letter++) {
    $letters[] = chr(65 + $letter);
}

foreach ($reportDataList as $reportData) {
    $sheet = array();
    $line = array();
    $warName = $reportData['file']['warName'];
    foreach ($reportData['list'] as $reportDatum) {
        $count = $reportDatum['count'];
        unset($reportDatum['count']);

        $goodsKey = 3;
        foreach ($reportDatum as $goodsType => $goodsTypeDataList) {

            foreach ($goodsTypeDataList as $goodsTypeData) {

                array_unshift($goodsTypeData, $goodsKey - 2);

                if ($goodsKey > 30 && array_key_exists($goodsKey - 30, $line)) {
                    $lineData = array_merge($line[$goodsKey - 30], $goodsTypeData);
                    unset($line[$goodsKey - 30]);
                    unset($line[$goodsTypeData]);
                    $line[$goodsKey - 30] = $lineData;
                } else {
                    $line[$goodsKey] = $goodsTypeData;
                }
                $goodsKey++;
            }

        }
        unset($reportDatum);
    }

    $line = array_chunk($line, 30);
    $data = array();
    foreach ($line as $lineKey => $lineValueArr) {
        $count = count($line);
        if ($count > 1) {
            $sheetNameStr = $lineKey + 1;
            $sheetName = $reportData['file']['name'] . "-" . $sheetNameStr;
        } else {
            $sheetName = $reportData['file']['name'];
        }
        $data[$sheetName] = array(
            "A1" => $sheetName,
        );

        foreach ($lineValueArr as $lineValue) {

            foreach ($lineValue as $lineValueRow => $lineValueCell) {
                $cellStr = $lineValue[0] + 2;
                $num = $letters[$lineValueRow] . $cellStr;
                $data[$sheetName][$num] = $lineValueCell;
            }

        }

        $goodsCountOne = 0;
        $goodsCountTwo = 0;

        foreach ($data[$sheetName] as $key => $datum) {
            if (substr($key, 0, 1) == "F") {
                $goodsCountOne += $datum;
            }
            if (substr($key, 0, 1) == "L") {
                $goodsCountTwo += $datum;
            }
        }
        $cellGoodsCount = 33;
        $numGoodsOne = $letters["5"] . $cellGoodsCount;
        $numGoodsTwo = $letters["11"] . $cellGoodsCount;
        $data[$sheetName][$numGoodsOne] = $goodsCountOne;
        $data[$sheetName][$numGoodsTwo] = $goodsCountTwo;
        unset($sheetNameStr);
        unset($sheetName);
    }
    unset($line);

    if (empty($data)) {
        $uptReportFnameArr[] = report_upt_data_null($reportData['file'], $REPORT_STATUS_DATANULL);
    } else {
        $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "030");
        $uptReportFnameArr[] = report_upt_data($reportData['file'], $fileName, $REPORT_STATUS_FINISH);

    };
    unset($fileName);
    unset($data);
    unset($warName);
}
