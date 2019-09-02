<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/8/7
 * Time: 10:19
 */

namespace Wms\Service;


use Common\Service\PassportService;
use Wms\Dao\InvoiceDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\WorkerDao;

class TaskService
{
    static private $TASK_STATUS_CREATE = "1";//工单创建状态/未领取
    static private $TASK_STATUS_UNDERWAY = "2";//underway工单进行中状态/领取后处理中
    static private $TASK_STATUS_FINISH = "3";//工单完成状态
    static private $TASK_STATUS_CANCEL = "4";//工单取消状态

    static private $TASK_TYPE_RECEIPT = "1";//工单类型:入仓业务-入仓
    static private $TASK_TYPE_INSPECTION = "2";//工单类型:入仓业务-验货
    static private $TASK_TYPE_PUTAWAY = "3";//工单类型:入仓业务-上架
    static private $TASK_TYPE_UPTPOS = "4";//工单类型:仓内业务-补货移区
    static private $TASK_TYPE_INVPICKORDER = "5";//工单类型:出仓业务-拣货捡单
    static private $TASK_TYPE_INVINSPECTION = "6";//工单类型:出仓业务-验货出仓
    static private $TASK_TYPE_INVUNUAUAL = "7";//工单类型:出仓业务-异常

    static private $GOODSBATCH_STATUS_CREATE = "1";//货品批次创建状态
    static private $GOODSBATCH_STATUS_INSPECTION = "2";//货品批次验货状态
    static private $GOODSBATCH_STATUS_PUTAWAY = "3";//Putaway货品批次上架状态
    static private $GOODSBATCH_STATUS_FINISH = "4";//货品批次使用完状态

    static private $SUBGOODSBATCH_STATUS_PUTAWAY = "1";//Putaway货品批次未上架状态
    static private $SUBGOODSBATCH_STATUS_FINISH = "2";//货品批次已上架状态

    public $warCode;
    public $worcode;

    public function __construct()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if (empty($workerData)) {
            venus_throw_exception(110);
        }

        $this->warCode = $workerData["war_code"];
        $this->worcode = $workerData["wor_code"];
//        $this->warCode = "WA000001";
    }

    /**
     * @return array
     * 工单管理
     */
    public function task_search()
    {
        $warCode = $this->warCode;
        $type = $_POST['data']['type'];
        $status = $_POST['data']['status'];
        $oCode = $_POST['data']['code'];
        $worCode=$_POST['data']['worCode'];

        $pageCurrent = $_POST['data']['pageCurrent'];//当前页数

        $clause = array();

        if (!empty($type) && $type != 1) {
            $clause['type'] = $type;
        }
        if (!empty($oCode)) {
            $clause['ocode'] = $oCode;
        }
        if (!empty($worCode)) {
            $clause['worcode'] = $worCode;
        }
        if (!empty($status)) {
            $clause['status'] = $status;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $taskModel = \Common\Service\TaskService::getInstance();
        $workerModel = WorkerDao::getInstance($warCode);
        $totalCount = $taskModel->query_task_count_by_search($warCode, $clause);
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $taskData = $taskModel->query_task_list_by_search($warCode, $clause, $pageLimit['page'], $pageLimit['pSize']);
        $data = array();
        $data = array(
            "pageCurrent" => $pageCurrent,
            "pageSize" => $pageLimit['pageSize'],
            "totalCount" => $totalCount,
        );

        foreach ($taskData as $value) {
            $data['list'][] = array(
                "tCode" => $value['task_code'],
                "tCtime" => $value['task_ctime'],
                "tFtime" => $value['task_ftime'],
                "tType" => venus_task_type_desc($value['task_type']),
                "tStatus" => $value['task_status'],
                "tStatMsg" => venus_task_status_desc($value['task_status']),
                "worName" => $value['worname'],
                "code" => $value['task_ocode']
            );
        }

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }


    /**
     * @return array|bool
     * 取消工单
     */
    public
    function task_cancel()
    {
        $warCode = $this->warCode;

        $data = array();
        $tCode = $_POST['data']['tCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            venus_throw_exception(1, $message);
            return false;
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $status = self::$TASK_STATUS_CREATE;
        $uptTask = $taskModel->task_update_status_and_worcode_by_taskcode($warCode, $tCode, $status,'');
        if ($uptTask) {
            $success = true;
            $message = "";
            return array($success, $data, $message);
        } else {
            venus_throw_exception(2, "取消工单");
            return false;
        }

    }
}