<?php

namespace Wms\Service;

use Common\Service\ExcelService;
use Common\Service\PassportService;
use Common\Service\PHPRpcService;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Wms\Dao\ReturntaskDao;

class ReturntaskService
{
    /*
     *0:"全部"
      1:"实收不足,产生退货"
      2:"商品包装破损",
      3:"实际到货商品与页面描述不符",
      4:"商品保质期已过半",
      5:"下错单（数量、品牌、规格）",
      6:"其它（电话沟通）"
      7:"多余货品"
     * */
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
    static protected $ORDERGOODS_BEFORE_INSPECTION = 1;//验货前
    static protected $ORDERGOODS_AFTER_INSPECTION = 2;//验货后

    static protected $ORDERGOODS_EDIT_RECEIPT = 1;//编辑实收
    static protected $ORDERGOODS_REVISED_ORDER = 2;//修正订单
    static protected $ORDERGOODS_RETURNING_LIBRARY = 3;//回库操作
    static protected $ORDERGOODS_RETURN_OPERATION = 4;//退货操作
    static protected $ORDERGOODS_WAREHOUSING = 5;//入库操作
    static protected $ORDERGOODS_REFUSE_APPLICATION = 6;//拒绝申请
    static protected $ORDERGOODS_TRANSFER_WAREHOUSE = 7;//转仓配
    static protected $ORDERGOODS_TRANSFER_OPERATE = 8;//转运营
    public $waCode;

    function __construct()
    {
        $userData = PassportService::getInstance()->loginUser();
        if (empty($userData)) {
            venus_throw_exception(110);
        }
        $this->uCode = $userData["user_code"];
        $this->waCode = $userData["warehousecode"];
        $this->worCode = $userData["wor_code"];
        $this->uToken = $userData["user_token"];
        $this->workerWarehouseCode = $userData["war_code"];//user所代表的第三方仓库工作人员的仓库编号$userData["war_code"]
        $this->workerWarehouseName = $userData["war_name"];
    }

