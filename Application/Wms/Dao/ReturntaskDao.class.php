<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/*
*退货
 */
class ReturntaskDao extends BaseDao implements BaseDaoInterface
{
    private $dbname = "";

    function __construct()
    {
        $this->dbname = C("WMS_CLIENT_DBNAME");
    }

    //添加数据[] 退货任务
    public function insert($item)
    {
        $code = venus_unique_code("RT");
        $data = array(
            "rt_code" => $code,
            "rt_addtime" => date("Y-m-d",time()),
            "rt_status" => $item["rtStatus"],
            "war_code" => $item["warCode"],
            "war_name" => $item["warName"],
            "timestamp" => venus_current_datetime(),
        );

        return M("returntask")->add($data) ? $code : false;
    }

    public function insert_returngoods($item)
    {
        $code = venus_unique_code("OG");
        $data = array(
            "ogr_node" => $item["oNode"],
            "ogr_code" => $code,
            "ogr_type" => $item["otype"],
            "ogr_status" => $item["ostatus"],
            "rt_code" => $item["rtCode"],
            "goods_code" => $item["gcode"],
            "goods_count" => $item["gcount"],
            "actual_count" => $item["gcount"],
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
            "ogr_log" => $item["ogrLog"],//退货日志
            "igo_code" => $item["igoCode"],//项目组出仓批次编号
            "apply_time" => venus_current_datetime(),//申请退货时间
            "timestamp" => venus_current_datetime(),
        );

        return M("ordergoodsreturn")->add($data) ? $code : false;
    }

    //条件过滤
    private function conditionFilter($cond)
    {
        if (isset($cond["rtCode"])) {
            $condition["rt_code"] = $cond["rtCode"];
        }
        if (isset($cond["rtStatus"])) {
            $condition["rt_status"] = $cond["rtStatus"];
        }
        if (isset($cond["ogrStatus"])) {
            $condition["ogr_status"] = $cond["ogrStatus"];
        }
        if (isset($cond["sTime"]) && isset($cond["eTime"])) {
            $condition["rt_addtime"] = array(
                array('EGT', $cond["sTime"]),
                array('ELT', $cond["eTime"]),
                'AND'
            );
        } else if (isset($cond["sTime"])) {
            $condition["rt_addtime"] = array("EGT", $cond["sTime"]);
        } else if (isset($cond["eTime"])) {
            $condition["rt_addtime"] = array("ELT", $cond["eTime"]);
        }
        if (isset($cond["rtAddtime"])) {
            $condition["rt_addtime"] = $cond["rtAddtime"];
        }
        if (isset($cond["warCode"])) {
            $condition["war_code"] = $cond["warCode"];
        }
        if(isset($cond["isTwarehouse"])){
            $condition["is_transfer_warehouse"] = $cond["isTwarehouse"];
        }
        if(isset($cond["oCode"])){
            $condition["order_code"] = $cond["oCode"];
        }
        if(isset($cond["ogrNode"])){
            $condition["ogr_node"] = $cond["ogrNode"];
        }
        if (isset($cond["%name%"])) {
            $spuname = str_replace(array("'","\""),"",$cond["%name%"]);
            $condition["spu_name"] = array('like', "%{$spuname}%");
        }
        return $condition;
    }

