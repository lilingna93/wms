<?php

namespace Erp\Service;

use Common\Service\ExcelService;
use Common\Service\PassportService;

class TborderService {

    public $waCode;

    function __construct()
    {
        /*$workerData = PassportService::getInstance()->loginUser();
        if(empty($workerData)){
            venus_throw_exception(110);
        }*/
        $this->waCode = $workerData["war_code"];
    }
    //private $this->$url = 'http://gw.api.taobao.com/router/rest';//正式环境
    private $url = 'http://gw.api.tbsandbox.com/router/rest';//沙盒环境  
    //淘宝已经卖出的订单 taobao.trades.sold.get
    public function taobao_olist(){
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new TradesSoldGetRequest;
        $req->setFields("tid,type,status,payment,orders,rx_audit_status");
        $req->setStartCreated("2000-01-01 00:00:00");
        $req->setEndCreated("2000-01-01 23:59:59");
        $req->setStatus("ALL_WAIT_PAY");
        $req->setBuyerNick("zhangsan");
        $req->setType("game_equipment");
        $req->setExtType("service");
        $req->setRateStatus("RATE_UNBUYER");
        $req->setTag("time_card");
        $req->setPageNo("1");
        $req->setPageSize("40");
        $req->setUseHasNext("true");
        $resp = $c->execute($req, $sessionKey);

    }
    //method taobao.trade.get
    public function order_detail(){

    }
    
    //天猫订单导入
    public function tianmao_import(){
        $datas = ExcelService::getInstance()->upload("file");
        $dicts = array(
            "A" => "skuCode",//订单单号
            "B" => "skuName",//支付单号
            "E" => "firstWarning",//买家应付款
            "F" => "secondWarning",//总金额
            "" => "",//订单状态
            "" => "",//收货人姓名
            "" => "",//收货地址
            "" => "",//联系手机号
            "" => "",//订单创建时间
            "" => "",//订单付款时间
            "" => "",//商品标题
            "" => "",//物流单号
            "" => "",//
            "" => "",//卖家订单备注
            "" => "",//扣款商家金额
        );

        $skuList = array();
        foreach ($datas as $sheetName => $list) {
            unset($list[0]);
            $skuList = array_merge($skuList, $list);
        }

        venus_db_starttrans();//启动事务
        $result = true;
        foreach ($skuList as $index => $skuItem) {
            $skuData = array();
            foreach ($dicts as $col => $key) {
                $skuData[$key] = isset($skuItem[$col]) ? $skuItem[$col] : "";
            }

            if (trim($skuData['skuCode']) == '' || trim($skuData['skuName']) == '') {
                if (trim($skuData['firstWarning']) == '' && trim($skuData['secondWarning']) == '') {
                    continue;
                } else {
                    if (trim($skuData['skuCode']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "货品编号不能为空");
                        return false;
                    }

                    if (trim($skuData['skuName']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "货品名称不能为空");
                        return false;
                    }

                    if (trim($skuData['firstWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品不足报警值不能为空");
                        return false;
                    }

                    if (trim($skuData['secondWarning']) == '') {
                        venus_db_rollback();//回滚事务
                        venus_throw_exception(1, "商品严重不足报警值不能为空");
                        return false;
                    }
                }
            } else {
                $result = $result && GoodsDao::getInstance($warCode)->updateWarningBySkuCode($skuData['skuCode'],$skuData['firstWarning'], $skuData['secondWarning'] );
                \Think\Log::write(json_encode($result.'--'.$skuData['skuCode']),'zk0311');
            }
        }
        if ($result) {
            venus_db_commit();
            /*$SkuService = new SkuService();
            $SkuService->release_latestsku();*/
            $success = true;
            $message = "导入商品预警值成功";

        } else {
            venus_db_rollback();
            $success = false;
            $message = "导入商品预警值失败";
        }
        return array($success, "", $message);
    }

    //淘宝导入
}



