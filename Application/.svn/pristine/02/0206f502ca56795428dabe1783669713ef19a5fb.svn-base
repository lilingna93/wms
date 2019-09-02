<?php
ini_set('memory_limit','256M');
define('APP_DIR', dirname(__FILE__) . '/../../../');
define('APP_DEBUG', true);
define('APP_MODE', 'cli');
define('APP_PATH', APP_DIR . './Application/');
define('RUNTIME_PATH', APP_DIR . './Runtime_script/'); // 系统运行时目录
require APP_DIR . './ThinkPHP/ThinkPHP.php';

use Wms\Dao\SpuDao;
use Wms\Dao\SkuDao;
use Common\Service\ExcelService;

$time = venus_script_begin("初始化Venus数据库的SPUSKU数据");

$files = "C:/Users/gfz_1/Desktop/spu/spu.xlsx";
//echo file_exists($files)?"yes":"no";exit();
$datas = ExcelService::GetInstance()->uploadByShell($files);
array_pop($datas);//过滤最后一个类型说明表
$dicts = array(
    "A" => "sku_code",//sku品类编号
    "C" => "spu_subtype",//spu二级分类编号
    "E" => "spu_brand",//spu品牌
    "F" => "sku_norm",//sku规格
    "G" => "sku_unit",//sku单位
    "H" => "spu_from",//spu货品产地
    "I" => "spu_storetype",//spu仓储方式
    "M" => "sku_mark",//sku备注
    "P" => "spu_count",//单位sku含spu数量
    "Q" => "spu_name",//spu货品名称
    "R" => "spu_norm",//spu规格
    "S" => "spu_unit",//spu计量单位
    "T" => "spu_img",//spu图片
    "U" => "spu_bprice",//spu采购价
    "V" => "spu_sprice",//spu销售价
    "N" => "spu"//spu销售价
);

$skuList = array();

foreach ($datas as $sheetName => $list) {
    unset($list[0]);
    $skuList = array_merge($skuList, $list);
}
$dataArr = array();
venus_db_starttrans();//启动事务
$result = true;
$spuCount = SpuDao::getInstance()->queryCountByCondition();
if ($spuCount > 0) {
    $i = $spuCount;
} else {
    $i = 0;
}

