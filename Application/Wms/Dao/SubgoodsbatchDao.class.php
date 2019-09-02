<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/11/8
 * Time: 15:53
 */

namespace Wms\Dao;


use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

class SubgoodsbatchDao extends BaseDao implements BaseDaoInterface
{
    /**
     * SubgoodsbatchDao constructor.
     */
    function __construct()
    {

    }

    /**
     * 添加数据[status,count,bprice,spucode,reccode]
     * @param $item
     * @return bool
     */
    public function insert($item)
    {
        $code = venus_unique_code("SG");
        $data = array(
            "sgb_code" => $code,
            "sgb_ctime" => venus_current_datetime(),
            "sgb_status" => $item["status"],
            "sgb_init" => $item["count"],  //spu的数量，该货品的实际数量，比如多少瓶
            "sgb_count" => $item["count"],  //spu的数量，该货品的实际数量，比如多少瓶
            "sgb_bprice" => $item["bprice"], //spu的采购价格
            "subgb_code" => $item['code'], //spu二级批次编号
            "spu_code" => $item["spucode"],//spu编码
            "sku_code" => $item["skucode"],//sku编码，该商品采购时的规格信息
            "sku_count" => $item["skucount"],//sku的数量，该商品采购时的采购数量，比如多少箱
            "gb_code" => $item["gbcode"],//所属批次编码
            "rec_code" => $item["reccode"],//所属入仓单编码
            "pos_code" => isset($item["poscode"]) ? $item["poscode"] : "NULL",//所属入仓单编码
            "war_code" => $this->warehousecode,
        );
        return M("Subgoodsbatch")->add($data) ? $item['code'] : false;
    }

