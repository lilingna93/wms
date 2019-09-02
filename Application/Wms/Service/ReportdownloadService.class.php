<?php

namespace Wms\Service;

use Common\Service\PassportService;
use Wms\Dao\ReportdownloadDao;

class ReportdownloadService
{

    public $waCode;
    function __construct()
    {
//        $userData = PassportService::getInstance()->loginUser();
//        if(empty($userData)){
//          //  venus_throw_exception(110);
//        }
//        $this->waCode = $userData["war_code"];
    }

    //1.默认用户列表（仓库账户管理）
    public function reportdownload_list()
    {
        $post = $_POST['data'];
        $sdepartments = $post['sdepartments'];//所属部门：1.市场部 2.采购部 3.财务部 4.仓配部$post['sdepartments']
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = $post['pageSize'];//每页显示条数

        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }
        if (empty($sdepartments)) {
            venus_throw_exception(1, "部门编号不能为空");
            return false;
        }
        $condition['sdepartments'] = $sdepartments;
        $ReportdownloadDao = ReportdownloadDao::getInstance();
        $totalCount = $ReportdownloadDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent, $pageSize);
        $reportDataList = $ReportdownloadDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);

        if (empty($reportDataList)) {
            $reportList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $reportList["list"] = array();
        } else {
            $reportList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($reportDataList as $index => $reportItem) {
                $reportList["list"][$index] = array(
                    "id" => $reportItem['id'],//列表展示文件名
                    "fname" => $reportItem['file_name'],//列表展示文件名
                    "sfname" => $reportItem['save_file_name'],//生成的原文件名
                    "scatalogue" => $reportItem['storage_catalogue'],//存放目录
                    "sdepartments" => $reportItem['subordinate_departments'],//所属部门：1.采购部 2.销售部 3.仓配部 4.财务部
                );
            }
        }
        return array(true, $reportList, "");
    }

    //下载记录
    public function download_file()
    {
        $post = $_POST['data'];
        $fileslogid = $post['id'];//报表id
        $username = $post['username'];

        if (empty($fileslogid)) {
            venus_throw_exception(1, "报表id不能为空");
            return false;
        }
        if (empty($username)) {
            venus_throw_exception(1, "用户名不能为空");
            return false;
        }
        $data = array(
            "fid" => $fileslogid,
            "uname" => $username,
        );
        $insert = ReportdownloadDao::getInstance()->insert_userdownloadlog($data);
        if($insert){
            $success = true;
            $message = "下载成功";
        }else{
            $success = false;
            $message = "下载失败";
        }
        return array($success,"",$message);
    }

}



