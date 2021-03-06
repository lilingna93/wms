<?php
namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * SPU数据
 * Class SpuDao
 * @package Wms\Dao
 */
class SpuDao extends BaseDao implements BaseDaoInterface {


    /**
     * SpuDao constructor.
     */
    function __construct() {
    }

    //查询
    /**
     * @param $data
     * @return mixed
     */
    public function insert($data) {
        $data['war_code']=$this->warehousecode;
        return M("spu")->fetchSql(true)->add($data);
    }

    //查询
    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code) {
        $condition = array("spu.spu_code" => $code);
        return M("spu")->alias('spu')->field('*,spu.spu_code')
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
            ->join("LEFT JOIN wms_profit pro ON pro.spu_code = spu.spu_code")
            ->where($condition)->order('spu.spu_code desc')->fetchSql(false)->find();
    }

    //查询
    /**
     * @param $cond
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByCondition($cond, $page = 0, $count = 100) {
        $condition = array();
        if (isset($cond["%name%"])) {
            $spuname = str_replace(array("'","\""),"",$cond["%name%"]);
            $condition["spu_name"] = array('like', "%{$spuname}%");
        }

        if (isset($cond["name"])) {
            $condition["spu_name"] = $cond["name"];
        }

        if (isset($cond["abname"])) {
            $spuabname = str_replace(array("'","\""),"",$cond["abname"]);
            $condition["spu_abname"] = array('like', "%#{$spuabname}%");
        }

        if (isset($cond["type"])) {
            $condition["spu_type"] = $cond["type"];
        }

        if (isset($cond["subtype"])) {
            $condition["spu_subtype"] = $cond["subtype"];
        }


        return M("spu")->alias('spu')->field('*,spu.spu_code')
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
            ->where($condition)->order('spu.spu_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();

//        if (isset($cond["exwarcode"])) {
//            $exwarcode= str_replace(array("'","\""),"",$cond["exwarcode"]);
//            $joinconds[] = "pro.exwar_code = '{$exwarcode}'";
//            $joinconds = empty($joinconds) ? "" : " AND " . implode(" AND ", $joinconds);
//            return M("spu")->alias('spu')->field('*,spu.spu_code')
//                ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
//                ->join("LEFT JOIN wms_profit pro ON pro.spu_code = spu.spu_code {$joinconds}")
//                ->where($condition)->order('spu.spu_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
//        }else{
//            return M("spu")->alias('spu')->field('*,spu.spu_code')
//                ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
//                ->where($condition)->order('spu.spu_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
//        }
    }

    //总数
    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($cond) {
        //$condition = array("spu.war_code" => $this->warehousecode);
        $condition = array();
        $joinconds = array();
        if (isset($cond["%name%"])) {
            $spuname = str_replace(array("'","\""),"",$cond["%name%"]);
            $condition["spu_name"] = array('like', "%{$spuname}%");
        }
        if (isset($cond["name"])) {
            $condition["spu_name"] = $cond["name"];
        }
        if (isset($cond["abname"])) {
            $spuabname = str_replace(array("'","\""),"",$cond["abname"]);
            $condition["spu_abname"] = array('like', "%#{$spuabname}%");
        }

        if (isset($cond["type"])) {
            $condition["spu_type"] = $cond["type"];
        }
        if (isset($cond["subtype"])) {
            $condition["spu_subtype"] = $cond["subtype"];
        }

        return M("spu")->alias('spu')->field('*,spu.spu_code')
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
            ->where($condition)->order('spu.spu_code asc')->fetchSql(false)->count();
//        if (isset($cond["exwarcode"])) {
//            $exwarcode= str_replace(array("'","\""),"",$cond["exwarcode"]);
//            $joinconds[] = "pro.exwar_code = '{$exwarcode}'";
//            $joinconds = empty($joinconds) ? "" : " AND " . implode(" AND ", $joinconds);
//            return M("spu")->alias('spu')->field('*,spu.spu_code')
//                ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
//                ->join("LEFT JOIN wms_profit pro ON pro.spu_code = spu.spu_code {$joinconds}")
//                ->where($condition)->order('spu.spu_code asc')->fetchSql(false)->count();
//        }else{
//            return M("spu")->alias('spu')->field('*,spu.spu_code')
//                ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
//                ->where($condition)->order('spu.spu_code asc')->fetchSql(false)->count();
//        }
    }

    //更新销售价(2018-07-19 新添加)
    /**
     * @param $code
     * @param $spu_sprice
     * @return mixed
     */
    public function updateSpriceCodeByCode($code, $spu_sprice) {
        return M("spu")
            ->where(array("war_code" => $this->warehousecode, "spu_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "spu_sprice" => $spu_sprice));
    }

    //更新采购价(2018-07-19 新添加)
    /**
     * @param $code
     * @param $spu_bprice
     * @return mixed
     */
    public function updateBpriceCodeByCode($code, $spu_bprice) {
        return M("spu")
            ->where(array("war_code" => $this->warehousecode, "spu_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "spu_bprice" => $spu_bprice));
    }

    //更新(2018-07-19 修改)
    /**
     * @param $code
     * @param $supCode
     * @return mixed
     */
    public function updateSupCodeByCode($code, $supCode) {
        return M("spu")
            ->where(array("war_code" => $this->warehousecode, "spu_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "sup_code" => $supCode));
    }

    //更新客户代收款利润(2018-10-17 新添加)
    /**
     * @param $code
     * @param $profit
     * @return mixed
     */
    public function updateProfitByCode($code, $profit) {
        return M("spu")
            ->where(array("war_code" => $this->warehousecode, "spu_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "profit_price" => $profit));
    }

    /**
     * 查询指定货品
     */
    public function queryOneByCondition($item,$getField) {
        $cond = array();
        if(isset($item['spu_subtype'])){//二级分类
            $cond['spu.spu_subtype'] = $item['spu_subtype'];
        }
        if(isset($item['spu_brand'])){//品牌
            $cond['spu.spu_brand'] = $item['spu_brand'];
        }
        if(isset($item['spu_storetype'])){//仓储方式
            $cond['spu.spu_storetype'] = $item['spu_storetype'];
        }
        if(isset($item['spu_mark'])){//spu备注
            $cond['spu.spu_mark'] = $item['spu_mark'];
        }
        if(isset($item['spu_name'])){//spu名称
            $cond['spu.spu_name'] = $item['spu_name'];
        }
        if(isset($item['spu_norm'])){//spu规格
            $cond['spu.spu_norm'] = $item['spu_norm'];
        }
        if(isset($item['spu_unit'])){//spu单位
            $cond['spu.spu_unit'] = $item['spu_unit'];
        }
        if(isset($item['spu_count'])){//单位sku含spu数量
            $cond['sku.spu_count'] = $item['spu_count'];
        }
        if(isset($item['sku_norm'])){//sku规格
            $cond['sku.sku_norm'] = $item['sku_norm'];
        }
        if(isset($item['sku_unit'])){//sku单位
            $cond['sku.sku_unit'] = $item['sku_unit'];
        }
        if(isset($item['sku_mark'])){//sku备注
            $cond['sku.sku_mark'] = $item['sku_mark'];
        }

//        return M("spu")->where($cond)->getField($getField);
        return M("spu")->alias('spu')->field('*,spu.spu_code')
            ->join("LEFT JOIN wms_sku sku ON sku.spu_code = spu.spu_code")
            ->where($cond)->getField($getField);
    }



    public function queryAllList($cond,$count = 10000) {
        if(!isset($cond['skStatus'])){
            $condition['sku.sku_status']  = 1;
        }
        return M("spu")->alias('spu')->field('*,spu.spu_code,spu.timestamp')
            ->join("LEFT JOIN wms_sku sku ON sku.spu_code = spu.spu_code")
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code")
            ->where($condition)->order('spu.spu_code asc')->limit($count)->fetchSql(false)->select();
    }

    public function queryBySkCodeAndTimestamp($cond)
    {
        if(isset($cond['skCode'])){
            $condition['sku_code'] = $cond['skCode'];
        }

        if(isset($cond['time'])){
            $condition['timestamp'] = array('ELT',$cond['time']);
        }

        return M("goodsbatch")->field("gb_bprice")->where($condition)->order('timestamp desc')->limit(2)->fetchSql(false)->select();
    }

    public function updateSupCodeAndIsSelfsupportByCode($code, $supCode, $IsSelfsupport) {
        return M("spu")
            ->where(array("war_code" => $this->warehousecode, "spu_code" => $code))
            ->save(array("timestamp" => venus_current_datetime(), "sup_code" => $supCode, "is_selfsupport" => $IsSelfsupport));
    }

    //仅用于更新spu数据脚本
    public function queryListByCode($code)
    {
        return M("spu")->alias('spu')
            ->join("LEFT JOIN wms_sku sku ON sku.spu_code = spu.spu_code")
            ->where(array("spu.spu_code"=>$code))->find();
    }
    //仅用于更新spu数据脚本
    public function updateSpuByCode($code,$item)
    {
        $condition = array();
        if (isset($item["spu_name"])) {
            $condition['spu_name'] = $item["spu_name"];
        }
        if (isset($item["spu_type"])) {
            $condition['spu_type'] = $item["spu_type"];
        }
        if (isset($item["spu_subtype"])) {
            $condition['spu_subtype'] = $item["spu_subtype"];
        }
        if (isset($item["spu_storetype"])) {
            $condition['spu_storetype'] = $item["spu_storetype"];
        }
        if (isset($item["spu_brand"])) {
            $condition['spu_brand'] = $item["spu_brand"];
        }
        if (isset($item["spu_mark"])) {
            $condition['spu_mark'] = $item["spu_mark"];
        }
        if (isset($item["spu_norm"])) {
            $condition['spu_norm'] = $item["spu_norm"];
        }
        if (isset($item["spu_unit"])) {
            $condition['spu_unit'] = $item["spu_unit"];
        }
//        if (isset($item["spu_bprice"])) {
//            $condition['spu_bprice'] = $item["spu_bprice"];
//        }
//        if (isset($item["spu_sprice"])) {
//            $condition['spu_sprice'] = $item["spu_sprice"];
//        }
//        if (isset($item["sup_code"])) {
//            $condition['sup_code'] = $item["sup_code"];
//        }

        return M("spu")->where(array("spu_code" => $code))->fetchSql(true)->save($condition);
    }
}