    /**
     * 根据货品批次号，查询一条货品批次数据
     * @param $code
     * @return mixed
     */
    public function queryByCode($code)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb_code" => $code);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->find();
    }

    /**
     * 根据入仓单号，查询多条货品批次数据
     * @param $reccode
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByRecCode($reccode, $page = 0, $count = 100)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb_status" => array("NEQ", 5), "rec_code" => $reccode);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->where($condition)->order('sgb.sgb_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    /**
     * 根据入仓单号，查询所办函的批次货品数据
     * @param $reccode
     * @return mixed
     */
    public function queryCountByRecCode($reccode)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "rec_code" => $reccode);
        return M("Subgoodsbatch")->alias('sgb')
            ->where($condition)->fetchSql(false)->count();
    }

    /**
     * 根据条件，查询所办函的批次货品数据
     * @param $condition
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($condition, $page = 0, $count = 100)
    {
        $joincond = "";
        if (isset($condition["spucode"])) {
            $joincond = ' AND sgb.spu_code = "' . $condition["spucode"] . '"';
        }

        $condition = $this->conditionFilter($condition);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code {$joincond}")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    /**
     * 根据条件，查询数据总数
     * @param $condition
     * @return mixed
     */
    public function queryCountByCondition($condition)
    {
        $joincond = "";
        if (isset($condition["spucode"])) {
            $joincond = ' AND sgb.spu_code = "' . $condition["spucode"] . '"';
        }
        $condition = $this->conditionFilter($condition);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code {$joincond}")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->count();
    }

    /**
     * 根据货品批次单号，更新数量，采购价格，sku数量
     * @param $code
     * @param $count
     * @param $bprice
     * @param $skucount
     * @return mixed
     */
    public function updateByCode($code, $count, $bprice, $skucount)
    {
        $condition = array("war_code" => $this->warehousecode, "sgb_code" => $code);
        return M("Subgoodsbatch")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "sgb_count" => $count, "sgb_bprice" => $bprice, "sku_count" => $skucount));
    }

    /**
     * 上架修改信息
     * @param $code
     * @param $init
     * @param $count
     * @param $skucount
     * @param $poscode
     * @param $status
     * @return mixed
     */
    public function updateInitAndCountAndSkucountByCode($code, $init, $count, $skucount)
    {
        $condition = array("war_code" => $this->warehousecode, "sgb_code" => $code);
        return M("Subgoodsbatch")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "sgb_init" => $init, "sgb_count" => $count, "sku_count" => $skucount));
    }

    /**
     * 根据货品批次单号，更新货品批次状态
     * @param $code
     * @param $status
     * @return mixed
     */
    public function updateStatusByCode($code, $status)
    {
        $condition = array("war_code" => $this->warehousecode, "sgb_code" => $code);
        return M("Subgoodsbatch")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(), "sgb_status" => $status));
    }

    public function updateStatusBySubgbCode($code, $status)
    {
        $condition = array("war_code" => $this->warehousecode, "subgb_code" => $code);
        return M("Subgoodsbatch")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "sgb_status" => $status));
    }

    /**
     * 根据入仓单号，更新货品批次状态
     * @param $reccode
     * @param $status
     * @return mixed
     */
    public function updateStatusByRecCode($reccode, $status)
    {
        $condition = array("war_code" => $this->warehousecode, "rec_code" => $reccode);
        return M("Subgoodsbatch")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(), "sgb_status" => $status));
    }

    /**
     * 根据SPU编号，查询货品批次数据列表
     * @param $code
     * @return mixed
     */
    public function queryListBySpuCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "spu_code" => $code);
        return M("Subgoodsbatch")->where($condition)->order('sgb_code desc')->fetchSql(false)->select();
    }

    public function deleteBySubgbCode($code)
    {
        $condition = array("war_code" => $this->warehousecode, "subgb_code" => $code);
        return M("Subgoodsbatch")->where($condition)
            ->delete();
    }

    public function queryPrevMonth($cond, $page = 0, $count = 100)
    {
        $condition = $this->conditionFilter($cond);
        if (isset($cond["spucode"])) {
            $joincond = ' AND sgb.spu_code = "' . $cond["spucode"] . '"';
        }

        return M("Subgoodsbatch")->alias('sgb')->field('sgb.sgb_count sgb_count,sgb.sgb_bprice sgb_bprice,sgb.spu_code spu_code,
        spu.spu_name spu_name,spu.spu_unit spu_unit,spu.spu_type spu_type')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code {$joincond}")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->join("JOIN wms_receipt rec ON rec.rec_code = sgb.rec_code")
            ->order('sgb_code desc')->limit("{$page},{$count}")
            ->where($condition)->fetchSql(false)->select();
    }

    public function queryListGoodsByCondition($condition, $page = 0, $count = 100)
    {
        $joincond = "";
        if (isset($condition["spucode"])) {
            $joincond = ' AND sgb.spu_code = "' . $condition["spucode"] . '"';
        }

        $condition = $this->conditionFilter($condition);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code {$joincond}")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->join("JOIN wms_receipt rec ON rec.rec_code = sgb.rec_code")
            ->where($condition)->order('sgb.sgb_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    public function queryListBySpuCodeAndStatus($code, $status)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb.spu_code" => $code, "sgb_status" => $status);
        return M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->select();
    }

    public function queryBySpuCodeAndStatusAndReccode($code, $status,$reccode)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb.spu_code" => $code, "sgb_status" => $status,"rec_code"=>$reccode);
        $list=M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->find();
        return $list;
    }

    public function queryListByStatusAndReccode($status,$reccode)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb_status" => $status,"rec_code"=>$reccode);
        $list=M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->select();
        return $list;
    }

    public function queryListByStatusAndGbcode($status,$code)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb_status" => $status,"gb_code"=>$code);
        $list=M("Subgoodsbatch")->alias('sgb')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_spu spu ON spu.spu_code = sgb.spu_code")
            ->join("JOIN wms_sku sku ON sku.sku_code = sgb.sku_code")
            ->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->select();
        return $list;
    }

    public function deleteByCodes($codes)
    {
        $condition = array("war_code" => $this->warehousecode, "sgb_code" => array("IN", $codes));
        return M("Subgoodsbatch")->where($condition)->fetchSql(false)->delete();
    }

    public function deleteByCode($code)
    {
        $condition = array("sgb.war_code" => $this->warehousecode, "sgb.sgb_code" => $code);
        return M("Subgoodsbatch")->alias('sgb')->where($condition)->order('sgb.sgb_code desc')->fetchSql(false)->delete();
    }

    /**
     * @param $cond
     * @return array
     */
    private function conditionFilter($cond)
    {
        $condition = array("sgb.war_code" => $this->warehousecode);
        if (isset($cond["recstatus"])) {
            $condition["rec.rec_status"] = $cond["recstatus"];
        }
        if (isset($cond["subgbcode"])) {
            $condition["subgb_code"] = $cond["subgbcode"];
        }
        if (isset($cond["reccode"])) {
            $condition["rec_code"] = $cond["reccode"];
        }
        if (isset($cond["poscode"])) {
            $condition["pos_code"] = $cond["poscode"];
        }
        if (isset($cond["status"])) {
            $condition["sgb_status"] = $cond["status"];
        }
        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["sgb_ctime"] = array(array('EGT', $cond["sctime"]), array('ELT', $cond["ectime"]), 'AND');
        } else if (isset($cond["sctime"])) {
            $condition["sgb_ctime"] = array("EGT", $cond["sctime"]);
        } else if (isset($cond["ectime"])) {
            $condition["sgb_ctime"] = array("ELT", $cond["ectime"]);
        }
        return $condition;
    }
}