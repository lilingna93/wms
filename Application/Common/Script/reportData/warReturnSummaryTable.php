<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/24
 * Time: 14:46
 */
venus_script_begin("开始获取仓配部退货统计表报表数据");
$excelData = array();
$fileName = "";

$excelData = getReturnDataWrst($stime, $etime);

$fileName = export_report_wrst($excelData, "058");
$fileArr["仓配部退货统计表"]["058"] = $fileName;

function getReturnDataWrst($stime, $etime)
{
    $condition = array();
    $condition["rt_addtime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND',
    );

    $returnTaskData = M("returntask")->where($condition)->field("rt_code,rt_addtime")->order('rt_addtime')->limit(0, 1000000)->select();
    $returnTaskCodes = array_column($returnTaskData, "rt_code");
    $returnAddTimeArr = array();
    foreach ($returnTaskData as $returnTaskDatum) {
        $returnAddTimeArr[$returnTaskDatum['rt_code']] = $returnTaskDatum['rt_addtime'];
    }
    $returnData = M("ordergoodsreturn")->alias("ogr")->field("*,ogr.war_code,ogr.spu_code,ogr.spu_bprice,ogr.supplier_code,ogr.timestamp")
        ->join("left join wms_spu spu on spu.spu_code=ogr.spu_code")
        ->where(array("rt_code" => array("in", $returnTaskCodes), "ogr.supplier_code" => "SU00000000000001", "ogr_status" => 2))
        ->order("ogr_code")
        ->limit(0, 1000000)->select();
    $excelData = array();
    foreach ($returnData as $returnDatum) {
        $status = $returnDatum["ogr_status"];
        if ($status != 2) continue;

        $rtCode = $returnDatum["rt_code"];//退货单单号
        $warName = $returnDatum["war_name"];//项目组名称
        $warCode=$returnDatum["war_code"];
        $isExternal = getWarIsExternalByWarCodeWrst($warCode);
        if(2==$isExternal){
            $sheetName="仓配部退货统计表(外部项目组)";
        }elseif(1==$isExternal){
            $sheetName="仓配部退货统计表(内部项目组)";
        }else{
            echo "找不到此项目组属于哪种项目组相关信息：" . $warCode;
            exit();
        }
        $spuName = $returnDatum["spu_name"];//sku名称
        $returnApplyCount = floatval($returnDatum['goods_count']);//申请退货时间
        $returnCount = floatval($returnDatum['actual_count']);//实收退货数量

        $spuSprice = $returnDatum["spu_sprice"];
        $spuCount = $returnDatum["spu_count"];
        $skuSprice = floatval(bcmul($spuSprice, $spuCount, 8));//sku单价
        $sprice = floatval(bcmul($skuSprice, $returnCount, 8));//退货总金额

        $returnApplytime = $returnDatum["apply_time"];//申请退货时间
        $returnFtime = $returnDatum["timestamp"];//实收退货时间
        $ogrType = venus_return_type_name($returnDatum["ogr_type"]);//退货原因
        $excelData[$sheetName][] = array(
            $rtCode, $warName, $spuName, $returnApplyCount, $returnApplytime, $returnCount, $returnFtime, $skuSprice, $sprice, $ogrType
        );

    }
    return $excelData;
}


/**
 * @param $data
 * @param $typeName
 * @return string
 */
function export_report_wrst($data, $typeName)
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
        $addLine = $line - 5;
        $sheet->insertNewRowBefore(4, $addLine);
//        exit();
        $lettersCount = 0;
        $line = 2;
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

function getWarIsExternalByWarCodeWrst($warCode)
{
    $datebase = C("WMS_CLIENT_DBNAME");
    return M("$datebase.warehouse")->where(array("war_code" => $warCode))->getField("war_is_external");
}