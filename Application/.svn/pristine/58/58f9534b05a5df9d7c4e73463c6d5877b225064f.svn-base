<?php

namespace Wms\Service;

use Common\Service\PassportService;
use Wms\Dao\WorkerDao;

class WorkerService
{

    public $waCode;
    function __construct()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if(empty($workerData)){
            venus_throw_exception(110);
        }
        $this->waCode = $workerData["war_code"];
    }

    //1.默认用户列表（仓库账户管理）
    public function worker_list()
    {
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 1000;//当前页面总条数

        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $condition = array();
        $WorkerDao = WorkerDao::getInstance($this->waCode);
        $totalCount = $WorkerDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $workerDataList = $WorkerDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);

        if (empty($workerDataList)) {
            $workerList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $workerList["list"] = array();
        } else {
            $workerList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($workerDataList as $index => $workerItem) {
                $workerList["list"][$index] = array(
                    "woCode" => $workerItem['wor_code'],//用户编号
                    "woName" => $workerItem['wor_name'],//用户姓名
                    "realName" => $workerItem['wor_rname'],//真实姓名
                    "woAuth" => $workerItem['wor_auth'],//用户权限值
                    "woToken" => $workerItem['wor_token']//第三方账户token
                );
            }
        }
        return array(true, $workerList, "");
    }

    //2.添加账户（仓库账户管理）
    public function worker_add()
    {
        $woName = $_POST['data']['woName'];
        $realName = $_POST['data']['realName'];
        $woPwd = $_POST['data']['woPwd'];
        $woToken = "";//自动生成
        $woAuth = $_POST['data']['woAuth'];

        if (empty($woName)) {
            venus_throw_exception(1, "用户名不能为空");
            return false;
        }

        if (empty($realName)) {
            venus_throw_exception(1, "真实姓名不能为空");
            return false;
        }

        if (empty($woPwd)) {
            venus_throw_exception(1, "密码不能为空");
            return false;
        }

        $workerData = array(
            "name" => $woName,
            "rname" => $realName,
            "pwd" => $woPwd,
            "token" => $woToken,
            "phone" => "",
            "auth" => $woAuth
        );
        $worAddResult = WorkerDao::getInstance($this->waCode)->insert($workerData);
        if ($worAddResult) {
            $success = true;
            $message = "添加账户成功";
        } else {
            $success = false;
            $message = "添加账户失败";
        }
        return array($success, "", $message);
    }

    //3.修改账户（仓库账户管理）
    public function worker_update()
    {

        $woCode = $_POST['data']['woCode'];
        $woName = $_POST['data']['woName'];
        $realName = $_POST['data']['realName'];
        $woPwd = $_POST['data']['woPwd'];
        $woAuth = $_POST['data']['woAuth'];
        $woToken = "";//自动生成

        if (empty($woCode)) {
            venus_throw_exception(1, "用户编号不能为空");
            return false;
        }

        $cond = array();
        if (!empty($woName)) {
            $cond['name'] = $woName;
        }
        if (!empty($realName)) {
            $cond['rname'] = $realName;
        }
        if (!empty($woPwd)) {
            $cond['pwd'] = $woPwd;
        }
        if (!empty($woAuth)) {
            $cond['auth'] = $woAuth;
        }
        if (!empty($woToken)) {
            $cond['token'] = $woToken;
        }
        $worUpdResult = WorkerDao::getInstance($this->waCode)->updateItemByCode($woCode, $cond);
        if ($worUpdResult) {
            $success = true;
            $message = "修改账户成功";
        } else {
            $success = false;
            $message = "修改账户失败";
        }
        return array($success, "", $message);
    }

    //4.删除账户（仓库账户管理）
    public function worker_delete()
    {

        $woCode = $_POST['data']['woCode'];
        if (empty($woCode)) {
            venus_throw_exception(1, "请选择要删除的账户");
            return false;
        }
        $worDelResult = WorkerDao::getInstance($this->waCode)->removeByCode($woCode);
        if ($worDelResult) {
            $success = true;
            $message = "删除账户成功";
        } else {
            $success = false;
            $message = "删除账户失败";
        }
        return array($success, "", $message);
    }
}



