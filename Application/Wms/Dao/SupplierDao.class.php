<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 供货商数据
 * Class SupplierDao
 * @package Wms\Dao
 */
class SupplierDao extends BaseDao implements BaseDaoInterface {


    /**
     * SkuDao constructor.
     */
    function __construct() {
    }
    //添加数据[fname,data,type,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item) {
        $code = venus_unique_code("SU");
        $data = array(
            "sup_code" => $code,
            "sup_name" => $item["name"],
            "sup_type" => $item["type"],
            "sup_mark" => $item["mark"],
            "sup_status" => 1,
            "sup_phone" => $item["phone"],
            "sup_manager" => $item["manager"],
            "wor_code" => $item["worcode"],
            "war_code" => $this->warehousecode,
        );
        return M("Supplier")->add($data) ? $code : false;
    }

    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        $condition = array("war_code" => $this->warehousecode, "sup_status" => 1, "sup_code" => $code);
        return M("Supplier")->where($condition)->find();
    }

    //查询所有
    public function queryAllByCode($code) {
//        $condition = array("war_code" => $this->warehousecode,"sup_code" => $code);
        $condition = array("sup_code" => $code);
        return M("Supplier")->where($condition)->fetchSql(false)->find();
    }

    //查询
    /**
     * @param $cond
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($condition, $page = 0, $count = 100) {
        $condition = $this->conditionFilter($condition);
        return M("Supplier")->where($condition)->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数
    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($condition) {
        $condition = $this->conditionFilter($condition);
        return M("Supplier")->where($condition)->fetchSql(false)->count();
    }

    //更新
    /**
     * @param $code
     * @param $status
     * @return mixed
     */
    public function updateStatusByCode($code, $status) {
        if("SU00000000000001" == $code){
            return false;
        }
        return M("Supplier")
            ->where(array("war_code" => $this->warehousecode, "sup_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "sup_status" => $status));
    }

    /**
     * @param $code
     * @param $data
     * @return mixed
     */
    public function updateDataByCode($code, $data) {
        $condition = array("war_code" => $this->warehousecode, "sup_code" => $code);
        $updateData = array("timestamp" => venus_current_datetime());
        if (isset($data["suptype"])) {
            $updateData["sup_type"] = $data["suptype"];
        }
        if (isset($data["supname"])) {
            $updateData["sup_name"] = $data["supname"];
        }
        if (isset($data["supmanager"])) {
            $updateData["sup_manager"] = $data["supmanager"];
        }
        if (isset($data["supphone"])) {
            $updateData["sup_phone"] = $data["supphone"];
        }
        return M("Supplier")->where($condition)
            ->save($updateData);
    }

    /**
     * @param $cond
     * @return array
     */
    private function conditionFilter($cond) {
        $condition = array("war_code" => $this->warehousecode, "sup_status" => 1);
        if (isset($cond["supcode"])) {
            $condition["sup_code"] = $cond["supcode"];
        }
        if (isset($cond["supname"])) {
            $condition["sup_name"] = $cond["supname"];
        }
        if (isset($cond["%supname%"])) {
            $condition["sup_name"] = array('like', "%" . $cond["%supname%"] . "%");
        }
        return $condition;
    }

}