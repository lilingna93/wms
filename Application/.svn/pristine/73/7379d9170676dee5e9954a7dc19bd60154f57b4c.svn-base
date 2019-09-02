<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 报表数据
 * Class ReportDao
 * @package Wms\Dao
 */
class ReportDao extends BaseDao implements BaseDaoInterface {

    //添加数据[fname,data,type,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item) {
        $code = venus_unique_code("FF");
        $data = array(
            "rep_code"  => $code,
            "rep_name"  => $item["name"],
            "rep_status" => '1',
            "rep_ctime" => venus_current_datetime(),
            "rep_fname" => $item["fname"],
            "rep_data"  => $item["data"],
            "rep_type"  => $item["type"],
            "wor_code"  => $item["worcode"],
            "war_code"  => $this->warehousecode,
        );
        return M("Report")->add($data) ? $code : false;
    }
    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        return M("Report")->where(array("war_code" => $this->warehousecode, "rep_code" => $code))->find();
    }

    public function queryByName($name){
        return M("Report")->where(array("war_code" => $this->warehousecode, "rep_name" => $name))->find();
    }

    public function queryByCodeAndFname($code,$fname){
        return M("Report")->where(array("war_code" => $this->warehousecode, "rep_fname" => $fname, "rep_code" => $code))->find();
    }

    //查询

    public function queryListByCondition($condition, $page = 0, $count = 100) {
        $condition = $this->conditionFilter($condition);
        return M("Report")->where($condition)->limit("{$page},{$count}")->order("rep_code desc")->fetchSql(false)->select();
    }
    //总数
    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($condition) {
        $condition = $this->conditionFilter($condition);
        return M("Report")->where($condition)->fetchSql(false)->count();
    }

    public function updateFnameByCode($code,$fname){
        $condition = array("rep_code" => $code);
        return M("Report")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "rep_fname" => $fname));
    }

    public function updateStatusByCode($code,$status){
        $condition = array("rep_code" => $code);
        return M("Report")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "rep_status" => $status));
    }

    public function updateStatusAndFinishTimeByCode($code,$status){
        $condition = array("rep_code" => $code);
        return M("Report")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "rep_status"=>$status,"rep_ftime" => venus_current_datetime()));
    }

    public function deleteByCode($code){
        $condition = array("rep_code" => $code);
        return M("Report")->where($condition)->delete();
    }

    public function queryListByConditionWithoutWarehouse($condition,$maxcount){
        $condition = $this->conditionFilter($condition);
        unset($condition["war_code"]);
        return M("Report")->where($condition)->limit("0,{$maxcount}")->order("rep_code desc")->fetchSql(false)->select();
    }


    private function conditionFilter($cond) {
        $condition = array("war_code" => $this->warehousecode);
        if (isset($cond["status"])) {
            $condition["rep_status"] = $cond["status"];
        }
        if (isset($cond["type"])) {
            $condition["rep_type"] = $cond["type"];
        }
        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["rep_ctime"] = array(array('EGT', $cond["sctime"]), array('ELT', $cond["ectime"]), 'AND');
        } else if (isset($cond["sctime"])) {
            $condition["rep_ctime"] = array("EGT", $cond["sctime"]);
        } else if (isset($cond["ectime"])) {
            $condition["rep_ctime"] = array("ELT", $cond["ectime"]);
        }
        return $condition;
    }

    public function queryListAndWorkerByCondition($cond, $page = 0, $count = 100) {
        $condition = array("report.war_code" => $this->warehousecode);
        if (isset($cond["type"])) {
            $condition["rep_type"] = $cond["type"];
        }
        return M("Report")
            ->alias("report")
            ->join("JOIN wms_worker wor ON wor.wor_code = report.wor_code")
            ->where($condition)
            ->limit("{$page},{$count}")->order("rep_code desc")->fetchSql(false)->select();
    }

}