foreach ($skuList as $index => $skuItem) {

    $skuData = array();
    foreach ($dicts as $col => $key) {
        $skuData[$key] = isset($skuItem[$col]) ? $skuItem[$col] : "";
    }

    $skuData['sku_status'] = 1;
    if (!empty($skuData['spu_subtype']) && strlen($skuData['spu_subtype']) <= 5) {
        $skuData['spu_type'] = substr($skuData['spu_subtype'], 0, 3);//一级分类编号

    }else if(!empty($skuData['spu_subtype']) && strlen($skuData['spu_subtype']) > 5){
        venus_throw_exception(5004, $skuData['spu_name']);
    }

    if (trim($skuData['spu_name']) == '' || trim($skuData['spu_subtype']) == '' || trim($skuData['spu_storetype']) == '') {
        if (trim($skuData['spu_name']) == '' && trim($skuData['spu_subtype']) == '' && trim($skuData['spu_storetype']) == '') {
            continue;
        } else {
            //品类名称不能为空
            if (empty($skuData['spu_name'])) {
                venus_db_rollback();//回滚事务
                venus_throw_exception(1, "货品名称不能为空");
                return false;
            }

            // 二级类目不能为空
            if (empty($skuData['spu_subtype'])) {
                venus_db_rollback();//回滚事务
                venus_throw_exception(1, "货品二级分类不能为空");
                return false;
            }

            // sku货品规格不能为空
            if (empty($skuData['sku_norm'])) {
                venus_db_rollback();//回滚事务
                venus_throw_exception(1, "sku货品规格不能为空");
                return false;
            }

            // sku货品规格不能为空
            if (empty($skuData['spu_unit'])) {
                venus_db_rollback();//回滚事务
                venus_throw_exception(1, "spu货品单位不能为空");
                return false;
            }

            // 仓储方式（常温，冷冻，冷藏）不能为空
            if (empty($skuData['spu_storetype'])) {
                venus_db_rollback();//回滚事务
                venus_throw_exception(1, "货品仓储方式不能为空");
                return false;
            }
        }
    } else {
        $condition = array(
            "spu_subtype" => $skuData['spu_subtype'],
            "spu_brand" => $skuData['spu_brand'],
            "sku_norm" => $skuData['sku_norm'],
            "sku_unit" => $skuData['sku_unit'],
            "spu_storetype" => $skuData['spu_storetype'],
            "sku_mark" => $skuData['sku_mark'],
            "spu_count" => $skuData['spu_count'],
            "spu_name" => $skuData['spu_name'],
            "spu_norm" => $skuData['spu_norm'],
            "spu_unit" => $skuData['spu_unit']
        );

        $jsonCon = json_encode($condition);
        if (in_array($jsonCon, $dataArr)) {//检测excel表里是否有重复的数据
            $redata = json_decode($jsonCon, true);
            $name = $redata['spu_name'];
            venus_throw_exception(5001, $name);
        } else {
            $dataArr[] = $jsonCon;
            //检测数据库是否已存在该品类
            $cond = array(
                "spu_subtype" => $skuData['spu_subtype'],
                "spu_brand" => $skuData['spu_brand'],
                "spu_storetype" => $skuData['spu_storetype'],
                "spu_mark" => $skuData['sku_mark'],
                "spu_count" => $skuData['spu_count'],
                "spu_name" => $skuData['spu_name'],
                "spu_norm" => $skuData['spu_norm'],
                "spu_unit" => $skuData['spu_unit']
            );
            $getField = 'spu_name';
            $totalCount = SpuDao::getInstance()->queryOneByCondition($cond,$getField);
            if(!empty($totalCount)){
                venus_throw_exception(5002, $totalCount);
            }
            $i++;
            $spuCode = "SP" . str_pad($i, 6, "0", STR_PAD_LEFT);

            if ($skuData['spu_img']) {
                $oldDir = "C:/Users/gfz_1/Desktop/spu/spuimg/";
                $newDir = "C:/Users/gfz_1/Desktop/spu/spuimages/";
                if (file_exists($oldDir . $skuData['spu_img'] . ".jpg")) {
                    $files = $oldDir . $skuData['spu_img'] . ".jpg";
                    $newName = $newDir . $spuCode . ".jpg";
                } else if (file_exists($oldDir . $skuData['spu_img'] . ".png")) {
                    $files = $oldDir . $skuData['spu_img'] . ".png";
                    $newName = $newDir . $spuCode . ".jpg";
                } else {
//                    venus_throw_exception(5003, $skuData['spu_img']);
                }
                copy($files, $newName);

                $spuImg = $spuCode . ".jpg";
            } else {
                $spuImg = "";
            }
//            $skumark = $skuData["sku_mark"];
            $spuDatas = array(
                "spu_code" => $spuCode,
                "spu_type" => $skuData['spu_type'],
                "spu_subtype" => $skuData["spu_subtype"],
                "spu_brand" => $skuData["spu_brand"],
                "spu_storetype" => $skuData["spu_storetype"],
                "spu_name" => $skuData["spu_name"],
                "spu_from" => $skuData["spu_from"],
                "spu_norm" => $skuData["spu_norm"],
                "spu_unit" => $skuData["spu_unit"],
                "spu_mark" => $skuData["sku_mark"],//$skuData["spu"].(empty($skumark) ? "":",{$skumark}")
                "spu_img" => $spuImg,
                "spu_bprice" => $skuData["spu_bprice"],
                "spu_sprice" => $skuData["spu_sprice"],
                "war_code" => "WA000001"
            );

            if (empty($skuData["sku_code"])) {
                $skuCode = "SK" . str_pad($i, 7, "0", STR_PAD_LEFT);
                $spuData = SpuDao::GetInstance("WA000001")->insert($spuDatas);
                $skuDatas = array(
                    "sku_code" => $skuCode,
                    "sku_norm" => $skuData["sku_norm"],
                    "sku_unit" => $skuData["sku_unit"],
                    "sku_mark" => $skuData["sku_mark"],
                    "spu_count" => $skuData["spu_count"],
                    "spu_code" => $spuCode,
                    "war_code" => "WA000001"
                );
                $result = $result && SkuDao::GetInstance()->insert($skuDatas);

            }
            if (!$result) {
                venus_db_rollback();
                return false;
            }
        }
    }
}

if ($result) {
    venus_db_commit();
    return true;
} else {
    venus_db_rollback();
    return false;
}







