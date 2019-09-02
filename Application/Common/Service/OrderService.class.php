<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/11/22
 * Time: 16:23
 */

namespace Common\Service;


class OrderService
{
    //保存类实例的静态成员变量
    private static $_instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function updatePrice($goodsList)
    {
        $totalBprice = 0;//订单总内部采购价
        $totalSprice = 0;//订单总内部销售价
        $totalSprofit = 0;//订单总内部利润金额
        $totalCprofit = 0;//订单客户总利润额
        $totalTprice = 0;//订单总金额
        foreach ($goodsList as $index => $goodsItem) {
            $bprice = bcmul($goodsItem['spu_bprice'], $goodsItem['goods_count'], 4);
            $sprice = bcmul($goodsItem['spu_sprice'], $goodsItem['goods_count'], 4);
            $totalBprice += $bprice;
            $totalSprice += $sprice;
            $totalSprofit = $totalSprice - $totalBprice;
//            $totalCprofit += round(bcmul($v['pro_percent'], $sprice, 3), 2);
            $totalCprofit += bcmul($goodsItem['profit_price'], $goodsItem['goods_count'], 4);
            $totalTprice += venus_calculate_sku_price_by_spu($goodsItem['spu_sprice'], $goodsItem['goods_count'], $goodsItem['profit_price']);
        }
        return array("totalBprice"=>$totalBprice,"totalSprice"=>$totalSprice,"totalSprofit"=>$totalSprofit,"totalCprofit"=>$totalCprofit,"totalTprice"=>$totalTprice);
    }
}