    //查询所有退货任务
    public function queryListByCondition($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("returntask")->alias('rt')
            ->where($condition)->order('rt.rt_addtime desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //统计符合条件任务
    public function queryCountByCondition($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("returntask")->alias('rt')->where($condition)->fetchSql(false)->count();
    }

    //根据退货任务编号，查询隶属的所有退货商品
    public function queryListByReturnTaskCode($condition, $page = 0, $count = 1000)
    {
        $condition = $this->conditionFilter($condition);
        return M("ordergoodsreturn")->alias('ogr')->field('*,ogr.id,ogr.supplier_code')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = ogr.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = ogr.spu_code")
            ->join("LEFT JOIN wms_user user ON user.user_code = ogr.user_code")
            ->where($condition)->order('ogr.id desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //统计对应任务编号下的所有退货商品
    public function queryCountByReturnTaskCode($condition)
    {
        $condition = $this->conditionFilter($condition);
        return M("ordergoodsreturn")->alias('ogr')->where($condition)->fetchSql(false)->count();
    }

    //小程序查询所有退货数据（我的退货申请）
    public function queryListByReturnGoodsCode($cond, $page = 0, $count = 1000)
    {
        $condition = array();
        if (isset($cond["ogrStatus"])) {
            $condition["ogr_status"] = $cond["ogrStatus"];
        }
        if (isset($cond["warCode"])) {
            $condition["ogr.war_code"] = $cond["warCode"];
        }
        return M("ordergoodsreturn")->alias('ogr')->field('*,ogr.id')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = ogr.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = ogr.spu_code")
            ->where($condition)->order('ogr.id desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //小程序统计所有退货数据（我的退货申请）
    public function queryCountByReturnGoodsCode($cond)
    {
        $condition = array();
        if (isset($cond["ogrStatus"])) {
            $condition["ogr_status"] = $cond["ogrStatus"];
        }
        if (isset($cond["warCode"])) {
            $condition["war_code"] = $cond["warCode"];
        }
        return M("ordergoodsreturn")->alias('ogr')->where($condition)->fetchSql(false)->count();
    }

    //更新仓库说明和实退数量
    public function updateWarmarkAndCounByCode($code,$data)
    {
        $condition = array("ogr_code" => $code);
        $updateData = array("timestamp" => venus_current_datetime());
        if (isset($data["warMark"])) {
            $updateData["warehouse_mark"] = $data["warMark"];
        }
        if (isset($data["aCount"])) {
            $updateData["actual_count"] = $data["aCount"];
        }
        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
            ->save($updateData);
    }

    //查询退货单货品数据
    public function queryByCode($code)
    {
        return M("ordergoodsreturn")->where(array("ogr_code" => $code))->find();
    }

    //查询退货单货品数据
    public function queryByUser($code)
    {
        return M("ordergoodsreturn")->alias('ogr')->field('*,ogr.id')
            ->join("LEFT JOIN wms_user user ON user.user_code = ogr.user_code")
            ->where(array("ogr_code" => $code))->fetchSql(false)->find();
    }

    //更新退货商品的任务编号
    public function updateRtcodeByCode($code,$rtcode)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->alias("ogr")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "rt_code" => $rtcode));
    }

    //查询退货单货品数据
    public function queryBy0codeAndSkcodeAndSpcode($ocode, $skcode, $spcode, $supcode)
    {
        return M("ordergoodsreturn")->where(array("order_code" => $ocode, "sku_code" => $skcode, "spu_code" => $spcode, "supplier_code"=>$supcode))->fetchSql(false)->select();
    }

    //更新退货申请单状态
    public function updateStatusByCode($code, $status)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->alias("ogr")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "ogr_status" => $status));
    }

    //更新退货任务单状态
    public function updateRtStatusByCode($code, $status)
    {
        $condition = array("rt_code" => $code);
        return M("returntask")->alias("rt")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "rt_status" => $status));
    }

    //更新项目组出仓批次编号
    public function updateIgoCodeByCode($code, $igocode)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "igo_code" => $igocode));
    }
    //更新actual_count
    public function updateActualCountByCode($code, $gcount)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "actual_count" => $gcount));
    }
    //更新退货日志
    public function updateOgrLogByCode($code, $ogrlog)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "ogr_log" => $ogrlog)) ? true : false;
    }

    //更新转仓配状态
    public function updateIsTransferWarehouseByOgrCode($code, $isTwarehouse)
    {
        $condition = array("ogr_code" => $code);
        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "is_transfer_warehouse" => $isTwarehouse));
    }

//    //更新退货商品采购价
//    public function updateBpriceByCode($code,$spucode,$bprice)
//    {
//        $condition = array("ogr_code" => $code, "spu_code" => $spucode);
//        return M("ordergoodsreturn")->where($condition)->fetchSql(false)
//            ->save(array("timestamp" => venus_current_datetime(), "spu_bprice" => $bprice));
//    }

    //仅用于下载退货单
    public function queryListByRtCode($condition)
    {
        $condition["ogr_status"] = array("NEQ",1);
        $condition["ogr_type"] = array("NEQ",7);
        return M("ordergoodsreturn")->alias('ogr')->field('*,ogr.id')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = ogr.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = ogr.spu_code")
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = ogr.supplier_code")
            ->join("LEFT JOIN wms_order o ON o.order_code = ogr.order_code")
            ->where($condition)->order('ogr.id desc')->fetchSql(false)->select();
    }

    /**
     * 下载退货单 统计退货品类
     */
    public function queryCountByRtCode($condition)
    {
        $condition["ogr_status"] = array("NEQ",1);
        $condition["ogr_type"] = array("NEQ",7);
        return M("ordergoodsreturn")->alias('ogr')
            ->where($condition)->fetchSql(false)->count('DISTINCT ogr.spu_code');
    }
}