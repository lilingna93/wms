<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/8
 * Time: 14:32
 */
//在命令行中输入 chcp 65001 回车, 控制台会切换到新的代码页,新页面输出可为中文
venus_script_begin("开始获取财务部供应商统计表数据");

$ctimeArr = array();
$recData = array();
$returnData = array();
$excelData = array();
$fileName ="";


$recData = getRecDataRarr($stime, $etime);
$returnData = getReturnDataRarr($stime, $etime);
ksort($ctimeArr);

$excelData = getExcelDataRarr($recData, $returnData);
$fileName = export_report_rarr($excelData, "055");
$fileArr["财务部供应商统计表"]["055"] = $fileName;

function getReturnDataRarr($stime, $etime)
{
    global $ctimeArr;
    $igsSupDataList = array();
    $condition = array(
        "inv_ctime" => array(array('EGT', $stime), array('ELT', $etime), 'AND'),
        "inv.inv_type" => 6,
    );
    $igsDataCount = M("igoodsent")
        ->alias("igs")
        ->join("LEFT JOIN `wms_goodstored` gs ON gs.`gs_code`=igs.`gs_code`")
        ->join("LEFT JOIN `wms_goodsbatch` gb ON gb.gb_code=gs.gb_code")
        ->join("JOIN `wms_invoice` inv ON inv.`inv_code`=igs.inv_code")
        ->where($condition)->count();
    $igsDataList = M("igoodsent")
        ->alias("igs")
        ->field("igs.igs_ctime,igs.sku_count,igs.sku_code,igs.spu_code,igs.igs_bprice,igs.gs_code,inv.inv_type,inv.inv_ctime,gb.sup_code,rec.rec_ctime,sku.spu_count")
        ->join("LEFT JOIN `wms_goodstored` gs ON gs.`gs_code`=igs.`gs_code`")
        ->join("LEFT JOIN `wms_goodsbatch` gb ON gb.gb_code=gs.gb_code")
        ->join("LEFT JOIN `wms_receipt` rec ON rec.rec_code=gb.rec_code")
        ->join("JOIN `wms_sku` sku ON sku.sku_code=gb.sku_code")
        ->join("JOIN `wms_invoice` inv ON inv.`inv_code`=igs.inv_code")
        ->where($condition)
        ->limit(0, $igsDataCount)->select();
    foreach ($igsDataList as $igsData) {
        $supCode = $igsData["sup_code"];
        $skuCode = $igsData["sku_code"];
        $spuCode = $igsData["spu_code"];
        $spuCount = $igsData["spu_count"];
        $skuCount = $igsData["sku_count"];
        $bprice = $igsData["igs_bprice"];
        $ctime = $igsData["igs_ctime"];
        $money = floatval(bcmul($bprice, bcmul($skuCount,$spuCount,4), 6));
        if (!in_array($ctime, $ctimeArr[$supCode])) $ctimeArr[$supCode][] = $ctime;
        asort($ctimeArr[$supCode]);
        $igsSupDataList[$supCode][$ctime][$spuCode]["money"] = bcadd($igsSupDataList[$supCode][$ctime][$spuCode]["money"], $money, 2);
        $igsSupDataList[$supCode][$ctime][$spuCode]["count"] = bcadd($igsSupDataList[$supCode][$ctime][$spuCode]["count"], $skuCount, 2);
        $igsSupDataList[$supCode][$ctime][$spuCode]["bprice"] = bcdiv($igsSupDataList[$supCode][$ctime][$spuCode]["money"], $igsSupDataList[$supCode][$ctime][$spuCode]["count"], 2);
    }
//    echo json_encode($igsDataList);
//    echo json_encode($igsSupDataList);
//    exit();
    return $igsSupDataList;
}