    /**
     * 小程序端发起创建退货单
     */
    public function returngoods_create()
    {
        $post = json_decode($_POST['data'], true);
        $gCode = $post['gcode'];
        $count = $post['count'];//退货数量
        $ogrType = $post['type'];//退货原因
        $warName = $this->workerWarehouseName;//项目组名称
        $ogrStatus = 1;
        $intradayTime = date("Y-m-d", time());//当天的时间
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
        $success = true;
        //查询当前申请退货商品的信息
        $orderGoodsList = OrdergoodsDao::getInstance()->queryByGcode($gCode);

        //查询是否重复申请
        $aCount = 0;//实际退货数量
        $returnGoods = ReturntaskDao::getInstance()->queryBy0codeAndSkcodeAndSpcode($orderGoodsList['order_code'],
            $orderGoodsList['sku_code'], $orderGoodsList['spu_code'], $orderGoodsList['supplier_code']);
        foreach ($returnGoods as $index => $rgItem) {
            if($rgItem['ogr_status'] == '3'){
                $actual_count = 0;
            }else{
                $actual_count = $rgItem['actual_count'];
            }
            $aCount += $actual_count;
        }
        $rgCount = bcadd($count, $aCount, 2);//当前申请的退货数量加上已经申请的退货数量

        if ($rgCount > $orderGoodsList['sku_init']) {
            return array(false, "", "申请退货数量不可以大于下单数量");
        }

        if ($ogrStatus == self::$RETURN_STATUS_CREATE) {
            $orderGoodsReturnData = array(
                "oNode" => 2,
                "otype" => $ogrType,
                "ostatus" => 1,
                "rtCode" => "",
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
                "ogrLog" => "",
                "igoCode" => "",
            );

            $ogrCode = ReturntaskDao::getInstance()->insert_returngoods($orderGoodsReturnData);
            //查询此项目组当天是否已经退过货
            $condition = array(
                "rtAddtime" => $intradayTime,
                "warCode" => $this->workerWarehouseCode,
            );
            $intradayWarCodeReturn = ReturntaskDao::getInstance()->queryListByCondition($condition, 0, 10000);
            if (empty($intradayWarCodeReturn)) {
                $returntaskData = array(
                    "rtStatus" => 1,//当前任务状态 1.申请中 2.已处理
                    "warCode" => $orderGoodsList['war_code'],
                    "warName" => $warName,
                );
                $RtCode = ReturntaskDao::getInstance()->insert($returntaskData);
                $success = $success && ReturntaskDao::getInstance()->updateRtcodeByCode($ogrCode, $RtCode);
            } else {
                $success = $success && ReturntaskDao::getInstance()->updateRtcodeByCode($ogrCode, $intradayWarCodeReturn[0]['rt_code']);
                $success = $success && ReturntaskDao::getInstance()->updateRtStatusByCode($intradayWarCodeReturn[0]['rt_code'], 1);
            }
            if ($success) {
                $orderList = OrderDao::getInstance()->queryByCode($orderGoodsList['order_code']);
                $result = PHPRpcService::getInstance()->request($orderList['user_token'], "venus.wms.return.return.invoice.create", array(
                    "skCode" => $orderGoodsList['sku_code'],
                    "spCode" => $orderGoodsList['spu_code'],
                    "oCode" => $orderGoodsList['order_code'],
                    "init" => bcmul($orderGoodsList['sku_count'], $orderGoodsList['spu_count'], 4),//入库时的数量
                    "count" => bcmul($count, $orderGoodsList['spu_count'], 4),//退货数量
                ));
                if (!$result["success"]) {
                    venus_db_rollback();
                    $success = false;
                    $message = $result["message"];

                } else {
                    venus_db_commit();
                    //更新当前退货商品的igocode、退货日志
                    $success = $success && ReturntaskDao::getInstance()->updateIgoCodeByCode($ogrCode, $result['data']["igoCode"]);
                    $success = $success && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $result["message"]);
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
        return array($success, "", $message);
    }

    /**
     * 小程序查询退货申请单
     */
    public function returngoods_searchs()
    {
        $post = json_decode($_POST['data'], true);
        $ogrStatus = $post['ogrStatus'];
        $warCode = $post['warCode'];
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = 1000;//当前页面总条数

        if (empty($ogrStatus)) {
            venus_throw_exception(1, "货品退货状态不能为空");
            return false;
        }

        if (empty($warCode)) {
            venus_throw_exception(1, "项目组编号不能为空");
            return false;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }
        $condition = array(
            "ogrStatus" => $ogrStatus,
            "warCode" => $warCode,
        );

        $returntaskDao = ReturntaskDao::getInstance();
        $totalCount = $returntaskDao->queryCountByReturnGoodsCode($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent, $pageSize);
        $returnResults = $returntaskDao->queryListByReturnGoodsCode($condition, $pageLimit['page'], $pageLimit['pSize']);
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
                $count = bcmul($returnItem['actual_count'], $returnItem['spu_count'], 2);
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
                    "gCount" => floatval($returnItem['actual_count']),//实际退货数量
                    "skUnit" => $returnItem['sku_unit'],//单位
                    "statusName" => venus_return_status_name($returnItem['ogr_status']),//申请状态
                );
            }
        }
        return array(true, $returnList, "");
    }

    //管理端退货单搜索、列表
    public function returntask_search()
    {
        $post = $_POST['data'];
        $rtStatus = $post['rtStatus'];
        $sTime = $post['sTime'];//退货数量
        $eTime = $post['eTime'];//退货原因
        $warCode = $post['warCode'];//项目组编号
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数 $post['pageSize']
        if (!empty($rtStatus)) {
            $condition['rtStatus'] = $rtStatus;
        }

        if (!empty($sTime)) {
            $condition['sTime'] = $sTime;
        }

        if (!empty($eTime)) {
            $condition['eTime'] = $eTime;
        }
        if (!empty($warCode)) {
            $condition['warCode'] = $warCode;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $returntaskDao = ReturntaskDao::getInstance();
        $totalCount = $returntaskDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $returntaskResults = $returntaskDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);
        if (empty($returntaskResults)) {
            $returntaskList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $returntaskList["list"] = array();
        } else {
            $returntaskList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($returntaskResults as $index => $returntaskItem) {
                $returntaskList["list"][$index] = array(
                    "rtCode" => $returntaskItem['rt_code'],//退货单任务编号
                    "warName" => $returntaskItem['war_name'],//项目组名称
                    "rtAddtime" => $returntaskItem['rt_addtime'],//创建时间
                    "rtStatus" => $returntaskItem['rt_status'],//当前任务状态
                    "rtStatusName" => venus_rt_status_name($returntaskItem['rt_status']),//当前任务对应名称
                );
            }
        }
        return array(true, $returntaskList, "");
    }

    //管理端退货单详情
    public function details_list()
    {
        $post = $_POST['data'];
        $rtCode = $post['rtCode'];
        $spName = $post['name'];
        $isTwarehouse = $post['isTwarehouse'];//是否转仓配
        $pageCurrent = $post['pageCurrent'];//当前页码
        $pageSize = $post['pageSize'];//当前页面总条数 $post['pageSize']
        if (empty($isTwarehouse)) {
            if (empty($rtCode)) {
                venus_throw_exception(1, "退货单任务编号不能为空");
                return false;
            }
        }

        if(!empty($spName)){
            $condition['%name%'] = $spName;
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }
        if (empty($pageSize)) {
            $pageSize = 100;
        }
        $condition['rtCode'] = $rtCode;
        $condition['isTwarehouse'] = $isTwarehouse;
        $returnDao = ReturntaskDao::getInstance();
        $totalCount = $returnDao->queryCountByReturnTaskCode($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent, $pageSize);
        $returnResults = $returnDao->queryListByReturnTaskCode($condition, $pageLimit['page'], $pageLimit['pSize']);
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
                //显示按钮
                if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] == "SU00000000000001"
                    && $returnItem['ogr_type'] == "1") {
                    $buttonType = 1;//编辑、确认、拒绝、回库、验货
                } else if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] == "SU00000000000001"
                    && $returnItem['ogr_type'] == "7") {
                    $buttonType = 2;//确认
                } else if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] !== "SU00000000000001"
                    && $returnItem['supplier_code'] !== "SU00000000000002" && $returnItem['ogr_type'] == "1") {
                    $buttonType = 3;//编辑、确认
                } else if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] !== "SU00000000000001"
                    && $returnItem['supplier_code'] !== "SU00000000000002" && $returnItem['ogr_type'] == "7") {
                    $buttonType = 2;//确认
                } else if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] == "SU00000000000002"
                    && $returnItem['ogr_type'] == "1") {
                    $buttonType = 1;//编辑、确认、拒绝、回库、验货
                } else if ($returnItem['ogr_node'] == "1" && $returnItem['supplier_code'] == "SU00000000000002"
                    && $returnItem['ogr_type'] == "7") {
                    $buttonType = 2;//确认
                } else if ($returnItem['ogr_node'] == "2" && $returnItem['supplier_code'] == "SU00000000000001") {
                    $buttonType = 7;//编辑、入库、拒绝
                } else if ($returnItem['ogr_node'] == "2" && $returnItem['supplier_code'] == "SU00000000000002") {
                    $buttonType = 7;//编辑、入库、拒绝
                } else if ($returnItem['ogr_node'] == "2" && $returnItem['supplier_code'] !== "SU00000000000001"
                    && $returnItem['supplier_code'] !== "SU00000000000002") {
                    $buttonType = 8;//编辑、入库、验收、拒绝
                }

                if ($returnItem['supplier_code'] == "SU00000000000001") {
                    $color = 1;//自营
                } else if ($returnItem['supplier_code'] == "SU00000000000002") {
                    $color = 2;//缺货转直采
                } else if ($returnItem['supplier_code'] == "SU00000000000003") {
                    $color = 3;//直采（鲜鱼水菜）
                } else {
                    $color = 4;//直采
                }
                $sprice = $returnItem['spu_sprice'];
                $spcount = $returnItem['spu_count'];
                $spercent = $returnItem['spu_percent'];
                $acount = $returnItem['actual_count'];
                $skPrice = venus_calculate_sku_price_by_spu($sprice,$spcount,$spercent);
                $totalSprice = bcmul($skPrice,$acount,4);

                $returnList["list"][$index] = array(
                    "warName" => $returnItem['war_name'],//项目组名称
                    "userName" => $returnItem['user_name'],//申请人
                    "oCode" => $returnItem['order_code'],//采购单单号
                    "ogrCode" => $returnItem['ogr_code'],//货品编号（退货单单号）
                    "spName" => $returnItem['spu_name'],//货品名称
                    "skNorm" => $returnItem['sku_norm'],//规格
                    "spBrand" => $returnItem['spu_brand'],//品牌
                    "spMark" => $returnItem['spu_mark'],//备注
                    "ogrType" => $returnItem['ogr_type'],//退货原因
                    "typeName" => venus_return_type_name($returnItem['ogr_type']),//退货原因
                    "gCount" => floatval($returnItem['goods_count']),//退货数量
                    "skUnit" => $returnItem['sku_unit'],//单位
                    "warMark" => $returnItem['warehouse_mark'],//仓库说明
                    "aCount" => $returnItem['actual_count'],//实退数量
//                    "isEditCount" => $isEditCount,//是否可编辑数量 //1.可编辑  2.不可编辑
                    "buttonType" => $buttonType,//显示的按钮
                    "ogrStatus" => venus_return_status_name($returnItem['ogr_status']),//申请状态
                    "supCode" => $returnItem['supplier_code'],//供货商编号
                    "ogrNode" => $returnItem['ogr_node'],//验货节点
                    "color" => $color,//背景颜色
                    "isTwarehouse" => $returnItem['is_transfer_warehouse'],//是否转仓配
                    "totalSprice" => floatval($totalSprice),//货品总金额
                );
            }
        }
        return array(true, $returnList, "");

    }

    public function returngoods()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $buttonCode = $post['buttonCode'];
