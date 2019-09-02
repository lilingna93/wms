<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 客户SKU利润表
 * Class ProfitDao
 * @package Wms\Dao
 */
class ProfitDao extends BaseDao implements BaseDaoInterface {

    

    function __construct() {
    }
    //添加数据[status,ecode,receiver,address,postal,worcode]
    /**
     * @param $item
     * @return bool
     */
    public function insert($item) {
        $data = array(
            "spu_code" => $item["spucode"],
            "pro_percent" => 0,//$item["percent"]
            "pro_price" => $item["proprice"],
            "exwar_code" => $item["exwarcode"],
        );
        return M("Profit")->add($data) ? true : false;
    }

    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        return M("Profit")->where(array("spu_code" => $code))->find();
    }

    //查询数据是否已存在
    /**
     * @param $code
     * @param $exwarcode
     * @return mixed
     */
    public function queryByCondition($code, $exwarcode) {
        $condition = array( "spu_code" => $code, "exwar_code" => $exwarcode);
        return M("Profit")->where($condition)->find();
    }


    //更新利润百分比
    /**
     * @param $code
     * @param $exwarcode
     * @param $percent
     * @return mixed
     */
    public function updatePercentBySkucode($code, $exwarcode, $percent) {
        $condition = array( "spu_code" => $code, "exwar_code" => $exwarcode);
        return M("Profit")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(), "pro_percent" => $percent));
    }

    //2018-10-16新添加  更新利润价
    public function updatePropriceBySkucode($code, $exwarcode, $proprice) {
        $condition = array( "spu_code" => $code, "exwar_code" => $exwarcode);
        return M("Profit")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(), "pro_price" => $proprice));
    }
}