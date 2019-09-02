<?php

namespace Wms\Debug;

class SupplierService {
    //1.查询供货商
    public function sup_search() {

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
            $data['list'][] = array("suCode" => "SU32090872092000",
                "suName" => "酒水供货商",
                "suManager" => "小明",
                "suPhone" => "13789076648",
                "suType" => "0"
            );
            $data['list'][] = array("suCode" => "SU30606170622119",
                "suName" => "鲜肉供货商",
                "suManager" => "小红",
                "suPhone" => "13522009389",
                "suType" => "1"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //2.添加供货商
    public function sup_add() {
        if (empty($_POST['data']['suName']) || empty($_POST['data']['suManager'])) {
//            $data = array("success" => false, "msg" => "供货商名称、联系人姓名不能为空");
            $success = false;
            $data = "";
            $message = "供货商名称、联系人姓名不能为空";
        } else {
//            $data = array("success" => true, "msg" => "添加供货商成功");
            $success = true;
            $data = "";
            $message = "添加供货商成功";
        }
        return array($success, $data, $message);
    }

    //3.删除供货商
    public function sup_delete() {
        if (empty($_POST['data']['suCode'])) {
//            $data = array("success" => false, "msg" => "请选择要删除的供货商");
            $success = false;
            $data = "";
            $message = "请选择要删除的供货商";
        } else {
//            $data = array("success" => true, "msg" => "删除供货商成功");
            $success = true;
            $data = "";
            $message = "删除供货商成功";
        }
        return array($success, $data, $message);
    }

    //4.修改供货商
    public function sup_update() {
        if (empty($_POST['data']['suCode'])) {
//            $data = array("success" => false, "msg" => "请选择要修改的供货商");
            $success = false;
            $data = "";
            $message = "请选择要修改的供货商";
        } else {
//            $data = array("success" => true, "msg" => "修改供货商成功");
            $success = true;
            $data = "";
            $message = "修改供货商成功";
        }
        return array($success, $data, $message);
    }
}



