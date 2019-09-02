<?php

namespace Wms\Service;

use Common\Service\ExcelService;
use Common\Service\PassportService;
use Wms\Dao\ShopordersDao;

class OrderonlineService {

    public $waCode;

    function __construct()
    {
        /*$workerData = PassportService::getInstance()->loginUser();
        \Think\Log::write(json_encode(session_id()),'zk0418');
        \Think\Log::write(json_encode($workerData),'zk0418');

        if(empty($workerData)){
            venus_throw_exception(110);
        }*/
        $this->warCode = 'WA000001';//$workerData["war_code"];
        $this->worCode = 'WO40428134034434';//$workerData["wor_code"];
    }
    //店铺列表
    public function store_list(){
      $orderModel = ShopordersDao::getInstance($warCode);  
      $data = $orderModel->queryByStoreId();
      return  array(true, $data, '店铺列表查询成功');
    }
    //
    public function order_list(){
        $status = $_POST['data']['status'];
        $post = $_POST['data'];
        $shopId = $post['shopId'];
        $orderNum = $post['tradeNum'];
        $mobile = $post['mobile'];
        $buyerName = $post['buyerName'];
        $page = $post['pageCurrent'];
        $count = $post['pageSize'];
        if(!empty($shopId)){
            $condition['shop_id'] = $shopId;
        }
        if(!empty($orderNum)){
            $condition['tradenum'] = $orderNum;
        }
        if(!empty($mobile)){
            $condition['buyer_mobile'] = $mobile;
        }
        if(!empty($buyerName)){
            $condition['buyer_name'] = $buyerName;
        }
        $list = array();
        if($this->worCode == 'WO40428134019222'){
            $condition['logistics_status'] = array('in', array(0,1,2,3)); 
            $role = 1;
        }elseif($this->worCode == 'WO40428134034434'){
            $condition['logistics_status'] = 2;
            $role = 2;
        }
        $orderModel = ShopordersDao::getInstance($warCode);
        $list = $orderModel->queryBySearch($condition, $page, $count);
        $list['role'] = $role;
        return array(true, $list, '');

    }
    //method taobao.trade.get
    public function order_detail(){

    }
    
    //客服审核通过/批量审核通过
    public function status_update(){
        $status = $_POST['data']['status'];
        $ids = $_POST['data']['id'];//审核通过的id
        $orderModel = ShopordersDao::getInstance($warCode);
        /*$res = $orderModel->querymsgById($id);
        if(!$res){
            return array(false, '', '未查询到数据');
        }
        if($res["logistics_status"] !== 0){
            return array(false, '', '订单状态不对哦');
        }*/

        $data = $orderModel->updateByIds($ids, 1);
        return array(true, '', '审核通过');
    }

    //打印面单//批量打印面单
    public function get_pdf(){
        $ids = $_POST['data']['ids'];
        $orderModel = ShopordersDao::getInstance($warCode);
        $data = $orderModel->querymsgByIds($ids);
        $res = $this->make_pdf($data);
        if($res){
            $result = $orderModel->updateByIds($ids, 3);
            if(!$result){
                return array(false, '', '修改面单状态失败');
            }
        }     
        return array(true, $res, '返回url成功');
    }

    //将图片转换成PDF
    public function make_pdf($arr){
        $im = new Imagick();    
        for( $i=0;$i<3;$i++ ) 
        { 
            $auxIMG = new Imagick(); 
            $auxIMG->$auxIMG->readImage($arr[$i]);//readImage('001.jpg');
            $im->addImage($auxIMG); 
        }
        $name = md5(time());
        $res = $im->writeImages($name.'.pdf', true); 
        if($res){
            return $name.'.pdf';//生成的PDF路径
        }
    }

    //EXcel订单导入
    public function order_import(){
        $shopId = 1;
        $shopmes = array(
            'name' => '禾先生',
            'shop_id' => 1,
            'shop_type' => 0,
        );
        $datas = ExcelService::getInstance()->upload("file");
        $dicts = array(
            "A" => "tradenum",//订单单号
            "B" => "buyer_name",//收货人姓名
            "D" => "partner_trade_no",//支付单号
            "F" => "price_total",//买家应付款
            "I" => "total_fee",//总金额
            "M" => "order_status",//订单状态---需要二次处理  
            "P" => "address",//收货地址---需要二次处理
            "S" => "buyer_mobile",//联系手机号
            "T" => "created_at",//订单创建时间
            "U" => "partner_trade_no",//订单付款时间
            "V" => "goods_name",//商品标题
            "X" => "logistics_num",//物流单号---需要二次处理
            //"" => "",//
            //"" => "seller_message",//卖家订单备注
            "AA" => "num",//商品数量
            //"Y" => "",//扣款商家金额
        );

        $skuList = array();
        foreach ($datas as $sheetName => $list) {
            unset($list[0]);
            $skuList = array_merge($skuList, $list);
        }

        venus_db_starttrans();//启动事务
        $result = true;
        $filter[0] = "/=/";
        $filter[1] = '/"/';
        $filter[2] = "/'/";
        $filtered[2] = "";
        $filtered[1] = "";
        $filtered[0] = "";
        foreach ($skuList as $index => $orderItem) {
            $orderData = array();
            foreach ($dicts as $col => $key) {
                $orderData[$key] = isset($orderItem[$col]) ? preg_replace($filter, $filtered, $orderItem[$col]) : "";
            }
            //\Think\Log::write(json_encode($orderData["partner_trade_no"]),'zk0428b');
            if(!empty($orderData['address'])){
                $address = explode(' ', $orderData['address']);
                $orderData['buyer_state'] = $address[0];
                $orderData['buyer_city'] = $address[1];
                $orderData['buyer_district'] = $address[2];
                $orderData['buyer_address'] = $address[3];
            }
            $orderStatus = $orderData['order_status'];
            if(!empty($orderStatus)){
                if($orderStatus == '买家已付款，等待卖家发货'){
                    $orderData['logistics_status'] = 0;
                }else{
                    continue;
                } 
            }
            

            if (trim($orderData['tradenum']) == '' || trim($orderData['partner_trade_no']) == '') {
                if (trim($orderData['address']) == '' && trim($orderData['buyer_mobile']) == '') {
                    continue;
                } else {
                    if (trim($orderData['address']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "买家地址不能为空");
                        return false;
                    }
                    if (trim($orderData['buyer_mobile']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "买家手机号不能为空");
                        return false;
                    }

                   /* if (trim($orderData['firstWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品不足报警值不能为空");
                        return false;
                    }

                    if (trim($orderData['secondWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品严重不足报警值不能为空");
                        return false;
                    }*/
                }
            } else {
                $orderModel = ShopordersDao::getInstance($warCode);
                //查询订单号判断该订单是否已经存在
                $res = $orderModel->querymsgByTradenum($orderData['tradenum']);
                if($res){
                    continue;
                }
                $result = $result && $orderModel->insert($orderData, $shopmes);
            }
            
        }
        if ($result) {
            venus_db_commit();
            /*$SkuService = new SkuService();
            $SkuService->release_latestsku();*/
            $success = true;
            $message = "店铺订单导入成功";

        } else {
            venus_db_rollback();
            $success = false;
            $message = "店铺订单导入失败";
        }
        return array($success, "", $message);
    }
    
}



