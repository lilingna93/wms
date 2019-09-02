<?php

namespace Wms\Service;

use Common\Service\PassportService;
use Common\Service\PHPRpcService;
use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Wms\Dao\ReturnDao;

class ReturnService
{

    /*
     *
      0:"实收不足,产生退货"
      1:"商品包装破损",
      2:"实际到货商品与页面描述不符",
      3:"商品保质期已过半",
      4:"下错单（数量、品牌、规格）",
      5:"其它（电话沟通）"
     * */
//    static private $RETURN_TYPE_DATARETURN = 1;//数量不足，退回不足货品数量
//    static private $RETURN_TYPE_GOODSRETURN = 2;//质量不符，退回相应数量货品

    static private $RETURN_STATUS_CREATE = 1;//退货申请单状态：申请中
    static private $RETURN_STATUS_CONFIRM = 2;//退货申请单状态：已处理
    static private $RETURN_STATUS_REJECT = 3;//退货申请单状态：已拒绝

    static protected $ORDER_STATUS_HANDLE_CREATE = 1;//待处理
    static protected $ORDER_STATUS_HANDLE = 2;//处理中
    static protected $ORDER_STATUS_HANDLE_FINISH = 3;//已处理
    static protected $ORDER_STATUS_HANDLE_CANCEL = 4;//已取消
    static protected $ORDERGOODS_STATUS_HANDLE_CREATE = 1;//待处理
    static protected $ORDERGOODS_STATUS_HANDLE = 2;//处理中
    static protected $ORDERGOODS_STATUS_HANDLE_FINISH = 3;//已处理

    public $waCode;

    function __construct()
    {
        $userData = PassportService::getInstance()->loginUser();
        if (empty($userData)) {// || $userData["type"] !== "oms"
            venus_throw_exception(110);
        }
        $this->uCode = $userData["user_code"];
        $this->waCode = $userData["warehousecode"];
        $this->uToken = $userData["user_token"];
        $this->workerWarehouseCode = $userData["war_code"];//user所代表的第三方仓库工作人员的仓库编号
    }

