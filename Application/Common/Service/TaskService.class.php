<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/7/24
 * Time: 15:12
 */

namespace Common\Service;


use Wms\Dao\GoodsbatchDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\SubgoodsbatchDao;
use Wms\Dao\TaskDao;

class TaskService
{
    //保存类实例的静态成员变量
    private static $_instance;

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

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * @param $warCode仓库编号
     * @param $ocode使用的编号，例如入仓单编号
     * @param $type工单类型
     * @param $status工单状态
     * @param $worCode工人编号
     * @return mixed
     * 创建工单
     */
    public function task_create($warCode, $data, $ocode, $type, $status, $extra)
    {

        $taskCond["type"] = $type;
        $taskCond["data"] = json_encode($data);
        $taskCond["status"] = $status;
        $taskCond["ocode"] = $ocode;
        $taskCond["extra"] = $extra;
        $taskCond["worcode"] = '';
        return TaskDao::getInstance($warCode)->insert($taskCond);
    }

    /**
     * @param $warcode仓库编号
     * @param $ocode使用的编号，例如入仓单编号
     * @param $status工单状态
     * @return mixed
     * 修改工单状态
     */
    public function update_task_finish_status_by_code($warCode, $tcode, $status)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->updateStatusAndFinishTimeByCode($tcode, $status);
    }

    /**
     * @param $warCode仓库编号
     * @param $clause查询的条件
     * @param $page
     * @param $pSize
     * @return mixed
     *搜索工单列表
     */
    public function query_task_list_by_search($warCode, $clause, $page, $pSize)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->queryListByCondition($clause, $page, $pSize);
    }

    /**
     * @param $warCode仓库编号
     * @param $clause查询的条件
     * @return mixed
     * 统计搜索工单
     */
    public function query_task_count_by_search($warCode, $clause)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->queryCountByCondition($clause);
    }

    public function query_task_by_data($warCode, $tData)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->queryByTdata(json_encode($tData));
    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $status
     * @return mixed
     * 工单取消及申领
     */
    public function task_update_status_and_worcode_by_taskcode($warCode, $tCode, $status, $worCode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->updateStatusAndWorCodeByCode($tCode, $status, $worCode);
    }

    /**
     * @param $warCode
     * @param $tCode
     * @return mixed
     * 查询工单信息
     */
    public function query_taskinfo_by_taskcode($warCode, $tCode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        $taskInfo = $taskModel->queryByCode($tCode);
        $taskInfo['task_data'] = json_decode($taskInfo['task_data'], true);
        return $taskInfo;
    }

    public function query_taskdata_by_taskcode($warCode, $tCode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        $taskInfo = $taskModel->queryByCode($tCode);
        $data = json_decode($taskInfo['task_data'], true);
        return $data;
    }

    public function query_ocode_by_taskcode($warCode, $tCode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->queryOcodeByCode($tCode);
    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $status
     * @return mixed
     */
    public function query_gb_detail_by_taskcode($warCode, $tCode, $status)
    {
        $taskModel = TaskDao::getInstance($warCode);
        $oCode = $taskModel->queryOcodeByCode($tCode);
        $clause = array(
            "reccode" => $oCode,
            "status" => $status
        );
        return GoodsbatchDao::getInstance($warCode)->queryListByCondition($clause);
    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $status
     * @param $code
     * @return mixed
     */
    public function query_sgb_detail_by_taskcode($warCode, $tCode, $status, $code)
    {
        $taskModel = TaskDao::getInstance($warCode);
        $oCode = $taskModel->queryOcodeByCode($tCode);
        $taskInfo = $this->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskType = $taskInfo['task_type'];
        $clause = array(
            "reccode" => $oCode,
            "status" => $status,
        );
        if (!empty($code)) {
            if (substr($code, 0, 1) == "S") {
                $clause['subgbcode'] = $code;
            }
            if (substr($code, 0, 1) == "P") {
                $clause['poscode'] = $code;
                $clause['subgbcode'] = $taskInfo['task_data']['subgbCode'];
            }
        } else {
            $code = array('exp', 'is not null');
            if ($taskType == self::$TASK_TYPE_INSPECTION) {
                $clause['subgbcode'] = $code;
            } else {
                $clause['subgbcode'] = $taskInfo['task_data']['subgbCode'];
                $clause['poscode'] = $code;
            }
        }
        return SubgoodsbatchDao::getInstance($warCode)->queryListByCondition($clause);
    }

    public
    function query_status_by_taskcode($warCode, $tCode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        return $taskModel->queryStatusByCode($tCode);
    }

    public
    function query_igs_detail_by_taskcode_and_benchcode($warCode, $tCode, $bcode)
    {
        $taskModel = TaskDao::getInstance($warCode);
        $oCode = $taskModel->queryOcodeByCode($tCode);
        $clause = array(
            "invcode" => $oCode,
            "bcode" => $bcode,
        );
        return IgoodsentDao::getInstance($warCode)->queryListByInvCodeAndBenchCode($clause, 0, 10000);
    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $data
     * @param bool $isMerge 是否追加数据
     * @return mixed
     */
    public function update_data_by_taskcode($warCode, $tCode, $data, $isMerge = true)
    {
        if ($isMerge) {
            $taskData = $this->query_taskdata_by_taskcode($warCode, $tCode);
            $uptdateData = json_encode($taskData + $data);
        } else {
            $uptdateData = json_encode($data);
        }
        return TaskDao::getInstance($warCode)->updateDataByCode($tCode, $uptdateData);
    }

    public function query_taskinfo_by_dataOcode($warCode, $code)
    {
        $substrCode = substr($code, 0, 2);
        if ($substrCode == "RE") {
            $type = self::$TASK_TYPE_INSPECTION;
        }
        if ($substrCode == "IN") {
            $type = self::$TASK_TYPE_INVPICKORDER;
        }
        $taskData = array("like", "{\"code\":\"" . $code . "\"}");
        $clause = array(
            "data" => $taskData,
            "ocode" => $code,
        );
        isset($type) ? $clause["type"] = $type : "";
        $taskInfo = TaskDao::getInstance($warCode)->queryListByCondition($clause);
        return $taskInfo;
    }

    private
    function __clone()
    {

    }
}