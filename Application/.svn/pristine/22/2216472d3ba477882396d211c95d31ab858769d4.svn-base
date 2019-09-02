<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/10
 * Time: 14:28
 */
include_once "start_generate_report.php";
include_once "common_report_function.php";

use Wms\Dao\GoodsbatchDao;
use Wms\Dao\InvoiceDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\GoodsDao;
use Common\Service\ExcelService;

$goodstoredAccountType = $REPORT_TYPE_GOODSTROED_ACCOUNT;
$reportData = $repDataByType[$goodstoredAccountType];

$spuDataGoods = array();
$reportDataList = array();
$receiptDataList = array();
$receiptDataArr = array();
foreach ($reportData as $reportDatum) {
    $goodsModel = GoodsDao::getInstance($reportDatum['warCode']);
    $list = array();
    $spuData = $goodsModel->queryBySpuCode($reportDatum['spCode']);

    $file = array(
        'repCode' => $reportDatum['repCode'],
        'name' => $reportDatum['name'],
        'warCode' => $reportDatum['warCode'],//仓库编号
        'warName' => $reportDatum['warName'],
        'spCode' => $spuData['spu_code'],//货品编号
        'spNorm' => $spuData['spu_norm'],//规格
        'spUnit' => $spuData['spu_unit'],//单位
        'spName' => $spuData['spu_name'],//名称
        'count' => $spuData['goods_count'],//库存
        'year' => date("Y", strtotime($reportDatum['stime'])),
        'month' => date("m", strtotime($reportDatum['stime'])),
    );
    unset($spuData);
    $clause = array();
    $clause['sctime'] = $reportDatum['stime'];
    $clause['ectime'] = $reportDatum['etime'];
    $clause['spucode'] = $reportDatum['spCode'];
    $clause['recstatus'] = array("not in", array($RECEIPT_STATUS_CREATE, $RECEIPT_STATUS_INSPECTION));

    $goodsbatchModel = GoodsbatchDao::getInstance($reportDatum['warCode']);
    $receiptDataList = $goodsbatchModel->queryListGoodsByCondition($clause, 0, 100000);

    if (!empty($receiptDataList)) {
        $goodsbatchDataToList = array();
        foreach ($receiptDataList as $receiptData) {
            $money = round(bcmul($receiptData['gb_count'], $receiptData['gb_bprice'], 6), 2);
            $date = date("m-d", strtotime($receiptData['gb_ctime']));
            $month = date("m", strtotime($receiptData['gb_ctime']));
            $day = date("d", strtotime($receiptData['gb_ctime']));
            $goodsbatchDataToList = array(
                "0" => $month,
                "1" => $day,
                "3" => $receiptData['rec_code'],
                "8" => $receiptData['gb_count'],
                "9" => $receiptData['gb_bprice'],
                "10" => $money,
            );
            $list[$receiptData['gb_ctime']][] = $goodsbatchDataToList;
            unset($money);
        }
    }

    $igoodsentModel = IgoodsentDao::getInstance($reportDatum['warCode']);
    $igoodsSpu = array();
    $igoodsentDataList = array();
    $igoodsentDataToList = array();

    $igoodsentDataList = $igoodsentModel->queryListByCondition($clause, 0, 100000);
    $igsDataArr = array();
    foreach ($igoodsentDataList as $igoodsentKey => $igoodsentData) {
        $money = round(bcmul($igoodsentData['igs_count'], $igoodsentData['igs_bprice'], 6), 2);
        $date = date("m-d", strtotime($igoodsentData['igs_ctime']));
        $month = date("m", strtotime($igoodsentData['igs_ctime']));
        $day = date("d", strtotime($igoodsentData['igs_ctime']));
        $igoodsentDataToList = array(
            "0" => $month,
            "1" => $day,
            "3" => $igoodsentData['inv_code'],
            "11" => $igoodsentData['igs_count'],
            "12" => $igoodsentData['igs_bprice'],
            "13" => $money,
        );

        $list[$igoodsentData['igs_ctime']][] = $igoodsentDataToList;
        unset($money);
        if (!in_array($reportDatum['spCode'], $igoodsSpu)) {
            $igoodsSpu[] = $reportDatum['spCode'];
        }
    }
    if (empty($receiptDataList) && empty($igoodsSpu)) {
        $uptReportFnameArr[] = report_upt_data_null($file, $REPORT_STATUS_DATANULL);
    } else {
        ksort($list);
        unset($invoiceDataList);
        unset($receiptDataList);
        unset($clause);
        $prevMonthDataList = array();
        $clause = array(
            "spucode" => $reportDatum['spCode'],
            "ectime" => $reportDatum['stime'],
            "recstatus" => array("not in", array($RECEIPT_STATUS_CREATE, $RECEIPT_STATUS_INSPECTION))
        );

        $prevMonthGbData = $goodsbatchModel->queryPrevMonth($clause, 0, 100000);

        if (!empty($prevMonthGbData)) {
            $prevMonthDataList['gb'][] = $prevMonthGbData;
        }
        unset($clause);
        $clause = array(
            "ectime" => $reportDatum['stime'],
            "spucode" => $reportDatum['spCode'],
        );
        $igsData = array();
        $igsData = $igoodsentModel->queryPrevMonth($clause, 0, 100000);
        $prevMonthDataList['igs'][] = $igsData;

        if (!empty($prevMonthDataList['gb'])) {
            $gbCount = 0;
            $gbPrice = 0;
            foreach ($prevMonthDataList['gb'] as $prevMonthData) {
                foreach ($prevMonthData as $prevMonthDatum) {
                    $gbCount += $prevMonthDatum['gb_count'];
                    $gbPrice += round(bcmul($prevMonthDatum['gb_count'], $prevMonthDatum['gb_bprice'], 6), 2);
                }
            }

        } else {
            $gbCount = 0;
            $gbPrice = 0;
        }

        if (!empty($prevMonthDataList['igs'])) {
            $igsCount = 0;
            $igsPrice = 0;
            foreach ($prevMonthDataList['igs'] as $prevMonthData) {
                foreach ($prevMonthData as $prevMonthDatum) {
                    $igsCount += $prevMonthDatum['igs_count'];
                    $igsPrice += round(bcmul($prevMonthDatum['igs_count'], $prevMonthDatum['igs_bprice'], 6), 2);
                }
            }

        } else {
            $igsCount = 0;
            $igsPrice = 0;
        }

        $count = $gbCount - $igsCount;
        $price = $gbPrice - $igsPrice;
        $sprice = round(bcdiv($price, $count, 6), 2);
        $file['count'] = $count;
        $file['sprice'] = $sprice;
        $file['price'] = $price;
        $reportDataList[] = array(
            'list' => $list,
            'file' => $file
        );
        unset($prevMonthDataList);
        unset($igoodsentDataArr);

    }
}

