<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2019/1/23
 * Time: 17:47
 */

namespace Wms\Service;

use Wms\Dao\GoodsbatchDao;
use Wms\Dao\GoodsDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\InvoiceDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SkuDao;

class WarehouseService
{
    function __construct()
    {
    }

    /**
     * @param $data [type入仓单类型,warcode仓库编号,worcode入仓人员编号,mark备注信息;list货品信息["count","bprice","spucode","supcode","skucode","skucount"]];
     * @return array
     * 入库操作[入+1，存+1]
     */
    public function create_receipt($data)
    {
        $type = $data["type"];
        $warCode = $data["warCode"];
        $worCode = $data["worCode"];
        $mark = $data["mark"];
        $recData = $data["list"];

        if (empty($type)) return array(false, array(), "入仓单类型不能为空");
        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (empty($worCode)) return array(false, array(), "下单人编号不能为空");
        if (empty($mark)) return array(false, array(), "入仓单备注不能为空");
        if (empty($recData)) return array(false, array(), "入仓单货品不能为空");

        $recModel = ReceiptDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $skuModel = SkuDao::getInstance($warCode);
        $isSuccess = true;

        $addRecData = array(
            "worcode" => $worCode,
            "mark" => $mark,
            "status" => 3,
            "type" => $type
        );
        if (!empty($mark)) {
            $issetRec = $recModel->queryByEcode($mark);
            if (!empty($issetRec)) {
                $recCode = $issetRec['rec_code'];
            } else {
                $recCode = $recModel->insert($addRecData);
            }
        } else {
            $recCode = $recModel->insert($addRecData);
        }
        $isSuccess = $isSuccess && !empty($recCode);
        foreach ($recData as $recDatum) {
            $skuCode = $recDatum["skucode"];
            $skuCount = $recDatum["skucount"];
            if (empty($skuCode)) return array(false, array(), "sku编号不能为空");
            if (empty($skuCount)) return array(false, array(), "sku数量不能为空");
            if ($skuCount == 0) continue;
            $bPrice = $recDatum["bprice"];
            $skuData = $skuModel->queryByCode($skuCode);
            $spuCunit = $skuData['spu_cunit'];
            $spuCount = $skuData['spu_count'];
            $count = bcmul($spuCount, $skuCount, 2);
            $spuName = $skuData['spu_name'];
            if (ceil(round(bcmod(($skuCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");
            $spuCode = $skuData["spu_code"];
            $supCode = !empty($recDatum["supcode"]) ? $recDatum["supcode"] : $skuData['sup_code'];
            $addGbData = array(
                "status" => 3,
                "count" => $count,
                "bprice" => $bPrice,
                "spucode" => $spuCode,
                "supcode" => $supCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
                "reccode" => $recCode,
            );
            $gbCode = $goodsbatchModel->insert($addGbData);
            $isSuccess = $isSuccess && !empty($gbCode);
            $addGsData = array(
                "init" => $count,
                "count" => $count,
                "bprice" => $bPrice,
                "gbcode" => $gbCode,
                "spucode" => $spuCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
            );
            $gsCode = $goodstoredModel->insert($addGsData);
            $isSuccess = $isSuccess && !empty($gsCode);
            $queryGoods = $goodsModel->queryBySkuCode($skuCode);
            if (!empty($queryGoods)) {
                $goodsCode = $queryGoods["goods_code"];
                $goodsInit = $queryGoods['goods_init'];
                $goodsCount = $queryGoods['goods_count'];
                $goodsSkuInit = $queryGoods['sku_init'];
                $goodsSkuCount = $queryGoods['sku_count'];
                $newInit = bcadd($goodsInit, $count, 2);
                $newCount = bcadd($goodsCount, $count, 2);
                $newSkuInit = bcadd($goodsSkuInit, $skuCount, 2);
                $newSkuCount = bcadd($goodsSkuCount, $skuCount, 2);
                $isSuccess = $isSuccess &&
                    $goodsModel->updateCountAndInitByCode($goodsCode, $newInit, $newCount, $newSkuInit, $newSkuCount);
            } else {
                $addGoodsData = array(
                    "init" => $count,
                    "count" => $count,
                    "skuinit" => $skuCount,
                    "skucount" => $skuCount,
                    "skucode" => $skuCode,
                    "spucode" => $spuCode,
                );
                $isSuccess = $isSuccess &&
                    $goodsModel->insert($addGoodsData);
            }
        }
        if ($isSuccess) {
            return array(true, array(), "入仓成功");
        } else {
            return array(false, array(), "入仓失败");
        }
    }

    //出库操作[存-1, 出+1]
    public function create_invoice($data)
    {
        $type = $data["type"];
        $ecode = $data["ecode"];//订单编号
        $warCode = $data["warCode"];//仓库编号
        $worCode = $data["worCode"];//人员编号
        $phone = $data["phone"];
        $address = $data["address"];
        $postal = $data["postal"];
        $receiver = $data['receiver'];//客户名称
        $invData = $data["list"];
        $mark = $data['mark'];//小程序单(直采)/小程序单(自营)
        $isAllowLessgoods = $data['isallowlessgoods'];//是否允许缺货

        $returnData = array();

        if (empty($type)) return array(false, array(), "出仓单类型不能为空");
        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (isset($data["ecode"]) && empty($data["ecode"])) return array(false, array(), "订单编号不能为空");
        if (empty($receiver)) return array(false, array(), "下单人不能为空");
        if (empty($worCode)) return array(false, array(), "下单人编号不能为空");
        if (empty($mark)) return array(false, array(), "出仓单备注不能为空");
        if (empty($invData)) return array(false, array(), "货品不能为空");


        $invModel = InvoiceDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $igoodsModel = IgoodsDao::getInstance($warCode);
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $skuModel = SkuDao::getInstance($warCode);

        $isSuccess = true;
        $invAddData = array(
            "status" => 5,//出仓单状态已出仓
            "receiver" => $receiver,//客户名称
            "type" => $type,//出仓单类型
            "mark" => $mark,//出仓单备注
            "worcode" => $worCode,//人员编号
            "phone" => $phone,
            "address" => $address,
            "postal" => $postal,
            "ecode" => $ecode,
        );//出仓单新增数据
        !empty($ecode) ? $issetInv = $invModel->queryByEcodeAndMark($ecode, $mark) : $issetInv = null;
        if (!empty($issetInv)) {
            if (count($issetInv) == 1) {
                $invCode = $issetInv["inv_code"];
            } else {
                return array(false, "", "系统检测出现重复出仓单数据");
            }
        } else {
            $invCode = $invModel->insert($invAddData);
        }
        $isSuccess = $isSuccess && !empty($invCode);
        $lessGoods = array();
        foreach ($invData as $invDatum) {
            $skCode = $invDatum['skucode'];
            $skCount = $invDatum['skucount'];
            if (empty($skCode)) return array(false, array(), "sku编号不能为空");
            if (empty($skCount)) return array(false, array(), "sku数量不能为空");
            if ($skCount == 0) continue;
            $goodsData = $goodsModel->queryBySkuCode($skCode);//获取sku库存
            if (empty($goodsData) || $goodsData['sku_count'] <= 0) {
                if (!empty($isAllowLessgoods) && $isAllowLessgoods == 1) {
                    $skCount = 0;
                } else {
                    $skuData = $skuModel->queryByCode($skCode);
                    $lessGoods[$skuData['spu_name']] = $skCount . $skuData['sku_unit'];
                    continue;
                }

            }
            if ($skCount == 0) continue;
            $goodsSkuCount = $goodsData['sku_count'];//spu库存
            $goodsCount = $goodsData['goods_count'];//spu库存
            $spuCount = $goodsData['spu_count'];
            $spCode = $goodsData['spu_code'];
            $count = bcmul($spuCount, $skCount, 2);
            if ($goodsSkuCount < $skCount) {
                if ($goodsSkuCount >= 0) {
                    if (!empty($isAllowLessgoods) && $isAllowLessgoods == 1) {
                        $skCount = $goodsSkuCount;
                    } else {
                        $lessGoods[$goodsData['spu_name']] = bcsub($skCount, $goodsSkuCount, 2) . $goodsData['sku_unit'];
                        continue;
                    }
                } else {
                    $lessGoods[$goodsData['spu_name']] = bcsub($skCount, $goodsSkuCount, 2) . $goodsData['sku_unit'];
                    continue;
                }

            }
            if (!empty($invDatum['goodscode'])) $returnData[$invDatum['goodscode']][$skCode] = bcadd($returnData[$invDatum['goodscode']][$skCode], $skCount, 2);
            $returnData[$skCode] = bcadd($returnData[$skCode], $skCount, 2);
            $sprice = !empty($goodsData['spu_sprice']) ? $goodsData['spu_sprice'] : 0;
            $pprice = !empty($goodsData['pro_price']) ? $goodsData['pro_price'] : 0;//spu利润价
            $percent = !empty($goodsData['pro_percent']) ? $goodsData['pro_percent'] : 0;//spu利润率
            $spuCunit = $goodsData['spu_cunit'];
            $spuName = $goodsData['spu_name'];
            if (ceil(round(bcmod(($skCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");

            $goodsCode = $goodsData["goods_code"];
            //更新库存
            $addIgoData = array(
                "count" => $count,
                "spucode" => $spCode,
                "sprice" => $sprice,
                "pprice" => $pprice,
                "percent" => $percent,
                "goodscode" => $goodsCode,
                "skucode" => $skCode,
                "skucount" => $skCount,
                "invcode" => $invCode,
            );
            $igoCode = $igoodsModel->insert($addIgoData);
            $isSuccess = $isSuccess && !empty($igoCode);
            $updatedGoodsSkuCount = bcsub($goodsSkuCount, $skCount, 2);//更新后的SKU
            $updatedGoodsCount = bcmul($updatedGoodsSkuCount, $spuCount, 2);

            $isSuccess = $isSuccess &&
                $goodsModel->updateCountByCode($goodsCode, $goodsCount, $updatedGoodsCount, $updatedGoodsSkuCount);
            $gsCount = $goodstoredModel->queryCountBySkuCode($skCode);
            $gsDataList = $goodstoredModel->queryListBySkuCode($skCode, 0, $gsCount);

            foreach ($gsDataList as $gsData) {
                $gsCode = $gsData["gs_code"];
                $gsCount = $gsData['gs_count'];
                $gsSkuCount = $gsData['sku_count'];
                $bPrice = $gsData['gb_bprice'];
                if ($gsCount == 0 || $gsSkuCount == 0 || $gsCount < 0 || $gsSkuCount < 0) continue;
                if ($skCount == 0) break;
                $igsSkuCount = 0;
                if ($skCount <= $gsSkuCount) {
                    $updatedSkuCount = bcsub($gsSkuCount, $skCount, 2);
                    $updatedGoodsCount = bcmul($spuCount, $updatedSkuCount, 2);
                    $igsSkuCount = $skCount;
                } else {
                    $updatedSkuCount = 0;
                    $updatedGoodsCount = bcmul($spuCount, $updatedSkuCount, 2);
                    $igsSkuCount = $gsSkuCount;
                }
                $isSuccess = $isSuccess &&
                    $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updatedGoodsCount, $updatedSkuCount);
                if ($updatedSkuCount == 0 && $gsData['gb_status'] != 4) {
                    $isSuccess = $isSuccess && $goodsbatchModel->updateStatusByCode($gsData['gb_code'], 4);
                }
                $addIgsData = array(
                    "count" => bcmul($igsSkuCount, $spuCount, 2),
                    "bprice" => $bPrice,
                    "spucode" => $spCode,
                    "gscode" => $gsCode,
                    "igocode" => $igoCode,
                    "skucode" => $skCode,
                    "skucount" => $igsSkuCount,
                    "invcode" => $invCode,
                );
                $igsCode = $igoodsentModel->insert($addIgsData);
                $isSuccess = $isSuccess && !empty($igsCode);
                $skCount = bcsub($skCount, $igsSkuCount, 2);
            }
            $isSuccess = $isSuccess && ($skCount == 0);
        }
        $queryCountByIgoods = $igoodsModel->queryCountByInvCode($invCode);
        $queryCountByIgoodsent = $igoodsentModel->queryCountByInvCode($invCode);
        if ($queryCountByIgoods == 0 && $queryCountByIgoodsent == 0) $isSuccess = $isSuccess && $invModel->deleteByCode($invCode);

        if (!empty($lessGoods)) {
            //输出库存不足商品的缺货数量
            $message = "以下货品库存不足:" . PHP_EOL;
            foreach ($lessGoods as $spuName => $lessGood) {
                $message = $message . $spuName . ":" . $lessGood . "," . PHP_EOL;
            }
            return array(false, array(), $message);
        } else {
            if ($isSuccess) {
                return array(true, $returnData, "出仓成功");
            } else {
                return array(false, $returnData, "出仓失败");
            }
        }


    }

    //更新出库单[存+1，出-1/存-1，出+1]
    public function update_invoice_goods($data)
    {
        $warCode = $data["warCode"];//仓库编号
        $ecode = $data["ecode"];
        $mark = $data["mark"];//小程序单(自营),小程序单(直采)
        $type = $data['type'];//1少收，7多收
        $skuCode = $data['skuCode'];
        $skuCount = $data['skuCount'];
        $isRegular = $data['isRegular'];//是否是常规更新出仓单0:退货，1：正常

        $invModel = InvoiceDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $igoodsModel = IgoodsDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);

        if (empty($isRegular) && empty($type)) return array(false, array(), "退货类型不能为空");
        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (empty($ecode)) return array(false, array(), "订单编号不能为空");
        if (empty($mark)) return array(false, array(), "出仓单备注不能为空");
        if (empty($skuCode)) return array(false, array(), "sku编号不能为空");
        if (empty($skuCount)) return array(false, array(), "sku数量不能为空");

        $isSuccess = true;
        $issetInv = $invModel->queryByEcodeAndMark($ecode, $mark);
        if (empty($issetInv)) return array(true, array(), "此出仓单无相关信息");
        $invCode = $issetInv["inv_code"];
        $goodsData = $goodsModel->queryBySkuCode($skuCode);
        $goodsCode = $goodsData["goods_code"];
        $goodsSkuCount = $goodsData["sku_count"];
        $goodsCount = $goodsData["goods_count"];
        $spuCount = $goodsData["spu_count"];
        $spuCode = $goodsData['spu_code'];
        $spuCunit = $goodsData['spu_cunit'];
        $spuName = $goodsData['spu_name'];
        if (ceil(round(bcmod(($skuCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");
        if ($skuCount < 0 && bcadd($skuCount, $goodsSkuCount) < 0 && $type != 1) {
            if ($goodsSkuCount > 0) {
                $lessSkuCount = bcadd($skuCount, $goodsSkuCount, 2);
                $skuUnit = $goodsData['sku_unit'];
                if (empty($isRegular)) {
                    $fileData = "#验货多收时发现[{$ecode}]缺货信息更新如下：<br>\n" . "{$spuName}: {$lessSkuCount} {$skuUnit}" . "<br>" . "当前库存:{$goodsSkuCount} {$skuUnit},需要出库:{$skuCount} {$skuUnit}" . "<br>\n";
                } else {
                    $fileData = "#抄码出库时发现[{$ecode}]缺货信息更新如下：<br>\n" . "{$spuName}: {$lessSkuCount} {$skuUnit}" . "<br>" . "当前库存:{$goodsSkuCount} {$skuUnit},需要出库:{$skuCount} {$skuUnit}" . "<br>\n";
                }
                $oosFilePath = C("FILE_SAVE_PATH") . C("FILE_TYPE_NAME.WAREHOUSE_OUT_OF_STOCK") . "/" . date("Y-m-d", time()) . ".log";
                file_put_contents($oosFilePath, $fileData, FILE_APPEND);
                $skuCount = bcsub(0, $goodsSkuCount, 2);//将库存数量出库，多于数量计入缺货信息
            } else {
                $skuUnit = $goodsData['sku_unit'];
                if (empty($isRegular)) {
                    $fileData = "#验货多收时发现[{$ecode} ]缺货信息更新如下：<br>\n" . "{$spuName}:{$skuCount} {$skuUnit}" . "<br>\n";
                } else {
                    $fileData = "#抄码出库时发现[{$ecode} ]缺货信息更新如下：<br>\n" . "{$spuName}:{$skuCount} {$skuUnit}" . "<br>\n";
                }
                $oosFilePath = C("FILE_SAVE_PATH") . C("FILE_TYPE_NAME.WAREHOUSE_OUT_OF_STOCK") . "/" . date("Y-m-d", time()) . ".log";
                file_put_contents($oosFilePath, $fileData, FILE_APPEND);
                $success = true;
                $data = array();
                $message = "更新出仓单信息成功";
                return array($success, $data, $message);
            }
        }
        $igoodsData = $igoodsModel->queryByInvCodeAndSkuCode($invCode, $skuCode);
        if (empty($igoodsData)) return array(true, array(), "没有出库记录");
        if ($type != 1 && $goodsSkuCount < $skuCount) return array(false, array(), "此货品库存不足,请核实");
        $igoCode = $igoodsData["igo_code"];

        if ($igoodsData["sku_count"] < $skuCount && $type == 1) {
            $skuCount = $igoodsData["sku_count"];
        }
        $updateIgoodsSkuCount = bcsub($igoodsData["sku_count"], $skuCount, 2);
        $updateIgoodsCount = bcmul($updateIgoodsSkuCount, $spuCount, 2);
        $isSuccess = $isSuccess &&
            $igoodsModel->updateCountAndSkuCountByCode($igoCode, $updateIgoodsCount, $updateIgoodsSkuCount);
        $updateGoodsSkuCount = bcadd($goodsSkuCount, $skuCount, 2);
        $updateGoodsCount = bcmul($updateGoodsSkuCount, $spuCount, 2);
        $isSuccess = $isSuccess &&
            $goodsModel->updateCountByCode($goodsCode, $goodsCount, $updateGoodsCount, $updateGoodsSkuCount);
        $queryIgsClause = array(
            "invcode" => $invCode,
            "skucode" => $skuCode,
        );
        $igoodsentCount = $igoodsentModel->queryCountByCondition($queryIgsClause);
        $igoodsentData = $igoodsentModel->queryListByCondition($queryIgsClause, 0, $igoodsentCount);

        if ($type == 1) {
            $isSuccess = $isSuccess &&
                $this->update_invoice_goods_less($igoodsentData, $skuCount, $spuCount, $warCode);
        } else {

            $isSuccess = $isSuccess &&
                $this->update_invoice_goods_more($igoodsentData, $skuCode, $skuCount, $spuCount, $spuCode, $igoCode, $invCode, $warCode);
        }
        if ($updateIgoodsSkuCount == 0) $isSuccess = $isSuccess && $igoodsModel->deleteByCode($igoCode, $invCode);
        $queryCountByIgoods = $igoodsModel->queryCountByInvCode($invCode);
        $queryCountByIgoodsent = $igoodsentModel->queryCountByInvCode($invCode);
        if ($queryCountByIgoods == 0 && $queryCountByIgoodsent == 0) $isSuccess = $isSuccess && $invModel->deleteByCode($invCode);

        if ($isSuccess) {
            $success = true;
            $data = array();
            $message = "更新出仓单信息成功";
        } else {
            $success = false;
            $data = array();
            $message = "更新出仓单信息失败";
        }
        return array($success, $data, $message);
    }

    //更新快进快出订单[入-1，存-1，出-1/入+1，存+1，出+1]
    public function update_virtual_goods($data)
    {
        $warCode = $data["warCode"];//仓库编号
        $ecode = $data["ecode"];
        $skuCode = $data['skuCode'];
        $skuCount = $data['skuCount'];
        $mark = "小程序单(直采)";

        $recModel = ReceiptDao::getInstance($warCode);
        $invModel = InvoiceDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $igoodsModel = IgoodsDao::getInstance($warCode);
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);

        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (empty($ecode)) return array(false, array(), "订单编号不能为空");
        if (empty($skuCode)) return array(false, array(), "sku编号不能为空");
        if (empty($skuCount)) return array(false, array(), "sku数量不能为空");

        $isSuccess = true;
        $issetInv = $invModel->queryByEcodeAndMark($ecode, $mark);
        if (empty($issetInv)) return array(false, array(), "此出仓单无相关信息");
        $invCode = $issetInv["inv_code"];
        $goodsData = $goodsModel->queryBySkuCode($skuCode);
        $goodsCode = $goodsData["goods_code"];
        if (empty($goodsCode) || $goodsCode == null) return array(false, array(), "库存货品编号为空");
        $spuCunit = $goodsData['spu_cunit'];
        $spuName = $goodsData['spu_name'];
        if (ceil(round(bcmod(($skuCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");

        $goodsSkuInit = $goodsData["sku_init"];
        $goodsInit = $goodsData["goods_init"];
        $spuCount = $goodsData["spu_count"];
        $updateGoodsSkuInit = bcsub($goodsSkuInit, $skuCount, 2);
        $updateGoodsInit = bcmul($updateGoodsSkuInit, $spuCount, 2);
        $isSuccess = $isSuccess &
            $goodsModel->updateInitByCode($goodsCode, $goodsInit, $updateGoodsInit, $updateGoodsSkuInit);
        $igoodsData = $igoodsModel->queryByInvCodeAndSkuCode($invCode, $skuCode);
        $igoCode = $igoodsData["igo_code"];
        if (empty($igoCode) || $igoCode == null) return array(false, array(), "出仓清单编号为空");
        $igsData = $igoodsentModel->queryByIgoCode($igoCode);
        $igsData = $igsData[0];//直采只有一条数据
        $igsCode = $igsData["igs_code"];
        $gsCode = $igsData["gs_code"];
        if (empty($igsCode) || $igsCode == null) return array(false, array(), "出仓批次编号为空");
        if (empty($gsCode) || $gsCode == null) return array(false, array(), "入仓批次编号为空");
        $gsData = $goodstoredModel->queryByCode($gsCode);
        $gbCode = $gsData["gb_code"];
        $queryGbData = $goodsbatchModel->queryByCode($gbCode);
        $recCode = $queryGbData['rec_code'];
        if (empty($gbCode) || $gbCode == null) return array(false, array(), "入仓清单编号为空");
        if (empty($recCode) || $recCode == null) return array(false, array(), "入仓单编号为空");
        $updateIgoodsSkuCount = bcsub($igoodsData["sku_count"], $skuCount, 2);
        if ($updateIgoodsSkuCount < 0) return array(false, array(), "快进快出货品数量小于0");
        $updateIgoodsCount = bcmul($updateIgoodsSkuCount, $spuCount, 2);
        $gsSkuInit = $gsData["sku_init"];
        $updateGsSkuInit = bcsub($gsSkuInit, $skuCount, 2);
        $updateGsInit = bcmul($updateGsSkuInit, $spuCount, 2);
        if ($updateIgoodsSkuCount == 0) {
            $isSuccess = $isSuccess &&
                $igoodsModel->deleteByCode($igoCode, $invCode);
            $isSuccess = $isSuccess &&
                $igoodsentModel->deleteByIgoCode($igoCode);
        } else {
            $isSuccess = $isSuccess &&
                $igoodsModel->updateCountAndSkuCountByCode($igoCode, $updateIgoodsCount, $updateIgoodsSkuCount);
            $updateIgsSkuCount = bcsub($igsData["sku_count"], $skuCount, 2);
            $updateIgsCount = bcmul($updateIgsSkuCount, $spuCount, 2);
            $isSuccess = $isSuccess &&
                $igoodsentModel->updateCountAndSkuCountByCode($igsCode, $updateIgsCount, $updateIgsSkuCount);
        }
        if ($updateGsSkuInit == 0) {
            $isSuccess = $isSuccess &&
                $goodstoredModel->deleteByCode($gsCode);
            $isSuccess = $isSuccess &&
                $goodsbatchModel->deleteByGbCode($gbCode);
            $queryCountByIgoods = $igoodsModel->queryCountByInvCode($invCode);
            $queryCountByIgoodsent = $igoodsentModel->queryCountByInvCode($invCode);
            if ($queryCountByIgoods == 0 && $queryCountByIgoodsent == 0) $isSuccess = $isSuccess && $invModel->deleteByCode($invCode);
            $queryCountByGb = $goodsbatchModel->queryCountByRecCode($recCode);
            if ($queryCountByGb == 0) $isSuccess = $isSuccess && $recModel->deleteByCode($recCode);
        } else {
            $isSuccess = $isSuccess &&
                $goodstoredModel->updateInitAndSkuInitByCode($gsCode, $updateGsInit, $updateGsSkuInit);
            $isSuccess = $isSuccess &&
                $goodsbatchModel->updateCountAndSkuCountByCode($gbCode, $updateGsInit, $updateGsSkuInit);
        }
        if (!$isSuccess) {
            $success = false;
            $message = "更新快进快出操作失败";
        } else {
            $success = true;
            $message = "更新快进快出操作成功";
        }

        $data = array();
        return array($success, $data, $message);

    }

    //拆分订单2=1+1
    public function seperate_invoice($data)
    {
        $warCode = $data["warCode"];//仓库编号
        $ecode = $data["ecode"];//order_code
        $skuCode = $data['skuCode'];
        $skuCount = $data['skuCount'];
        $receiver = $data['receiver'];
        $address = $data['address'];//可无
        $postal = $data['postal'];//可无
        $phone = $data['phone'];//可无
        $worCode = $data['worCode'];//用户的worcode
        $mark = $data["mark"];//小程序单(自营)，小程序单(直采)

        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (empty($ecode)) return array(false, array(), "订单编号不能为空");
        if (empty($skuCode)) return array(false, array(), "sku编号不能为空");
        if (empty($skuCount)) return array(false, array(), "sku数量不能为空");
        if (empty($receiver)) return array(false, array(), "下单人不能为空");
        if (empty($worCode)) return array(false, array(), "下单人编号不能为空");
        if (empty($mark)) return array(false, array(), "出仓单备注不能为空");

        $invModel = InvoiceDao::getInstance($warCode);
        $igoodsModel = IgoodsDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);

        $isSuccess = true;
        $goodsData = $goodsModel->queryBySkuCode($skuCode);
        $goodsCode = $goodsData['goods_code'];
        $spuCount = $goodsData['spu_count'];
        $spuCode = $goodsData['spu_code'];
        $spuCunit = $goodsData['spu_cunit'];
        $spuName = $goodsData['spu_name'];
        if (ceil(round(bcmod(($skuCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");

        //查询之前数据
        $issetInv = $invModel->queryByEcodeAndMark($ecode, $mark);
        if (empty($issetInv)) return array(false, array(), "此出仓单无相关信息");
        $invCode = $issetInv["inv_code"];
        $igoodsData = $igoodsModel->queryByInvCodeAndSkuCode($invCode, $skuCode);
        if (empty($igoodsData)) return array(true, array(), "此出仓单无相关出仓信息");
        $igoCode = $igoodsData["igo_code"];
        $igoSkuCount = $igoodsData["sku_count"];
        if (empty($goodsCode) || $goodsCode == null) return array(false, array(), "库存货品编号为空");
        if (empty($igoCode) || $igoCode == null) return array(false, array(), "出仓清单编号为空");
        $issetInvNew = $invModel->queryListByCondition(array("mark" => $ecode . "验货时退货出仓"));
        if (empty($issetInvNew)) {
            $addInvData = array(
                'receiver' => $receiver,
                'phone' => $phone,
                'address' => $address,
                'postal' => $postal,
                'type' => 6,
                'mark' => $ecode . "验货时退货出仓",
                'worcode' => $worCode,
                'status' => 5,
            );
            $invCodeNew = $invModel->insert($addInvData);
        } else {
            $invCodeNew = $issetInvNew[0]["inv_code"];
        }
        $isSuccess = $isSuccess && !empty($invCodeNew);
        if ($igoSkuCount <= $skuCount) {
            $isSuccess = $isSuccess &&
                $igoodsModel->updateInvCodeByCode($igoCode, $invCodeNew);
            $isSuccess = $isSuccess &&
                $igoodsentModel->updateInvCodeByIgoCode($igoCode, $invCodeNew);
        } else {
            $sprice = $igoodsData['spu_sprice'];
            $pprice = $igoodsData['spu_pprice'];
            $percent = $igoodsData['spu_percent'];
            $addIgoData = array(
                "count" => bcmul($spuCount, $skuCount, 2),
                "spucode" => $spuCode,
                "sprice" => $sprice,
                "pprice" => $pprice,
                "percent" => $percent,
                "goodscode" => $goodsCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
                "invcode" => $invCodeNew,
            );
            $igoCodeNew = $igoodsModel->insert($addIgoData);
            $isSuccess = $isSuccess && !empty($igoCodeNew);
            $updateIgoSkuCount = bcsub($igoSkuCount, $skuCount, 2);
            $updateIgoCount = bcmul($updateIgoSkuCount, $spuCount, 2);
            $isSuccess = $isSuccess &&
                $igoodsModel->updateCountAndSkuCountByCode($igoCode, $updateIgoCount, $updateIgoSkuCount);
            $igsData = $igoodsentModel->queryByIgoCode($igoCode);
            foreach ($igsData as $igsDatum) {
                if (empty($igsCode) || $igsCode == null) return array(false, array(), "出仓批次编号为空");
                $igsSkuCount = $igsDatum['sku_count'];
                $igsCode = $igsDatum['igs_code'];
                $bprice = $igsDatum['igs_bprice'];
                $gsCode = $igsDatum['gs_code'];
                if ($skuCount == 0) break;
                if ($igsSkuCount <= $skuCount) {
                    $isSuccess = $isSuccess &&
                        $igoodsentModel->updateIgoCodeByCode($igsCode, $igoCodeNew);
                    $isSuccess = $isSuccess &&
                        $igoodsentModel->updateInvCodeByCode($igsCode, $invCodeNew);
                    $skuCount = bcsub($skuCount, $igsSkuCount, 2);
                } else {
                    $updateIgsSkuCount = bcsub($igsSkuCount, $skuCount, 2);
                    $updateIgsCount = bcmul($updateIgsSkuCount, $spuCount, 2);
                    $isSuccess = $isSuccess &&
                        $igoodsentModel->updateCountAndSkuCountByCode($igsCode, $updateIgsCount, $updateIgsSkuCount);
                    $addIgsData = array(
                        "count" => bcmul($skuCount, $spuCount, 2),
                        "spucode" => $spuCode,
                        "bprice" => $bprice,
                        "gscode" => $gsCode,
                        "igocode" => $igoCodeNew,
                        "skucode" => $skuCode,
                        "skucount" => $skuCount,
                        "invcode" => $invCodeNew,
                    );
                    $igsCodeNew = $igoodsentModel->insert($addIgsData);
                    $isSuccess = $isSuccess && !empty($igsCodeNew);
                    $skuCount = bcsub($skuCount, $skuCount, 2);
                }
            }
        }
        if (!$isSuccess) {
            $success = false;
            $data = array();
            $msg = "拆分订单失败";
            return array($success, $data, $msg);
        } else {
            $success = true;
            $data = array();
            $msg = "拆分订单成功";
            return array($success, $data, $msg);
        }
    }

    //创建快进快出
    public function create_virtual($data)
    {
        $recType = $data["recType"];
        $invType = $data["invType"];
        $warCode = $data["warCode"];
        $worCode = $data["worCode"];
        $mark = $data["mark"];//order_code
        $recData = $data["list"];
        $phone = $data["phone"];//可无
        $address = $data["address"];//可无
        $postal = $data["postal"];//可无
        $receiver = $data['receiver'];//客户名称

        $recModel = ReceiptDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $invModel = InvoiceDao::getInstance($warCode);
        $igoodsModel = IgoodsDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $skuModel = SkuDao::getInstance($warCode);

        if (empty($recType)) return array(false, array(), "入仓单类型不能为空");
        if (empty($invType)) return array(false, array(), "出仓单类型不能为空");
        if (empty($warCode)) return array(false, array(), "仓库编号不能为空");
        if (empty($mark)) return array(false, array(), "订单编号不能为空");
        if (empty($receiver)) return array(false, array(), "下单人不能为空");
        if (empty($worCode)) return array(false, array(), "下单人编号不能为空");
        if (empty($recData)) return array(false, array(), "无货品");

        $isSuccess = true;
        $addRecData = array(
            "worcode" => $worCode,
            "mark" => $mark,
            "status" => 3,
            "type" => $recType
        );
        if (!empty($mark)) {
            $issetRec = $recModel->queryByEcode($mark);
            if (!empty($issetRec)) {
                $recCode = $issetRec['rec_code'];
            } else {
                $recCode = $recModel->insert($addRecData);
            }
        } else {
            $recCode = $recModel->insert($addRecData);
        }

        $isSuccess = $isSuccess && !empty($recCode);
        foreach ($recData as $recDatum) {
            $skuCount = $recDatum["skucount"];
            $skuCode = $recDatum["skucode"];
            $bPrice = $recDatum["bprice"];
            $sprice = $recDatum['sprice'];
            $pprice = $recDatum['pprice'];
            $percent = $recDatum['percent'];
            $skuData = $skuModel->queryByCode($skuCode);
            $spuCount = $skuData['spu_count'];
            $count = bcmul($spuCount, $skuCount, 2);
            $spuName = $skuData['spu_name'];
            if (empty($skuCode)) return array(false, array(), "sku编号不能为空");
            if (empty($skuCount)) return array(false, array(), "sku数量不能为空");
            if (empty($bPrice)) return array(false, array(), $spuName . "成本价不能为空");
            if (empty($sprice)) return array(false, array(), $spuName . "销售价不能为空");
            if (empty($pprice)) return array(false, array(), $spuName . "利润价不能为空");
            if (empty($percent)) return array(false, array(), $spuName . "利润率不能为空");
            $spuCunit = $skuData['spu_cunit'];
            if (ceil(round(bcmod(($skuCount * 100), ($spuCunit * 100)))) > 0) return array(false, array(), "货品" . $spuName . "数量格式不正确");

            $spuCode = $skuData["spu_code"];
            $supCode = !empty($recDatum["supcode"]) ? $recDatum["supcode"] : $skuData['sup_code'];
            $addGbData = array(
                "status" => 4,
                "count" => $count,
                "bprice" => $bPrice,
                "spucode" => $spuCode,
                "supcode" => $supCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
                "reccode" => $recCode,
            );
            $gbCode = $goodsbatchModel->insert($addGbData);
            $isSuccess = $isSuccess && !empty($gbCode);
            $addGsData = array(
                "init" => $count,
                "count" => $count,
                "bprice" => $bPrice,
                "gbcode" => $gbCode,
                "spucode" => $spuCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
            );
            $gsCode = $goodstoredModel->insert($addGsData);
            $isSuccess = $isSuccess && !empty($gsCode);
            $updateGsSkuCount = 0;
            $updateGsCount = 0;
            $isSuccess = $isSuccess &&
                $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updateGsCount, $updateGsSkuCount);
            $queryGoods = $goodsModel->queryBySkuCode($skuCode);
            if (!empty($queryGoods)) {
                $goodsCode = $queryGoods['goods_code'];
                $goodsInit = $queryGoods['goods_init'];
                $goodsSkuInit = $queryGoods['sku_init'];
                $updateGoodsSkuInit = bcadd($goodsSkuInit, $skuCount, 2);
                $updateGoodsInit = bcmul($updateGoodsSkuInit, $spuCount, 2);
                $isSuccess = $isSuccess &&
                    $goodsModel->updateInitByCode($goodsCode, $goodsInit, $updateGoodsInit, $updateGoodsSkuInit);
            } else {
                $addGoodsData = array(
                    "init" => $count,
                    "count" => 0,
                    "skuinit" => $skuCount,
                    "skucount" => 0,
                    "skucode" => $skuCode,
                    "spucode" => $spuCode,
                );
                $goodsCode = $goodsModel->insert($addGoodsData);
            }
            $isSuccess = $isSuccess && !empty($goodsCode);
            $issetInv = $invModel->queryByEcode($recCode);
            if (!empty($issetInv)) {
                $invCode = $issetInv[0]['inv_code'];
            } else {
                $invAddData = array(
                    "status" => 5,//出仓单状态已出仓
                    "receiver" => $receiver,//客户名称
                    "type" => $invType,//出仓单类型
                    "mark" => $mark,//出仓单备注
                    "worcode" => $worCode,//人员编号
                    "phone" => $phone,
                    "address" => $address,
                    "postal" => $postal,
                    "ecode" => $recCode,
                );//出仓单新增数据
                $invCode = $invModel->insert($invAddData);
            }
            $isSuccess = $isSuccess && !empty($invCode);
            $addIgoData = array(
                "count" => $count,
                "spucode" => $spuCode,
                "sprice" => $sprice,
                "pprice" => $pprice,
                "percent" => $percent,
                "goodscode" => $goodsCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
                "invcode" => $invCode,
            );
            $igoCode = $igoodsModel->insert($addIgoData);
            $isSuccess = $isSuccess && !empty($igoCode);
            $addIgsData = array(
                "count" => $count,
                "spucode" => $spuCode,
                "bprice" => $bPrice,
                "gscode" => $gsCode,
                "igocode" => $igoCode,
                "skucode" => $skuCode,
                "skucount" => $skuCount,
                "invcode" => $invCode,
            );
            $igsCode = $igoodsentModel->insert($addIgsData);
            $isSuccess = $isSuccess && !empty($igsCode);
        }
        if ($isSuccess) {
            return array(true, array(), "快进快出操作成功");
        } else {
            return array(false, array(), "快进快出操作失败");
        }
    }

    //更新出库单少收
    private function update_invoice_goods_less($igoodsentData, $skuCount, $spuCount, $warCode)
    {
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);
        $isSuccess = true;
        foreach ($igoodsentData as $igoodsentDatum) {
            $gsCode = $igoodsentDatum["gs_code"];
            $igsCode = $igoodsentDatum["igs_code"];
            $igsSkuCount = $igoodsentDatum["sku_count"];
            $gsData = $goodstoredModel->queryByCode($gsCode);
            $gsSkuCount = $gsData["sku_count"];
            $gbCode = $gsData['gb_code'];
            $gbData = $goodsbatchModel->queryByCode($gbCode);
            $gbStatus = $gbData['gb_status'];
            $sentSkuCount = 0;
            if ($skuCount == 0) break;
            if ($skuCount <= $igsSkuCount) {
                $updateIgsSkuCount = bcsub($igsSkuCount, $skuCount, 2);
                $updateIgsCount = bcmul($spuCount, $updateIgsSkuCount, 2);
                if ($updateIgsSkuCount == 0) {
                    $isSuccess = $isSuccess && $igoodsentModel->deleteByCode($igsCode);
                } else {
                    $isSuccess = $isSuccess && $igoodsentModel->updateCountAndSkuCountByCode($igsCode, $updateIgsCount, $updateIgsSkuCount);
                }
                $updateGsSkuCount = bcadd($gsSkuCount, $skuCount, 2);
                $updateGsCount = bcmul($updateGsSkuCount, $spuCount, 2);
                $isSuccess = $isSuccess && $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updateGsCount, $updateGsSkuCount);
                $sentSkuCount = $skuCount;
                if ($updateGsSkuCount == 0 && $gbStatus != 3) {
                    $isSuccess = $isSuccess && $goodsbatchModel->updateStatusByCode($gbCode, 3);
                }
            } else {
                $isSuccess = $isSuccess && $igoodsentModel->deleteByCode($igsCode);
                $updateGsSkuCount = bcadd($gsSkuCount, $igsSkuCount, 2);
                $updateGsCount = bcmul($updateGsSkuCount, $spuCount, 2);
                $isSuccess = $isSuccess && $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updateGsCount, $updateGsSkuCount);
                $sentSkuCount = $igsSkuCount;
                if ($updateGsSkuCount == 0 && $gbStatus != 3) {
                    $isSuccess = $isSuccess && $goodsbatchModel->updateStatusByCode($gbCode, 3);
                }
            }
            $skuCount = bcsub($skuCount, $sentSkuCount, 2);
        }
        return $isSuccess;
    }

    //更新出库单多收
    private function update_invoice_goods_more($igoodsentData, $skuCode, $skuCount, $spuCount, $spuCode, $igoCode, $invCode, $warCode)
    {
        $goodstoredModel = GoodstoredDao::getInstance($warCode);
        $igoodsentModel = IgoodsentDao::getInstance($warCode);
        $goodsbatchModel = GoodsbatchDao::getInstance($warCode);
        $isSuccess = true;
        //$skuCount为负数
        foreach ($igoodsentData as $igoodsentDatum) {
            if ($skuCount == 0) break;
            $gsCode = $igoodsentDatum["gs_code"];
            $igsCode = $igoodsentDatum["igs_code"];
            $igsSkuCount = $igoodsentDatum["sku_count"];
            $gsData = $goodstoredModel->queryByCode($gsCode);
            $gsSkuCount = $gsData["sku_count"];
            $gbCode = $gsData['gb_code'];
            $gbData = $goodsbatchModel->queryByCode($gbCode);
            $gbStatus = $gbData['gb_status'];
            if (bcsub(0, $skuCount, 2) <= $gsSkuCount) {
                $updateIgsSkuCount = bcsub($igsSkuCount, $skuCount, 2);
                $updateIgsCount = bcmul($spuCount, $updateIgsSkuCount, 2);
                $isSuccess = $isSuccess && $igoodsentModel->updateCountAndSkuCountByCode($igsCode, $updateIgsCount, $updateIgsSkuCount);
                $updateGsSkuCount = bcadd($gsSkuCount, $skuCount, 2);
                $updateGsCount = bcmul($updateGsSkuCount, $spuCount, 2);
                $isSuccess = $isSuccess &&
                    $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updateGsCount, $updateGsSkuCount);
                if ($updateGsSkuCount == 0 && $gbStatus != 4) {
                    $isSuccess = $isSuccess && $goodsbatchModel->updateStatusByCode($gbCode, 4);
                }
                $skuCount = 0;
            } else {
                $gsDataCount = $goodstoredModel->queryCountBySkuCode($skuCode);
                $gsDataList = $goodstoredModel->queryListBySkuCode($skuCode, 0, $gsDataCount);
                foreach ($gsDataList as $gsData) {
                    $gsCode = $gsData["gs_code"];
                    $gsCount = $gsData['gs_count'];
                    $gsSkuCount = $gsData['sku_count'];
                    $bPrice = $gsData['gb_bprice'];
                    if ($gsCount == 0 || $gsSkuCount == 0 || $gsCount < 0 || $gsSkuCount < 0) continue;
                    if ($skuCount == 0) break;
                    $igsSkuCount = 0;
                    if (bcsub(0, $skuCount, 2) <= $gsSkuCount) {
                        $updatedGsSkuCount = bcadd($gsSkuCount, $skuCount, 2);
                        $updatedGsCount = bcmul($spuCount, $updatedGsSkuCount, 2);
                        $igsSkuCount = bcsub(0, $skuCount, 2);
                    } else {
                        $updatedGsSkuCount = 0;
                        $updatedGsCount = bcmul($spuCount, $updatedGsSkuCount, 2);
                        $igsSkuCount = $gsSkuCount;
                    }
                    $isSuccess = $isSuccess &&
                        $goodstoredModel->updateCountAndSkuCountByCode($gsCode, $updatedGsCount, $updatedGsSkuCount);
                    if ($updatedGsCount == 0 && $gbStatus != 4) {
                        $isSuccess = $isSuccess && $goodsbatchModel->updateStatusByCode($gbCode, 4);
                    }

                    $addIgsData = array(
                        "count" => bcmul($igsSkuCount, $spuCount, 2),
                        "bprice" => $bPrice,
                        "spucode" => $spuCode,
                        "gscode" => $gsCode,
                        "igocode" => $igoCode,
                        "skucode" => $skuCode,
                        "skucount" => $igsSkuCount,
                        "invcode" => $invCode,
                    );
                    $igsCode = $igoodsentModel->insert($addIgsData);
                    $isSuccess = $isSuccess && !empty($igsCode);
                    $skuCount = bcadd($skuCount, $igsSkuCount, 2);
                }
            }
        }
        return $isSuccess;
    }
}