<?php

namespace Wms\Service;

use Common\Service\ExcelService;
use Common\Service\PassportService;
use Common\Service\PHPRpcService;
use Wms\Dao\OrderDao;
use Wms\Dao\SkuDao;
use Wms\Dao\OrdergoodsDao;
use Wms\Dao\UserDao;

class OrderService
{
    private static $ORDER_STATUS_CREATE = "1";
    private static $ORDER_STATUS_FINISH = "2";
    private static $ORDER_STATUS_CANCEL = "3";

    public $waCode;

    function __construct()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if (empty($workerData)) {
            venus_throw_exception(110);
        }
        $this->waCode = $workerData["war_code"];
    }

    //1.搜索订单
    public function ord_search()
    {

        $startTime = $_POST['data']['startTime'];//开始时间
        $endTime = $_POST['data']['endTime'];//结束时间
        $oStatus = $_POST['data']['oStatus'];//订单状态
        $warCode = $_POST['data']['warCode'];//客户仓库code
        $oPdate = $_POST['data']['oPdate'];//送货时间
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数

        if (!empty($startTime)) {
            $condition['sctime'] = $startTime;
        }

        if (!empty($endTime)) {
            $condition['ectime'] = $endTime;
        }

        if (!empty($oStatus)) {//订单状态
            $condition['status'] = $oStatus;
        }

        if (!empty($warCode)) {//客户仓库code
            $condition['warcode'] = $warCode;
        }

        if (!empty($oPdate)) {//送货时间
            $condition['pdate'] = $oPdate;
        }

        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $totalCount = OrderDao::getInstance()->queryCountByCondition($condition);//获取指定条件的总条数

        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $orderList = OrderDao::getInstance()->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);

        if (empty($orderList)) {
            $orderListResult = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0,
                "list" => array()
            );
        } else {
            $orderListResult = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($orderList as $index => $orderItem) {
                $orderListResult["list"][$index] = array(
                    "oCode" => $orderItem['order_code'],//订单编号
                    "oCtime" => $orderItem['order_ctime'],//下单时间
                    "oPdate" => $orderItem['order_pdate'],//送货日期
                    "oStatus" => $orderItem['order_status'],//订单状态
                    "oBprice" => $orderItem['order_bprice'],//内部采购价
                    "oSprice" => ($orderItem['order_sprice']==intval($orderItem['order_sprice']))?intval($orderItem['order_sprice']):round($orderItem['order_sprice'],2),//内部销售价
                    "oProfit" => $orderItem['order_sprofit'],//内部利润
                    "cltSprice" => ($orderItem['order_tprice']==intval($orderItem['order_tprice']))?intval($orderItem['order_tprice']):round($orderItem['order_tprice'],2),//客户销售金额
                    "cltProfit" => ($orderItem['order_cprofit']==intval($orderItem['order_cprofit']))?intval($orderItem['order_cprofit']):round($orderItem['order_cprofit'],2),//客户利润
                    "warName" => $orderItem['war_name'],//客户单位
                    "uName" => $orderItem['user_name'],//下单人
                    "uPhone" => $orderItem['user_phone']//联系电话
                );
            }
        }
        return array(true, $orderListResult, "");
    }

    //2.修改订单状态
    public function status_update()
    {

        $oCode = $_POST['data']['oCode'];
        $oStatus = $_POST['data']['oStatus'];

        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }

        if (empty($oStatus)) {
            venus_throw_exception(1, "订单状态不能为空");
            return false;
        }
        if ($oStatus == self::$ORDER_STATUS_FINISH) {
            $orderList = OrderDao::getInstance()->queryByCode($oCode);//获取订单信息
            $goodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $page = 0, $count = 1000);//获取订单里的所有货品数据

            if (!empty($orderList['user_token'])) {
                foreach ($goodsList as $index => $goodsItem) {
                    $count = round(bcmul($goodsItem['sku_count'], $goodsItem['spu_count'], 3), 2);
                    $msg = array(
                        'spCode' => $goodsItem["spu_code"],
                        'spName' => $goodsItem["spu_name"],
                        'spAbname' => $goodsItem["spu_abname"],
                        'spType' => $goodsItem["spu_type"],
                        'spSubtype' => $goodsItem["spu_subtype"],
                        'spStoretype' => $goodsItem["spu_storetype"],
                        'spBrand' => $goodsItem["spu_brand"],
                        'spFrom' => $goodsItem["spu_from"],
                        'spNorm' => $goodsItem["spu_norm"],
                        'spUnit' => $goodsItem["spu_unit"],
                        'spMark' => $goodsItem["spu_mark"],
                        'spCunit' => $goodsItem["spu_cunit"],
                        'skCode' => $goodsItem["sku_code"],
                    );
                    $list[$index] = array(
                        "skCode" => $goodsItem['sku_code'],
                        "skCount" => $count,
                        "spCode" => $goodsItem['spu_code'],
                        "spBprice" => $goodsItem['spu_sprice'],//副仓成本价是主仓销售价
						"supCode" => "SU00000000000001",
                        "suCode" => $goodsItem['sup_code'],
                        "count" => $count,
                        "spCunit" => $goodsItem['spu_cunit'],
                        "msg" => $msg
                    );
                }
                $oMark = $orderList['order_mark'] . "订单编号:" . $orderList['order_code'];
                $result = PHPRpcService::getInstance()->request($orderList['user_token'], "venus.wms.receipt.receipt.create", array(
                    "isFast" => 1,
                    "list" => $list,
                    "mark" => $oMark
                ));
                if ($result['success']==true) {
                    $recRes = true;
                } else {
                    $recRes = false;
                    $message=$result['msg'];
                }
            }

        } else {
            $recRes = true;
        }
        if ($recRes) {
            $data = OrderDao::getInstance()->updateStatusByCode($oCode, $oStatus);
            if ($data) {
                $success = true;
                $message = "修改订单状态成功";
            } else {
                $success = false;
                $message = "修改订单状态失败";
            }
            return array($success, "", $message);
        } else {
            return array(false, "", $message);
        }
    }


    //3.导出分单（根据所选订单按供应商分单）科贸自有供货商
    public function ord_export()
    {

        $oCodes = $_POST['data']['oCodes'];
        if (empty($oCodes)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }
        $supType = "1";
        $message = "无科贸自采订单";
        return $this->order_download($oCodes, $supType, $message);
    }

    //导出分单（根据所选订单按供应商分单）非科贸自有供货商
    public function nottheirown_ord_export()
    {
        $oCodes = $_POST['data']['oCodes'];
        if (empty($oCodes)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }
        $supType = "0";
        $message = "无客户自采订单";
        return $this->order_download($oCodes, $supType, $message);
    }

    //4.搜索货品
    public function sku_search()
    {

        $skCode = $_POST['data']['skCode'];

        if (empty($skCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }

        $spuList = SkuDao::getInstance($this->waCode)->queryByCode($skCode);//查询时必须添加仓库编号

        if ($spuList) {
            $spuData = array(
                "skCode" => $spuList['sku_code'],
                "spName" => $spuList['spu_name'],
                "spCount" => $spuList['spu_count'],
                "spCode" => $spuList['spu_code'],
                "spSprice" => $spuList['spu_sprice'],
                "spBprice" => $spuList['spu_bprice'],
                "suCode" => $spuList['sup_code'],
                "proPrice" => $spuList['pro_price']//利润价
            );
            $success = true;
            $message = "";
        } else {
            $success = false;
            $spuData = "";
            $message = "没有此货品";
        }
        return array($success, $spuData, $message);
    }

    //5.导出货品清单
    public function detailedList_export()
    {
        $oCode = $_POST['data']['oCode'];
        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }
        $pnumber = "0";
        $pSize = "10000";
        $orderGoodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $pnumber, $pSize);
