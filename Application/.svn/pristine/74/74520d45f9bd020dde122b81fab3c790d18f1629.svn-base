<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/8
 * Time: 16:66
 */

namespace Wms\Service;


use Common\Service\ExcelService;
use Common\Service\PassportService;
use Wms\Dao\GoodsDao;
use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Wms\Dao\ReportDao;
use Wms\Dao\ReturnDao;
use Wms\Dao\ReturntaskDao;
use Wms\Dao\SpuDao;
use Wms\Dao\WarehouseDao;
use Wms\Dao\WorkerDao;

class ReportService
{
    static private $REPORT_TYPE_RECEIPT = "2";//入仓单
    static private $REPORT_TYPE_INVOICE = "6";//出仓单
    static private $REPORT_TYPE_RECEIPT_COLLECT = "6";//入库汇总
    static private $REPORT_TYPE_INVOICE_COLLECT = "8";//出库汇总
    static private $REPORT_TYPE_GOODSTROED_COLLECT = "10";//库存汇总
    static private $REPORT_TYPE_GOODSTROED_ACCOUNT = "12";//台账登记表
    static private $REPORT_TYPE_APPLY = "16";//申领单
    static private $REPORT_TYPE_PURCHUSE = "16";//采购单

    static private $REPORT_STATUS_CREATE = "1";//报表状态已创建
    static private $REPORT_STATUS_UNDERWAY = "2";//报表状态处理中
    static private $REPORT_STATUS_FINISH = "3";//报表状态已生成
    static private $REPORT_STATUS_DATANULL = "6";//报表状态无数据
    static private $REPORT_STATUS_INVUNUAUAL = "5";//报表状态异常

    public $warCode;
    public $worcode;

    public function __construct()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if (empty($workerData)) {
            venus_throw_exception(110);
        }