$letters = array();
for ($letter = 0; $letter < 17; $letter++) {
    $letters[] = chr(65 + $letter);
}

foreach ($reportDataList as $reportData) {
    $line = array();
    $warName = $reportData['file']['warName'];
    foreach ($reportData['list'] as $reportDatum) {
        foreach ($reportDatum as $reportDaitem)
            $line[] = $reportDaitem;
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
            "B2" => $reportData['file']['spCode'],
            "D2" => $reportData['file']['spNorm'],
            "O2" => $reportData['file']['spUnit'],
            "Q2" => $reportData['file']['spName'],
            "A3" => $reportData['file']['year'],
            "A5" => $reportData['file']['month'],
            "B5" => "01",
            "D5" => "上月合计",
            "I5" => $reportData['file']['count'],
            "J5" => $reportData['file']['sprice'],
            "K5" => $reportData['file']['price'],
            "O5" => $reportData['file']['count'],
            "P5" => $reportData['file']['sprice'],
            "Q5" => $reportData['file']['price']

        );

        foreach ($lineValueArr as $lineValueKey => $lineValue) {

            foreach ($lineValue as $lineValueRow => $lineValueCell) {
                $cellStr = $lineValueKey + 6;
                $num = $letters[$lineValueRow] . $cellStr;
                $data[$sheetName][$num] = $lineValueCell;
                if ($lineValueRow == "10" || $lineValueRow == "13") {
                    $cellStrPrev = $lineValueKey + 5;
                    $numPrevToNum = $letters["14"] . $cellStrPrev;
                    $numPrevToPrice = $letters["15"] . $cellStrPrev;
                    $numPrevToMoney = $letters["16"] . $cellStrPrev;
                    $numToNum = $letters["14"] . $cellStr;
                    $numToPrice = $letters["15"] . $cellStr;
                    $numToMoney = $letters["16"] . $cellStr;
                    if ($lineValueRow == "10") {
                        $numNowToNum = $letters["8"] . $cellStr;
                        $numNowToPrice = $letters["9"] . $cellStr;
                        $numNowToMoney = $letters["10"] . $cellStr;
                        $data[$sheetName][$numToNum] = $data[$sheetName][$numPrevToNum] + $data[$sheetName][$numNowToNum];
                        $data[$sheetName][$numToMoney] = $data[$sheetName][$numNowToMoney] + $data[$sheetName][$numPrevToMoney];
                        $data[$sheetName][$numToPrice] = round(bcdiv($data[$sheetName][$numToMoney], $data[$sheetName][$numToNum], 6), 2);
                    } else {
                        $numNowToNum = $letters["11"] . $cellStr;
                        $numNowToPrice = $letters["12"] . $cellStr;
                        $numNowToMoney = $letters["13"] . $cellStr;
                        $data[$sheetName][$numToNum] = $data[$sheetName][$numPrevToNum] - $data[$sheetName][$numNowToNum];
                        $data[$sheetName][$numToMoney] = $data[$sheetName][$numPrevToMoney] - $data[$sheetName][$numNowToMoney];
                        $data[$sheetName][$numToPrice] = round(bcdiv($data[$sheetName][$numToMoney], $data[$sheetName][$numToNum], 6), 2);
                    }
                }
            }
            if ($lineValueKey == count($lineValueArr) - 1) {
                $recCount = 0;
                $recMoney = 0;
                $recPrice = 0;
                $invCount = 0;
                $invMoney = 0;

                foreach ($data[$sheetName] as $key => $datum) {
                    $invPrice = 0;
                    if (substr($key, 0, 1) == "I") {
                        $recCount += $datum;
                    }
                    if (substr($key, 0, 1) == "K") {
                        $recMoney += $datum;
                    }
                    if (substr($key, 0, 1) == "L") {
                        $invCount += $datum;
                    }
                    if (substr($key, 0, 1) == "N") {
                        $invMoney += $datum;
                    }
                }

                $recPrice = round(bcdiv($recMoney, $recCount, 6), 2);
                $invPrice = round(bcdiv($invMoney, $invCount, 6), 2);
                $countLastLine = $recCount - $invCount;
                $moneyLastLine = $recMoney - $invMoney;
                $priceLastLine = round(bcdiv($moneyLastLine, $countLastLine, 6), 2);
                $cellLastLineStr = 35;
                $numLastLineToRecNum = $letters["8"] . $cellLastLineStr;
                $numLastLineToRecPrice = $letters["9"] . $cellLastLineStr;
                $numLastLineToRecMoney = $letters["10"] . $cellLastLineStr;
                $numLastLineToInvNum = $letters["11"] . $cellLastLineStr;
                $numLastLineToInvPrice = $letters["12"] . $cellLastLineStr;
                $numLastLineToInvMoney = $letters["13"] . $cellLastLineStr;
                $numLastLineToNum = $letters["14"] . $cellLastLineStr;
                $numLastLineToPrice = $letters["15"] . $cellLastLineStr;
                $numLastLineToMoney = $letters["16"] . $cellLastLineStr;
                $data[$sheetName][$numLastLineToRecNum] = $recCount;
                $data[$sheetName][$numLastLineToRecPrice] = $recPrice;
                $data[$sheetName][$numLastLineToRecMoney] = $recMoney;
                $data[$sheetName][$numLastLineToInvNum] = $invCount;
                $data[$sheetName][$numLastLineToInvPrice] = $invPrice;
                $data[$sheetName][$numLastLineToInvMoney] = $invMoney;
                $data[$sheetName][$numLastLineToNum] = $countLastLine;
                $data[$sheetName][$numLastLineToPrice] = $priceLastLine;
                $data[$sheetName][$numLastLineToMoney] = $moneyLastLine;
                unset($recCount);
                unset($recPrice);
                unset($recMoney);
                unset($invCount);
                unset($invPrice);
                unset($invMoney);
                unset($countLastLine);
                unset($moneyLastLine);
                unset($priceLastLine);
            }
        }
        unset($sheetNameStr);
        unset($sheetName);
    }

    if (empty($line)) {
        $uptReportFnameArr[] = report_upt_data_null($reportData['file'], $REPORT_STATUS_DATANULL);

    } else {
        $fileName = ExcelService::getInstance()->exportExcelByTemplate($data, "040");
        $uptReportFnameArr[] = report_upt_data($reportData['file'], $fileName, $REPORT_STATUS_FINISH);
    };

    unset($fileName);
    unset($data);
    unset($warName);
    unset($line);
}
unset($reportData);
unset($reportDataList);
