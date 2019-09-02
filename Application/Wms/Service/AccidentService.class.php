<?php
/**
 * Created by PhpStorm.
 * User: lingn
 * Date: 2018/10/9
 * Time: 15:27
 * 订单相关，售后，快进快出
 */

namespace Wms\Service;


use Common\Service\PassportService;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\GoodsDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\InvoiceDao;
use Wms\Dao\OrderDao;
use Wms\Dao\OrdergoodsDao;
use Wms\Dao\PositionDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SkuDao;
use Wms\Dao\SpuDao;
use Wms\Dao\UserDao;

class AccidentService
{
    static private $GOODSBATCH_STATUS_FINISH = "4";//货品批次使用完状态
    static private $RECEIPT_STATUS_FINISH = "3";//入仓单完成状态
    static private $INVOICE_STATUS_FINISH = "5";//出仓单已出仓状态
    static private $INVOICE_CREATE_TYPE_HANDWORK = "1";//手工创建
    static protected $ORDER_STATUS_HANDLE_CREATE = 1;//待处理
    static protected $ORDER_STATUS_HANDLE = 2;//处理中
    static protected $ORDER_STATUS_HANDLE_FINISH = 3;//已处理
    static protected $ORDER_STATUS_HANDLE_CANCEL = 4;//已取消
    static protected $ORDERGOODS_STATUS_HANDLE_CREATE = 1;//待处理
    static protected $ORDERGOODS_STATUS_HANDLE = 2;//处理中
    static protected $ORDERGOODS_STATUS_HANDLE_FINISH = 3;//已处理

    public $warCode;
    public $worcode;

    public function __construct()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if (empty($workerData)) {
            venus_throw_exception(110);
        }

        $this->warCode = $workerData["war_code"];
        $this->worcode = $workerData["wor_code"];
//        $this->warCode = $workerData["war_code"] = "WA000001";
//        $this->worcode = $workerData["wor_code"] = "WO000001";
    }

    public function return_goods($param)
    {

        $oCode = $param['oCode'];
        $skCode = $param['skCode'];
        $skCount = $param['skCount'];
        $count = $param['count'];
        $spCode = $param['spCode'];

        if (empty($oCode)) {
            return array(false, array(), "订单编号不能为空");
        }
        if (empty($skCode)) {
            return array(false, array(), "商品编号不能为空");
        }
        if (empty($skCount)) {
            return array(false, array(), "商品数量不能为空");
        }
        if (empty($spCode)) {
            return array(false, array(), "商品最小计量编号不能为空");
        }
        if (empty($count)) {
            return array(false, array(), "商品最小计量总数量不能为空");
        }

        $gsModel = GoodstoredDao::getInstance($this->warCode);
        $gbModel = GoodsbatchDao::getInstance($this->warCode);
        $goodsModel = GoodsDao::getInstance($this->warCode);
        $igsModel = IgoodsentDao::getInstance($this->warCode);
        $igoModel = IgoodsDao::getInstance($this->warCode);
        $recModel = ReceiptDao::getInstance($this->warCode);
        $invModel = InvoiceDao::getInstance($this->warCode);
        $skuModel = SkuDao::getInstance();

        $skuData = $skuModel->queryByCode($skCode);
        if ($skuData['sup_code'] != "SU00000000000001") {
            venus_db_starttrans();
            $returngoodsData = $invModel->queryByInvEcodeAndSkuCode($oCode, $skCode);
            $gsCode = $returngoodsData['gs_code'];
            $gbCode = $returngoodsData['gb_code'];
            $recCode = $returngoodsData['rec_code'];
            $igoCode = $returngoodsData['igo_code'];
            $igsCode = $returngoodsData['igs_code'];
            $invCode = $returngoodsData['inv_code'];
            $gsInit = $returngoodsData['gs_init'] - $count;
            $gbCount = $returngoodsData['gb_count'] - $count;
            $igoCount = $returngoodsData['igo_count'] - $count;
            $igsCount = $returngoodsData['igs_count'] - $count;
            if ($returngoodsData['gb_count'] == $count && $returngoodsData['gb_sku_count'] == $skCount) {
                $gsUptRes = $gsModel->deleteByCode($gsCode);
                $gbUptRes = $gbModel->deleteByGbCode($gbCode);
            } else {
                $gsUptRes = $gsModel->updateInitAndSkuInitByCode($gsCode, $gsInit, $returngoodsData['gs_sku_init'] - $skCount);
                $gbUptRes = $gbModel->updateCountAndSkuCountByCode($gbCode, $gbCount, $returngoodsData['gb_sku_count'] - $skCount);
            }
            $goodsInfo = $goodsModel->queryBySpuCode($returngoodsData['spu_code']);
            $goodsUptRes = $goodsModel->updateInitByCode($goodsInfo['goods_code'], $goodsInfo['goods_init'], $goodsInfo['goods_init'] - $count);
            if ($returngoodsData['igo_count'] == $count && $returngoodsData['igo_sku_count'] == $skCount) {
                $igoUptRes = $igoModel->deleteByCode($igoCode, $returngoodsData['inv_code']);
                $igsUptRes = $igsModel->deleteByCode($igsCode);
            } else {
                $igsUptRes = $igsModel->updateCountAndSkuCountByCode($igsCode, $igsCount, $returngoodsData['igs_sku_count'] - $skCount);
                $igoUptRes = $igoModel->updateCountAndSkuCountByCode($igoCode, $igoCount, $returngoodsData['igo_sku_count'] - $skCount);
            }
            $issetGbList = $gbModel->queryListByRecCode($recCode);
            $issetIgoList = $igoModel->queryListByInvCode($invCode);
            if (empty($issetGbList)) {
                $delRecRes = $recModel->deleteByCode($recCode);//退货，如果入仓单无数据删除调用
            } else {
                $delRecRes = true;
            }
            if (empty($issetIgoList)) {
                $delInvRes = $invModel->deleteByCode($invCode);//退货，如果出仓单无数据删除调用
            } else {
                $delInvRes = true;
            }
            if (!$gsUptRes || !$gbUptRes || !$igoUptRes || !$igsUptRes || !$goodsUptRes || !$delRecRes || !$delInvRes) {
                venus_db_rollback();
                $success = false;
                $message = "操作失败";
            } else {
                venus_db_commit();
                $success = true;
                $message = "";
            }
        } else {
            $success = true;
            $message = "";
        }
        $data = array();
        return array($success, $data, $message);
    }

