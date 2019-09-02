<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 工单数据
 * Class TaskDao
 * @package Wms\Dao
 */
class TaskDao extends BaseDao implements BaseDaoInterface
{


    //添加数据[fname,data,type,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item)
    {
        $code = venus_unique_code("TA");
        $data = array(
            "task_code" => $code,
            "task_ctime" => venus_current_datetime(),
            "task_ftime" => '',
            "task_type" => $item["type"],
            "task_data" => $item["data"],
            "task_status" => $item["status"],
            "task_ocode" => $item["ocode"],
            "task_extra" => $item["extra"],
            "creator_code" => $item["worcode"],
            "war_code" => $this->warehousecode,

        );
        return M("Task")->add($data) ? $code : false;
    }

    //查询

    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code)
    {
        return M("Task")->alias("task")->field("task.*,worker.wor_rname")
            ->where(array("task.war_code" => $this->warehousecode, "task_code" => $code))
            ->join("left join wms_worker worker on task.wor_code=worker.wor_code")->fetchSql(false)->find();
    }

    /**
     * @param $code
     * @return mixed
     * 查询入仓单出仓单编号
     */
    public function queryOcodeByCode($code)
    {
        return M("Task")->alias("task")->field("task.*,worker.wor_rname worname")
            ->join("left join wms_worker worker on task.wor_code=worker.wor_code")
            ->where(array("task.war_code" => $this->warehousecode, "task_code" => $code))->getField("task_ocode");
    }

    /**
     * @param $ocode
     * @return mixed
     */
    public function queryByTdata($data)
    {
        return M("Task")->alias("task")->field("task.*,worker.wor_rname worname")
            ->join("left join wms_worker worker on task.wor_code=worker.wor_code")
            ->where(array("task.war_code" => $this->warehousecode, "task_data" => $data))->find();
    }

    //查询

    /**
     * @param $cond
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($cond, $page = 0, $count = 100)
    {
        $condition = $this->conditionFilter($cond);
        return M("Task")->alias("task")->field("task.*,worker.wor_rname worname")
            ->join("left join wms_worker worker on task.wor_code=worker.wor_code")
            ->where($condition)->limit("{$page},{$count}")
            ->order("task_ctime desc")->fetchSql(false)->select();
    }

    //总数

    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($cond)
    {
        $condition = $this->conditionFilter($cond);
        return M("Task")->alias("task")->where($condition)
            ->order("task_ctime desc")->fetchSql(false)->count();
    }

    //更新状态并完成

    /**
     * @param $code
     * @param $status
     * @return mixed
     */
    public function updateStatusAndFinishTimeByCode($code, $status)
    {
        $condition = array("war_code" => $this->warehousecode, "task_code" => $code);
        return M("Task")->alias("task")->where($condition)->fetchSql(false)
            ->save(array("task_status" => $status, "task_ftime" => venus_current_datetime(),
                "timestamp" => venus_current_datetime()));
    }

    public function queryStatusByCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "task_code" => $code);
        return M("Task")->alias("task")->where($condition)->fetchSql(false)
            ->getField("task_status");
    }

    //更新状态

    /**
     * @param $code
     * @param $status
     * @return mixed
     */
    public function updateStatusByCode($code, $status)
    {
        $condition = array("war_code" => $this->warehousecode, "task_code" => $code);
        return M("Task")->alias("task")->where($condition)->fetchSql(false)
            ->save(array("task_status" => $status,
                "timestamp" => venus_current_datetime()));
    }

    public function updateStatusAndWorCodeByCode($code, $status, $worcode)
    {
        $condition = array("war_code" => $this->warehousecode, "task_code" => $code);
        return M("Task")->alias("task")->where($condition)->fetchSql(false)
            ->save(array("task_status" => $status, "wor_code" => $worcode,
                "timestamp" => venus_current_datetime()));
    }

    public function updateDataByCode($code, $data)
    {
        $condition = array("war_code" => $this->warehousecode, "task_code" => $code);
        return M("Task")->alias("task")->where($condition)->fetchSql(false)
            ->save(array("task_data" => $data, "timestamp" => venus_current_datetime()));
    }

    public function queryByExtra($extra)
    {
        return M("Task")->alias("task")->field("task.*,worker.wor_rname worname")
            ->where(array("task.war_code" => $this->warehousecode, "task_extra" => $extra))
            ->join("left join wms_worker worker on task.wor_code=worker.wor_code")->fetchSql(false)->find();
    }

    private function conditionFilter($cond)
    {
        $condition = array("task.war_code" => $this->warehousecode);
        if (isset($cond["type"])) {
            $condition["task_type"] = $cond["type"];
        }
        if (isset($cond["status"])) {
            $condition["task_status"] = $cond["status"];
        }
        if (isset($cond["worcode"])) {
            $condition["task.wor_code"] = $cond["worcode"];
        }
        if (isset($cond["ocode"])) {
            $condition["task_ocode"] = $cond["ocode"];
        }
        if (isset($cond["data"])) {
            $condition["task_data"] = $cond["data"];
        }
        if (isset($cond["code"])) {
            $condition["task_code"] = $cond["code"];
        }
        return $condition;
    }

}