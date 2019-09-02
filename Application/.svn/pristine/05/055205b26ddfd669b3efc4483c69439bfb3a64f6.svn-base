<?php

namespace Wms\Service;

use Common\Service\ExcelService;
use Common\Service\PassportService;
use Wms\Service\SkuService;
use Wms\Dao\SkuDao;
use Wms\Dao\GoodsDao;
class StatusService {

    public $waCode;

    function __construct()
    {
        /*$workerData = PassportService::getInstance()->loginUser();
        if(empty($workerData)){
            venus_throw_exception(110);
        }*/
        $this->waCode = $workerData["war_code"];
    }
    //导入预警值模板
    public function gwarning_import(){
        $datas = ExcelService::getInstance()->upload("file");
        $dicts = array(
            "A" => "skuCode",//sku品类编号
            "B" => "skuName",//sku名称
            "E" => "firstWarning",//一级预警值
            "F" => "secondWarning",//二级预警值
        );

        $skuList = array();
        foreach ($datas as $sheetName => $list) {
            unset($list[0]);
            $skuList = array_merge($skuList, $list);
        }

        venus_db_starttrans();//启动事务
        $result = true;
        foreach ($skuList as $index => $skuItem) {
            $skuData = array();
            foreach ($dicts as $col => $key) {
                $skuData[$key] = isset($skuItem[$col]) ? $skuItem[$col] : "";
            }

            if (trim($skuData['skuCode']) == '' || trim($skuData['skuName']) == '') {
                if (trim($skuData['firstWarning']) == '' && trim($skuData['secondWarning']) == '') {
                    continue;
                } else {
                    if (trim($skuData['skuCode']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "货品编号不能为空");
                        return false;
                    }

                    if (trim($skuData['skuName']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "货品名称不能为空");
                        return false;
                    }

                    if (trim($skuData['firstWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品不足报警值不能为空");
                        return false;
                    }

                    if (trim($skuData['secondWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品严重不足报警值不能为空");
                        return false;
                    }
                }
            } else {
                $result = $result && GoodsDao::getInstance($warCode)->updateWarningBySkuCode($skuData['skuCode'],$skuData['firstWarning'], $skuData['secondWarning'] );
                \Think\Log::write(json_encode($result.'--'.$skuData['skuCode']),'zk0311');
            }
        }
        if ($result) {
            venus_db_commit();
            /*$SkuService = new SkuService();
            $SkuService->release_latestsku();*/
            $success = true;
            $message = "导入商品预警值成功";

        } else {
            venus_db_rollback();
            $success = false;
            $message = "导入商品预警值失败";
        }
        return array($success, "", $message);
    }

    //导出商品预警值
    public function gwarning_export()
    {
        $goodsData = GoodsDao::getInstance($warCode)->goodsList();
        $warnData = array();
        $fname = "所有商品预警值";
        $header = array("货品编号", "名称","一级名称", "二级名称", "库存不足预警值", "库存严重不足预警值");
        
        foreach ($goodsData as $index => $goodsItem) {
            $goodsList = array(
                "skCode" => $goodsItem['sku_code'],
                "spName" => $goodsItem['spu_name'],
                "class_1" => venus_spu_type_name($goodsItem['spu_type']),
                "class_2" => venus_spu_catalog_name($goodsItem['spu_subtype']),
                "swarn1" => $goodsItem['sku_warning_1'],
                "swarn2" => $goodsItem['sku_warning_2'],
            );
            $warnData[$fname][] = array(
                    $goodsList['skCode'],$goodsList['spName'],$goodsList['class_1'],$goodsList['class_2'],$goodsList['swarn1'],$goodsList['swarn2']
                );
        }
        $fileName = ExcelService::getInstance()->exportExcel($warnData, $header, "001");
        if ($fileName) {
            $success = true;
            $data = $fileName;
            $message = "导出成功";
            return array($success, $data, $message);
        } else {
            $success = false;
            $data = "";
            $message = "下载失败";
        }
        return array($success, $data, $message);
    }
    
    public function gwarning_email(){
        $success = true;
        $data = array(
            'email' => array(
                "yu.gao@shijijiaming.com",
                "xiaolong.hu@shijijiaming.com",
                "jinwei.cao@shijijiaming.com",
                "panjing@shijijiaming.com",
                "wangyantao@shijijiaming.com",       
            )
        );
        $message = "信息展示";

        return array($success, $data, $message);
    }

    
}



