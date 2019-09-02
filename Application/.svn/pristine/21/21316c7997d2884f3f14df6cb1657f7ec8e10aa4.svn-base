<?php

namespace Wms\Debug;

class OrderService {
    //1.查询订单
    public function ord_search() {

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
            $data['list'][] = array("oCode" => "re33060617064162",
                "oCtime" => "2018-07-11",
                "oPdate" => "2018-07-12",
                "oStatus" => "1",
                "oBprice" => "45.00",
                "oSprice" => "55.00",
                "oProfit" => "11.30",
                "cltSprice" => "55.00",
                "cltProfit" => "0.00",
                "warName" => "市委党校",
                "uName" => "小李",
                "uPhone" => "13963738902"
            );
            $data['list'][] = array("oCode" => "re30606170641628",
                "oCtime" => "2018-07-13",
                "oPdate" => "2018-07-15",
                "oStatus" => "2",
                "oBprice" => "75.00",
                "oSprice" => "88.00",
                "oProfit" => "11.30",
                "cltSprice" => "88.00",
                "cltProfit" => "0.00",
                "warName" => "中央党校",
                "uName" => "小明",
                "uPhone" => "13510290088"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //2.修改订单状态
    public function status_update() {
        if (empty($_POST['data']['oCode']) || empty($_POST['data']['oStatus'])) {
//            $data = array("success" => false, "msg" => "订单编码、订单状态不能为空");
            $success = false;
            $data = "";
            $message = "订单编码、订单状态不能为空";
        } else {
//            $data = array("success" => true, "msg" => "订单已标记完成");
            $success = true;
            $data = "";
            $message = "订单已标记完成";

        }
        return array($success, $data, $message);
    }

    //3.搜索货品
    public function sku_search() {
        if (empty($_POST['data']['skCode'])) {
//            $data = array("success" => false, "msg" => "货品编号不能为空");
            $success = false;
            $data = "";
            $message = "货品编号不能为空";
        } else {
            $success = true;
//            $data = "";

            $data = array(
                "skCode" => "SK0001",
                "spName" => "鸡肉"
            );
            $message = "";
        }
        return array($success, $data, $message);
    }

    //取消订单
    public function ord_cancel() {
        if (empty($_POST['data']['oCode'])) {
//            $data = array("success" => false, "msg" => "订单编号不能为空");
            $success = false;
            $data = "";
            $message = "订单编号不能为空";
        } else {
//            $data = array("success" => true, "msg" => "订单取消成功");
            $success = true;
            $data = "";
            $message = "订单取消成功";
        }
        return array($success, $data, $message);
    }

    //添加货品
    public function goods_add() {
        if (empty($_POST['data']['skCode']) || empty($_POST['data']['spName']) || empty($_POST['data']['skCount'])) {
//            $data = array("success" => false, "msg" => "货品编号、货品名称、货品数量不能为空");
            $success = false;
            $data = "";
            $message = "货品编号、货品名称、货品数量不能为空";
        } else {
//            $data = array("success" => true, "msg" => "添加货品成功");
            $success = true;
            $data = "";
            $message = "添加货品成功";
        }
        return array($success, $data, $message);
    }

    //修改数量
    public function skucount_update() {
        if (empty($_POST['data']['goodsCode']) || empty($_POST['data']['skCount'])) {
//            $data = array("success" => false, "msg" => "货品编号、货品数量不能为空");
            $success = false;
            $data = "";
            $message = "货品编号、货品数量不能为空";
        } else {
//            $data = array("success" => true, "msg" => "修改货品数量成功");
            $success = true;
            $data = "";
            $message = "修改货品数量成功";
        }
        return array($success, $data, $message);
    }

    //删除货品
    public function goods_delete() {
        if (empty($_POST['data']['goodsCode']) || empty($_POST['data']['goodsCode'])) {
//            $data = array("success" => false, "msg" => "请选择要删除的货品");
            $success = false;
            $data = "";
            $message = "请选择要删除的货品";
        } else {
//            $data = array("success" => true, "msg" => "删除货品成功");
            $success = true;
            $data = "";
            $message = "删除货品成功";
        }
        return array($success, $data, $message);
    }

    //修改订单备注
    public function mark_update() {
        if (empty($_POST['data']['oCode']) || empty($_POST['data']['oMark'])) {
//            $data = array("success" => false, "msg" => "订单编码、订单备注不能为空");
            $success = false;
            $data = "";
            $message = "订单编码、订单备注不能为空";
        } else {
//            $data = array("success" => true, "msg" => "修改订单备注成功");
            $success = true;
            $data = "";
            $message = "修改订单备注成功";
        }
        return array($success, $data, $message);
    }

    //订单详情列表(下单管理)
    public function user_list() {

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
            $data['list'][] = array("uCode" => "U00001",
                "uName" => "陈某某",
                "uPhone" => "13890654411",
                "uToken" => "2534654757586"
            );
            $data['list'][] = array("uCode" => "U00002",
                "uName" => "张某某",
                "uPhone" => "13890654422",
                "uToken" => "1234325345533"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }


    //添加账户（下单管理）
    public function user_add() {
        if (empty($_POST['data']['uPhone']) || empty($_POST['data']['uName'])) {
//            $data = array("success" => false, "msg" => "用户编码、用户名不能为空");
            $success = false;
            $data = "";
            $message = "用户编码、用户名不能为空";
        } else {
//            $data = array("success" => true, "msg" => "添加账户成功");
            $success = true;
            $data = "";
            $message = "添加账户成功";
        }
        return array($success, $data, $message);
    }

    //修改账户（下单管理）
    public function user_update() {
        if (empty($_POST['data']['uCode']) || empty($_POST['data']['uName'])) {
//            $data = array("success" => false, "msg" => "用户编码、用户名不能为空");
            $success = false;
            $data = "";
            $message = "用户编码、用户名不能为空";
        } else {
//            $data = array("success" => true, "msg" => "修改用户名成功");
            $success = true;
            $data = "";
            $message = "修改用户名成功";
        }
        return array($success, $data, $message);
    }

    //删除账户（下单管理）
    public function user_delete() {
        if (empty($_POST['data']['uCode'])) {
//            $data = array("success" => false, "msg" => "请选择要删除的账户");
            $success = false;
            $data = "";
            $message = "请选择要删除的账户";
        } else {
//            $data = array("success" => true, "msg" => "删除账户成功");
            $success = true;
            $data = "";
            $message = "删除账户成功";
        }
        return array($success, $data, $message);
    }

    //订单详情列表
    public function details_list() {

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
            $data['info'] = array("oCode" => "O30610225623983",
                "oStatus" => "1",
                "oPdate" => "2018-07-24",
                "warName" => "市政党校",
                "uName" => "rousi",
                "uPhone" => "13900000000",
                "oMark" => "订单备注"
            );
            $data['list'][] = array("goodsCode" => "G30610225623983",
                "spName" => "土豆",
                "skNorm" => "5kg/箱",
                "skCount" => "1",
                "skUnit" => "1436864169",
                "skMark" => ""
            );
            $data['list'][] = array("goodsCode" => "G30610225623332",
                "spName" => "胡萝卜",
                "skNorm" => "10kg/箱",
                "skCount" => "2",
                "skUnit" => "箱",
                "skMark" => ""
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //导出分单
    public function ord_export() {

        $fileName = "dingdan";
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

    //导出分单
    public function detailedList_export() {

        $fileName = "huopinqingdan";
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



