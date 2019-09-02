<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 发货清单数据
 * Class IgoodsDao
 * @package Wms\Dao
 */
class IgoodsDao extends BaseDao implements BaseDaoInterface
{


    /**
     * IgoodsDao constructor.
     */
    function __construct()
    {
    }

    //添加数据[status,count,spucode,reccode]

    /**
     * @param $item
     * @return bool
     */
    public function insert($item)
    {

        $code = venus_unique_code("GO");
        $data = array(
            "igo_code" => $code,
            "igo_count" => $item["count"],  //要出仓的spu数量
            "spu_code" => $item["spucode"], //要出仓的sku编号
            "spu_sprice" => $item["sprice"],//货品的销售价
            "spu_pprice" => $item["pprice"],//货品的利润价
            "spu_percent" => $item["percent"],//货品的利润点
            "goods_code" => $item["goodscode"],//所属库存编号
            "sku_code" => $item["skucode"],//规格上所属的sku编号
            "sku_count" => $item["skucount"],//规格上对应的sku数量
            "inv_code" => $item["invcode"],//所属的出仓单编号
            "war_code" => $this->warehousecode,//所属仓库编号
        );
        return M("Igoods")->add($data) ? $code : false;
    }
    //查询

    /**
     * @param $code
     * @return mixed
     */
    public function queryByCode($code)
    {
        $condition = array("igo.war_code" => $this->warehousecode, "igo_code" => $code);
        return M("Igoods")->alias('igo')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = igo.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = igo.spu_code")
            ->where($condition)->order('igo.igo_code desc')->find();
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
        $condition = array("igo.war_code" => $this->warehousecode);
        return M("Igoods")->alias('igo')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = igo.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = igo.spu_code")
            ->where($condition)->order('igo.igo_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }
    //总数

    /**
     * @param $cond
     * @return mixed
     */
    public function queryCountByCondition($cond)
    {
        $condition = array("igo.war_code" => $this->warehousecode);
        return M("Igoods")->alias('igo')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = igo.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = igo.spu_code")
            ->where($condition)->order('igo.igo_code desc')->fetchSql(false)->count();
    }


    //根据出仓单号，查询多条货品数据

    /**
     * @param $invcode
     * @param int $page
     * @param int $count
     * @return mixed
     */
    public function queryListByInvCode($invcode, $page = 0, $count = 100)
    {
        $condition = array("igo.war_code" => $this->warehousecode, "inv_code" => $invcode);
        return M("Igoods")->alias('igo')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = igo.sku_code")
            ->join("JOIN wms_spu spu ON spu.spu_code = igo.spu_code")
            ->join("JOIN wms_supplier sup ON spu.sup_code = sup.sup_code")
            ->where($condition)->order('igo.igo_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }
    //根据出仓单号，查询多条货品数量

    /**
     * @param $invcode
     * @return mixed
     */
    public function queryCountByInvCode($invcode)
    {
        $condition = array("igo.war_code" => $this->warehousecode, "inv_code" => $invcode);
        return M("Igoods")->alias('igo')
            ->where($condition)->fetchSql(false)->count();
    }


    /**
     * @param $code
     * @param $count
     * @return mixed
     */
    public function updateByCode($code, $count)
    {
        $condition = array("war_code" => $this->warehousecode, "igo_code" => $code);
        return M("Igoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "igo_count" => $count));
    }

    /**
     * @param $code
     * @param $goodsCode
     * @return mixed
     */
    public function updateGoodsCodeByCode($code, $goodsCode)
    {
        $condition = array("war_code" => $this->warehousecode, "igo_code" => $code);
        return M("Igoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "goods_code" => $goodsCode));
    }


    /**
     * @param $code
     * @param $invcode
     * @return mixed
     */
    public function deleteByCode($code, $invcode)
    {
        $condition = array("war_code" => $this->warehousecode, "igo_code" => $code, "inv_code" => $invcode);
        return M("Igoods")->where($condition)->fetchSql(false)->delete();
    }

    //退货，直采专用
    public function updateCountAndSkuCountByCode($code, $count, $skucount)
    {
        $condition = array("war_code" => $this->warehousecode, "igo_code" => $code);
        return M("Igoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "igo_count" => $count, "sku_count" => $skucount));
    }

    public function queryByInvCodeAndSkuCode($invcode, $skucode)
    {
        $condition = array("igo.war_code" => $this->warehousecode, "igo.inv_code" => $invcode, "igo.sku_code" => $skucode);
        return M("Igoods")->alias('igo')->field('*,spu.spu_code,sku.sku_code')
            ->join("JOIN wms_sku sku ON sku.sku_code = igo.sku_code AND sku.sku_code = '{$skucode}'")
            ->join("JOIN wms_spu spu ON spu.spu_code = igo.spu_code")
            ->join("JOIN wms_supplier sup ON spu.sup_code = sup.sup_code")
            ->where($condition)->fetchSql(false)->find();
    }

    //退货拆分订单修改所属出仓单
    public function updateInvCodeByCode($code, $invcode)
    {
        $condition = array("war_code" => $this->warehousecode, "igo_code" => $code);
        return M("Igoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(), "inv_code" => $invcode));
    }
}