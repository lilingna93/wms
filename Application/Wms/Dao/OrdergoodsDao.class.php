<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class OrdergoodsDao
 * @package Wms\Dao
 */
class OrdergoodsDao extends BaseDao implements BaseDaoInterface
{
    private $dbname = "";

    function __construct()
    {
    }

    //添加数据[init,count,skucode,spucode,spucount,sprice,bprice,supcode,warcode,ucode]
    public function insert($item)
    {
        $code = venus_unique_code("G");
        $data = array(
            "goods_code" => $code,
            "goods_count" => $item["count"],//当前货品中，含有的spu的数量
            "goods_status" => 0,//是否已经收货
            "sku_code" => $item["skucode"],//spu编号
            "sku_init" => $item["skuinit"],//当前货品中下单时的sku的数量
            "sku_count" => $item["skuinit"],//当前货品中收件时sku的数量
            "w_sku_count" => $item["skuinit"],//当前货品中收件时sku的数量

            "spu_code" => $item["spucode"],//spu编号
            "spu_count" => $item["spucount"],//1个sku规格中含有的spu数量

            "spu_sprice" => $item["sprice"],    //spu的销售价
            "spu_bprice" => $item["bprice"],    //spu的采购价

            "pro_percent" => 0, //spu需要增加的客户利润$item["ppercent"]

            "profit_price" => $item["pproprice"], //spu需要增加的客户利润价   2018-10-16 新增

            "order_code" => $item["ocode"],//订单编号

            "supplier_code" => $item["supcode"],
            "war_code" => $item["warcode"],
            "user_code" => $item["ucode"],
        );
        if (isset($item['otcode'])) {
            $data['ot_code'] = $item['otcode'];
        }
        return M("Ordergoods")->add($data) ? $code : false;
    }

