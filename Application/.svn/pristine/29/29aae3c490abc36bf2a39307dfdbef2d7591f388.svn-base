<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 入仓单数据
 * Class ReceiptDao
 * @package Wms\Dao
 */
class ReceiptDao extends BaseDao implements BaseDaoInterface
{

    //添加数据[status,mark,tracecode,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item)
    {
        $code = venus_unique_code("RE");
        $ctime = $item["ctime"];
        $data = array(
            "rec_code" => $code,
            "rec_ctime" => empty($ctime) ? venus_current_datetime() : $ctime,
            "rec_status" => $item["status"],
            "rec_mark" => $item["mark"],
            "trace_code" => $item["tracecode"],
            "wor_code" => $item["worcode"],
            "war_code" => $this->warehousecode,
            "timestamp" => venus_current_datetime(),
            "rec_type" => $item["type"],//20190118添加
            "rec_supcode" => $item["supcode"],//20190521添加
        );
        return M("Receipt")->add($data) ? $code : false;
    }

    //查询

    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code)
    {
        return M("Receipt")->where(array("war_code" => $this->warehousecode, "rec_code" => $code))->fetchSql(false)->find();
    }

    //查询

    /**
     * @param $condition
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($condition, $page = 0, $count = 100)
    {
        $condition = $this->conditionFilter($condition);
        return M("Receipt")->where($condition)->order("id desc")->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数

    /**
     * @param $condition
     * @return mixed
     */
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("Receipt")->where($condition)->order("id desc")->fetchSql(false)->count();
    }

    //更新状态

    /**
     * @param $code
     * @param $status
     * @return mixed
     */
    public function updateStatusByCode($code, $status, $finishtime = "")
    {
        $condition = array("war_code" => $this->warehousecode, "rec_code" => $code);
        $data = array("timestamp" => venus_current_datetime(), "rec_status" => $status);
        if (!empty($finishtime)) {
            $data["rec_ftime"] = $finishtime;
        }
        return M("Receipt")->where($condition)->fetchSql(false)->save($data);
    }

    public function updateFinishTimeByCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "rec_code" => $code);
        $data = array("timestamp" => venus_current_datetime(), "rec_ftime" => venus_current_datetime());
        return M("Receipt")->where($condition)->fetchSql(false)->save($data);
    }


    //查询条件过滤[worcode,pdate,ctime,sctime,ectime,status]

    /**
     * @param $cond
     * @return array
     */
    private function conditionFilter($cond)
    {
        $condition = array("war_code" => $this->warehousecode);
        if (isset($cond["worcode"])) {
            $condition["wor_code"] = $cond["worcode"];
        }
        if (isset($cond["pdate"])) {
            $condition["rec_pdate"] = $cond["pdate"];
        }
        if (isset($cond["ctime"])) {
            $condition["rec_ctime"] = $cond["ctime"];
        }
        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["rec_ctime"] = array(array('EGT', $cond["sctime"]), array('ELT', $cond["ectime"]), 'AND');
        } else if (isset($cond["sctime"])) {
            $condition["rec_ctime"] = array("EGT", $cond["sctime"]);
        } else if (isset($cond["ectime"])) {
            $condition["rec_ctime"] = array("ELT", $cond["ectime"]);
        }


        if (isset($cond["ftime"])) {
            $condition["rec_ftime"] = $cond["ftime"];
        }
        if (isset($cond["sftime"]) && isset($cond["eftime"])) {
            $condition["rec_ftime"] = array(array('EGT', $cond["sftime"]), array('ELT', $cond["eftime"]), 'AND');
        } else if (isset($cond["sftime"])) {
            $condition["rec_ftime"] = array("EGT", $cond["sftime"]);
        } else if (isset($cond["eftime"])) {
            $condition["rec_ftime"] = array("ELT", $cond["eftime"]);
        }


        if (isset($cond["status"])) {
            $condition["rec_status"] = $cond["status"];
        }
        if (isset($cond["code"])) {
            $condition["rec_code"] = $cond["code"];
        }
        if (isset($cond["supcode"])) {
            $condition["rec_supcode"] = $cond["supcode"];
        }//20190521添加
        return $condition;
    }

    //退货，如果入仓单无数据删除调用
    public function deleteByCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "rec_code" => $code);
        return M("Receipt")->where($condition)->fetchSql(false)->delete();
    }

    public function queryByEcode($ecode)
    {
        $condition = array("war_code" => $this->warehousecode, "rec_mark" => $ecode);
        return M("Receipt")->where($condition)->find();
    }
}