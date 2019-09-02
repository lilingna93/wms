<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class OrderDao
 * @package Wms\Dao
 */
class ReturnDao extends BaseDao implements BaseDaoInterface
{
    private $dbname = "";

    function __construct()
    {
        $this->dbname = C("WMS_CLIENT_DBNAME");
    }

    //添加数据[]
    public function insert($item)
    {
        $code = venus_unique_code("OG");
        $data = array(
            "ogr_code" => $code,
            "ogr_type" => $item["otype"],
            "ogr_status" => $item["ostatus"],
            "goods_code" => $item["gcode"],
            "goods_count" => $item["gcount"],
            "sku_code" => $item["skucode"],
            "sku_count" => $item["skucount"],
            "spu_code" => $item["spucode"],
            "spu_count" => $item["spucount"],
            "spu_sprice" => isset($item["sprice"]) ? $item["sprice"] : "0",
            "spu_bprice" => isset($item["sbrice"]) ? $item["sbrice"] : "0",
            "pro_percent" => isset($item["percent"]) ? $item["percent"] : "0",
            "profit_price" => isset($item["proprice"]) ? $item["proprice"] : "0",
            "order_code" => $item["ocode"],
            "ot_code" => $item["otcode"],
            "supplier_code" => $item["supcode"],
            "user_code" => $item["ucode"],
            "war_code" => $item["warcode"],
            "war_name" => $item["warname"],
            "timestamp" => venus_current_datetime(),
        );

        return M("ordergoodsreturn")->add($data) ? $code : false;
    }

    //条件过滤
    private function conditionFilter($cond)
    {
        if (isset($cond["ogrStatus"])) {
            $condition["ogr_status"] = $cond["ogrStatus"];
        }
        if (isset($cond["ogrType"])) {
            $condition["ogr_type"] = $cond["ogrType"];
        }
        if (isset($cond["warCode"])) {
            $condition["ogr.war_code"] = $cond["warCode"];
        }
        if (isset($cond["supcode"])) {
            $condition["ogr.supplier_code"] = $cond["supcode"];
        }
        if (isset($cond["rtcodes"])) {
            $condition["rt_code"] = array("in", $cond["rtcodes"]);
        }
        return $condition;
    }

    //查询所有退货数据
    public function queryListByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("ordergoodsreturn")->alias('ogr')->field('*,ogr.id,ogr.supplier_code')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = ogr.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = ogr.spu_code")
            ->where($condition)->order('ogr.id desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //统计符合条件货品
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("ordergoodsreturn")->alias('ogr')->where($condition)->fetchSql(false)->count();
    }

    //更新退货申请单状态
    public function updateStatusByCode($code, $status)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->alias("ogr")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "ogr_status" => $status));
    }

    //查询退货单货品数据
    public function queryByCode($code)
    {
        return M("ordergoodsreturn")->where(array("ogr_code" => $code))->find();
    }

    //查询退货单货品数据
    public function queryBy0codeAndSkcodeAndSpcode($ocode, $skcode, $spcode)
    {
        return M("ordergoodsreturn")->where(array("order_code" => $ocode, "sku_code" => $skcode, "spu_code" => $spcode))->find();
    }
}