//        $val = array();
        $skuBprice = array();
        $skuBprice[$oCode][] = array('货品编号', '货品品名', '仓储方式', '货品类型', '品牌', '单位', '数量', '备注');
        foreach ($orderGoodsList as $index => $orderGoodsItem) {
            $orderGoodsData = array(
                "skCode" => $orderGoodsItem['sku_code'],
                "spName" => $orderGoodsItem['spu_name'],
                "spStoretype" => $orderGoodsItem['spu_storetype'],
                "spSubtype" => $orderGoodsItem['spu_subtype'],
                "spBrand" => $orderGoodsItem['spu_brand'],
                "skUnit" => $orderGoodsItem['sku_unit'],
                "skCount" => $orderGoodsItem['sku_count'],
                "skMark" => $orderGoodsItem['sku_mark']
            );
            $skuBprice[$oCode][] = array($orderGoodsData['skCode'], $orderGoodsData['spName'], venus_spu_storage_desc($orderGoodsData['spStoretype']), venus_spu_catalog_name($orderGoodsData['spSubtype']), $orderGoodsData['spBrand'], $orderGoodsData['skUnit'], $orderGoodsData['skCount'], $orderGoodsData['skMark']);
        }
        $fileName = ExcelService::GetInstance()->exportExcel($skuBprice, '', "003");
        if ($fileName) {
            $success = true;
            $data = $fileName;
            $message = "";
        } else {
            $success = false;
            $data = "";
            $message = "下载失败";
        }
        return array($success, $data, $message);
    }

    //6.添加货品
    public function goods_add()
    {

        $oCode = $_POST['data']['oCode'];
        $skCode = $_POST['data']['skCode'];
        $spName = $_POST['data']['spName'];
        $skCount = $_POST['data']['skCount'];
        $spCount = $_POST['data']['spCount'];
        $spCode = $_POST['data']['spCode'];
        $spSprice = $_POST['data']['spSprice'];
        $spBprice = $_POST['data']['spBprice'];
//        $pPercent = $_POST['data']['pPercent'];
        $proPrice = $_POST['data']['proPrice'];
        $suCode = $_POST['data']['suCode'];
        $warCode = $this->waCode;
        $uCode = $_POST['data']['uCode'];

        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }

        if (empty($skCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }

        if (empty($spName)) {
            venus_throw_exception(1, "货品名称不能为空");
            return false;
        }

        if (empty($skCount)) {
            venus_throw_exception(1, "sku货品数量不能为空");
            return false;
        }

        if (empty($spCount)) {
            venus_throw_exception(1, "spu货品数量不能为空");
            return false;
        }

        if (empty($spCode)) {
            venus_throw_exception(1, "spu编号不能为空");
            return false;
        }

        if (empty($spSprice)) {
            venus_throw_exception(1, "spu销售价不能为空");
            return false;
        }

        if (empty($spBprice)) {
            venus_throw_exception(1, "spu采购价不能为空");
            return false;
        }

        if (empty($suCode)) {
            venus_throw_exception(1, "供货商不能为空");
            return false;
        }

        if (empty($uCode)) {
            venus_throw_exception(1, "用户编号不能为空");
            return false;
        }

        $orderGoodsData = array(
            "ocode" => $oCode,
            "skucode" => $skCode,
            "spuname" => $spName,
            "skuinit" => $skCount,
            "spucode" => $spCode,
            "spucount" => $spCount,
            "sprice" => $spSprice,
            "bprice" => $spBprice,
            "pproprice" => $proPrice,
            "supcode" => $suCode,
            "warcode" => $warCode,
            "ucode" => $uCode,
            "count" => round(bcmul($skCount, $spCount, 3), 2)
        );

        $spuAddResult = OrdergoodsDao::getInstance()->insert($orderGoodsData);
        if ($spuAddResult) {
            $this->updatePrice($oCode);//更新订单相关的所有价格
            $success = true;
            $message = "添加成功";
        } else {
            $success = false;
            $message = "添加失败";
        }
        return array($success, "", $message);
    }

    //7.修改数量
    public function skucount_update()
    {
        $goodsCode = $_POST['data']['goodsCode'];
        $skCount = $_POST['data']['skCount'];
        $spCount = $_POST['data']['spCount'];
        $oCode = $_POST['data']['oCode'];

        if (empty($goodsCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;//false
        }

        if (empty($skCount)) {
            venus_throw_exception(1, "sku货品数量不能为空");
            return false;
        }

        if (empty($spCount)) {
            venus_throw_exception(1, "spu货品数量不能为空");
            return false;
        }
        $goodscount = round(bcmul($skCount, $spCount, 3), 2);
        $amountUpd = OrdergoodsDao::getInstance()->updateCountByCode($goodsCode, $skCount, $goodscount);
        if ($amountUpd) {
            $this->updatePrice($oCode);
            $success = true;
            $message = "修改成功";
        } else {
            $success = false;
            $message = "修改失败";
        }
        return array($success, "", $message);
    }

    //8.删除货品
    public function goods_delete()
    {
        $goodsCode = $_POST['data']['goodsCode'];
        $oCode = $_POST['data']['oCode'];

        if (empty($goodsCode)) {
            venus_throw_exception(1, "货品编号不能为空");
            return false;
        }
        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }

        $goodsDel = OrdergoodsDao::getInstance()->removeByCode($goodsCode, $oCode);
        if ($goodsDel) {
            $this->updatePrice($oCode);
            $success = true;
            $message = "删除成功";
        } else {
            $success = false;
            $message = "删除失败";
        }
        return array($success, "", $message);
    }

    //9.修改备注
    public function mark_update()
    {
        $oCode = $_POST['data']['oCode'];
        $oMark = $_POST['data']['oMark'];

        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }

        if (empty($oMark)) {
            venus_throw_exception(1, "订单备注不能为空");
            return false;
        }

        $markUpd = OrderDao::getInstance()->updateMarkByCode($oCode, $oMark);
        if ($markUpd) {
            $success = true;
            $message = "修改备注成功";
        } else {
            $success = false;
            $message = "修改备注失败";
        }
        return array($success, "", $message);
    }

    //10.订单详情列表
    public function details_list()
    {
        $oCode = $_POST['data']['oCode'];
        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数

        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }
        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $GoodsDao = OrdergoodsDao::getInstance();
        $totalCount = $GoodsDao->queryCountByOrderCode($oCode);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $ordergoodsDetail = $GoodsDao->queryListByOrderCode($oCode, $pageLimit['page'], $pageLimit['pSize']);

        $orderData = OrderDao::getInstance()->queryByCode($oCode);
        if (empty($ordergoodsDetail)) {
            $detailList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $detailList["list"] = array();
        } else {
            $detailList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            $detailList['info'] = array(
                "oCode" => $orderData['order_code'],
                "oStatus" => $orderData['order_status'],
                "oPdate" => $orderData['order_pdate'],
                "warName" => $orderData['war_name'],
                "uName" => $orderData['user_name'],
                "uPhone" => $orderData['user_phone'],
                "oMark" => $orderData['order_mark'],
                "uCode" => $orderData['user_code']
            );
            foreach ($ordergoodsDetail as $index => $ordergoodsItem) {
                $detailList["list"][$index] = array(
                    "goodsCode" => $ordergoodsItem['goods_code'],
                    "spName" => $ordergoodsItem['spu_name'],
                    "skNorm" => $ordergoodsItem['sku_norm'],
                    "skCount" => ($ordergoodsItem['sku_count']==intval($ordergoodsItem['sku_count']))?intval($ordergoodsItem['sku_count']):round($ordergoodsItem['sku_count'],2),
                    "skUnit" => $ordergoodsItem['sku_unit'],
                    "skMark" => $ordergoodsItem['sku_mark'],
                    "spCount" => $ordergoodsItem['spu_count']
                );
            }
        }
        return array(true, $detailList, "");
    }

    //11.下单默认账户管理（下单账户管理）
    public function user_list()
    {

        $pageCurrent = $_POST['data']['pageCurrent'];//当前页码
        $pageSize = 100;//当前页面总条数
        //当前页码
        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $condition = array();
        $UserDao = UserDao::getInstance();
        $totalCount = $UserDao->queryCountByCondition($condition);//获取指定条件的总条数
        $pageLimit = pageLimit($totalCount, $pageCurrent);
        $userDataList = $UserDao->queryListByCondition($condition, $pageLimit['page'], $pageLimit['pSize']);

        if (empty($userDataList)) {
            $userList = array(
                "pageCurrent" => 0,
                "pageSize" => 100,
                "totalCount" => 0
            );
            $userList["list"] = array();
        } else {
            $userList = array(
                "pageCurrent" => $pageCurrent,
                "pageSize" => $pageSize,
                "totalCount" => $totalCount
            );
            foreach ($userDataList as $index => $userItem) {
                $userList["list"][$index] = array(
                    "uCode" => $userItem['user_code'],//用户编号
                    "uName" => $userItem['user_name'],//用户名称
                    "uPhone" => $userItem['user_phone'],//用户电话
                    "uToken" => $userItem['user_token']//用户token
                );
            }
        }
        return array(true, $userList, "");
    }

    //12.添加账户（下单账户管理）
    public function user_add()
    {
        $uName = $_POST['data']['uName'];
        $uPhone = $_POST['data']['uPhone'];
        $uToken = $_POST['data']['uToken'];

        if (empty($uName)) {
            venus_throw_exception(1, "用户名不能为空");
            return false;
        }

        if (empty($uPhone)) {
            venus_throw_exception(1, "联系电话不能为空");
            return false;
        }

        if (empty($uToken)) {
            venus_throw_exception(1, "用户token不能为空");
            return false;
        }
        $userData = array(
            "name" => $uName,
            "phone" => $uPhone,
            "token" => $uToken,
            "wxcode" => "",
            "infor" => ""
        );
        $userAdd = UserDao::getInstance($this->waCode)->insert($userData);
        if ($userAdd) {
            $success = true;
            $message = "添加用户成功";
        } else {
            $success = false;
            $message = "添加用户失败";
        }
        return array($success, "", $message);
    }

    //13.修改账户（下单账户管理）
    public function user_update()
    {
        $uCode = $_POST['data']['uCode'];
        $uName = $_POST['data']['uName'];
        $uPhone = $_POST['data']['uPhone'];
        $uToken = $_POST['data']['uToken'];

        $cond = array();
        if (empty($uCode)) {
            venus_throw_exception(1, "用户编号不能为空");
            return false;

        }

        if (!empty($uName)) {
            $cond['name'] = $uName;
        }

        if (!empty($uPhone)) {
            $cond['phone'] = $uPhone;
        }

        if (!empty($uToken)) {
            $cond['token'] = $uToken;
        }
        $userUpd = UserDao::getInstance($this->waCode)->updateItemByCode($uCode, $cond);
        if ($userUpd) {
            $success = true;
            $message = "修改用户成功";
        } else {
            $success = false;
            $message = "修改用户失败";
        }
        return array($success, "", $message);
    }

    //14.删除账户
    public function user_delete()
    {
        $uCode = $_POST['data']['uCode'];
        if (empty($uCode)) {
            venus_throw_exception(1, "用户编号不能为空");
            return false;
        }

        $userDel = UserDao::getInstance($this->waCode)->removeByCode($uCode);
        if ($userDel) {
            $success = true;
            $message = "删除用户成功";
        } else {
            $success = false;
            $message = "删除用户失败";
        }
        return array($success, "", $message);
    }


    //15.导出科贸公司需要导入的出仓单
    public function ord_inv_export()
    {

        $oCodes = $_POST['data']['oCodes'];
        if (empty($oCodes)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }

        $supOrderGoods = array();
        foreach ($oCodes as $oCode) {
            $orderData = OrderDao::getInstance()->queryByCode($oCode);//获取订单信息
            $goodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $page = 0, $count = 1000);//获取订单里的所有货品数据
            $pdate = $orderData["order_pdate"];
            $warcode = $orderData["war_code"];
            $userModel = UserDao::getInstance($orderData["war_code"]);
            $worCode = $userModel->queryByToken($orderData['user_token'])['wor_code'];

            foreach ($goodsList as $goodsData) {

                if ($goodsData['supplier_code'] == "SU00000000000001") {
                    $key = "{$warcode}|{$pdate}";
                    $goodsData['wor_code'] = $worCode;
                    $goodsData['user_name'] = $orderData["user_name"];
                    $goodsData['user_phone'] = $orderData["user_phone"];
                    $goodsData['order_mark'] = $orderData["order_mark"];
                    $supOrderGoods[$key][] = $goodsData;
                    unset($goodsData);
                } else {
                    continue;
                }
            }
            unset($warcode);
            unset($userModel);
            unset($worCode);
            unset($orderData);
            unset($goodsList);
        }
        if (empty($supOrderGoods)) {
            $success = false;
            $data = "";
            $message = "无科贸订单";
        } else {
            $OrderExport = array();
            $header = array('序号', '货品编号', '货品品名', '仓储方式', '货品类型', '品牌', '单位', '规格', '数量', '订单编号', '订单备注', '收货人编号', '收货人名称', '收货人手机号');
            foreach ($supOrderGoods as $key => $val) {
                foreach ($val as $keys => $item) {
                    $OrderExport[$key][] = array($keys + 1, $item['sku_code'], $item['spu_name'], venus_spu_storage_desc($item['spu_storetype']), venus_spu_type_name($item['spu_type']), $item['spu_brand'], $item['sku_unit'], $item['sku_norm'], $item['sku_count'], $item['order_code'], $item['order_mark'], $item['wor_code'], $item['user_name'], " " . $item['user_phone']);
                }
            }

            $fileName = ExcelService::getInstance()->exportExcel($OrderExport, $header, "002");
            if ($fileName) {
                $success = true;
                $data = $fileName;
                $message = "";
            } else {
                $success = false;
                $data = "";
                $message = "下载失败";

            }
        }

        return array($success, $data, $message);
    }

    /**
     * 导出订单方法
     */
    public function order_download($oCodes, $supType, $message)
    {
        $supOrderGoods = array();
        foreach ($oCodes as $oCode) {
            $orderData = OrderDao::getInstance()->queryByCode($oCode);//获取订单信息
            $goodsList = OrdergoodsDao::getInstance()->queryListByOrderCode($oCode, $page = 0, $count = 1000);//获取订单里的所有货品数据
            $pdate = $orderData["order_pdate"];
            $warcode = $orderData["war_code"];
            $warname = $orderData["war_name"];
            foreach ($goodsList as $goodsData) {
                if ($goodsData['sup_type'] == $supType && $goodsData['supplier_code'] !== "SU00000000000001") {
                    $supName = $goodsData["sup_name"];
                    $key = "{$supName}|{$pdate}";
                    if (!array_key_exists($key,$supOrderGoods)) {
                        $supOrderGoods[$key] = array();
                    }
                    if (!array_key_exists($warcode,$supOrderGoods[$key])) {
                        $supOrderGoods[$key][$warcode] = array(
                            "code" => $warcode,
                            "name" => $warname,
                            "pdate" => $pdate,
                            "list" => array()
                        );
                    }
                    $supOrderGoods[$key][$warcode]["list"][] = $goodsData;
                }
            }
        }

        if (empty($supOrderGoods)) {
            $success = false;
        } else {
            $OrderExport = array();
            foreach ($supOrderGoods as $key => $val) {
                foreach ($val as $item) {
                    $OrderExport[$key][] = array('项目名称', $item['name'], '', '客户编号', $item['code']);
                    $OrderExport[$key][] = array('序号', '货品编号', '货品品名', '仓储方式', '货品类型', '品牌', '单位', '规格', '数量');
                    foreach ($item as $items) {
                        if (is_array($items)) {
                            foreach ($items as $keys => $itemss) {
                                $OrderExport[$key][] = array($keys + 1, $itemss['sku_code'], $itemss['spu_name'], venus_spu_storage_desc($itemss['spu_storetype']), venus_spu_type_name($itemss['spu_type']), $itemss['spu_brand'], $itemss['sku_unit'], $itemss['sku_norm'], $itemss['goods_count']);
                            }
                        }
                    }
                    $OrderExport[$key][] = array('', '', '', '', '', '', '', '');
                    $OrderExport[$key][] = array('', '', '', '', '', '', '', '');
                }
            }

            $fileName = ExcelService::getInstance()->exportExcel($OrderExport, '', "002", 1);

            if ($fileName) {
                $success = true;
                $data = $fileName;
                $message = "";
            } else {
                $success = false;
                $data = "";
                $message = "下载失败";

            }
        }
        return array($success, $data, $message);
    }

    //修改订单备注

    /**
     * @return mixed
     */
    public function ord_mark_update()
    {
        $post = $_POST['data'];
        $oCode = $post['oCode'];
        $oMark = $post['oMark'];
        if (empty($oCode)) {
            venus_throw_exception(1, "订单编号不能为空");
            return false;
        }
        if (empty($oMark)) {
            venus_throw_exception(1, "订单备注不能为空");
            return false;
        }
        $updateOrderMark = OrderDao::getInstance()->updateMarkByCode($oCode,$oMark);
        if ($updateOrderMark) {
            $success = true;
            $message = "备注修改成功";
        } else {
            $success = false;
            $message = "备注修改失败";
        }
        return array($success, "", $message);
    }


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
//            $totalCprofit += round(bcmul($v['pro_percent'], $sprice, 3), 2);
            $totalCprofit += bcmul($goodsItem['pro_price'], $goodsItem['goods_count'], 4);
            $totalTprice += venus_calculate_sku_price_by_spu($goodsItem['spu_sprice'], $goodsItem['goods_count'], $goodsItem['pro_price']);
        }
        $updatePrice = OrderDao::getInstance()->updatePriceByCode($oCode, $totalBprice, $totalSprice, $totalSprofit, $totalCprofit, $totalTprice);
    }

}