        $this->warCode = $workerData["war_code"];
        $this->worcode = $workerData["wor_code"];
//        $this->warCode = $workerData["war_code"] = "WA000001";
//        $this->worcode = $workerData["wor_code"] = "WO000001";
    }

    /**
     * @param $param
     * @return array|bool
     * 创建报表
     */
    public function report_create($param)
    {
        if (!isset($param)) {
            $param = $_POST;
        }
        $userWarCode = $this->warCode;
        $worCode = $this->worcode;
        $warCode = $param['data']['warCode'];
        $type = $param['data']['type'];
        $stime = $param['data']['stime'];
        $etime = $param['data']['etime'];
        $otherMsg = $param['data']['otherMsg'];

        if (empty($type)) {
            $message = "报表类型为空";
            venus_throw_exception(1, $message);
            return false;
        }
        if (empty($warCode)) {
            $message = "客户单位为空";
            venus_throw_exception(1, $message);
            return false;
        }
        if (empty($stime) || empty($etime)) {
            $message = "日期为空";
            venus_throw_exception(1, $message);
            return false;
        }

        if (empty($otherMsg['repName'])) {
            $message = "报表名称为空";
            venus_throw_exception(1, $message);
            return false;
        }

        if ($type == self::$REPORT_TYPE_GOODSTROED_ACCOUNT) {
            if (empty($otherMsg['spCode'])) {
                $message = "请选择货品";
                venus_throw_exception(1, $message);
                return false;
            }
        }

        $repData = array(
            "stime" => $stime,
            "etime" => $etime,
            "type" => $type,
            "warCode" => $warCode,
        );

        if (!empty($otherMsg['spCode'])) {
            $spuModel = SpuDao::getInstance($warCode);
            $spName = $spuModel->queryByCode($otherMsg['spCode'])['spu_name'];
            $repNames = $otherMsg['repName'] . "(" . $spName . ")";
            $repData["spCode"] = $otherMsg['spCode'];
        } else {
            $repNames = $otherMsg['repName'];
        }
        $repNameArr = explode("-", $repNames);

        if (array_key_exists(3, $repNameArr)) {
            $repName = $repNameArr[0] . "年" . $repNameArr[1] . "月" . $repNameArr[2] . "日" . $repNameArr[3];
        } else {
            $repName = $repNameArr[0] . "年" . $repNameArr[1] . "月" . $repNameArr[2];
        }

        $reportModel = ReportDao::getInstance($userWarCode);
        $issetReport = $reportModel->queryByName($repName);
        if ($issetReport) {
            venus_throw_exception(4001);
            return false;
        }
        $reportAddData = array(
            "name" => $repName,
            "fname" => "NULL",
            "data" => json_encode($repData),
            "type" => $type,
            "worcode" => $worCode,
        );
        $addRep = $reportModel->insert($reportAddData);

        if (!$addRep) {
            $message = '创建报表失败';
            venus_throw_exception(2, $message);
            return false;
        } else {
            $success = true;
            $data = array();
            $message = '';
            return array($success, $data, $message);
        }
    }

    /**
     * @return array
     * 报表列表
     */
    public function report_search()
    {
        $warCode = $this->warCode;

        $type = $_POST['data']['type'];
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页数
        $clause = array();
        if (!empty($type)) {
            $clause['type'] = $type;
        }
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $reportModel = ReportDao::getInstance($warCode);
        $workerModel = WorkerDao::getInstance($warCode);

        $totalCount = $reportModel->queryCountByCondition($clause);
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $reportDataList = $reportModel->queryListAndWorkerByCondition($clause, $pageLimit['page'], $pageLimit['pSize']);
        $data = array();
        $data = array(
            "pageCurrent" => $pageCurrent,
            "pageSize" => $pageLimit['pageSize'],
            "totalCount" => $totalCount,
        );

        foreach ($reportDataList as $value) {

            $data['list'][] = array(
                "code" => $value['rep_code'],
                "repName" => $value['rep_name'],
                "repFname" => $value['rep_fname'],
                "repCtime" => $value['rep_ctime'],
                "repStatus" => $value['rep_status'],
                "repStatMsg" => venus_report_status_desc($value['rep_status']),
                "worName" => $value['wor_name'],
            );
        }
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    public function report_delete()
    {
        $warCode = $this->warCode;

        $repCode = $_POST['data']['repCode'];
        if (empty($repCode)) {
            $message = "报表编号为空，请选择报表";
            venus_throw_exception(1, $message);
            return false;
        }

        $reportModel = ReportDao::getInstance($warCode);
        $delReport = $reportModel->deleteByCode($repCode);
        if (!$delReport) {
            $message = '报表删除失败';
            venus_throw_exception(2, $message);
            return false;
        } else {
            $success = true;
            $data = array();
            $message = '';
            return array($success, $data, $message);
        }
    }

    /**
     * @param $param
     * 报表月度毛利
     */
    public function report_month_sputype($param)
    {
        if (!isset($param)) {
            $param = $_POST;
        }
        $time = $param['data']['time'];
        if (empty($time)) return array(false, array(), "请选择月份");
        $stime = $time . "-01 00:00:00";
        $timeArr = explode("-", $time);
        $year = $timeArr[0];
        $month = $timeArr[1];
        if($year=="2019"&&$month<3){
            return array(false,array(),"请选择2019年3月及以后数据");
        }
        if($year<2019){
            return array(false,array(),"请选择2019年3月及以后数据");
        }
        $etime = $year . "-0" . ($month + 1) . "-01 00:00:00";
        $clauseOrder = array(
            "sctime" => $stime,
            "ectime" => $etime,
            "wstatus" => 3
        );
        $orderModel = OrderDao::getInstance();
        $orderData = $orderModel->queryListByCondition($clauseOrder, 0, 10000000);
        $orderCodeArr = array_column($orderData, "order_code");
        $orderTimeArr = array();
        foreach ($orderData as $orderDatum) {
            $orderTimeArr[$orderDatum['order_code']] = $orderDatum['order_ctime'];
        }
        $clauseOrdergoods = array(
            "ocodes" => $orderCodeArr,
            "supcode" => "SU00000000000001"
        );
        $ordergoodsCount = OrdergoodsDao::getInstance()->queryCountByCondition($clauseOrdergoods);
        $ordergoodsData = OrdergoodsDao::getInstance()->queryListByCondition($clauseOrdergoods, 0, $ordergoodsCount);
        $warData = array();
        $timeData = array();
        $spuTypeData = array();
        $returnDataArr = array();
        foreach ($ordergoodsData as $ordergoodsDatum) {
            $warCode = $ordergoodsDatum['war_code'];
            $warName = WarehouseDao::getInstance()->queryClientByCode($warCode)[("war_name")];
            $orderCode = $ordergoodsDatum['order_code'];
            $orderTime = date("m/d", strtotime($orderTimeArr[$orderCode]));
            $spuName = $ordergoodsDatum['spu_name'];
            $spuType = venus_spu_type_name($ordergoodsDatum['spu_type']);
            $spuBprice = $ordergoodsDatum['spu_bprice'];
            $spuSprice = $ordergoodsDatum['spu_sprice'];
            $spuPprice = $ordergoodsDatum["profit_price"];
            if ($spuType == "鲜鱼水菜") continue;

            $skuCount = floatval($ordergoodsDatum['sku_init']);
            $spuCount = $ordergoodsDatum['spu_count'];
            $skuSprice = floatval(bcmul($spuSprice, $spuCount, 6));
            $skuBprice = floatval(bcmul($spuBprice, $spuCount, 6));
            $skuPprice = floatval(bcmul($spuPprice, $spuCount, 6));
            $sprice = floatval(bcmul($skuSprice, $skuCount, 6));
            $bprice = floatval(bcmul($skuBprice, $skuCount, 6));
        $pprice = floatval(bcmul($skuPprice, $skuCount, 6));

            if (!array_key_exists("money", $warData[$warName][$spuType])) {
                $warData[$warName][$spuType]['money'] = 0;
            }
            if (!array_key_exists("bprice", $warData[$warName][$spuType])) {
                $warData[$warName][$spuType]['bprice'] = 0;
            }
            $warData[$warName][$spuType]['money'] = floatval(bcadd($warData[$warName][$spuType]['money'], $sprice, 8));
            $warData[$warName][$spuType]['bprice'] = floatval(bcadd($warData[$warName][$spuType]['bprice'], $bprice, 8));

            if (!array_key_exists("money", $warData[$warName][$spuType])) {
                $timeData[$orderTime][$spuType]['money'] = 0;
            }
            if (!array_key_exists("bprice", $warData[$warName][$spuType])) {
                $timeData[$orderTime][$spuType]['bprice'] = 0;
            }
            $timeData[$orderTime][$spuType]['money'] = floatval(bcadd($timeData[$orderTime][$spuType]['money'], $sprice, 8));
            $timeData[$orderTime][$spuType]['bprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['bprice'], $bprice, 8));


            if (!array_key_exists("money", $warData[$warName][$spuType])) {
                $spuTypeData[$spuType][$spuName]['money'] = 0;
            }
            if (!array_key_exists("bprice", $warData[$warName][$spuType])) {
                $spuTypeData[$spuType][$spuName]['bprice'] = 0;
            }
            $spuTypeData[$spuType][$spuName]['money'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
            $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));

            if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
                $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
                $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
                $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
            } else {
                $warData[$warName][$spuType]['pprice'] = floatval(bcadd($warData[$warName][$spuType]['pprice'], $pprice, 8));
                $timeData[$orderTime][$spuType]['pprice'] = floatval(bcadd($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
                $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcadd($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
            }
        }

        $clauseReturntask = array(
            "sTime" => $stime,
            "eTime" => $etime
        );
        $returntaskModel = ReturntaskDao::getInstance();
        $returnTaskData = $returntaskModel->queryListByCondition($clauseReturntask, 0, 10000000);
        $returnTaskCodes = array_column($returnTaskData, "rt_code");
        $returnAddTimeArr = array();
        foreach ($returnTaskData as $returnTaskDatum) {
            $returnAddTimeArr[$returnTaskDatum['rt_code']] = $returnTaskDatum['rt_addtime'];
        }
        $returnModel = ReturnDao::getInstance();
        $clauseReturn = array(
            "supcode" => "SU00000000000001",
            "ogrStatus" => 2,
            "rtcodes" => $returnTaskCodes
        );
        $returnData = $returnModel->queryListByCondition($clauseReturn, 0, 1000000);
        foreach ($returnData as $returnDatum) {
            $warName = $returnDatum["war_name"];
            $orderCode = $returnDatum["order_code"];
            $spuName = $returnDatum["spu_name"];
            $spuBprice = $returnDatum["spu_bprice"];
            $spuSprice = $returnDatum["spu_sprice"];
            $spuPprice = $returnDatum["profit_price"];
            $spuCount = $returnDatum["spu_count"];
            $goodsCode = $returnDatum["goods_code"];
            $rtCode = $returnDatum["rt_code"];
            $status = $returnDatum["ogr_status"];
            if ($status != 2) continue;
            $spuType = venus_spu_type_name($returnDatum['spu_type']);
            if ($spuType == "鲜鱼水菜") continue;
            $returnCount = floatval($returnDatum['actual_count']);


            $skuSprice = floatval(bcmul($spuSprice, $spuCount, 6));
            $skuBprice = floatval(bcmul($spuBprice, $spuCount, 6));
            $skuPprice = floatval(bcmul($spuPprice, $spuCount, 6));
            $sprice = floatval(bcmul($skuSprice, $returnCount, 6));
            $bprice = floatval(bcmul($skuBprice, $returnCount, 6));
            $pprice = floatval(bcmul($skuPprice, $returnCount, 6));

            $returnDataArr[$warName][$returnAddTimeArr[$rtCode]][$orderCode][$goodsCode][$spuName][$skuBprice][$skuSprice]["returncount"] = $returnCount;
            $time = date("m/d", strtotime($returnAddTimeArr[$rtCode]));
            if (!array_key_exists("money", $warData[$warName][$spuType])) {
                $warData[$warName][$spuType]['money'] = 0;
            }
            if (!array_key_exists("bprice", $warData[$warName][$spuType])) {
                $warData[$warName][$spuType]['bprice'] = 0;
            }
            if (!array_key_exists("pprice", $warData[$warName][$spuType])) {
                $warData[$warName][$spuType]['pprice'] = 0;
            }
            if (!array_key_exists("money", $timeData[$orderTime][$spuType])) {
                $timeData[$orderTime][$spuType]['money'] = 0;
            }
            if (!array_key_exists("bprice", $timeData[$orderTime][$spuType])) {
                $timeData[$orderTime][$spuType]['bprice'] = 0;
            }
            if (!array_key_exists("pprice", $timeData[$orderTime][$spuType])) {
                $timeData[$orderTime][$spuType]['pprice'] = 0;
            }
            if (!array_key_exists("money", $spuTypeData[$spuType][$spuName])) {
                $spuTypeData[$spuType][$spuName]['money'] = 0;
            }
            if (!array_key_exists("bprice", $spuTypeData[$spuType][$spuName])) {
                $spuTypeData[$spuType][$spuName]['bprice'] = 0;
            }
            if (!array_key_exists("pprice", $spuTypeData[$spuType][$spuName])) {
                $spuTypeData[$spuType][$spuName]['pprice'] = 0;
            }
            $warData[$warName][$spuType]['money'] = floatval(bcsub($warData[$warName][$spuType]['money'], $sprice, 8));
            $warData[$warName][$spuType]['bprice'] = floatval(bcsub($warData[$warName][$spuType]['bprice'], $bprice, 8));

            $timeData[$time][$spuType]['money'] = floatval(bcsub($timeData[$time][$spuType]['money'], $sprice, 8));
            $timeData[$time][$spuType]['bprice'] = floatval(bcsub($timeData[$time][$spuType]['bprice'], $bprice, 8));

            $spuTypeData[$spuType][$spuName]['money'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['money'], $sprice, 8));
            $spuTypeData[$spuType][$spuName]['bprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['bprice'], $bprice, 8));
            if ($orderTimeArr[$orderCode] > '2019-03-15 00:00:00') {
                $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
                $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], bcmul($sprice, 0.1, 4), 8));
                $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], bcmul($sprice, 0.1, 4), 8));
            } else {
                $warData[$warName][$spuType]['pprice'] = floatval(bcsub($warData[$warName][$spuType]['pprice'], $pprice, 8));
                $timeData[$orderTime][$spuType]['pprice'] = floatval(bcsub($timeData[$orderTime][$spuType]['pprice'], $pprice, 8));
                $spuTypeData[$spuType][$spuName]['pprice'] = floatval(bcsub($spuTypeData[$spuType][$spuName]['pprice'], $pprice, 8));
            }
        }