    /**
     * 小程序端发起创建退货单
     */
    public function returngoods_create()
    {

        $ReturntaskService = new ReturntaskService();
        return $ReturntaskService->returngoods_create();

        /*$post = json_decode($_POST['data'], true);
         * $gCode = $post['gcode'];
        $count = $post['count'];//退货数量
        $ogrType = $post['type'];//退货原因
        $warName = $post['rname'];//项目组名称
        $ogrStatus = 1;
        if (empty($gCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }

        if (empty($count)) {
            venus_throw_exception(1, "退回数量不能为空");
            return false;
        }

        if (empty($ogrType)) {
            venus_throw_exception(1, "退货原因不能为空");
            return false;
        }

        if (empty($warName)) {
            venus_throw_exception(1, "项目组名称不能为空");
            return false;
        }

        venus_db_starttrans();
        //查询当前申请退货商品的信息
        $orderGoodsList = OrdergoodsDao::getInstance()->queryByGcode($gCode);

        //查询是否重复申请
        $returnGoods = ReturnDao::getInstance()->queryBy0codeAndSkcodeAndSpcode($orderGoodsList['order_code'], $orderGoodsList['sku_code'], $orderGoodsList['spu_code']);
        if (!empty($returnGoods)) {
            return array(false, "", "此货品已提交过申请\n请不要重复提交");
        }
        if ($ogrStatus == self::$RETURN_STATUS_CREATE) {
            $orderGoodsReturnData = array(
                "otype" => $ogrType,
                "ostatus" => 1,
                "gcode" => $gCode,
                "gcount" => $count,
                "skucode" => $orderGoodsList['sku_code'],
                "skucount" => $orderGoodsList['sku_count'],
                "spucode" => $orderGoodsList['spu_code'],
                "spucount" => $orderGoodsList['spu_count'],
                "sprice" => $orderGoodsList['spu_sprice'],
                "sbrice" => $orderGoodsList['spu_bprice'],
                "percent" => $orderGoodsList['pro_percent'],
                "proprice" => $orderGoodsList['profit_price'],
                "ocode" => $orderGoodsList['order_code'],
                "otcode" => $orderGoodsList['ot_code'],
                "supcode" => $orderGoodsList['supplier_code'],
                "ucode" => $orderGoodsList['user_code'],
                "warcode" => $orderGoodsList['war_code'],
                "warname" => $warName,
            );

            $ReturnAddResult = ReturnDao::getInstance()->insert($orderGoodsReturnData);
            $ordergoodsData = OrdergoodsDao::getInstance()->queryByCode($gCode);
            //更新数量
            $uptOrdergoodsRes = OrdergoodsDao::getInstance()->updateCountByCode($gCode, $ordergoodsData['sku_count'] - $count, $ordergoodsData['goods_count'] - $count * $orderGoodsList['spu_count']);
//            $uptOrdergoodsWskuCount = OrdergoodsDao::getInstance()->updateWskuCountByCode($gCode,$ordergoodsData['sku_count'] - $count);
            $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($orderGoodsList['order_code'], 0, 10000);
            $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);
            $uptOrderRes = OrderDao::getInstance()->updatePriceByCode($orderGoodsList['order_code'], $uptOrderData['totalBprice'], $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
            if ($ReturnAddResult && $uptOrdergoodsRes && $uptOrderRes) {
                $orderList = OrderDao::getInstance()->queryByCode($orderGoodsList['order_code']);
                $result = PHPRpcService::getInstance()->request($orderList['user_token'], "venus.wms.return.return.goods.create", array(
                    "skCode" => $orderGoodsList['sku_code'],
                    "oCode" => $orderGoodsList['order_code'],
                    "skCount" => $count * $orderGoodsList['spu_count'],
                    "warCode" => $orderList['war_code'],
                ));
                if (!$result["success"]) {
                    venus_db_rollback();
                    $success = false;
                    $message = $result["message"];
                } else {
                    venus_db_commit();
                    $success = true;
                    $message = "退货单添加成功";
                }
            } else {
                venus_db_rollback();
                $success = false;
                $message = "退货单添加失败";

            }
        } else {
            venus_db_rollback();
            $success = false;
            $message = "此退货单不支持此操作";
        }

        return array($success, "", $message);*/
        //业务逻辑，需要考虑小程序提交的数据中哪些是关键数据，已经如何根据关键数据完成退货申请单的创建，这块需数据模型及Dao层设计有关
    }


    /**
     * 管理端查询退货申请单
     */
    public function returngoods_search()
    {
        $post = $_POST['data'];
        $ogrStatus = $post['ogrStatus'];
        $ogrType = $post['ogrType'];
        $warCode = $post['warCode'];
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数

        if (!empty($ogrStatus)) {
            $condition['ogrStatus'] = $ogrStatus;
        }

        if (!empty($ogrType)) {
            $condition['ogrType'] = $ogrType;
        }

        if (!empty($warCode)) {
            $condition['warCode'] = $warCode;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $returnDao = ReturnDao::getInstance();
        $totalCount = $returnDao->queryCountByCondition($condition);//获取指定条件的总条数

        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $returnResults = $returnDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);
        if (empty($returnResults)) {
            $returnList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $returnList["list"] = array();
        } else {
            $returnList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($returnResults as $index => $returnItem) {
                $returnList["list"][$index] = array(
                    "warName" => $returnItem['war_name'],//项目组名称
                    "oCode" => $returnItem['order_code'],//采购单单号
                    "ogrCode" => $returnItem['ogr_code'],//货品编号（退货单单号）
                    "spName" => $returnItem['spu_name'],//货品名称
                    "skNorm" => $returnItem['sku_norm'],//规格
                    "spBrand" => $returnItem['spu_brand'],//品牌
                    "spFrom" => $returnItem['spu_from'],//产地
                    "spMark" => $returnItem['spu_mark'],//备注
                    "typeName" => venus_return_type_name($returnItem['ogr_type']),//退货原因
                    "gCount" => floatval($returnItem['goods_count']),//退货数量
                    "skUnit" => $returnItem['sku_unit'],//单位
                    "statusName" => venus_return_status_name($returnItem['ogr_status']),//申请状态
                );
            }
        }
        return array(true, $returnList, "");
    }

