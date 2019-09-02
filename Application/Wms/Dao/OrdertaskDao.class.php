<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class OrderDao
 * @package Wms\Dao
 */
class OrdertaskDao extends BaseDao implements BaseDaoInterface {

    function __construct() {
    }

    //添加数据[]
    public function insert($item) {
        $code = venus_unique_code("OT");
        $data = array(
            "ot_code"       => $code,
            "ot_ctime"      => $item["ctime"],
            "ot_ownstatus"  => $item["ownstatus"],  //0:无数据,1:未处理,2:已处理
            "ot_supstatus"  => $item["supstatus"],  //0:无数据,1:未处理,2:已处理
            "ot_ordercount" => $item["ordercount"], //订单数量
            "or_mark"       => $item["mark"],       //任务备注
            "timestamp"     => venus_current_datetime(),
        );
        return M("Ordertask")->add($data) ? $code : false;
    }

    //查询订单任务
    public function queryByCode($code){
        $condition  = array("ot_code"=>$code);
        return M("Ordertask")->where($condition)->find();
    }

    //查询订单任务列表
    public function queryListByCondition($condition,$page=0,$count=1000){
        $condition = $this->conditionFilter($condition);
        return M("Ordertask")->where($condition)->limit("{$page},{$count}")->fetchSql(false)->select();
    }
    //查询订单任务数量
    public function queryCountByCondition($condition){
        $condition = $this->conditionFilter($condition);
        return M("Ordertask")->where($condition)->count();
    }

    //删除订单任务
    public function deleteByCode($code){
        $condition  = array("ot_code"=>$code);
        return M("Ordertask")->where($condition)->delete();
    }

    //更新订单任务的自采订单状态
    //0:无数据,1:未处理,2:已处理
    public function updateSupStatusByCode($code,$status){
        $condition = array("ot_code"=>$code);
        return M("Ordertask")->where($condition)
            ->save(array("timestamp"=>venus_current_datetime(),
                "ot_supstatus"=>$status));
    }

    //更新订单任务的自营订单状态
    //0:无数据,1:未处理,2:已处理
    public function updateOwnStatusByCode($code,$status){
        $condition = array("ot_code"=>$code);
        return M("Ordertask")->where($condition)
            ->save(array("timestamp"=>venus_current_datetime(),
                "ot_ownstatus"=>$status));
    }

    private function conditionFilter($cond) {
        $condition = array();
        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["ot_ctime"] = array(
                array('EGT',$cond["sctime"]),
                array('ELT',$cond["ectime"]),
                'AND'
            );
        }else if (isset($cond["sctime"])&& !empty($cond["sctime"])) {
            $condition["ot_ctime"] = array("EGT", $cond["sctime"]);
        }else if (isset($cond["ectime"])&& !empty($cond["ectime"])) {
            $condition["ot_ctime"] = array("ELT", $cond["ectime"]);
        }

        if (isset($cond["otcode"]) && !empty($cond["otcode"])) {
            $condition["ot_code"] = $cond["otcode"];
        }

        if (isset($cond["supstatus"]) && !empty($cond["supstatus"])) {
            $condition["ot_supstatus"] = $cond["supstatus"];
        }
        if (isset($cond["ownstatus"]) && !empty($cond["ownstatus"])) {
            $condition["ot_ownstatus"] = $cond["ownstatus"];
        }
        return $condition;
    }


}