//        echo md5(json_encode($spuTypeData)).PHP_EOL;
        $excelData = $this->get_sputype_excel_data($spuTypeData, $stime, $etime);
//        echo md5(json_encode($excelData)).PHP_EOL;
        $fileName = $this->export_report($excelData, "052");
        $saveName = $year . "年" . $month . "月月度毛利统计表七大品类.xlsx";
        return array(true, array("sname" => $saveName, "fname" => $fileName), "");
    }

    /**
     * @param $spuTypeData品类维度数据
     * @param $stime开始时间
     * @param $etime结束时间
     * @return array
     */
    private function get_sputype_excel_data($spuTypeData, $stime, $etime)
    {
        $excelData = array();
        foreach ($spuTypeData as $spuType => $spuTypeDatum) {
            $timeCell = "C2";
            $excelData["月度毛利统计表-品类{$spuType}"][$timeCell] = "制表期间:" . $stime . "-" . $etime;
            $typeCell = 'C6';
            $excelData["月度毛利统计表-品类{$spuType}"][$typeCell] = $spuType;
            $line = 6;
            foreach ($spuTypeDatum as $spuName => $spuItem) {
                $numCell = 'A' . $line;
                $excelData["月度毛利统计表-品类{$spuType}"][$numCell] = $line - 5;
                $spuNameCell = 'B' . $line;
                $excelData["月度毛利统计表-品类{$spuType}"][$spuNameCell] = $spuName;
                $spriceCell = 'C' . $line;//销售额
                $mpriceCell = 'D' . $line;//管理费
                $bpriceCell = 'E' . $line;//采购成本
                $ppriceCell = 'F' . $line;//毛利
                $excelData["月度毛利统计表-品类{$spuType}"][$spriceCell] = $spuItem['money'];
                $excelData["月度毛利统计表-品类{$spuType}"][$bpriceCell] = $spuItem['bprice'];
//            $excelData["月度毛利统计表-品类{$spuType}"][$mpriceCell] = $spuItem['pprice'];
                $excelData["月度毛利统计表-品类{$spuType}"][$mpriceCell] = "=$spriceCell*10%";
                $excelData["月度毛利统计表-品类{$spuType}"][$ppriceCell] = "=$spriceCell-$mpriceCell-$bpriceCell";
                $line++;
            }
            $excelData["月度毛利统计表-品类{$spuType}"]['line'] = $line - 6;
        }
        return $excelData;
    }

    /**
     * @param $data
     * @param $typeName
     * @return string
     */
    private function export_report($data, $typeName)
    {
        $template = C("FILE_TPLS") . $typeName . ".xlsx";
        $saveDir = C("FILE_SAVE_PATH") . $typeName;

        $fileName = md5(json_encode($data)) . ".xlsx";
        vendor('PHPExcel.class');
        vendor('PHPExcel.IOFactory');
        vendor('PHPExcel.Writer.Excel2007');
        vendor("PHPExcel.Reader.Excel2007");
        $objReader = new \PHPExcel_Reader_Excel2007();
        $objPHPExcel = $objReader->load($template);    //加载excel文件,设置模板

        $templateSheet = $objPHPExcel->getSheet(0);


        foreach ($data as $sheetName => $list) {
            $line = $list['line'];
            unset($list['line']);

            $excelSheet = $templateSheet->copy();

            $excelSheet->setTitle($sheetName);
            //创建新的工作表
            $sheet = $objPHPExcel->addSheet($excelSheet);
            if ($typeName != "053" && $line > 11) {
                $addLine = $line - 11;
                $sheet->insertNewRowBefore(11, $addLine);   //在行3前添加n行
            }
            if ($typeName == "053") {

                if (isset($list['mell'])) {
                    $mellList = $list['mell'];
                    unset($list['mell']);
                }
                if (isset($list['insert'])) {
                    foreach ($list['insert'] as $line => $addLine) {
                        $sheet->insertNewRowBefore($line, $addLine);   //在行3前添加n行
                    }
                    unset($list['insert']);
                }
            }
//        exit();

            foreach ($list as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            if (isset($mellList)) {
                foreach ($mellList as $mell) {
                    $sheet->mergeCells($mell);
                }
            }

        }
        //移除多余的工作表
        $objPHPExcel->removeSheetByIndex(0);
        //设置保存文件名字

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        if (!file_exists($saveDir)) {
            mkdir("$saveDir");
        }
        $objWriter->save($saveDir . "/" . $fileName);
        return $fileName;
    }
}