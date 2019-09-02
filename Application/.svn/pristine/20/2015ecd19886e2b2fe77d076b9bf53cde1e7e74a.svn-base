<?php
namespace Wms\Service;
use Common\Service\PassportService;
use Wms\Dao\ExportallfileDao;


class ExportallfileService
{

    public $uName;
    public function __construct()
    {
        $ExportallfileItemData = PassportService::getInstance()->loginUser();
        if (empty($ExportallfileItemData)) {
            venus_throw_exception(110);
        }
        $this->uName = $ExportallfileItemData["user_name"];
    }

    public function fileslog_list()
    {
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数

        //当前页码
        $pageCurrent = empty($pageCurrent) ? 0 : $pageCurrent;

        $condition = array();
        $ExportallfileDao = ExportallfileDao::getInstance();
        $totalCount = $ExportallfileDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $ExportallfileDataList = $ExportallfileDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);

        if (empty($ExportallfileDataList)) {
            $exportallfileList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $exportallfileList["list"] = array();
        } else {
            $exportallfileList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($ExportallfileDataList as $index => $ExportallfileItem) {
                $exportallfileList["list"][$index] = array(
                    "fName" => $ExportallfileItem['wor_code'],//文件名
                    "type" => $ExportallfileItem['wor_name'],//数据类型
                    "timeStamp" => $ExportallfileItem['wor_rname'],//创建时间
                );
            }
        }
        return array(true, $exportallfileList, "");
    }

    public function user_download_file_log()
    {
        $uName = $this->uName;
        $downloadTime = date("Y-m-d H:i:s",time());
        $filesLogId = $_POST['data']['fLogId'];//获取文件日志id

        $userdownloadData = array(
            "fileslog_id" => $filesLogId,
            "user_name" => $uName,
            "timestamp" => $downloadTime,
        );
        $dfAddResult = ExportallfileDao::GetInstance()->insert($userdownloadData);
        if ($dfAddResult) {
            $success = true;
            $message = "添加账户成功";
        } else {
            $success = false;
            $message = "添加账户失败";
        }
        return array($success, "", $message);
    }

}