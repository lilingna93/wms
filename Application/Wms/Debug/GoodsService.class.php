<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/7/16
 * Time: 23:58
 */

namespace Wms\Debug;


class GoodsService {
    /**
     * @return array
     * 库存管理
     */
    public function goods_search() {
        $data = array();
        $tCode=$_POST['data']['tCode'];
        $cgCode=$_POST['data']['cgCode'];
        $spName=$_POST['data']['spName'];

        if (!empty($tCode)) {
            $clause['type']=$tCode;
        }
        if (!empty($cgCode)) {
            $clause['subtype']=$tCode;
        }
        if (!empty($spName)) {
            $clause['%name%']=$tCode;
        }
        $data["pageCurrent"] = "1";
        $data["pageSize"] = "3";
        $data["totalCount"] = "3";
        $data["list"][] = array(
            "spCode" => "SP30606170641628",
            "spName" => "绿宝非转调和",
            "spNorm" => "桶",
            "spCount" => "1000",
            "spUnit" => "桶",
        );
        $data["list"][] = array(
            "spCode" => "SP30606170641629",
            "spName" => "绿宝非转调和",
            "spNorm" => "桶",
            "spCount" => "1000",
            "spUnit" => "桶",
        );
        $data["list"][] = array(
            "spCode" => "SP30606170641630",
            "spName" => "绿宝非转调和",
            "spNorm" => "桶",
            "spCount" => "1000",
            "spUnit" => "桶",
        );
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array
     *  库存管理-批次详情
     */
    public function goods_stored() {
        $data = array();
        if (empty($_POST['data']['spCode'])) {
            $success = false;
            $message = "spu编号不能为空";
            return array($success, $data, $message);
        } //return "spu编号不能为空";
        $data["pageCurrent"] = "1";
        $data["pageSize"] = "3";
        $data["totalCount"] = "3";
        $data["spCode"] = "SP30606170641628";
        $data["spName"] = "绿宝非转调和";
        $data["spNorm"] = "桶";
        $data["spCount"] = "1000";
        $data["spUnit"] = "桶";
        $data["list"][] = array(
            "gsCode" => "GS30606170641628",
            "recCode" => "REC30606170641628",
            "recCtime" => "2018-07-01 00:00:00",
            "gsNum" => "100",
            "sprice" => "100",
            "gsInit" => "100",
            "gsCount" => "100",
            "posCode" => "POS30606170641628"
        );
        $data["list"][] = array(
            "gsCode" => "GS30606170641629",
            "recCode" => "REC30606170641629",
            "recCtime" => "2018-07-01 00:00:00",
            "gsNum" => "100",
            "sprice" => "100",
            "gsInit" => "100",
            "gsCount" => "100",
            "posCode" => "POS30606170641629"
        );
        $data["list"][] = array(
            "gsCode" => "GS30606170641630",
            "recCode" => "REC30606170641630",
            "recCtime" => "2018-07-01 00:00:00",
            "gsNum" => "100",
            "sprice" => "100",
            "gsInit" => "100",
            "gsCount" => "100",
            "posCode" => "POS30606170641630"
        );
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }
}