function getRecDataRarr($stime, $etime)
{
    global $ctimeArr;
    $condition = array(
        "rec_ctime" => array(array('EGT', $stime), array('ELT', $etime), 'AND'),
        "rec_type" => 1,
        "rec_mark" => array("notlike", "OT%")
    );
    $gbDataCount = M("goodsbatch")
        ->alias("gb")
        ->join("LEFT JOIN `wms_receipt` rec ON rec.rec_code=gb.rec_code")
        ->where($condition)->count();
    $gbDataList = M("goodsbatch")
        ->alias("gb")
        ->field("gb.gb_ctime,gb.gb_bprice,gb.spu_code,gb.sku_code,gb.sku_count,gb.sup_code,sku.spu_count")
        ->join("LEFT JOIN `wms_sku` sku ON sku.sku_code=gb.sku_code")
        ->join("JOIN `wms_receipt` rec ON rec.rec_code=gb.rec_code")
        ->where($condition)
        ->limit(0, $gbDataCount)->select();
    $gbSupDataList = array();
    foreach ($gbDataList as $gbData) {
        $supCode = $gbData["sup_code"];
        $skuCode = $gbData["sku_code"];
        $spuCode = $gbData["spu_code"];
        $skuCount = $gbData["sku_count"];
        $spuCount = $gbData["spu_count"];
        $bprice = $gbData["gb_bprice"];
        $ctime = $gbData["gb_ctime"];
        $money = floatval(bcmul($bprice, bcmul($skuCount,$spuCount,4), 6));
        if (!in_array($ctime, $ctimeArr[$supCode])) $ctimeArr[$supCode][] = $ctime;
        asort($ctimeArr[$supCode]);
        $gbSupDataList[$supCode][$ctime][$spuCode]["money"] = bcadd($gbSupDataList[$supCode][$ctime][$spuCode]["money"], $money, 2);
        $gbSupDataList[$supCode][$ctime][$spuCode]["count"] = bcadd($gbSupDataList[$supCode][$ctime][$spuCode]["count"], $skuCount, 2);
        $gbSupDataList[$supCode][$ctime][$spuCode]["bprice"] = bcdiv($gbSupDataList[$supCode][$ctime][$spuCode]["money"], $gbSupDataList[$supCode][$ctime][$spuCode]["count"], 2);
    }
    return $gbSupDataList;
}


function getSupNameByCodeRarr($supCode)
{
    return M("supplier")->where(array("sup_code" => $supCode))->getField("sup_name");
}

function getSpuNameByCodeRarr($spuCode)
{
    return M("spu")->where(array("spu_code" => $spuCode))->getField("spu_name");
}

function getExcelDataRarr($recData, $returnData)
{
    $excelData = array();
    global $ctimeArr;
    foreach ($ctimeArr as $supCode => $ctimes) {
        $supName = getSupNameByCodeRarr($supCode);
        foreach ($ctimes as $ctime) {
            if (array_key_exists($supCode, $recData) && array_key_exists($ctime, $recData[$supCode])) {
                foreach ($recData[$supCode][$ctime] as $spuCode => $recDatum) {
                    $spuName = getSpuNameByCodeRarr($spuCode);
                    $excelData["财务部供应商统计表"][] = array($supName, $ctime, $spuName, $recDatum['count'], $recDatum['bprice'], $recDatum['money'], '', '', '', '', '');
                }
            }
            if (array_key_exists($supCode, $returnData) && array_key_exists($ctime, $returnData[$supCode])) {
                foreach ($returnData[$supCode][$ctime] as $spuCode => $returnDatum) {
                    $spuName = getSpuNameByCodeRarr($spuCode);
                    $excelData["财务部供应商统计表"][] = array($supName, '', '', '', '', '', $ctime, $spuName, $returnDatum['count'], $returnDatum['bprice'], $returnDatum['money']);
                }
            }
        }

    }
    return $excelData;
}

/**
 * @param $data
 * @param $typeName
 * @return string
 */
function export_report_rarr($data, $typeName)
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
        $line = count($list);

        $excelSheet = $templateSheet->copy();

        $excelSheet->setTitle($sheetName);
        //创建新的工作表
        $sheet = $objPHPExcel->addSheet($excelSheet);
        $addLine = $line - 2;
        $sheet->insertNewRowBefore(4, $addLine);
//        exit();
        $lettersCount = 0;
        $line = 3;
        $lettersLength = count($list[0]);
        $letters = array();
        for ($letter = 0; $letter < $lettersLength; $letter++) {
            $letterCell = getLettersCell($letter);
            $letters[] = $letterCell;
        }
        foreach ($list as $index => $arr) {
            //输出数据
            foreach ($arr as $i => $value) {
                $sheet->setCellValue("$letters[$i]$line", $value);
            }
            $line++;
            if ($lettersCount < $lettersLength) {
                $lettersCount = $lettersLength;
            }
        }
        $total = "=F{$line}+K{$line}";
        $line++;
        $totalCell = "C" . $line;
        $sheet->setCellValue("$totalCell", $total);
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