    /**
     * 小程序查询退货申请单
     */
    public function returngoods_searchs()
    {
        $ReturntaskService = new ReturntaskService();
        return $ReturntaskService->returngoods_searchs();

        /*$post = json_decode($_POST['data'], true);
        $ogrStatus = $post['ogrStatus'];
        $ogrType = $post['ogrType'];
        $warCode = $post['warCode'];
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数

        if (!empty($ogrStatus)) {
            $condition['ogrStatus'] = $ogrStatus;
        }

        if (!empty($ogrType)) {
            $condition['ogrType'] = $ogrType;
        }

        if (!empty($warCode)) {
            $condition['warCode'] = $warCode;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $returnDao = ReturnDao::getInstance();
        $totalCount = $returnDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $returnResults = $returnDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);
        if (empty($returnResults)) {
            $returnList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $returnList["list"] = array();
        } else {
            $returnList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($returnResults as $index => $returnItem) {
                $count = bcmul($returnItem['goods_count'],$returnItem['spu_count'],2);
                $spSprice = venus_calculate_sku_price_by_spu($returnItem['spu_sprice'], $count, $returnItem['profit_price']);
                $returnList["list"][$index] = array(
                    "warName" => $returnItem['war_name'],//项目组名称
                    "oCode" => $returnItem['order_code'],//采购单单号
                    "ogrCode" => $returnItem['ogr_code'],//货品编号（退货单单号）
                    "spName" => $returnItem['spu_name'],//货品名称
                    "spImg" => $returnItem['spu_img'],//货品图片
                    "spSprice" => ($spSprice == intval($spSprice)) ? intval($spSprice) : round($spSprice, 2),//货品价格
                    "skNorm" => $returnItem['sku_norm'],//规格
                    "spBrand" => $returnItem['spu_brand'],//品牌
                    "spFrom" => $returnItem['spu_from'],//产地
                    "spMark" => $returnItem['spu_mark'],//备注
                    "typeName" => venus_return_type_name($returnItem['ogr_type']),//退货原因
                    "gCount" => floatval($returnItem['goods_count']),//退货数量
                    "skUnit" => $returnItem['sku_unit'],//单位
                    "statusName" => venus_return_status_name($returnItem['ogr_status']),//申请状态
                );
            }
        }
        return array(true, $returnList, "");*/
    }

    /**
     * 管理端确认退货申请单
     */
    public function returngoods_confirm()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrStatus = 1;//确认退货
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $returnList = ReturnDao::getInstance()->queryByCode($ogrCode);
        $returngoodsList = array(
            "skCode" => $returnList['sku_code'],
            "oCode" => $returnList['order_code'],
            "skCount" => $returnList['goods_count'],
            "count" => bcmul($returnList['goods_count'], $returnList['spu_count']),
            "spCode" => $returnList['spu_code'],
        );

