<?php

namespace Wms\Debug;

class SkuService {
    //1.SKU搜索
    public function sku_search() {
        $results = "123";
        if (empty($results)) {
            $data["pageCurrent"] = 0;
            $data["pageSize"] = "100";
            $data["totalCount"] = 0;
            $data["list"] = array();
            return array($success, $data, $message);
        } else {

            $data = array("pageCurrent" => "0",
                "pageSize" => "10",
                "totalCount" => "100"
            );
            $data['list'][] = array("skCode" => "SK20390987653092",
                "spCode" => "SP31029098789039",
                "spName" => "京华香米",
                "spCount" => "50",
                "skUnit" => "袋",
                "skNorm" => "50斤/袋",
                "skStatus" => "1"
            );
            $data['list'][] = array("skCode" => "SK32309809876543",
                "spCode" => "SP40392098765390",
                "spName" => "吾尝吾愿稻花香大米",
                "spCount" => "45",
                "skUnit" => "袋",
                "skNorm" => "45斤/袋",
                "skStatus" => "2"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //2.SKU所选设为上线
    public function status_online() {
        if (empty($_POST['data']['skCode']) || empty($_POST['data']['skStatus'])) {
//            $data = array("success" => false, "msg" => "请选择要上线的货品");
            $success = false;
            $data = "";
            $message = "请选择要上线的货品";
        } else {
//            $data = array("success" => true, "msg" => "所选sku上线成功");
            $success = true;
            $data = "";
            $message = "所选sku上线成功";
        }
        return array($success, $data, $message);
    }

    //3.SKU所选设为下线
    public function status_offline() {
        if (empty($_POST['data']['skCode']) || empty($_POST['data']['skStatus'])) {
//            $data = array("success" => false, "msg" => "请选择要下线的货品");
            $success = false;
            $data = "";
            $message = "请选择要下线的货品";
        } else {
//            $data = array("success" => true, "msg" => "所选sku下线成功");
            $success = true;
            $data = "";
            $message = "所选sku下线成功";
        }
        return array($success, $data, $message);
    }

    //4.发布最新SKU
    public function publish() {
        if (empty($_POST['data']['push'])) {
//			$data = array("success" => false, "msg" => "发布失败");
            $success = false;
            $data = "";
            $message = "发布失败";
        } else {
//			$data = array("success" => true, "msg" => "发布最新sku成功");
            $success = true;
            $data = "";
            $message = "发布最新sku成功";
        }
        return array($success, $data, $message);
    }
}