//    //主仓退货操作/验货前
//    public function return_goods_handle_befo($param)
//    {
//        $oCode = $param['oCode'];
//        $supCode = $param['supCode'];
//        $skCode = $param['skCode'];
//        $skCount = $param['skCount'];//退货sku数量
//        $count = $param['count'];//退货spu数量
//        $spCode = $param['spCode'];
//        $skInit = $param['skInit'];//退货前sku数量/验货前为ordergoodsreturn中goods_count与sku_count的和
//        $type = $param['type'];//1少收2多收
//
//        if (empty($oCode)) {
//            return array(false, array(), "订单编号不能为空");
//        }
//        if (empty($skCode)) {
//            return array(false, array(), "商品编号不能为空");
//        }
//        if (empty($skCount)) {
//            return array(false, array(), "商品数量不能为空");
//        }
//        if (empty($spCode)) {
//            return array(false, array(), "商品最小计量编号不能为空");
//        }
//        if (empty($count)) {
//            return array(false, array(), "商品最小计量总数量不能为空");
//        }
//        if (empty($supCode)) {
//            return array(false, array(), "供货商编号不能为空");
//        }
//        if (empty($skInit)) {
//            return array(false, array(), "商品退货前数量不能为空");
//        }
//
//        $gsModel = GoodstoredDao::getInstance($this->warCode);
//        $gbModel = GoodsbatchDao::getInstance($this->warCode);
//        $goodsModel = GoodsDao::getInstance($this->warCode);
//        $igsModel = IgoodsentDao::getInstance($this->warCode);
//        $igoModel = IgoodsDao::getInstance($this->warCode);
//        $recModel = ReceiptDao::getInstance($this->warCode);
//        $invModel = InvoiceDao::getInstance($this->warCode);
//        $isSuccess = true;
//        $goodsInfo = $goodsModel->queryBySkuCode($skCode);
//        //退货相关数据
//        if ($supCode != "SU00000000000001") {
//            $returngoodsData = $invModel->queryByInvEcodeAndSkuCodeAndSkuInitAndSupCode($oCode, $skCode, $skInit, $supCode);
//            if (!empty($returngoodsData)) {
//                $returngoodsDatum = $returngoodsData[0];//直采入仓出仓均一条数据
//                $gsCode = $returngoodsDatum['gs_code'];
//                $gbCode = $returngoodsDatum['gb_code'];
//                $recCode = $returngoodsDatum['rec_code'];
//                $igoCode = $returngoodsDatum['igo_code'];
//                $igsCode = $returngoodsDatum['igs_code'];
//                $invCode = $returngoodsDatum['inv_code'];
//                if ($type == 1) {
//                    $gsInit = bcsub($returngoodsDatum['gs_init'], $count, 2);//最终gs数量
//                    $gbCount = bcsub($returngoodsDatum['gb_count'], $count, 2);//最终gb数量
//                    $igoCount = bcsub($returngoodsDatum['igo_count'], $count, 2);//最终igo数量
//                    $igsCount = bcsub($returngoodsDatum['igs_count'], $count, 2);//最终igs数量
//                    if ($returngoodsDatum['gb_count'] == $count && $returngoodsDatum['gb_sku_count'] == $skCount) {
//                        $isSuccess = $isSuccess && $gsModel->deleteByCode($gsCode);
//                        $isSuccess = $isSuccess && $gbModel->deleteByGbCode($gbCode);
//                    } else {
//                        $isSuccess = $isSuccess && $gsModel->updateInitAndSkuInitByCode($gsCode, $gsInit, bcsub($returngoodsDatum['gs_sku_init'], $skCount, 2));
//                        $isSuccess = $isSuccess && $gbModel->updateCountAndSkuCountByCode($gbCode, $gbCount, bcsub($returngoodsDatum['gb_sku_count'], $skCount, 2));
//                    }
//                    if ($returngoodsDatum['igo_count'] == $count && $returngoodsDatum['igo_sku_count'] == $skCount) {
//                        $isSuccess = $isSuccess && $igoModel->deleteByCode($igoCode, $returngoodsDatum['inv_code']);
//                        $isSuccess = $isSuccess && $igsModel->deleteByIgoCode($igoCode);
//                    } else {
//                        if ($returngoodsDatum['igs_count'] == $count && $returngoodsDatum['igs_sku_count'] == $skCount) {
//                            $isSuccess = $isSuccess && $igsModel->deleteByCode($igsCode);
//                        } else {
//                            $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsCount, bcsub($returngoodsDatum['igs_sku_count'], $skCount, 2));
//                        }
//                        $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCode, $igoCount, bcsub($returngoodsDatum['igo_sku_count'], $skCount, 2));
//                    }
//                    $issetGbList = $gbModel->queryListByRecCode($recCode);
//                    $issetIgoList = $igoModel->queryListByInvCode($invCode);
//                    if (empty($issetGbList)) {
//                        $isSuccess = $isSuccess && $recModel->deleteByCode($recCode);//退货，如果入仓单无数据删除调用
//                    }
//                    if (empty($issetIgoList)) {
//                        $isSuccess = $isSuccess && $invModel->deleteByCode($invCode);//退货，如果出仓单无数据删除调用
//                    }
//                    $isSuccess = $isSuccess & $goodsModel->updateInitByCode($goodsInfo['goods_code'], $goodsInfo['goods_init'], bcsub($goodsInfo['goods_init'], $count, 2), bcsub($goodsInfo['sku_init'], $skCount, 2));
//                } else {
//                    $gsInit = bcsub($returngoodsDatum['gs_init'], $count, 2);//最终gs数量
//                    $gbCount = bcsub($returngoodsDatum['gb_count'], $count, 2);//最终gb数量
//                    $igoCount = bcsub($returngoodsDatum['igo_count'], $count, 2);//最终igo数量
//                    $igsCount = bcsub($returngoodsDatum['igs_count'], $count, 2);//最终igs数量
//                    $isSuccess = $isSuccess && $gsModel->updateInitAndSkuInitByCode($gsCode, $gsInit, bcsub($returngoodsDatum['gs_sku_init'], $skCount, 2));
//                    $isSuccess = $isSuccess && $gbModel->updateCountAndSkuCountByCode($gbCode, $gbCount, bcsub($returngoodsDatum['gb_sku_count'], $skCount, 2));
//                    $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsCount, bcsub($returngoodsDatum['igs_sku_count'], $skCount, 2));
//                    $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCode, $igoCount, bcsub($returngoodsDatum['igo_sku_count'], $skCount, 2));
//                    $isSuccess = $isSuccess & $goodsModel->updateInitByCode($goodsInfo['goods_code'], $goodsInfo['goods_init'], bcsub($goodsInfo['goods_init'], $count, 2), bcsub($goodsInfo['sku_init'], $skCount, 2));
//                }
//            } else {
//                $success = false;
//                $message = "无相关退货出仓信息";
//                $data = array();
//                return array($success, $data, $message);
//            }
//        } else {
//            if ($type == 1) {
//                $returngoodsData = $invModel->queryByInvEcodeAndSkuCodeAndSkuInitAndSupCode($oCode, $skCode, $skInit, $supCode);
//                if (empty($returngoodsData)) {
//                    $success = false;
//                    $message = "无相关退货出仓信息";
//                    $data = array();
//                    return array($success, $data, $message);
//                }
//                $igoCodeArr = array();
//                $newCount = $count;
//                $newSkCount = $skCount;
//                //一个出仓单一个供货商只有一条igo数据
//                foreach ($returngoodsData as $returngoodsDatum) {
//                    $gsCode = $returngoodsDatum['gs_code'];
//                    $igoCode = $returngoodsDatum['igo_code'];
//                    $igsCode = $returngoodsDatum['igs_code'];
//                    $igoCount = bcsub($returngoodsDatum['igo_count'], $count, 2);//最终igo数量
//                    $igoSkCount = bcsub($returngoodsDatum['igo_sku_count'], $skCount, 2);//最终igo数量
//                    if (!in_array($igoCode, $igoCodeArr)) {
//                        $igoCodeArr["invCode"] = $returngoodsDatum['inv_code'];
//                        $igoCodeArr["igoCode"] = $igoCode;
//                        $igoCodeArr["igoCount"] = $igoCount;
//                        $igoCodeArr["igoSkuCount"] = $igoSkCount;
//                    }
//
//                    if ($returngoodsDatum['igs_count'] <= $newCount) {
//                        $isSuccess = $isSuccess && $igsModel->deleteByCode($igsCode);
//                        $gsCount = bcadd($returngoodsDatum['gs_count'], $returngoodsDatum['igs_count'], 2);
//                        $gsSkCount = bcadd($returngoodsDatum['gs_sku_count'], $returngoodsDatum['igs_sku_count'], 2);
//                        $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($gsCode, $gsCount, $gsSkCount);
//                        $newCount = bcsub($newCount, $returngoodsDatum['gs_count'], 2);
//                        $newSkCount = bcsub($newSkCount, $returngoodsDatum['gs_sku_count'], 2);
//                    } else {
//                        $gsCount = bcadd($returngoodsDatum['gs_count'], $newCount, 2);
//                        $gsSkCount = bcadd($returngoodsDatum['gs_sku_count'], $newSkCount, 2);
//                        $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($gsCode, $gsCount, $gsSkCount);
//                        $igsNewCount = bcsub($returngoodsDatum['igs_count'], $newCount, 2);
//                        $igsNewSkCount = bcsub($returngoodsDatum['igs_sku_count'], $newSkCount, 2);
//                        $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsNewCount, $igsNewSkCount);
//                    }
//                }
//                if ($igoCodeArr['igoCount'] == 0 && $igoCodeArr['igoSkuCount'] == 0) {
//                    $isSuccess = $isSuccess && $igoModel->deleteByCode($igoCodeArr['igoCode'], $igoCodeArr['invCode']);
//                } else {
//                    $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCodeArr['igoCode'], $igoCodeArr['igoCount'], $igoCodeArr['igoSkuCount']);
//                }
//                $isSuccess = $isSuccess & $goodsModel->updateCountByCode($goodsInfo['goods_code'], $goodsInfo['goods_count'], bcadd($goodsInfo['goods_count'], $count, 2), bcadd($goodsInfo['sku_count'], $skCount, 2));
//            } else {
//                $goodsData = $goodsModel->queryBySkuCode($skCode);
//                $igoCount = bcsub(0, $count, 2);
//                if ($goodsData["goods_count"] >= $igoCount) {
//                    $igoSkCount = bcsub(0, $skCount, 2);
//                    $invList = $invModel->queryByEcode($oCode);
//                    $invCode = $invList[0]["inv_code"];
//                    $returngoodsData = $invModel->queryByInvEcodeAndSkuCodeAndSkuInitAndSupCode($oCode, $skCode, $skInit, $supCode);
//                    $tgData = array_pop($returngoodsData);
//                    $gsCode = $tgData["gs_code"];
//                    $igsCode = $tgData["igs_code"];
//                    $igoCode = $tgData["igo_code"];
//                    $igoData = $igoModel->queryByCode($igoCode);
//                    $invCode = $igoData['inv_code'];
//                    $oldGsData = $gsModel->queryByCode($gsCode);
//                    if ($oldGsData['gs_count'] >= $igoCount) {
//                        $gsCount = bcsub($tgData['gs_count'], $igoCount, 2);
//                        $gsSkCount = bcsub($tgData['gs_sku_count'], $igoSkCount, 2);
//                        $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($gsCode, $gsCount, $gsSkCount);
//                        $igsNewCount = bcadd($tgData['igs_count'], $igoCount, 2);
//                        $igsNewSkCount = bcadd($tgData['igs_sku_count'], $igoSkCount, 2);
//                        $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsNewCount, $igsNewSkCount);
//                        $igoNewCount = bcadd($tgData['igo_count'], $igoCount, 2);
//                        $igoNewSkCount = bcadd($tgData['igo_sku_count'], $igoSkCount, 2);
//                        $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCode, $igoNewCount, $igoNewSkCount);
//                    } else {
//                        $igoNewCount = bcadd($tgData['igo_count'], $igoCount, 2);
//                        $igoNewSkCount = bcadd($tgData['igo_sku_count'], $igoSkCount, 2);
//                        $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCode, $igoNewCount, $igoNewSkCount);
//                        $igsAllCount = bcsub($igoCount, $oldGsData['gs_count'], 2);
//                        $gsClause = array(
//                            "gscode" => array("GT", $gsCode)
//                        );
//                        $gsList = $gsModel->queryListByCondition($gsClause);
//                        $sentNum = 0;
//                        foreach ($gsList as $item) {
//                            $skuCode = $item['sku_code'];
//                            if ($item['gs_count'] <= 0) continue;
//                            if ($igsAllCount - $sentNum - $item['gs_count'] >= 0) {
//                                $sentNum += $item['gs_count'];
//                                $igsAddData = array(
//                                    "count" => $item['gs_count'],
//                                    "bprice" => $item['gb_bprice'],
//                                    "spucode" => $spCode,
//                                    "gscode" => $item['gs_code'],
//                                    "igocode" => $igoCode,
//                                    "skucode" => $skuCode,
//                                    "skucount" => bcdiv($item['gs_count'], $item['spu_count'], 4),
//                                    "invcode" => $invCode,
//                                    "remaining" => 0
//                                );
//                                $isSuccess = $isSuccess && $igsModel->insert($igsAddData);
//                                $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($item['gs_code'], 0, 0);
//                            } else {
//                                if ($igsAllCount - $sentNum != 0) {
//                                    $gscount = $item['gs_count'] - ($igsAllCount - $sentNum);
//                                    $gsskcount = bcdiv($gscount, $item['spu_count'], 4);
//                                    $igoodsentCount = $igsAllCount - $sentNum;
//                                    $sentNum += $igoodsentCount;
//                                    $igsAddData = array(
//                                        "count" => $igoodsentCount,
//                                        "bprice" => $item['gb_bprice'],
//                                        "spucode" => $spCode,
//                                        "gscode" => $item['gs_code'],
//                                        "igocode" => $igoCode,
//                                        "skucode" => $skuCode,
//                                        "skucount" => bcdiv($igoodsentCount, $item['spu_count'], 4),
//                                        "invcode" => $invCode,
//                                    );
//                                    $isSuccess = $isSuccess && $igsModel->insert($igsAddData);
//                                    $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($item['gs_code'], $gscount, $gsskcount);
//                                    break;
//                                }
//                            }
//                        }
//                    }
//                    $isSuccess = $isSuccess & $goodsModel->updateCountByCode($goodsData['goods_code'], $goodsData['goods_count'], bcadd($goodsInfo['goods_count'], $count, 2), bcadd($goodsInfo['sku_count'], $skCount, 2));
//                } else {
//                    $success = false;
//                    $skNum = bcsub($goodsData["sku_count"], bcsub(0, $skCount, 2));
//                    $message = "此货品还有" . $skNum . $goodsData["sku_unit"] . "未入库，请先确认库存";
//                    $data = array();
//                    return array($success, $data, $message);
//                }
//            }
//        }
//        if (!$isSuccess) {
//            $success = false;
//            $message = "确认退货操作失败";
//        } else {
//            $success = true;
//            $message = "确认退货操作成功";
//        }
//
//        $data = array();
//        return array($success, $data, $message);
//    }
//
//    //验货后确认退货
//    public function return_goods_handle($param)
//    {
//        $oCode = $param['oCode'];
//        $ogCode = $param['ogCode'];//ordergoods表中的goods_code
//        $supCode = $param['supCode'];
//        $skCode = $param['skCode'];
//        $skCount = $param['skCount'];//退货sku数量
//        $count = $param['count'];//退货spu数量
//        $spCode = $param['spCode'];
//        $skInit = $param['skInit'];//退货前sku数量/验货后为ordergoodsturn中sku_count
//        $type = 6;
//        $time = venus_current_datetime();
//
//        if (empty($oCode)) {
//            return array(false, array(), "订单编号不能为空");
//        }
//        if (empty($skCode)) {
//            return array(false, array(), "商品编号不能为空");
//        }
//        if (empty($skCount)) {
//            return array(false, array(), "商品数量不能为空");
//        }
//        if (empty($spCode)) {
//            return array(false, array(), "商品最小计量编号不能为空");
//        }
//        if (empty($count)) {
//            return array(false, array(), "商品最小计量总数量不能为空");
//        }
//        if (empty($supCode)) {
//            return array(false, array(), "供货商编号不能为空");
//        }
//        if (empty($skInit)) {
//            return array(false, array(), "商品退货前数量不能为空");
//        }
//        if (empty($ogCode)) {
//            return array(false, array(), "商品下单批次编号不能为空");
//        }
//        $gsModel = GoodstoredDao::getInstance($this->warCode);
//        $gbModel = GoodsbatchDao::getInstance($this->warCode);
//        $goodsModel = GoodsDao::getInstance($this->warCode);
//        $igsModel = IgoodsentDao::getInstance($this->warCode);
//        $igoModel = IgoodsDao::getInstance($this->warCode);
//        $recModel = ReceiptDao::getInstance($this->warCode);
//        $invModel = InvoiceDao::getInstance($this->warCode);
//        $orderModel = OrderDao::getInstance($this->warCode);
//        $ogModel = OrdergoodsDao::getInstance($this->warCode);
//
//        $isSuccess = true;
//
//        $orderMsg = $orderModel->queryByCode($oCode);
//        $ogMsg = $ogModel->queryByCode($ogCode);
//        $invSpuData = array();
//        if ($supCode != "SU00000000000001" && $supCode != "SU00000000000002") {
//            //出仓单数据
//            $addInvData = array();
//            $addInvData['receiver'] = $orderMsg['user_name'];
//            $addInvData['phone'] = $orderMsg['user_phone'];
//            $addInvData['address'] = $orderMsg['war_address'];
//            $addInvData['postal'] = $orderMsg['war_postal'];
//            $addInvData['type'] = $type;
//            $addInvData['mark'] = $orderMsg['order_mark'];
//            $addInvData['worcode'] = $this->worcode;
//            $addInvData['ctime'] = $orderMsg['order_ctime'];
//            $addInvData['ecode'] = $oCode;
//            $goodsToInv = array();
//            $goodsToInv['skucode'] = $skCode;
//            $goodsToInv['skucount'] = $skCount;
//            $goodsToInv['spucode'] = $spCode;
//            $goodsToInv['count'] = $count;
//            $goodsToInv['sprice'] = $ogMsg['spu_sprice'];
//            $goodsToInv['bprice'] = $ogMsg['spu_bprice'];
//            $goodsToInv['pprice'] = $ogMsg['profit_price'];
//            $goodsToInv['percent'] = $ogMsg['pro_percent'];
//            $invSpuData['goods'] = $goodsToInv;
//            $invSpuData['invMsg'] = $addInvData;
//            unset($goodsToInv);
//            unset($addInvData);
//        }
//        //入仓单数据
//        $goodsToRec = array();
//        $goodsToRec['skucode'] = $skCode;
//        $goodsToRec['spucode'] = $spCode;
//        $goodsToRec['supcode'] = $supCode;
//        $goodsToRec['bprice'] = $ogMsg['spu_bprice'];
//        $goodsToRec['ctime'] = venus_current_datetime();
//        $recSpuData = $goodsToRec;
//        $recSpuData['count'] = $count;
//        $recSpuData['skucount'] = $skCount;
//        if (!empty($recSpuData) && !empty($invSpuData)) {
//            $issetEcodeByRec = $recModel->queryByEcode($oCode);
//            if (!$issetEcodeByRec) {
//                $addRecData['ctime'] = $time;
//                $addRecData['mark'] = $oCode;
//                $addRecData['status'] = 3;
//                $addRecData['worcode'] = $this->worcode;
//                $addRecData['type'] = 2;
//                $recCode = $recModel->insert($addRecData);
//                $isSuccess = $isSuccess && $recCode;
//            } else {
//                $recCode = $issetEcodeByRec['rec_code'];
//            }
//            $addGbData = $recSpuData;
//            $addGbData['status'] = 4;
//            $addGbData['reccode'] = $recCode;
//            $gbCode = $gbModel->insert($addGbData);
//            $isSuccess = $isSuccess && $gbCode;
//            $addGsData = $recSpuData;
//            $addGsData['init'] = $recSpuData['count'];
//            $addGsData['gbcode'] = $gbCode;
//            $gsCode = $gsModel->insert($addGsData);
//            $gsData[$gsCode]['count'] = $recSpuData['count'];
//            $gsData[$gsCode]['skucount'] = $recSpuData['skucount'];
//            $isSuccess = $isSuccess && $gsCode;
//            $issetGoods = $goodsModel->queryBySpuCode($recSpuData['spucode']);
//            if ($issetGoods) {
//                $goodsCode = $issetGoods['goods_code'];
//                $init = bcadd($issetGoods['goods_init'], $recSpuData['count'], 2);
//                $count = bcadd($issetGoods['goods_count'], $recSpuData['count'], 2);
//                $skuinit = bcadd($issetGoods['sku_init'], $recSpuData['skucount'], 2);
//                $skucount = bcadd($issetGoods['sku_count'], $recSpuData['skucount'], 2);
//                $isSuccess = $isSuccess && $goodsModel->updateCountAndInitByCode($goodsCode, $init, $count, $skuinit, $skucount);
//            } else {
//                $goodsAddData = array(
//                    'init' => $recSpuData['count'],
//                    'count' => $recSpuData['count'],
//                    'spucode' => $recSpuData['spucode'],
//                    'skucode' => $recSpuData['skucode'],
//                    'skuinit' => $recSpuData['skucount'],
//                    'skucount' => $recSpuData['skucount'],
//                );
//                $isSuccess = $isSuccess && $goodsModel->insert($goodsAddData);
//            }
//            $invSpuDatum = $invSpuData['goods'];
//            $issetEcodeByInv = $invModel->queryByEcode($recCode);
//            if (!empty($issetEcodeByInv)) {
//                $invCode = $issetEcodeByInv[0]['inv_code'];
//            } else {
//                $addInvData = $invSpuData['invMsg'];
//                $addInvData['ecode'] = $recCode;
//                $addInvData['status'] = 5;
//                $invCode = $invModel->insert($addInvData);
//                $isSuccess = $isSuccess && $invCode;
//            }
//            $addIgoData = $invSpuDatum;
//            unset($addIgoData['bprice']);
//            $addIgoData['invcode'] = $invCode;
//            $goodsData = $goodsModel->queryBySkuCode($invSpuDatum['skucode']);
//            $addIgoData['goodscode'] = $goodsData['goods_code'];
//            $igoCode = $igoModel->insert($addIgoData);
//            $isSuccess = $isSuccess && $igoCode;
//            unset($invSpuDatum['sprice']);
//            unset($invSpuDatum['pprice']);
//            unset($invSpuDatum['percent']);
//            $addIgsData = $invSpuDatum;
//            $addIgsData['gscode'] = $gsCode;
//            $addIgsData['igocode'] = $igoCode;
//            $addIgsData['invcode'] = $invCode;
//            $igsCode = $igsModel->insert($addIgsData);
//            $isSuccess = $isSuccess && $igsCode;
//            $isSuccess = $isSuccess && $gsModel->updateCountAndSkuCountByCode($gsCode, 0, 0);
//            $issetGoods = $goodsModel->queryBySkuCode($recSpuData['skucode']);
//            $goodsCode = $issetGoods['goods_code'];
//            $count = bcsub($issetGoods['goods_count'], $addIgsData['count'], 2);
//            $skucount = bcsub($issetGoods['sku_count'], $addIgsData['skucount'], 2);
//            $isSuccess = $isSuccess && $goodsModel->updateCountByCode($goodsCode, $issetGoods['goods_count'], $count, $skucount);
//        } elseif (!empty($recSpuData) && empty($invSpuData)) {
//            $issetEcodeByRec = $recModel->queryByEcode($oCode);
//            if (!$issetEcodeByRec) {
//                $addRecData['ctime'] = $time;
//                $addRecData['mark'] = $oCode;
//                $addRecData['status'] = 3;
//                $addRecData['worcode'] = $this->worcode;
//                $addRecData['type'] = 2;
//                $recCode = $recModel->insert($addRecData);
//                $isSuccess = $isSuccess && $recCode;
//            } else {
//                $recCode = $issetEcodeByRec['rec_code'];
//            }
//            $addGbData = $recSpuData;
//            $addGbData['status'] = 4;
//            $addGbData['reccode'] = $recCode;
//
//            $gbCode = $gbModel->insert($addGbData);
//            $isSuccess = $isSuccess && $gbCode;
//            $addGsData = $recSpuData;
//            $addGsData['init'] = $recSpuData['count'];
//            $addGsData['gbcode'] = $gbCode;
//            $gsCode = $gsModel->insert($addGsData);
//            $gsData[$gsCode] = $recSpuData;
//            $isSuccess = $isSuccess && $gsCode;
//            $issetGoods = $goodsModel->queryBySpuCode($recSpuData['spucode']);
//            if ($issetGoods) {
//                $goodsCode = $issetGoods['goods_code'];
//                $init = bcadd($issetGoods['goods_init'], $recSpuData['count'], 2);
//                $count = bcadd($issetGoods['goods_count'], $recSpuData['count'], 2);
//                $skuinit = bcadd($issetGoods['sku_init'], $recSpuData['skucount'], 2);
//                $skucount = bcadd($issetGoods['sku_count'], $recSpuData['skucount'], 2);
//                $isSuccess = $isSuccess && $goodsModel->updateCountAndInitByCode($goodsCode, $init, $count, $skuinit, $skucount);
//            } else {
//                $goodsAddData = array(
//                    'init' => $recSpuData['count'],
//                    'count' => $recSpuData['count'],
//                    'spucode' => $recSpuData['spucode'],
//                    'skucode' => $recSpuData['skucode'],
//                    'skuinit' => $recSpuData['skucount'],
//                    'skucount' => $recSpuData['skucount'],
//                );
//                $isSuccess = $isSuccess && $goodsModel->insert($goodsAddData);
//            }
//        } else {
//            $isSuccess = $isSuccess && false;
//        }
//        if (!$isSuccess) {
//            $success = false;
//            $data = array();
//            $msg = "确认退货失败";
//            return array($success, $data, $msg);
//        } else {
//            $success = true;
//            $data = array();
//            $msg = "确认退货成功";
//            return array($success, $data, $msg);
//        }
//    }
//
//    //验收前，自营少收并且有质量问题的退货操作
//    public function return_goods_update($param)
//    {
////        $param = array(
////            "oCode" => "O40123153544353",
////            "ogCode" => "G40123153544760",
////            "supCode" => "SU00000000000001",
////            "skCode" => "SK0000294",
////            "skCount" => "2.00",//退货sku数量
////            "count" => "100.00",
////            "spCode" => "SP000294",
////            "skInit" => "4.00",//退货前sku数量
////        );
//        $oCode = $param['oCode'];
//        $ogCode = $param['ogCode'];//ordergoods表中的goods_code
//        $supCode = $param['supCode'];//
//        $skCode = $param['skCode'];
//        $skCount = $param['skCount'];//退货sku数量
//        $count = $param['count'];//退货spu数量
//        $spCode = $param['spCode'];
//        $skInit = $param['skInit'];//退货前sku数量/验货后为ordergoodsturn中sku_count
//        $invType = 6;
//        $time = venus_current_datetime();
//
//        $igsModel = IgoodsentDao::getInstance($this->warCode);
//        $igoModel = IgoodsDao::getInstance($this->warCode);
//        $invModel = InvoiceDao::getInstance($this->warCode);
//        $orderModel = OrderDao::getInstance($this->warCode);
//        $ogModel = OrdergoodsDao::getInstance($this->warCode);
//        $goodsModel = GoodsDao::getInstance($this->warCode);
//
//        $isSuccess = true;
//
//        $orderMsg = $orderModel->queryByCode($oCode);
//        $ogMsg = $ogModel->queryByCode($ogCode);
//        $addInvData = array(
//            'receiver' => $orderMsg['user_name'],
//            'phone' => $orderMsg['user_phone'],
//            'address' => $orderMsg['war_address'],
//            'postal' => $orderMsg['war_postal'],
//            'type' => $invType,
//            'mark' => $oCode . "验货时退货出仓",
//            'worcode' => $this->worcode,
//            'ctime' => $orderMsg['order_ctime'],
//            'status' => 5,
//        );
//        $invCodeNew = $invModel->insert($addInvData);
//        $isSuccess = $isSuccess && $invCodeNew;
//        $goodsData = $goodsModel->queryBySkuCode($skCode);
//        $addIgoData = array(
//            "count" => $count,
//            "spucode" => $spCode,
//            "sprice" => $ogMsg['spu_sprice'],
//            "pprice" => $ogMsg['profit_price'],
//            "percent" => $ogMsg['pro_percent'],
//            "goodscode" => $goodsData['goods_code'],
//            "skucode" => $skCode,
//            "skucount" => $skCount,
//            "invcode" => $invCodeNew,
//        );
//        $igoCodeNew = $igoModel->insert($addIgoData);
//        $isSuccess = $isSuccess && $igoCodeNew;
//        $returngoodsData = $invModel->queryByInvEcodeAndSkuCodeAndSkuInitAndSupCode($oCode, $skCode, $skInit, $supCode);
//        if (empty($returngoodsData)) {
//            $success = false;
//            $message = "无相关退货出仓信息";
//            $data = array();
//            return array($success, $data, $message);
//        }
//        $igoCodeArr = array();
//        $newCount = $count;
//        $newSkCount = $skCount;
//        //一个出仓单一个供货商只有一条igo数据
//        foreach ($returngoodsData as $returngoodsDatum) {
//            $gsCode = $returngoodsDatum['gs_code'];
//            $igoCode = $returngoodsDatum['igo_code'];
//            $igsCode = $returngoodsDatum['igs_code'];
//            $igoCount = bcsub($returngoodsDatum['igo_count'], $count, 2);//最终igo数量
//            $igoSkCount = bcsub($returngoodsDatum['igo_sku_count'], $skCount, 2);//最终igo数量
//            if (empty($igoCodeArr) && !in_array($igoCode, $igoCodeArr)) {
//                $igoCodeArr["invCode"] = $returngoodsDatum['inv_code'];
//                $igoCodeArr["igoCode"] = $igoCode;
//                $igoCodeArr["igoCount"] = $igoCount;
//                $igoCodeArr["igoSkuCount"] = $igoSkCount;
//            }
//
//            if ($returngoodsDatum['igs_count'] <= $newCount) {
//                $isSuccess = $isSuccess && $igsModel->updateIgoCodeByCode($igsCode, $igoCodeNew);
//                $isSuccess = $isSuccess && $igsModel->updateInvCodeByCode($igsCode, $invCodeNew);
//                $newCount = bcsub($newCount, $returngoodsDatum['gs_count'], 2);
//                $newSkCount = bcsub($newSkCount, $returngoodsDatum['gs_sku_count'], 2);
//            } else {
//                $igsNewCount = bcsub($returngoodsDatum['igs_count'], $newCount, 2);
//                $igsNewSkCount = bcsub($returngoodsDatum['igs_sku_count'], $newSkCount, 2);
//                $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsNewCount, $igsNewSkCount);
//                $addIgsData = array(
//                    "count" => $newCount,
//                    "spucode" => $spCode,
//                    "bprice" => $ogMsg['spu_bprice'],
//                    "gscode" => $returngoodsDatum['gs_code'],
//                    "igocode" => $igoCodeNew,
//                    "skucode" => $skCode,
//                    "skucount" => $newSkCount,
//                    "invcode" => $invCodeNew,
//                );
//                $igsCodeNew = $igsModel->insert($addIgsData);
//                $isSuccess = $isSuccess && $igsCodeNew;
//            }
//        }
//        if ($igoCodeArr['igoCount'] == 0 && $igoCodeArr['igoSkuCount'] == 0) {
//            $isSuccess = $isSuccess && $igoModel->deleteByCode($igoCodeArr['igoCode'], $igoCodeArr['invCode']);
//        } else {
//            $isSuccess = $isSuccess && $igoModel->updateCountAndSkuCountByCode($igoCodeArr['igoCode'], $igoCodeArr['igoCount'], $igoCodeArr['igoSkuCount']);
//        }
//        if (!$isSuccess) {
//            $success = false;
//            $data = array();
//            $msg = "验收退货失败";
//            return array($success, $data, $msg);
//        } else {
//            $success = true;
//            $data = array();
//            $msg = "验收退货成功";
//            return array($success, $data, $msg);
//        }
//
//    }

}