        if ($ogrStatus == self::$RETURN_STATUS_CREATE) {
            $orderInfo = OrderDao::getInstance()->queryByCode($returnList['order_code']);
            $updateOgrstatus = ReturnDao::getInstance()->updateStatusByCode($ogrCode, 2);
            $orderStatus = $orderInfo['w_order_status'];
            if ($updateOgrstatus) {
                venus_db_commit();
                $success = true;
                $message = "确认退货成功";
            } else {
                venus_db_commit();
                $success = true;
                $message = "确认退货成功";
            }
//                if ($orderStatus == self::$ORDER_STATUS_HANDLE_FINISH) {
//                    $accidentService = new AccidentService();
//                    $rejectResult = $accidentService->return_goods($returngoodsList);
//                    if ($rejectResult[0] == true) {
//                        venus_db_commit();
//                        $success = true;
//                        $message = "确认退货成功";
//                    } else {
//                        venus_db_rollback();
//                        $success = false;
//                        $message = $rejectResult[2];
//                    }
//                } else {
//                    venus_db_commit();
//                    $success = true;
//                    $message = "确认退货成功";
//                }
//            } else {
//                venus_db_rollback();
//                $success = false;
//                $message = "确认退货失败";
//            }

        } else {
            venus_db_rollback();
            $success = false;
            $message = "此退货单不支持此操作";
        }
        return array($success, "", $message);
        //业务逻辑，确认的结果,1:调用大仓的相应接口，2:变更当前退货申请单状态为已处理。
    }

    /**
     * 管理端拒绝退货申请单
     */
    public function returngoods_reject()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrStatus = 3;
        venus_db_starttrans();
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }

        if ($ogrStatus == self::$RETURN_STATUS_REJECT) {
            $returnData = ReturnDao::getInstance()->queryByCode($ogrCode);//获取退货单信息
            $orderData = OrderDao::getInstance()->queryByCode($returnData['order_code']);//获取订单warcode usertoken
            $orderGoodsData = OrdergoodsDao::getInstance()->queryByOcodeAndSkucodeAndSpucode($returnData['order_code'], $returnData['sku_code'], $returnData['spu_code']);
            //更新ordergoods表里的skucount、goodscount
            $uptOrdergoodsRes = OrdergoodsDao::getInstance()->updateCountByCode($returnData['goods_code'], $orderGoodsData['sku_count'] + $returnData['goods_count'], $orderGoodsData['goods_count'] + bcmul($returnData['goods_count'], $returnData['spu_count']));
//            $uptOrdergoodsWskuCount = OrdergoodsDao::getInstance()->updateWskuCountByCode($returnData['goods_code'],$orderGoodsData['sku_count'] + $returnData['goods_count']);
            //获取ordergoods表里的所有信息
            $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($returnData['order_code'], 0, 10000);
            //修改订单价格
            $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);
            $uptOrderRes = OrderDao::getInstance()->updatePriceByCode($returnData['order_code'], $uptOrderData['totalBprice'], $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
            //修改退货单状态
            $updateOgrstatus = ReturnDao::getInstance()->updateStatusByCode($ogrCode, $ogrStatus);
            if ($uptOrdergoodsRes && $uptOrderRes && $updateOgrstatus) {
                //远程调用拒绝接口，恢复副仓库存
                $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.cancel", array(
                    "skCode" => $returnData['sku_code'],
                    "oCode" => $returnData['order_code'],
                    "skCount" => bcmul($returnData['goods_count'], $returnData['spu_count']),
                    "warCode" => $orderData['war_code'],
                ));

                if (!$result["success"]) {
                    venus_db_rollback();
                    $success = false;
                    $message = $result["message"];
                } else {
                    venus_db_commit();
                    $success = true;
                    $message = "取消退货成功";
                }
            } else {
                venus_db_rollback();
                $success = false;
                $message = "取消退货失败";
            }
        }
        return array($success, "", $message);
        //业务逻辑，拒绝的结果,1:调用小仓的"加法"接口，2:变更当前退货申请单状态为已拒绝。
    }

    //更新订单相关金额
    private function updatePrice($oCode)
    {
        $goodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $page = 0, $count = 10000);//获取订单里的所有货品数据
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
            $totalCprofit += bcmul($goodsItem['profit_price'], $goodsItem['goods_count'], 4);
            $totalTprice += venus_calculate_sku_price_by_spu($goodsItem['spu_sprice'], $goodsItem['goods_count'], $goodsItem['profit_price']);
        }
        $updatePrice = OrderDao::getInstance()->updatePriceByCode($oCode, $totalBprice, $totalSprice, $totalSprofit, $totalCprofit, $totalTprice);
    }

}




