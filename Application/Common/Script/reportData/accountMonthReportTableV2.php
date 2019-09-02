<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/10
 * Time: 11:22
 * 项目组月度毛利统计表
 * 20190523新改版
 */

//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
venus_script_begin("开始获取财务部月度财务报表数据");

$skuCodeExtra = array(
    "110" => array(
        "SK0001012", "SK0000697", "SK0000696"
    )
);
$warData = array();
$accountMonthData = array();
$warExcelData = array();
$warFileData = "";

$accountMonthData = getMonthDataPMPT($stime, $etime, $skuCodeExtra);
$warData = $accountMonthData["war"];

$warExcelData = get_war_excel_data_pmpt($warData, $stime, $etime);
$warFileData = export_report_pmpt($warExcelData, "0502");
$fileArr["财务部月度财务报表"]["0502"] = $warFileData;


/**
 * @param $stime
 * @param $etime
 * @param $skuCodeExtra免税货品
 * @return array
 * 获取订单中自营货品信息
 */
function getMonthDataPMPT($stime, $etime, $skuCodeExtra)
{
    $condition = array();
    $condition["order_ctime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND'
    );
    $condition["w_order_status"] = array('EQ', 3);
    $orderData = M("order")->where($condition)->field("order_code,order_ctime")->order("order_code desc")->limit(0, 1000000)->fetchSql(false)->select();
    $orderCodeArr = array_column($orderData, "order_code");
    $orderTimeArr = array();
    foreach ($orderData as $orderDatum) {
        $orderTimeArr[$orderDatum['order_code']] = $orderDatum['order_ctime'];
    }
    $ordergoodsCount = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice,goods.profit_price,goods.spu_bprice spu_bprice,goods.spu_count spu_count")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr)))
        ->count();
    $ordergoodsData = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice sprice,goods.profit_price,goods.spu_bprice bprice")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr), "goods.goods_count" => array("neq", 0)))
        ->order('goods.goods_code desc')->limit(0, $ordergoodsCount)->fetchSql(false)->select();

    $warData = array();
    $timeData = array();
    $spuTypeData = array();
    $returnDataArr = array();
    foreach ($ordergoodsData as $ordergoodsDatum) {
        $warCode = $ordergoodsDatum['war_code'];
        $dbName = C('WMS_CLIENT_DBNAME');
        $warName = M("$dbName.warehouse")->where(array("war_code" => $warCode))->getField("war_name");
        if (empty($warName)) {
            echo M("$dbName.warehouse")->where(array("war_code" => $warCode))->fetchSql(true)->getField("war_name");
            echo "找不到此项目组相关信息：" . $warCode;
            exit();
        }
        $orderCode = $ordergoodsDatum['order_code'];
        $orderTime = date("m/d", strtotime($orderTimeArr[$orderCode]));
        $spuName = $ordergoodsDatum['spu_name'];
        $spuTypeNum = $ordergoodsDatum['spu_type'];
        $spuType = venus_spu_type_name($ordergoodsDatum['spu_type']);
        $spuSubType = venus_spu_catalog_name($ordergoodsDatum['spu_subtype']);
        $spuBprice = $ordergoodsDatum['bprice'];
        $spuSprice = $ordergoodsDatum['sprice'];
        $spuPprice = $ordergoodsDatum["profit_price"];
        $skuCode = $ordergoodsDatum["sku_code"];
        if ($spuType == "鲜鱼水菜") continue;

        $skuCount = floatval($ordergoodsDatum['sku_count']);
        $spuCount = $ordergoodsDatum['spu_count'];
        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));
        $skuBprice = floatval(bcmul($spuBprice, $spuCount, 8));
        $skuPprice = floatval(bcmul($spuPprice, $spuCount, 8));
        $sprice = floatval(bcmul($skuSprice, $skuCount, 8));
        $bprice = floatval(bcmul($skuBprice, $skuCount, 8));
        $pprice = floatval(bcmul($skuPprice, $skuCount, 8));

        if (in_array($skuCode, $skuCodeExtra[$spuTypeNum])) {
            if ($spuType == "鸡鸭禽蛋") {
                $spuType = "鸡蛋(免税)";
            }
        }
        if ($spuSubType == "大餐酸奶") $spuType = "大餐酸奶";

        $warData[$warName][$spuType]['money'] = floatval(bcadd($warData[$warName][$spuType]['money'], $sprice, 8));
        $warData[$warName][$spuType]['bprice'] = floatval(bcadd($warData[$warName][$spuType]['bprice'], $bprice, 8));
        $warData[$warName][$spuType]['count'] = floatval(bcadd($warData[$warName][$spuType]['count'], $skuCount, 8));
    }

    $data = array(
        "war" => $warData,
        "return" => $returnDataArr
    );

    return $data;
}

/**
 * @param $warData项目维度数据
 * @param $stime开始时间
 * @param $etime结束时间
 * @return array
 */
