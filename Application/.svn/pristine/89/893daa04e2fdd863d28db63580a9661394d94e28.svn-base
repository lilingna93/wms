<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/7/17
 * Time: 0:11
 */

namespace Wms\Debug;


class TaskService {
    /**
     * @return mixed
     * 工单管理
     */
    public function task_search() {
        $data = array();
        $data["pageCurrent"] = "1";
        $data["pageSize"] = "3";
        $data["totalCount"] = "3";
        $data["list"][] = array(
            "tCode" => "T30606170641628",
            "tCtime" => "2018-07-01 00:00:00",
            "tFtime" => "2018-07-01 00:00:00",
            "tType" => "入仓业务-入仓",
            "tStatus" => "1",
            "tStatMsg" => "已创建",
            "worName" => "111",
            "code" => "111"
        );
        $data["list"][] = array();
        $data["list"][] = array(
            "tCode" => "T30606170641629",
            "tCtime" => "2018-07-01 00:00:00",
            "tFtime" => "2018-07-01 00:00:00",
            "tType" => "入仓业务-验货",
            "tStatus" => "1",
            "tStatMsg" => "已创建",
            "worName" => "111",
            "code" => "111"
        );
        $data["list"][] = array(
            "tCode" => "T30606170641630",
            "tCtime" => "2018-07-01 00:00:00",
            "tFtime" => "2018-07-01 00:00:00",
            "tType" => "入仓业务-上架",
            "tStatus" => "1",
            "tStatMsg" => "已创建",
            "worName" => "111",
            "code" => "111"
        );
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }

    /**
     * @return array
     * 工单管理之取消
     */
    public function task_cancel() {
        $data = array();
        if (empty($_POST['data']['tCode'])) {
            $success = false;
            $message = "工单编号不能为空";
            return array($success, $data, $message);
        } //return "工单编号不能为空";
        $success = true;
        $message = "";
        return array($success, $data, $message);
    }
}