//        $warMark = $post['warMark'];//仓库说明
//        $aCount = $post['aCount'];//实退数量

        $returnList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单货品编号不能为空");
            return false;
        }

        if (empty($buttonCode)) {
            venus_throw_exception(1, "按钮编号不能为空");
            return false;
        }
        if ($buttonCode == self::$ORDERGOODS_EDIT_RECEIPT) {//1.编辑实收
            return $this->returngoods_update();

        } else if ($buttonCode == self::$ORDERGOODS_REVISED_ORDER) {//2.修正订单
            if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000001"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品、少收、确认修正订单";
                return $this->returngoods_confirm_normal();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000001"
                && $returnList['ogr_type'] == "7") {
//                $message = "验货前、自营货品、多收、确认修正订单";
                return $this->returngoods_confirm_normal();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] !== "SU00000000000001"
                && $returnList['supplier_code'] !== "SU00000000000002" && $returnList['ogr_type'] == "1") {
//                $message = "验货前、直采货品、少收、确认修正订单";
                return $this->returngoods_confirm_isfast();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] !== "SU00000000000001"
                && $returnList['supplier_code'] !== "SU00000000000002" && $returnList['ogr_type'] == "7") {
//                $message = "验货前、直采货品、多收、确认修正订单";
                return $this->returngoods_confirm_isfast();
            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000002"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品、少收、确认修正订单";
                return $this->returngoods_confirm_isfast();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000002"
                && $returnList['ogr_type'] == "7") {
//                $message = "验货前、自营货品、多收、确认修正订单";
                return $this->returngoods_confirm_isfast();

            }
        } else if ($buttonCode == self::$ORDERGOODS_RETURNING_LIBRARY) {//3.回库操作
            if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000001"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品(SU00000000000001)、少收、回库操作";
                return $this->returngoods_confirm_normal();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000002"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品(SU00000000000002)、少收、回库操作";
                return $this->returngoods_returning_library();

            }
        } else if ($buttonCode == self::$ORDERGOODS_RETURN_OPERATION) {//4.退货操作
            if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000001"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品(SU00000000000001)、少收、验收退货操作";
                return $this->returngoods_before_inspection();

            } else if ($returnList['ogr_node'] == "1" && $returnList['supplier_code'] == "SU00000000000002"
                && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品(SU00000000000002)、少收、验收退货操作";
                return $this->returngoods_before_inspection();

            } else if ($returnList['ogr_node'] == "2" && $returnList['supplier_code'] !== "SU00000000000001"
                && $returnList['supplier_code'] !== "SU00000000000002") {
//                $message = "验货前、直采货品(su***)、常规退货操作";
                return $this->returngoods_after_inspection();

            }
        } else if ($buttonCode == self::$ORDERGOODS_WAREHOUSING) {//5.入库操作
            if ($returnList['ogr_node'] == "2" && $returnList['supplier_code'] == "SU00000000000001") {
//                $message = "验货后、自营货品(su01)、入库操作";
                return $this->returngoods_warehousing();

            } else if ($returnList['ogr_node'] == "2" && $returnList['supplier_code'] == "SU00000000000002") {
//                $message = "验货后、自营货品(su02)、入库操作";
                return $this->returngoods_warehousing();

            } else if ($returnList['ogr_node'] == "2" && $returnList['supplier_code'] !== "SU00000000000001"
                && $returnList['supplier_code'] !== "SU00000000000002") {
//                $message = "验货前、直采货品(su***)、入库操作";
                return $this->returngoods_warehousing();

            }

        } else if ($buttonCode == self::$ORDERGOODS_REFUSE_APPLICATION) {//6.拒绝申请
            if ($returnList['ogr_node'] == "1" && $returnList['ogr_type'] == "1") {
//                $message = "验货前、自营货品(SU00000000000001)、少收、拒绝";
//                $message = "验货前、不限制供货商类型、少收、拒绝";//2019-04-03 新修改
                return $this->returngoods_reject();

            } else if ($returnList['ogr_node'] == "1" && $returnList['ogr_type'] == "7") {
//                $message = "验货前、不限制供货商类型、多收、拒绝";
                return $this->returngoods_reject();

            } else if ($returnList['ogr_node'] == "2") {
//                $message = "验货后、不限制供货商类型、拒绝";
                return $this->returngoods_reject();

            }
        } else if ($buttonCode == self::$ORDERGOODS_TRANSFER_WAREHOUSE || $buttonCode == self::$ORDERGOODS_TRANSFER_OPERATE) {// 7.转仓配 8.转运营
            return $this->transfer_warehouse();
        }
    }

    //编辑实退数量、仓库说明  1.编辑实收
    public function returngoods_update()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];//退货单货品编号
        $warMark = $post['warMark'];//仓库说明
        $aCount = $post['aCount'];//实退数量

        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单货品编号不能为空");
            return false;
        }
        if (!empty($warMark)) {
            $updateData['warMark'] = $warMark;
        }
        if (!empty($aCount)) {
            $updateData['aCount'] = $aCount;//实退数量
        }

        $returntaskDao = ReturntaskDao::getInstance();
        $returngoodsList = $returntaskDao->queryByCode($ogrCode);//查询当前退货商品是验货前还是验货后
        $ogrNode = $returngoodsList['ogr_node'];//验货节点 1.验货前 2.验货后
        $gCount = $returngoodsList['goods_count'];
        $goodsCode = $returngoodsList['goods_code'];
        if ($aCount > $gCount) {
            venus_throw_exception(1, "实退数量不能大于申请退货数量");
            return false;
        }

        if ($ogrNode == 1) {
            //更新仓库说明和实退数量
            $returngoodsUpdate = $returntaskDao->updateWarmarkAndCounByCode($ogrCode, $updateData);
            $ordergoodsData = OrdergoodsDao::getInstance()->queryByGcode($returngoodsList['goods_code']);
            $finalReturngoodsCount = bcsub($gCount, $aCount, 2);
            $skucount = bcadd($ordergoodsData['sku_count'], $finalReturngoodsCount, 2);
            $goodscount = bcmul($skucount, $ordergoodsData['spu_count'], 2);
            //更新ordergoods里的skucount、goodscount数量
            $uptOrdergoodsRes = OrdergoodsDao::getInstance()->updateCountByCode($goodsCode, $skucount, $goodscount);
            //更新订单order表里的相关价格
            $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($ordergoodsData['order_code'], 0, 10000);
            $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);
            $uptOrderRes = OrderDao::getInstance()->updatePriceByCode(
                $ordergoodsData['order_code'], $uptOrderData['totalBprice'],
                $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
            if ($returngoodsUpdate && $uptOrdergoodsRes && $uptOrderRes) {
                $success = true;
                $message = "编辑数量、仓库说明更新成功";
            } else {
                $success = false;
                $message = "编辑数量、仓库说明更新失败";
            }

        } else if ($ogrNode == 2) {
            $returngoodsUpdate = $returntaskDao->updateWarmarkAndCounByCode($ogrCode, $updateData);//更新仓库说明和实退数量
            if ($returngoodsUpdate) {
                $success = true;
                $message = "编辑数量、仓库说明更新成功";
            } else {
                $success = false;
                $message = "编辑数量、仓库说明更新失败";
            }

        }
        return array($success, "", $message);
    }

    /**
     * 管理端确认修正订单   ----修正正常出仓单(验货前)
     */
    public function returngoods_confirm_normal()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrNode = $post['ogrNode'];
        $isTwarehouse = $post['isTwarehouse'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        if (!empty($isTwarehouse) && $isTwarehouse == 1) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateIsTransferWarehouseByOgrCode($ogrCode, 3);
        }

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $supCode = $ogrList['supplier_code'];
        $oCode = $ogrList['order_code'];
        $spuCode = $ogrList['spu_code'];
        $goodsCode = $ogrList['goods_code'];
        $ogrSkCount = $ogrList['sku_count'];

        $orderGoodsList = OrdergoodsDao::getInstance()->queryByCode($goodsCode);
        $orderGoodsSpuBprice = $orderGoodsList['spu_bprice'];
        $oCode = $orderGoodsList['order_code'];

        $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, 0, 10000);
        $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);

        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if($ogrNode == 1){
            $cond = array(
                "oCode" => $ogrList['order_code'],
                "ogrNode" => 1,
                "ogrStatus" => 1
            );
            $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($cond);
            if (empty($returnGoodsList)) {
                $isSuccess = $isSuccess && OrderDao::getInstance()->updateIsFinalSalesOrderByCode($ogrList['order_code'], 2);//2.是最终销售单
            }

        }

        if ($isSuccess) {
            $param = array(
                "warCode" => "WA000001",
                "ecode" => $ogrList['order_code'],
                "type" => $ogrList['ogr_type'],
                "skuCode" => $ogrList['sku_code'],
                "skuCount" => $ogrList['actual_count'],//实退数量
                "mark" => "小程序单(自营)",
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->update_invoice_goods($param);
            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    $finalReturngoodsCount = bcsub($ogrList['goods_count'], $ogrList['actual_count'], 2);
                    $skCount = bcmul($finalReturngoodsCount, $ogrList['spu_count'], 4);
                    $skInit = bcmul($ogrList['sku_count'], $ogrList['spu_count'], 4);
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.rec.cancel", array(
                        "skCode" => $ogrList['sku_code'],
                        "oCode" => $ogrList['order_code'],
                        "skCount" => $skCount,
                        "warCode" => $ogrList['war_code'],
                        "skInit" => $skInit,
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }

                if($supCode == "SU00000000000001" && $ogrSkCount != 0){
                    $bPrice = $this->getOwnBpriceData($oCode,$spuCode);//出仓批次平均价
                    if($orderGoodsSpuBprice != $bPrice){
                        $isSuccess = $isSuccess && OrdergoodsDao::getInstance()->updateBpriceByCodeAndSupcode($goodsCode,$supCode,$bPrice);
                        $isSuccess = $isSuccess && OrderDao::getInstance()->updatePriceByCode(
                            $oCode, $uptOrderData['totalBprice'],
                            $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
                    }
                }

            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
            if ($isSuccess) {
                venus_db_commit();
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    /**
     * 管理端确认修正订单   ----修正快进快出出仓单(验货前)
     */
    public function returngoods_confirm_isfast()
    {

        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrNode = $post['ogrNode'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if($ogrNode == 1){
            $cond = array(
                "oCode" => $ogrList['order_code'],
                "ogrNode" => 1,
                "ogrStatus" => 1
            );
            $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($cond);
            if (empty($returnGoodsList)) {
                $isSuccess = $isSuccess && OrderDao::getInstance()->updateIsFinalSalesOrderByCode($ogrList['order_code'], 2);//2.是最终销售单
            }
        }

        if ($isSuccess) {
            $param = array(
                "warCode" => "WA000001",
                "ecode" => $ogrList['order_code'],
                "skuCode" => $ogrList['sku_code'],
                "skuCount" => $ogrList['actual_count'],//实退数量
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->update_virtual_goods($param);
            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    $finalReturngoodsCount = bcsub($ogrList['goods_count'], $ogrList['actual_count'], 2);
                    $skCount = bcmul($finalReturngoodsCount, $ogrList['spu_count'], 4);
                    $skInit = bcmul($ogrList['sku_count'], $ogrList['spu_count'], 4);
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.rec.cancel", array(
                        "skCode" => $ogrList['sku_code'],
                        "oCode" => $ogrList['order_code'],
                        "skCount" => $skCount,
                        "warCode" => $ogrList['war_code'],
                        "skInit" => $skInit,
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
            if ($isSuccess) {
                venus_db_commit();;
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);;
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    /**
     * 管理端回库操作(验货前)
     */
    public function returngoods_returning_library()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrNode = $post['ogrNode'];
        $isTwarehouse = $post['isTwarehouse'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        if (!empty($isTwarehouse) && $isTwarehouse == 1) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateIsTransferWarehouseByOgrCode($ogrCode, 3);
        }
        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $supCode = $ogrList['supplier_code'];
        $oCode = $ogrList['order_code'];
        $spuCode = $ogrList['spu_code'];
        $goodsCode = $ogrList['goods_code'];
        $ogrSkCount = $ogrList['sku_count'];

        $orderGoodsList = OrdergoodsDao::getInstance()->queryByCode($goodsCode);
        $orderGoodsSpuBprice = $orderGoodsList['spu_bprice'];
        $oCode = $orderGoodsList['order_code'];

        $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, 0, 10000);
        $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);

        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if($ogrNode == 1){
            $cond = array(
                "oCode" => $ogrList['order_code'],
                "ogrNode" => 1,
                "ogrStatus" => 1
            );
            $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($cond);
            if (empty($returnGoodsList)) {
                $isSuccess = $isSuccess && OrderDao::getInstance()->updateIsFinalSalesOrderByCode($ogrList['order_code'], 2);//2.是最终销售单
            }

        }
        if ($isSuccess) {
            if ($ogrList['supplier_code'] == "SU00000000000001") {
                $mark = "小程序单(自营)";
            } else {
                $mark = "小程序单(直采)";
            }
            $param = array(
                "warCode" => "WA000001",
                "ecode" => $ogrList['order_code'],
                "type" => "1",//1.少收
                "mark" => $mark,
                "skuCode" => $ogrList['sku_code'],
                "skuCount" => $ogrList['actual_count'],//实退数量
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->update_invoice_goods($param);
            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    $finalReturngoodsCount = bcsub($ogrList['goods_count'], $ogrList['actual_count'], 2);
                    $skCount = bcmul($finalReturngoodsCount, $ogrList['spu_count'], 4);
                    $skInit = bcmul($ogrList['sku_count'], $ogrList['spu_count'], 4);
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.rec.cancel", array(
                        "skCode" => $ogrList['sku_code'],
                        "oCode" => $ogrList['order_code'],
                        "skCount" => $skCount,
                        "warCode" => $ogrList['war_code'],
                        "skInit" => $skInit,
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }

                if($supCode == "SU00000000000001" && $ogrSkCount != 0){
                    $bPrice = $this->getOwnBpriceData($oCode,$spuCode);//出仓批次平均价
                    if($orderGoodsSpuBprice !== $bPrice){
                        $isSuccess = $isSuccess && OrdergoodsDao::getInstance()->updateBpriceByCodeAndSupcode($goodsCode,$supCode,$bPrice);
                        $isSuccess = $isSuccess && OrderDao::getInstance()->updatePriceByCode(
                                $oCode, $uptOrderData['totalBprice'],
                                $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);
                    }
                }
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }

            if ($isSuccess) {
                venus_db_commit();;
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);;
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    //验货前退货  -------验货退货
    public function returngoods_before_inspection()
    {
        return array(false, "", "<span style='font-size:40px;color: brown'>该功能暂不开放！</span>");
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $userNameList = ReturntaskDao::getInstance()->queryByUser($ogrCode);
        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if ($isSuccess) {
            if ($ogrList['supplier_code'] == "SU00000000000001") {
                $mark = "小程序单(自营)";
            } else {
                $mark = "小程序单(直采)";
            }
            $param = array(
                "warCode" => "WA000001",//当前登录用户的warcode
                "ecode" => $ogrList['order_code'],
                "skuCode" => $ogrList['sku_code'],
                "skuCount" => $ogrList['actual_count'],
                "receiver" => $userNameList['user_name'],
                "worCode" => $this->worCode,//当前登录用户的worcode
                "mark" => $mark,
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->seperate_invoice($param);
            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    $finalReturngoodsCount = bcsub($ogrList['goods_count'], $ogrList['actual_count'], 2);
                    $skCount = bcmul($finalReturngoodsCount, $ogrList['spu_count'], 4);
                    $skInit = bcmul($ogrList['sku_count'], $ogrList['spu_count'], 4);
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.rec.cancel", array(
                        "skCode" => $ogrList['sku_code'],
                        "oCode" => $ogrList['order_code'],
                        "skCount" => $skCount,
                        "warCode" => $ogrList['war_code'],
                        "skInit" => $skInit,
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
            if ($isSuccess) {
                venus_db_commit();;
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);;
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    //验货后退货 ---------常规退货
    public function returngoods_after_inspection()
    {
        return array(false, "", "<span style='font-size: 40px;color: brown'>该功能暂不开放！</span>");
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $userNameList = ReturntaskDao::getInstance()->queryByUser($ogrCode);
        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if ($isSuccess) {//成功的情况下调用玲娜的接口
            $param = array(
                "warCode" => "WA000001",
                "worCode" => $this->worCode,//当前登录用户的worcode
                "mark" => $ogrList['order_code'],
                "recType" => "2",//退货入仓
                "invType" => "6",//退货出仓
                "receiver" => $userNameList['user_name'],
                "list" => array(
                    array(
                        "bprice" => $ogrList['spu_bprice'],
                        "supcode" => $ogrList['supplier_code'],
                        "skucode" => $ogrList['sku_code'],
                        "skucount" => $ogrList['actual_count'],
                        "sprice" => $ogrList['spu_sprice'],
                        "pprice" => $ogrList['profit_price'],
                        "percent" => $ogrList['pro_percent'],
                    ),
                )
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->create_virtual($param);
//            $isSuccess = $isSuccess && $warehouseResult[0];
            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.goods.update", array(
                        "igoCode" => $ogrList['igo_code'],
                        "count" => bcmul($ogrList['actual_count'], $ogrList['spu_count'], 4),
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
            if ($isSuccess) {
                venus_db_commit();;
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);;
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    //入库操作
    public function returngoods_warehousing()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $isTwarehouse = $post['isTwarehouse'];
        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        if (!empty($isTwarehouse) && $isTwarehouse == 1) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateIsTransferWarehouseByOgrCode($ogrCode, 3);
        }

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 2);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if ($isSuccess) {//成功的情况下调用玲娜的接口
            $param = array(
                "type" => "2",
                "warCode" => "WA000001",
                "worCode" => $this->worCode,//当前登录用户的worcode
                "mark" => $ogrList['order_code'],
                "list" => array(
                    array(
                        "bprice" => $ogrList['spu_bprice'],
                        "supcode" => $ogrList['supplier_code'],
                        "skucode" => $ogrList['sku_code'],
                        "skucount" => $ogrList['actual_count'],
                    ),
                )
            );
            $warehouseService = new WarehouseService();
            $warehouseResult = $warehouseService->create_receipt($param);

            if ($warehouseResult[0] == true) {
                if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                    //先调用大仓，大仓返回成功再调用小仓
                    $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.goods.update", array(
                        "igoCode" => $ogrList['igo_code'],
                        "count" => bcmul($ogrList['actual_count'], $ogrList['spu_count'], 4),
                    ));
                    $isSuccess = $isSuccess && $result['success'];
                    $message = $result["message"];
                } else {
                    $message = $warehouseResult[2];
                }
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }

            if ($isSuccess) {
                venus_db_commit();
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);;
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $warehouseResult[2];
            }
        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败!";
        }
        return array($isSuccess, "", $message);
    }

    /**
     * 管理端拒绝退货申请单
     */
    public function returngoods_reject()
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $ogrNode = $post['ogrNode'];
        $aCount = $post['aCount'];//实退数量

        if (empty($ogrCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        venus_db_starttrans();
        $isSuccess = true;

        //更新当前货品的退货状态
        $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateStatusByCode($ogrCode, 3);
        //查询当前货品的任务编号
        $ogrList = ReturntaskDao::getInstance()->queryByCode($ogrCode);
        $clause = array(
            "rtCode" => $ogrList['rt_code'],
            "ogrStatus" => 1
        );
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($clause);
        if (empty($returnGoodsList)) {
            $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateRtStatusByCode($ogrList['rt_code'], 2);
        }
        if($ogrNode == 1){
            $cond = array(
                "oCode" => $ogrList['order_code'],
                "ogrNode" => 1,
                "ogrStatus" => 1
            );
            $returnGoodsList = ReturntaskDao::getInstance()->queryListByReturnTaskCode($cond);
            if (empty($returnGoodsList)) {
                $isSuccess = $isSuccess && OrderDao::getInstance()->updateIsFinalSalesOrderByCode($ogrList['order_code'], 2);//2.是最终销售单
            }
        }
        if ($isSuccess) {
            //如果要点击拒绝的情况下，不小心编辑了实退数量，就将申请的退货数量更新到实退数量上，保持一致
            if ($ogrList['goods_count'] !== $ogrList['actual_count']) {
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateActualCountByCode($ogrCode, $ogrList['goods_count']);
            }
            $orderData = OrderDao::getInstance()->queryByCode($ogrList['order_code']);
            if ($ogrList['ogr_node'] == self::$ORDERGOODS_BEFORE_INSPECTION) {//验货前
                //退货数量原封不动加回去
                $returntaskDao = ReturntaskDao::getInstance();
                $returngoodsList = $returntaskDao->queryByCode($ogrCode);
                $aCount = $returngoodsList['actual_count'];
                $goodsCode = $returngoodsList['goods_code'];
                $ordergoodsData = OrdergoodsDao::getInstance()->queryByGcode($returngoodsList['goods_code']);
//                $finalReturngoodsCount = bcsub($gCount, $aCount, 2);
                $skucount = bcadd($ordergoodsData['sku_count'], $aCount, 2);
                $goodscount = bcmul($skucount, $ordergoodsData['spu_count'], 2);
                //更新ordergoods里的skucount、goodscount数量
                $isSuccess = $isSuccess && OrdergoodsDao::getInstance()->updateCountByCode($goodsCode, $skucount, $goodscount);
                //更新订单order表里的相关价格
                $issetOrdergoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($ordergoodsData['order_code'], 0, 10000);
                $uptOrderData = \Common\Service\OrderService::getInstance()->updatePrice($issetOrdergoodsList);
                $isSuccess = $isSuccess && OrderDao::getInstance()->updatePriceByCode(
                        $ordergoodsData['order_code'], $uptOrderData['totalBprice'],
                        $uptOrderData['totalSprice'], $uptOrderData['totalSprofit'], $uptOrderData['totalCprofit'], $uptOrderData['totalTprice']);

                if ($isSuccess) {
                    $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.rec.cancel", array(
                        "skCode" => $ogrList['sku_code'],
                        "oCode" => $ogrList['order_code'],
                        "skCount" => bcmul($ogrList['goods_count'], $ogrList['spu_count'], 4),
                        "warCode" => $orderData['war_code'],
                        "skInit" => bcmul($ogrList['sku_count'], $ogrList['spu_count'], 4),//退货前sku数量
                    ));
                }

            } else {
                $result = PHPRpcService::getInstance()->request($orderData['user_token'], "venus.wms.return.return.goods.cancel", array(
                    "igoCode" => $ogrList['igo_code'],
                ));
            }
            $isSuccess = $isSuccess && $result['success'];
            $message = $result['message'];
            if ($isSuccess) {
                venus_db_commit();
                $isSuccess = $isSuccess && ReturntaskDao::getInstance()->updateOgrLogByCode($ogrCode, $message);
                $message = "更新货品状态成功";
            } else {
                venus_db_rollback();
                $isSuccess = false;
                $message = $result['message'];
            }

        } else {
            venus_db_rollback();
            $isSuccess = false;
            $message = "更新货品状态失败";
        }
        return array($isSuccess, "", $message);
    }

    public function transfer_warehouse()//转仓配
    {
        $post = $_POST['data'];
        $ogrCode = $post['ogrCode'];
        $isTwarehouse = $post['isTwarehouse'];
        $upTransferWarehouse = ReturntaskDao::getInstance()->updateIsTransferWarehouseByOgrCode($ogrCode, $isTwarehouse);
        if ($upTransferWarehouse) {
            $isSuccess = true;
            $message = "更新状态成功";
        } else {
            $isSuccess = false;
            $message = "更新状态失败";
        }
        return array($isSuccess, "", $message);
    }

    /**
     * 下载自营退货单 供货商为SU00000000000001
     */
    public function returngoods_selfsupport_export()
    {
        return array(false, "", "<span style='font-size:40px;color: brown'>该功能暂不开放！</span>");
        $post = $_POST['data'];
        $rtCode = $post['rtCode'];//任务编号
        if (empty($rtCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        $condition['rt_code'] = $rtCode;
        $condition["ogr.supplier_code"] = "SU00000000000001";//供货商编号
        $fname = "自营货品退货单";
        return $this->order_download($condition, $fname);

    }

    /**
     * 下载直采退货单 供货商非SU00000000000001和SU00000000000003
     */
    public function returngoods_directmining_export()
    {
        return array(false, "", "<span style='font-size:40px;color: brown'>该功能暂不开放！</span>");
        $post = $_POST['data'];
        $rtCode = $post['rtCode'];//任务编号
        if (empty($rtCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        $condition['rt_code'] = $rtCode;
        $condition["ogr.supplier_code"] = array(
            array('NEQ', 'SU00000000000001'),
            array('NEQ', 'SU00000000000003'),
            'AND'
        );
        $fname = "直采（缺货直采）货品退货单";
        return $this->order_download($condition, $fname);
    }

    /**
     * 下载直采退货单 供货商为SU00000000000003
     */
    public function returngoods_issup_export()
    {
        return array(false, "", "<span style='font-size:40px;color: brown'>该功能暂不开放！</span>");
        $post = $_POST['data'];
        $rtCode = $post['rtCode'];//任务编号
        if (empty($rtCode)) {
            venus_throw_exception(1, "退货单编号不能为空");
            return false;
        }
        $condition['rt_code'] = $rtCode;
        $condition["ogr.supplier_code"] = "SU00000000000003";//供货商编号
        $fname = "直采货品（鲜鱼水菜）退货单";
        return $this->order_download($condition, $fname);
    }

    /**
     * @return mixed
     */
    public function order_download($condition, $fname)
    {
        $returnGoodsList = ReturntaskDao::getInstance()->queryListByRtCode($condition);
        $totalCount = ReturntaskDao::getInstance()->queryCountByRtCode($condition);
        if (empty($returnGoodsList)) {
            return array(false, "", "无相关退货数据");
        }
        $returnGoddsData = array();
        $header = array("序号", "退货日期", "退货编号", "订单日期", "订单编号", "货品编号", "货品名称", "数量", "单位", "规格", "退货原因", "品牌", "供应商", "备注");
        $keys = 0;
        foreach ($returnGoodsList as $index => $rgItem) {
            $rgList = array(
                "aTime" => $rgItem['apply_time'],//申请退货日期
                "ogrCode" => $rgItem['ogr_code'],//退货编号
                "ctime" => $rgItem['order_ctime'],//订单日期
                "oCode" => $rgItem['order_code'],//订单编号
                "skCode" => $rgItem['sku_code'],//货品编号
                "spName" => $rgItem['spu_name'],//货品名称
                "aCount" => $rgItem['actual_count'],//退货数量
                "skUnit" => $rgItem['sku_unit'],//货品单位
                "skNorm" => $rgItem['sku_norm'],//货品规格
                "typeName" => $rgItem['ogr_type'],//退货原因venus_return_type_name($returnItem['ogr_type'])
                "spBrand" => $rgItem['spu_brand'],//品牌
                "supName" => $rgItem['sup_name'],//供货商名称
                "warMark" => $rgItem['warehouse_mark'],//仓库说明
            );
            $returnGoddsData[$fname][] = array(
                $keys + 1, $rgList['aTime'], $rgList['ogrCode'], $rgList['ctime'],
                $rgList['oCode'], $rgList['skCode'], $rgList['spName'], $rgList['aCount'],
                $rgList['skUnit'], $rgList['skNorm'], venus_return_type_name($rgList['typeName']),
                $rgList['spBrand'], $rgList['supName'], $rgList['warMark']
            );
            $keys++;
        }
        $categoryTotal = array('合计品类', '', '', '', '', '', $totalCount);
        array_push($returnGoddsData[$fname], $categoryTotal);
        $fileName = ExcelService::getInstance()->exportExcel($returnGoddsData, $header, "001");
        if ($fileName) {
            $success = true;
            $data = $fileName;
            $message = "";
            return array($success, $data, $message);
        } else {
            $success = false;
            $data = "";
            $message = "下载失败";
        }
        return array($success, $data, $message);
    }

    //获取自营货品出仓批次价
    private function getOwnBpriceData($orderCode, $spuCode)
    {
        $igoodsentModel = IgoodsentDao::getInstance("WA000001");
        //根据订单编号和spu编号获取货品出仓批次总条数
        $igsCount = $igoodsentModel->queryOwnCountByOcodeAndSpuCode($orderCode, $spuCode);
        //根据订单编号和spu编号获取货品出仓批次列表
        $igsData = $igoodsentModel->queryOwnListByOcodeAndSpuCode($orderCode, $spuCode, $igsCount);
        //货品实际加权平均
        $sum = 0;
        $count = 0;
        foreach ($igsData as $igsDatum) {
            //统计总的批次采购价
            $sum = floatval(bcadd($sum, bcmul($igsDatum['igs_count'], $igsDatum['igs_bprice'], 4), 4));
            //统计总的批次数量
            $count = floatval(bcadd($count, $igsDatum['igs_count'], 4));
        }
        //统计货品加权平均采购价
        return floatval(bcdiv($sum, $count, 2));
    }

}