function get_war_excel_data_pmpt($warData, $stime, $etime)
{
    $excelData = array();
    $timeCell = "C2";
    $sheetName = "财务部月度财务报表";
    $excelData[$sheetName][$timeCell] = "制表期间:" . $stime . "-" . $etime;
    $line = 6;
    foreach ($warData as $warName => $warDatum) {
        $numCell = 'A' . $line;
        $excelData[$sheetName][$numCell] = $line - 5;
        $warCell = 'B' . $line;
        $excelData[$sheetName][$warCell] = $warName;
        foreach ($warDatum as $spuType => $warItem) {
            if ($spuType == "鸡鸭禽蛋") {
                $spriceCell = 'C' . $line;//销售额
                $bpriceCell = 'D' . $line;//采购成本
                $ppriceCell = 'E' . $line;//毛利
                $pppriceCell = 'F' . $line;//毛利率
            } elseif ($spuType == "鸡蛋(免税)") {
                $spriceCell = 'G' . $line;//销售额
                $bpriceCell = 'H' . $line;//采购成本
                $ppriceCell = 'I' . $line;//毛利
                $pppriceCell = 'J' . $line;//毛利率
            } elseif ($spuType == "酒水饮料") {
                $spriceCell = 'K' . $line;//销售额
                $bpriceCell = 'L' . $line;//采购成本
                $ppriceCell = 'M' . $line;//毛利
                $pppriceCell = 'N' . $line;//毛利率
            } elseif ($spuType == "调味干货") {
                $spriceCell = 'O' . $line;//销售额
                $bpriceCell = 'P' . $line;//采购成本
                $ppriceCell = 'Q' . $line;//毛利
                $pppriceCell = 'R' . $line;//毛利率
            } elseif ($spuType == "米面粮油") {
                $spriceCell = 'S' . $line;//销售额
                $bpriceCell = 'T' . $line;//采购成本
                $ppriceCell = 'U' . $line;//毛利
                $pppriceCell = 'V' . $line;//毛利率
            } elseif ($spuType == "水产冻货") {
                $spriceCell = 'W' . $line;//销售额
                $bpriceCell = 'X' . $line;//采购成本
                $ppriceCell = 'Y' . $line;//毛利
                $pppriceCell = 'Z' . $line;//毛利率
            } elseif ($spuType == "休闲食品") {
                $spriceCell = 'AA' . $line;//销售额
                $bpriceCell = 'AB' . $line;//采购成本
                $ppriceCell = 'AC' . $line;//毛利
                $pppriceCell = 'AD' . $line;//毛利率
            } elseif ($spuType == "猪牛羊肉") {
                $spriceCell = 'AE' . $line;//销售额
                $bpriceCell = 'AF' . $line;//采购成本
                $ppriceCell = 'AG' . $line;//毛利
                $pppriceCell = 'AH' . $line;//毛利率
            } elseif ($spuType == "大餐酸奶") {
                $spriceCell = 'AI' . $line;//销售额
                $bpriceCell = 'AJ' . $line;//采购成本
                $ppriceCell = 'AK' . $line;//毛利
                $pppriceCell = 'AL' . $line;//毛利率
            } else {
                echo "war" . PHP_EOL;
                echo $warName . PHP_EOL;
                echo $spuType . PHP_EOL;
                echo "此一级分类不存在" . PHP_EOL;
                exit();
            }
            $excelData[$sheetName][$spriceCell] = $warItem['money'];
            $excelData[$sheetName][$bpriceCell] = $warItem['bprice'];
            $excelData[$sheetName][$ppriceCell] = "=$spriceCell-$bpriceCell";
            $excelData[$sheetName][$pppriceCell] = "=$ppriceCell/$spriceCell";
        }
        $totalSpriceCell = 'AM' . $line;//销售额
        $totalBpriceCell = 'AN' . $line;//采购成本
        $totalPpriceCell = 'AO' . $line;//毛利
        $totalPppriceCell = 'AP' . $line;//毛利率

        $excelData[$sheetName][$totalSpriceCell] = "=C$line+G$line+K$line+O$line+S$line+W$line+AA$line+AE$line+AI$line";
        $excelData[$sheetName][$totalBpriceCell] = "=D$line+H$line+L$line+P$line+T$line+X$line+AB$line+AF$line+AJ$line";
        $excelData[$sheetName][$totalPpriceCell] = "=$totalSpriceCell-$totalBpriceCell";
        $excelData[$sheetName][$totalPppriceCell] = "=$totalPpriceCell/$totalSpriceCell";
        $line++;
    }
    $excelData[$sheetName]["line"] = $line - 6;
    return $excelData;
}

/**
 * @param $data
 * @param $typeName
 * @return string
 */
function export_report_pmpt($data, $typeName)
{
    $template = C("FILE_TPLS") . $typeName . ".xlsx";
    $saveDir = C("FILE_SAVE_PATH") . $typeName;

    $fileName = md5(json_encode($data)) . ".xlsx";
    if (file_exists($fileName)) {
        return $fileName;
    }
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

function getFrequencyAllDataByStimeAndEtimePMPT($startTime, $endTime)
{
    return M("ordergoods")
        ->query("SELECT o.`war_code`,og.`sku_code`,spu.`spu_type`,o.order_ctime 
FROM `wms_ordergoods` og 
left join `wms_order` o on o.`order_code`=og.`order_code`
join `wms_spu` spu on spu.spu_code=og.spu_code
WHERE o.order_ctime>'{$startTime}' 
AND  o.order_ctime<'{$endTime}'");
}