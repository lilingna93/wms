<?php

namespace Wms\Debug;

class SpuService {
    //1.SPU搜索.
    public function spu_search() {

        $results = "123";
        if (empty($results)) {
            $data["pageCurrent"] = 0;
            $data["pageSize"] = "1";
            $data["totalCount"] = 0;
            $data["list"] = array();
            return array($success, $data, $message);
        } else {
            $data = array("pageCurrent" => "0",
                "pageSize" => "1",
                "totalCount" => "2"
            );
            $data['list'][] = array("spCode" => "SP30291029087654",
                "spName" => "色拉油",
                "spUnit" => "桶",
                "spNorm" => "400ml",
                "spBprice" => "45.00",
                "spSprice" => "55.00",
                "cltPercent" => "0.13",
                "cltProfit" => "5.5",
                "cltSprice" => "52.00",
                "supCode" => "SU00000000000001",
                "supName" => "肉类冻货供应商"
            );
            $data['list'][] = array("spCode" => "SP30606170641629",
                "spName" => "花生油",
                "spUnit" => "桶",
                "spNorm" => "600ml",
                "spBprice" => "43.00",
                "spSprice" => "51.00",
                "cltPercent" => "0.21",
                "cltProfit" => "8",
                "cltSprice" => "56.00",
                "supCode" => "SU00000000000002",
                "supName" => "粮油类供应商"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //2.SPU导入客户利润率
    public function percent_import() {
//        $data = array("success" => true, "msg" => "导入客户利润成功");
        $success = true;
        $data = "";
        $message = "导入客户利润成功";
        return array($success, $data, $message);
    }

    //3.SPU导入内部销售价
    public function sprice_import() {

//        $data = array("success" => true, "msg" => "导入内部销售价成功");
        $success = true;
        $data = "";
        $message = "导入内部销售价成功";
        return array($success, $data, $message);
    }

    //4.SPU导入内部采购价
    public function bprice_import() {

//        $data = array("success" => true, "msg" => "导入内部采购价成功");
        $success = true;
        $data = "";
        $message = "导入内部采购价成功";
        return array($success, $data, $message);
    }

    //5.SPU导入供货商设置
    public function supplier_import() {

//        $data = array("success" => true, "msg" => "导入供货商设置成功");
        $success = true;
        $data = "";
        $message = "导入供货商设置成功";
        return array($success, $data, $message);
    }

    //6.下载全部SPU数据
    public function spu_export() {

        $fileName = "SPUhuopinshujubiao";
        if ($fileName) {
//			$data = array("success" => true, "fileName" => $fileName);
            $success = true;
            $data = $fileName;
            $message = "";
        } else {
//			$data = array("success" => false, "msg" => "下载失败");
            $success = false;
            $data = "";
            $message = "下载失败";
        }
        return array($success, $data, $message);
    }

}



