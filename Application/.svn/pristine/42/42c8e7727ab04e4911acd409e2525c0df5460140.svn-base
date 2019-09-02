<?php

namespace Erp\Dao;

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
            "order_id" => $item['order_id'],
            "tradenum" => $item["tradenum"],//订单单号
            "partner_trade_no" => $item["partner_trade_no"],//支付单号
            "price_total" => $item["price_total"],//买家应付款
            "total_fee" => $item["total_fee"],//总金额
            "order_status" => $item["order_status"],//订单状态
            "buyer_name" => $item["buyer_name"],//收货人姓名
            "buyer_address" => $item["buyer_address"],//收货地址---需要二次处理
            "buyer_message" => $item["buyer_message"],//买家留言
            "buyer_state" => $item["buyer_state"],
            "buyer_city" => $item["buyer_city"],
            "buyer_district" => $item["buyer_district"],
            "buyer_mobile" => $item["buyer_mobile"],//联系手机号---需要二次处理
            "created_at" => date("Y-m-d H:i:s"),//$item["created_at"],//订单导入建时间
            "updated_at" => date("Y-m-d H:i:s"),
            "partner_trade_no" => $item["partner_trade_no"],//订单付款时间
            "goods_name" => $item["goods_name"],//商品标题
            "logistics_num" => $item["logistics_num"],//物流单号---需要二次处理
            //"" => "",//
            "seller_message" => $item["seller_message"],//卖家订单备注
            "num" => $item["num"],//商品数量

        );
        \Think\Log::write(json_encode($data),'zk0621-插入数据');
        $data =  M("shoporders")->add($data);
        $sql = M("shoporders")->_sql();
        \Think\Log::write(json_encode($sql),'zk0621-sql');
        return $data;
        
    }
    //修改订单数据
    public function update($condition,$data){

        return M("shoporders")->where($condition)->save($data);
    }
    //客服订单列表
    public function queryBySearch($condition, $page = 0, $count = 30){
        $page2 = $count * $page;
        $data['list'] =  M('shoporders')->where($condition)->order('logistics_status asc, created_at desc')->limit($page2, $count)->select();
        $data['total'] = M('shoporders')->where($condition)->order('logistics_status')->count();
        return $data;
    }
    //仓库订单列表
    public function queryCangSearch($condition, $page = 0, $count = 30){
        $page2 = $count * $page;
        $data['list'] = M('shopdismantleorder')->alias('a')
                                               ->field("a.order_id, a.tradenum, a.id,b.shop_name, b.shop_id, b.created_at, b.buyer_name, b.buyer_mobile, a.seller_message, b.buyer_state, b.buyer_city, b.buyer_district, b.buyer_address, b.logistics_type, b.updated_at, a.logistics_status, a.num, a.color, a.merchant_code")
                                               ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                                               ->where($condition)
                                               ->order('a.logistics_status asc, b.updated_at desc')
                                               ->limit($page2, $count)
                                               ->select();
        $data['total'] = M('shopdismantleorder')->alias('a')
                                               ->field('id')
                                               ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                                               ->where($condition)
                                               ->count();                                       

        return $data;
    }

    //查询订单详细信息
    public function querymsgById($id){
        $condition['id'] = $id;
        return M('shoporders')->field("logistics_status")->where($condition)->find();
    }
    //
    public function querymsgByIds($ids){
        $condition['a.id'] = array('in', $ids);
        return M('shopdismantleorder')->alias('a')->field('a.order_id, a.merchant_code, a.seller_message, a.logistics_img')
                                                  ->join('wms_shoporders as b on a.tradenum = b.tradenum', "LEFT")
                                                  ->where($condition)
                                                  ->select(); 
    }
    //
    public function querymsgByTradenum($tradenum){
        $condition['tradenum'] = $tradenum;
        $data = M('shoporders')->field('id, order_id, seller_message, tradenum')->where($condition)->find();
        return $data;
    }
    //查询所有状态为1的订单
    public function queryBystatus($status){
        $condition['logistics_status'] = $status;
        return M('shoporders')->field('order_id, tradenum, seller_message')->where($condition)->select();
    }
    //修改物流状态
    public function updateById($id, $status){
        $condition['id'] = $id;
        $data['logistics_status'] = $status;
        return M('shoporders')->where($condition)->save($data);
    }
    //客服批量修改
    public function updateByIds($ids ,$status){
        $condition['id'] = array('in', $ids);
        $data['logistics_status'] = $status;
        $result = M('shoporders')->where($condition)->save($data);
        $arr = M('shoporders')->field('tradenum')->where($condition)->select();
        $array = array();
        foreach($arr as $v){
            $array[] = $v['tradenum'];
        }
        //修改两个列表内容
        $cond['tradenum'] = array('in', $array);
        $data2['logistics_status'] = $status;
        $data2['updated_at'] = date("Y-m-d H:i:s");
        $res =  M('shopdismantleorder')->where($cond)->save($data2);
        $sql = M('shopdismantleorder')->_sql();
        if($res && $result){
            return true;
        }else{
            return false;
        }
        
    }
    //仓库批量修改
    public function updateByCangIds($ids ,$status){
        $condition['id'] = array('in', $ids);
        $data['logistics_status'] = $status;
        $data['updated_at'] = date("Y-m-d H:i:s");
        return M('shopdismantleorder')->where($condition)->save($data);
    }
    //添加订单成本
    public function updateByOrderId($orderId, $costData){
        $condition['order_id'] = $orderId;
        $data['order_cost'] = $costData;
        return M('shopdismantleorder')->where($condition)->save($data);
    }
    //查询店铺信息
    public function queryByStoreId(){
        return M('shoporders')->field('shop_name, shop_id')->group('shop_id', 'desc')->select();
    }

  //订单号
  function get_last($created_day,$type){
    $condition['day'] = $created_day;
    $trade_data = M('trade_day_ids')->where($condition)->find();
    if(!$trade_data){
      $data = [
        'day' => $created_day,
        'values' => 0,
        'values2' => 0
      ];
      $res = M('trade_day_ids')->add($data);
      if($res === false){
        return $res;
      }
      return self::get_last($created_day, $type);
    }
    if($type == 1){
        $data2['values'] = $trade_data['values'] + 1;
    }elseif($type == 2){
       $data2['values2'] = $trade_data['values2'] + 1; 
    }
    

    $ret = M('trade_day_ids')->where($condition)->save($data2);
    if(!$ret){
      return false;
    }
    if($type == 1){
        return $data2['values'];
    }else{
        return $data2['values2'];
    }
    
  }

  //
  public function goodsList($ids){
    $condition['a.id'] = array('in', $ids);
    $data = M('shoporders')->alias('a')
                           ->field("a.order_id, a.tradenum, a.buyer_name, a.buyer_mobile, a.seller_message, a.goods_name, b.number, b.merchant_code")
                           ->join('wms_shoporderdetail as b on a.tradenum = b.tradenum')
                           ->where($condition)
                           ->select();

    return $data;                       
  }
  //修改面时间
  public function updatedTime($ids){
    $condition['id'] = array('in', $ids);
    $data['updated_at'] = date('Y-m-d H:i:s');
    $res = M('shoporders')->where($condition)->save($data);
    return $res;
  }

  //
  public function queryCountByCondition($condition){
    return M("fileslog")->where($condition)->order('id desc')->select();
  }


}