    //查询
    public function queryByCode($code)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->alias('goods')->field('*,goods.spu_sprice,goods.profit_price,goods.spu_bprice,goods.supplier_code,goods.war_code')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code AND goods.goods_code = '{$code}'")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code AND goods.goods_code = '{$code}'")
            ->where($condition)
            ->find();
    }

    //查询
    public function queryListByOrderCode($ocode, $page = 0, $count = 100)
    {
        $condition = array("goods.order_code" => $ocode);
        return M("Ordergoods")->alias('goods')->field('*,goods.spu_sprice,goods.profit_price,goods.spu_bprice,goods.supplier_code,goods.war_code')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code")
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = goods.supplier_code")
            ->where($condition)->order('goods.goods_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //查询
    public function queryListAscByOrderCode($ocode, $page = 0, $count = 100)
    {
        $condition = array("goods.order_code" => $ocode);
        return M("Ordergoods")->alias('goods')->field('*,goods.spu_sprice,goods.profit_price,goods.supplier_code,goods.spu_bprice')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code")
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = goods.supplier_code")
            ->where($condition)->order('goods.goods_code')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //查询
    public function queryCountByOrderCode($ocode)
    {
        $condition = array("order_code" => $ocode);
        return M("Ordergoods")->alias('goods')->field('*')
//            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code AND goods.order_code ='{$ocode}'")
//            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code AND goods.order_code ='{$ocode}'")
//            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = spu.sup_code AND goods.order_code ='{$ocode}'")
            ->where($condition)->fetchSql(false)->count();
    }

    //查询
    public function queryListByCondition($cond, $page = 0, $count = 1000)
    {
        $condition = array();
        $joinconds = array();
        if (isset($cond["ocode"])) {
            array_push($joinconds, "goods.order_code = '" . $cond["ocode"] . "'");
        }
        if (isset($cond["ocodes"])) {
            $condition["order_code"] = array("IN", $cond["ocodes"]);
        }
        if (isset($cond["supcode"])) {
            $condition["supplier_code"] = $cond["supcode"];
        }
        if (isset($cond["otcode"]) && !empty($cond["otcode"])) {
            $condition["ot_code"] = $cond["otcode"];
        }
        if (isset($cond["suptype"])) {
            $condition["sup_type"] = $cond["suptype"];
        }
        if (isset($cond["goodscount"])) {
            $condition["goods_count"] = $cond["goodscount"];
        }

        if (isset($cond["wstatus"])) {
            $condition["w_goods_status"] = $cond["wstatus"];
        }

        if (isset($cond["spusubtype"])) {
            $condition["spu_subtype"] = $cond["spusubtype"];
        }
        $joinconds = empty($joinconds) ? "" : " AND " . implode(" AND ", $joinconds);
        return M("Ordergoods")->alias('goods')->field('*,goods.spu_code,goods.sku_code,goods.supplier_code,goods.spu_sprice,goods.profit_price,goods.spu_bprice')
            ->join("wms_sku sku ON sku.sku_code = goods.sku_code {$joinconds}")
            ->join("wms_spu spu ON spu.spu_code = goods.spu_code {$joinconds}")
            ->join("wms_supplier sup ON sup.sup_code = goods.supplier_code")
            ->where($condition)->order('goods.goods_code desc')->limit("{$page},{$count}")->fetchSql(false)->select();
    }

    //总数
    public function queryCountByCondition($cond)
    {
        $condition = array();
        $joinconds = array();
        if (isset($cond["ocode"])) {
            array_push($joinconds, "goods.order_code = " . $cond["ocode"]);
        }
        if (isset($cond["ocodes"])) {
            $condition["order_code"] = array("IN", $cond["ocodes"]);
        }
        if (isset($cond["supcode"])) {
            $condition["supplier_code"] = $cond["supcode"];
        }
        if (isset($cond["otcode"]) && !empty($cond["otcode"])) {
            $condition["ot_code"] = $cond["otcode"];
        }
        if (isset($cond["suptype"])) {
            $condition["sup_type"] = $cond["suptype"];
        }
        if (isset($cond["goodscount"])) {
            $condition["goods_count"] = $cond["goodscount"];
        }

        if (isset($cond["wstatus"])) {
            $condition["w_goods_status"] = $cond["wstatus"];
        }
        $joinconds = empty($joinconds) ? "" : " AND " . implode(" AND ", $joinconds);
        return M("Ordergoods")->alias('goods')->field('*')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code {$joinconds}")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code {$joinconds}")
            ->where($condition)->fetchSql(false)->count();
    }

    //根据订单任务编号，查询隶属的所有货品
    public function queryListByOrderTaskCode($code)
    {
        $condition = array("ot_code" => $code);
        return M("Ordergoods")->alias('goods')->field('*,goods.spu_code,goods.sku_code,goods.supplier_code,goods.spu_sprice,goods.profit_price,goods.spu_bprice')
            ->join("LEFT JOIN wms_sku sku ON sku.sku_code = goods.sku_code ")
            ->join("LEFT JOIN wms_spu spu ON spu.spu_code = goods.spu_code ")
            ->join("LEFT JOIN wms_supplier sup ON sup.sup_code = goods.supplier_code")
            ->where($condition)->order('goods.goods_code desc')->limit(10000)->fetchSql(false)->select();

    }

    public function queryCountByOrderTaskCode($code)
    {
        $condition = array("ot_code" => $code);
        return M("Ordergoods")->alias('goods')
            ->where($condition)->count();
    }

    //批量更新otcode
    public function updateOtCodeByOrderCodes($codes, $otcode)
    {
        $condition = array("order_code" => array("IN", $codes));
        return M("Ordergoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "ot_code" => $otcode));
    }

    //更新otcode
    public function updateOtCodeByOrderCode($code, $otcode)
    {
        $condition = array("order_code" => $code);
        return M("Ordergoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "ot_code" => $otcode));
    }

    //清除otcode
    public function clearOtCodeByOtCode($otcode)
    {
        $condition = array("ot_code" => $otcode);
        return M("Ordergoods")->where($condition)
            ->save(array("timestamp" => venus_current_datetime(),
                "ot_code" => ""));
    }

    //更新数量和状态
    public function updateCountAndStatusByCode($code, $skucount, $goodscount, $status)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "sku_count" => $skucount, "goods_count" => $goodscount, "goods_status" => $status));
    }

    //更新数量
    public function updateCountByCode($code, $skucount, $goodscount)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "sku_count" => $skucount, "goods_count" => $goodscount));
    }

    //更新数量
    public function updateWskuCountByCode($code, $skucount)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "w_sku_count" => $skucount));
    }

    //更新状态
    public function updateStatusByCode($code, $status)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "goods_status" => $status));
    }

    //修改供应商编号
    public function updateSupCodeByCode($code, $supCode)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "supplier_code" => $supCode));
    }

    //增加根据订单编号批量修改自营供应商编号20190305
    public function updateSupCodeByOrderCode($code, $supCode)
    {
        $condition = array("order_code" => $code, "supplier_code" => "SU00000000000001");//增加根据订单编号批量修改自营供应商编号
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "supplier_code" => $supCode));
    }

    public function updateSupCodeFetchSqlByCode($code, $supCode)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(true)
            ->save(array("timestamp" => venus_current_datetime(), "supplier_code" => $supCode));
    }

    //更新状态
    public function updateWStatusByCode($code, $status)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "w_goods_status" => $status));
    }

    //更新状态
    public function updateWStatusByOrderCodes($codes, $status)
    {
        $condition = array("order_code" => array("IN", $codes));
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "w_goods_status" => $status));
    }

    //otcode更新状态
    public function updateWStatusByOtCode($otcode, $status)
    {
        $condition = array("ot_code" => $otcode);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "w_goods_status" => $status));
    }

    //删除货品
    public function removeByCode($code, $ocode)
    {
        $condition = array("goods_code" => $code, "order_code" => $ocode);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "order_code" => "-{$ocode}"));
    }

    //批量修改成本价 spucode spubprice 订单编号列表
    public function updateBpriceByOrderCodeAndSpuCodeAndSpuBprice($spuCode, $spuBprice, $orderCodeList)
    {
        $condition = array("spu_code" => $spuCode);
        $condition['order_code'] = array("in", $orderCodeList);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(), "spu_bprice" => $spuBprice));
    }

    //查询符合条件的货品（退货商品信息）
    public function queryByGcode($gCode)
    {
        $condition = array("goods_code" => $gCode);
        return M("ordergoods")->alias('good')
            ->where($condition)->fetchSql(false)->find();
    }

    //查询符合条件的货品（退货商品信息）
    public function queryByOcodeAndSkucodeAndSpucode($oCode, $skCode, $spCode)
    {
        $condition = array("order_code" => $oCode, "sku_code" => $skCode, "spu_code" => $spCode);
        return M("ordergoods")->alias('good')
            ->where($condition)->fetchSql(false)->find();
    }

    //删除订单货品，退货
    public function deleteByCode($code)
    {
        $condition = array("goods_code" => $code);
        return M("ordergoods")->where($condition)->fetchSql(false)->delete();
    }

    //小程序删除订单，同时删除该订单下的所有货品
    public function deleteByOcode($code)
    {
        $condition = array("order_code" => $code);
        return M("ordergoods")->where($condition)->fetchSql(false)->delete();
    }

    //更新数量，直采退货专用
    public function updateCountAndSkuinitAndSkucountByCode($code, $skucount, $goodscount)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "sku_count" => $skucount, "goods_count" => $goodscount, "sku_init" => $skucount));
    }

    //更新数量相关
    public function updateAllCountByCode($code, $goodscount, $skuinit, $skucount, $wskucount)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "goods_count" => $goodscount, "sku_init" => $skuinit, "sku_count" => $skucount, "w_sku_count" => $wskucount));
    }

    //更新成本价
    public function updateBpriceByCode($code, $spuBprice)
    {
        $condition = array("goods_code" => $code);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "spu_bprice" => $spuBprice));
    }

    //更新自营（SU00000000000001）商品采购价
    public function updateBpriceByCodeAndSupcode($code, $supcode, $spubprice)
    {
        $condition = array("goods_code" => $code, "supplier_code" => $supcode);
        return M("Ordergoods")->where($condition)->fetchSql(false)
            ->save(array("timestamp" => venus_current_datetime(),
                "spu_bprice" => $spubprice));
    }

    //用于统计spu每个月平均每天的销售量
    public static function queryListBySkuCode($cond)
    {
        $condition = array();
//        if(isset($cond['skCode'])){
//            $condition["sku_code"] = $cond['skCode'];
//        }
        if (isset($cond["sctime"]) && isset($cond["ectime"])) {
            $condition["order_ctime"] = array(
                array('EGT', $cond["sctime"]),
                array('ELT', $cond["ectime"]),
                'AND'
            );
        }
//        $condition["goods_status"] = 1;
        $condition["w_order_status"] = 3;
        $condition["order_is_external"] = 1;
        return M("ordergoods")->alias('goods')->field('*,goods.goods_code')
            ->join("LEFT JOIN wms_order o ON o.order_code = goods.order_code ")
            ->where($condition)->fetchSql(false)->select();
    }
}