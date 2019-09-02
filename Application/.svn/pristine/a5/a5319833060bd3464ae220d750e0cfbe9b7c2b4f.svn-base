<?php

namespace Erp\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class GoodsDao
 * @package Wms\Dao
 */
class ShoporderdetailDao extends BaseDao implements BaseDaoInterface
{


    /**
     * GoodsDao constructor.
     */
    function __construct()
    {

    }
    /**
     * 添加数据[init,count,spucode]
     * @param $item $shop 
     * @return bool
     */
    public function insert($item)
    {
        $data = array(
            "order_id"      => $item["order_id"],
            "tradenum"      => $item["tradenum"],//订单单号
            "created_at"    => date("Y-m-d H:i:s"),
            //"seller_message"=> $item["seller_message"],
            "number"        => $item["num"],//商品数量
            "merchant_code" =>  $item["merchant"],//商品编号
        );
        return M("shoporderdetail")->add($data);
        
    }
    //判断订单详情信息是否已经插入进来
    public function detailExist($tradenum, $merchant){
        $condition['tradenum'] = $tradenum;
        $condition['merchant_code'] = $merchant;
        return M('shoporderdetail')->where($condition)->find();
    }
    //根据订单号查询订单详情
    public function orderDetail($tradenum){
        $condition['tradenum'] = $tradenum;
        return M('shoporderdetail')->field('merchant_code, number')->where($condition)->select();
    }
    //该订单详情信息是否已经插入进来
    public function queryDetail($tradenum, $merchant){
        $condition['tradenum'] = $tradenum;
        $condition['merchant_code'] = $merchant;
        return M('shopdismantleorder')->where($condition)->find();
    }
    //插入拆单数据
    public function insideAdd($item){
        
        $data = array(
            "order_id" => $item["order_id"],
            "tradenum" => $item["tradenum"],//订单单号
            "seller_message"=> $item["seller_message"],
            "created_at" => date("Y-m-d H:i:s"),
            "merchant_code" =>  $item["merchant"],//商品编号
        );
        if(!empty($item['number'])){
            $data['num'] = $item['number'];
        }else{
            $data['num'] = 1;
        }
        return M("shopdismantleorder")->add($data); 
    }
    //查询所有要打印的订单
    public function goodsList($ids){
        $condition['a.id'] = array('in', $ids);
        $data = M('shopdismantleorder')->alias('a')
                           ->field("a.order_id, a.tradenum, b.buyer_name, b.buyer_mobile, a.seller_message, b.goods_name, a.num, a.merchant_code, a.logistics_num")
                           ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                           ->where($condition)
                           ->order('a.id')
                           ->select();
        return $data;                       
    }
    //导出所有到excel
    public function goodsLists($condition, $page=0, $size=30){
        $page2 = $size * $page;
        $data['list'] = M('shopdismantleorder')->alias('a')
                           ->field("a.order_id, a.tradenum, b.buyer_name, b.buyer_mobile, a.seller_message, b.goods_name, a.num, a.merchant_code, a.logistics_num, a.order_cost")
                           ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                           ->where($condition)
                           ->order('a.id desc')
                           ->limit($page2, $size)
                           ->select();
        $data['total'] = M('shopdismantleorder')->alias('a')
                           ->field("a.order_id, a.tradenum, b.buyer_name, b.buyer_mobile, a.seller_message, b.goods_name, a.num, a.merchant_code, a.logistics_num")
                           ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                           ->where($condition)
                           ->count();
        return $data;                       
    }

    public function queryByDetailnum($tradenum){
        $condition['tradenum'] = $tradenum;
        $data = M('shoporderdetail')->field("order_id, created_at,number,merchant_code")
                                       ->where($condition)
                                       ->select();
        return $data;  
    }

    //
    public function detailBytradenum($tradenum){
        $condition['tradenum'] = $tradenum;
        $data = M('shopdismantleorder')->field("order_id, created_at,num,merchant_code, logistics_num, logistics_status")
                                       ->where($condition)
                                       ->select();
        return $data;                       
    }
    //判断该订单该商品有没有重复
    public function detailByMerchant($tradenum, $merchant){
        $condition['tradenum'] = $tradenum;
        $condition['merchant_code'] = $merchant;
        $data = M('shopdismantleorder')->field("order_id, created_at,num,merchant_code, logistics_num, logistics_status")
                                       ->where($condition)
                                       ->select();
        return $data;  
    }
    //查询拆单的最后一条内部订单号
    public function queryLastOrderId($tradenum){
        $condition['tradenum'] = $tradenum;
        $data = M('shopdismantleorder')->field("order_id")
                                       ->where($condition)
                                       ->order('id desc')
                                       ->find();
        return $data;   
    }
    public function queryByTradenum($list){
        $condition['tradenum'] = array('in', $list);
        $data = M('shopdismantleorder')->field('tradenum, logistics_num')->where($condition)->group('tradenum')->select();
        return $data;
    }
    //查询商品重量
    public function queryBySku($sku){
        $condition['sku_code'] = $sku;
        $data = M('erpsku')->field('sku_mark,sku_name,sku_weight')->where($condition)->find();
        /*$sql = M('erpsku')->_sql();
        \Think\Log::write('SQL信息-'.json_encode($sql),'zk0619-b');*/
        return $data;
    }
    //查询所有订单状态
    public function queryBystatus($status){
        $condition['logistics_status'] = $status;
        return M('shopdismantleorder')->field('id')->where($condition)->limit(10)->select();
    }


    //handorder
    public function handInsert($item){
        $data = array(
            'tradenum' => $item['tradenum'],
            'merchant_code' => $item['merchant_code'],
            'created_at' => date('Y-m-d H:i:s'),
        );
        return M('handorder')->add($data);
    }
    //handorder 
    public function contentBytradenum($tradenum){
        $condition['tradenum'] = $tradenum;
        return M('handorder')->where($condition)->select();
    }
    //修改人工拆单内容
    public function updateHandOrder($orderId, $item){
        $arr = explode(',', $item);
        $condition['id'] = array('in', $arr);
        $data = array(
            'order_id' => $orderId,
            'updated_at' => date('Y-m-d H:i:s'),
            'statut' => 1,
        );
        return M('handorder')->where($condition)->save($data);
    }

}