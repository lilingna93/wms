<?php

namespace Wms\Dao;

use Common\Common\BaseDao;
use Common\Common\BaseDaoInterface;

/**
 * 库存数据
 * Class GoodsDao
 * @package Wms\Dao
 */
class ShopordersDao extends BaseDao implements BaseDaoInterface
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
    public function insert($item,$shop)
    {
        $data = array(
            "shop_name" => $shop['name'],
            "shop_id" => $shop["shop_id"],
            "shop_type" => $shop["shop_type"],
            "tradenum" => $item["tradenum"],//订单单号
            "partner_trade_no" => $item["partner_trade_no"],//支付单号
            "price_total" => $item["price_total"],//买家应付款
            "total_fee" => $item["total_fee"],//总金额
            "order_status" => $item["order_status"],//订单状态
            "buyer_name" => $item["buyer_name"],//收货人姓名
            "buyer_address" => $item["buyer_address"],//收货地址---需要二次处理
            "buyer_state" => $item["buyer_state"],
            "buyer_city" => $item["buyer_city"],
            "buyer_district" => $item["buyer_district"],
            "buyer_mobile" => $item["buyer_mobile"],//联系手机号---需要二次处理
            "created_at" => $item["created_at"],//订单创建时间
            "partner_trade_no" => $item["partner_trade_no"],//订单付款时间
            "goods_name" => $item["goods_name"],//商品标题
            "logistics_num" => $item["logistics_num"],//物流单号---需要二次处理
            //"" => "",//
           // "seller_message" => $item["seller_message"],//卖家订单备注
            "num" => $item["num"],//商品数量

            /*"tradenum" => $item["tradenum"],
            "created_at" => $item["created_at"],
            "buyer_name" => $item["buyer_name"],
            "buyer_message" => $item["buyer_message"],
            "buyer_address" => $item['buyer_address'], 
            "seller_message" => $item['seller_message'],
            "goods_name" => $this->warehousecode,
            "shop_sku_code" => $item['shop_sku_code'],
            "sku_code" => $item['sku_code'],
            "num" => $item['num'],
            "price_total" => $item['price_total'],
            "total_fee" => $item['total_fee'],
            "logistics_name" => $item['logistics_name'],
            "logistics_num" => $item['logistics_num'],
            "logistics_status" => $item['logistics_status'],
            "order_status" => $item['order_status'],
            "updated_at" => $item['updated_at'],
            "pay_time" => $item['pay_time'],
            "partner_trade_no" => $item['partner_trade_no'],
            "platpartner_type" => $item['platpartner_type'],
            "complete_time" => $item['complete_time'],*/

        );
        return M("shoporders")->add($data);
        
    }
    //修改订单数据
    public function update($condition,$data){

        return M("shoporders")->where($condition)->save($data);
    }
    //
    public function queryBySearch($condition, $page = 0, $count = 30){
        \Think\Log::write(json_encode($page),'zk0428b');
        \Think\Log::write(json_encode($count),'zk0428b');
        $page2 = $count * $page;
        $data['list'] =  M('shoporders')->where($condition)->order('logistics_status')->limit($page2, $count)->select();
        $data['total'] = M('shoporders')->where($condition)->order('logistics_status')->count();
        return $data;
    }
    //查询订单详细信息
    public function querymsgById($id){
        $condition['id'] = $id;
        return M('shoporders')->field("logistics_status")->where($condition)->find();
    }
    //
    public function querymsgByIds($ids){
        $condition['id'] = array('in', $ids);
        return M('shoporders')->field("logistics_img")->where($condition)->select();
    }
    //
    public function querymsgByTradenum($tradenum){
        $condition['tradenum'] = $tradenum;
        return M('shoporders')->field('tradenum')->where($condition)->find();
    }
    //修改物流状态
    public function updateById($id, $status){
        $condition['id'] = $id;
        $data['logistics_status'] = $status;
        return M('shoporders')->where($condition)->save($data);
    }
    //批量修改
    public function updateByIds($ids ,$status){
        $condition['id'] = array('in', $ids);
        $data['logistics_status'] = $status;
        return M('shoporders')->where($condition)->save($data);
    }
    //查询店铺信息
    public function queryByStoreId(){
        return M('shoporders')->field('shop_name, shop_id')->group('shop_id', 'desc')->select();
    }

}