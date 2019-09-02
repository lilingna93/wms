<?php

namespace Wms\Debug;

class WorkerService {
    //1.默认用户列表
    public function worker_list() {

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
            $data['list'][] = array("woCode" => "SK20390987653092",
                "woName" => "赵某某",
                "realName" => "赵丽丽",
                "woAuth" => "1",
                "woauthName" => "货品数据管理",
                "woToken" => "24223546677"
            );
            $data['list'][] = array("woCode" => "SK20390987322234",
                "woName" => "李某某",
                "realName" => "李静",
                "woAuth" => "2",
                "woauthName" => "外部订单管理",
                "woToken" => "24223546611"
            );
        }
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    //2.添加账户
    public function worker_add() {
        if (empty($_POST['data']['woName']) || empty($_POST['data']['woPwd']) || empty($_POST['data']['woAuth'])) {
//            $data = array("success" => false, "msg" => "真实姓名、密码、权限不能为空");
            $success = false;
            $data = "";
            $message = "真实姓名、密码、权限不能为空";
        } else {
//            $data = array("success" => true, "msg" => "添加账户成功");
            $success = true;
            $data = "";
            $message = "添加账户成功";
        }
        return array($success, $data, $message);
    }

    //3.删除账户
    public function worker_delete() {
        if (empty($_POST['data']['woCode'])) {
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

    //4.修改账户
    public function worker_update() {
        if (empty($_POST['data']['woCode'])) {
//            $data = array("success" => false, "msg" => "请选择要修改的账户");
            $success = false;
            $data = "";
            $message = "请选择要修改的账户";
        } else {
//            $data = array("success" => true, "msg" => "修改账户成功");
            $success = true;
            $data = "";
            $message = "修改账户成功";
        }
        return array($success, $data, $message);
    }
}



