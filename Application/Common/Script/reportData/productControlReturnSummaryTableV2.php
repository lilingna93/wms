<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2019/5/24
 * Time: 14:46
 */
venus_script_begin("开始获取品控部追溯表报表数据");

$excelData = array();
$fileName = "";

$excelData = getOrdergoodsDataPcrst($stime, $etime);

$fileName = export_report_pcrst($excelData, "059");
$fileArr["品控部追溯表"]["059"] = $fileName;


function getOrdergoodsDataPcrst($stime, $etime)
{
    $condition = array();
    $condition["order_ctime"] = array(
        array('EGT', $stime),
        array('ELT', $etime),
        'AND'
    );
    $condition["w_order_status"] = 3;
    $orderData = M("order")->where($condition)->field("order_code,order_ctime,war_code,order_pdate")->order("order_code desc")->limit(0, 1000000)->fetchSql(false)->select();
    $orderCodeArr = array_column($orderData, "order_code");
    $orderInvCodeData = array();
    foreach ($orderCodeArr as $orderCode) {
        $invCode = getInvCodeByOcodePcrst($orderCode);
        if (!empty($invCode)) {
            $orderInvCodeData[$orderCode] = $invCode;
        }
    }
    $orderWarNameData = array();
    $orderPdateData = array();
    foreach ($orderData as $orderDatum) {
        $orderPdateData[$orderDatum['order_code']] = $orderDatum['order_pdate'];
        $warCode = $orderDatum['war_code'];
        $warName = getWarNameByWarCodePcrst($warCode);
        $orderWarNameData[$orderDatum['order_code']] = empty($orderDatum['room']) ? $warName : $warName . "(" . $orderDatum['room'] . ")";
    }

    $ordergoodsCount = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice,goods.profit_price,goods.spu_bprice spu_bprice,goods.spu_count spu_count")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr), "goods.supplier_code" => "SU00000000000001"))
        ->count();
    $ordergoodsData = M("ordergoods")->alias("goods")
        ->field("*,goods.spu_code,goods.sku_code,goods.war_code,goods.supplier_code,
        goods.spu_sprice sprice,goods.profit_price,goods.spu_bprice bprice")
        ->join("left join wms_sku sku on sku.sku_code=goods.sku_code")
        ->join("left join wms_spu spu on spu.spu_code=sku.spu_code")
        ->where(array("goods.order_code" => array("in", $orderCodeArr), "goods.supplier_code" => "SU00000000000001"))
        ->order('goods.goods_code desc')->limit(0, $ordergoodsCount)->fetchSql(false)->select();

    $excelData = array();
    foreach ($ordergoodsData as $ordergoodsDatum) {
        $skuCode = $ordergoodsDatum['sku_code'];
        $orderCode = $ordergoodsDatum['order_code'];
        $warCode=$ordergoodsDatum['war_code'];
        $isExternal = getWarIsExternalByWarCodePcrst($warCode);
        if (2 == $isExternal) {
            $sheetName = "品控部追溯表(外部项目组)";
        } elseif (1 == $isExternal) {
            $sheetName = "品控部追溯表(内部项目组)";
        } else {
            echo "找不到此项目组属于哪种项目组相关信息：" . $warCode;
            exit();
        }
        $invCode = $orderInvCodeData[$orderCode];
        $gsData = getGsDataByInvCodeAndSkuCodePcrst($invCode, $skuCode);
        foreach ($gsData as $index => $gsDatum) {
            $gsCode = $gsDatum['gs_code'];
            $skuCount = $gsDatum['sku_count'];
            $recCode = getRecCodeByGsCodePcrst($gsCode);
            $supCode = $gsDatum['sup_code'];
            $supName = getSupNameBySupCodePcrst($supCode);
            if ($index == 0) {
                $excelData[$sheetName][] = array(
                    $orderWarNameData[$orderCode],//项目名称（分餐厅）
                    $orderCode,//订单号
                    $orderPdateData[$orderCode],//送货日期
                    $ordergoodsDatum['sku_code'],//sku编号
                    $ordergoodsDatum['spu_name'],//sku名称
                    $supName,//供货商名称
                    venus_spu_type_name($ordergoodsDatum['spu_type']),//一级分类
                    venus_spu_catalog_name($ordergoodsDatum['spu_subtype']),//二级分类
                    $ordergoodsDatum['spu_brand'],//品牌
                    $ordergoodsDatum['spu_cunit'],//sku计量单位
                    $ordergoodsDatum['sku_norm'],//sku规格
                    $ordergoodsDatum['sku_init'],//订单数量
                    $ordergoodsDatum['sku_count'],//实收数量
                    $skuCount,//此批次数量
                    $invCode,//出库单号
                    $recCode,//入库单号
                );
            } else {
                $excelData[$sheetName][] = array(
                    $orderWarNameData[$orderCode],//项目名称（分餐厅）
                    $orderCode,//订单号
                    $orderPdateData[$orderCode],//送货日期
                    $ordergoodsDatum['sku_code'],//sku编号
                    $ordergoodsDatum['spu_name'],//sku名称
                    $supName,//供货商名称
                    venus_spu_type_name($ordergoodsDatum['spu_type']),//一级分类
                    venus_spu_catalog_name($ordergoodsDatum['spu_subtype']),//二级分类
                    $ordergoodsDatum['spu_brand'],//品牌
                    $ordergoodsDatum['spu_cunit'],//sku计量单位
                    $ordergoodsDatum['sku_norm'],//sku规格
                    "",//订单数量
                    "",//实收数量
                    $skuCount,//此批次数量
                    $invCode,//出库单号
                    $recCode,//入库单号
                );
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
function export_report_pcrst($data, $typeName)
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

function getWarNameByWarCodePcrst($warCode)
{
    $datebase = C("WMS_CLIENT_DBNAME");
    return M("$datebase.warehouse")->where(array("war_code" => $warCode))->getField("war_name");
}

function getWarIsExternalByWarCodePcrst($warCode)
{
    $datebase = C("WMS_CLIENT_DBNAME");
    return M("$datebase.warehouse")->where(array("war_code" => $warCode))->getField("war_is_external");
}

function getInvCodeByOcodePcrst($oCode)
{
    return M("invoice")->where(array("inv_ecode" => $oCode, "inv_mark" => "小程序单(自营)"))->getField("inv_code");
}

function getGsDataByInvCodeAndSkuCodePcrst($invCode, $skuCode)
{
    return M("igoodsent")
        ->alias("igs")
        ->field("igs.sku_code,igs.sku_count,igs.gs_code,gb.sup_code")
        ->join("left join `wms_goodstored` gs on gs.gs_code=igs.gs_code")
        ->join("left join `wms_goodsbatch` gb on gs.gb_code=gb.gb_code")
        ->where(array("igs.sku_code" => $skuCode, "igs.inv_code" => $invCode))->select();
}

function getRecCodeByGsCodePcrst($gsCode)
{
    return M("goodstored")
        ->alias("gs")
        ->join("left join `wms_goodsbatch` gb on gb.gb_code=gs.gb_code")
        ->where(array("gs.gs_code" => $gsCode))->getField("rec_code");
}

function getSupNameBySupCodePcrst($supCode)
{
    return M("supplier")->where(array("sup_code" => $supCode))->getField("sup_name");
}
