<?php
/**
 * Created by PhpStorm.
 * User=> lingn
 * Date=> 2018/7/16
 * Time=> 15=>17
 */

namespace Wms\Debug;


class ReceiptService {
    public function __construct() {
    }

    /**
     * @return array|string
     * 创建入仓单／获取sku
     */
    public function receipt_get_sku() {
        $data = array();
        if (empty($_POST['data']['sku'])) {
            $success = false;
            $message = "sku为空";
            return array($success, $data, $message);
//            return "sku为空";
        } else {
            $data['list'][] = array(
                "skName" => "绿宝非转调和(5升)",
                "skCode" => "SK30606170641628",
                "skNorm" => "4桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641628",
                "spCount" => "4",
                "spUnit" => "桶",
            );
            $data['list'][] = array(
                "skName" => "绿宝非转大豆（10升）",
                "skCode" => "SK30606170641579",
                "skNorm" => "2桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641579",
                "spCount" => "2",
                "spUnit" => "桶",
            );
            $data['list'][] = array(
                "skName" => "谷盟(5升)",
                "skCode" => "SK30606170641671",
                "skNorm" => "4桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641671",
                "spCount" => "4",
                "spUnit" => "桶",
            );
            $success = true;
            $message = '';
            return array($success, $data, $message);
        }
    }

    /**
     * @return array|string
     * 创建入仓单/创建入仓预报单
     */
    public function receipt_create() {
//        if (empty($_POST['data']['isFast']));
//        if (empty($_POST['data']['eCode']));
        //所有数据均在循环里面
        $list = $_POST['data']['list'];
        $data = array();
        foreach ($list as $k => $v) {
            if (empty($v['skCode'])) {
                $success = false;
                $message = "sku编号不能为空";
                return array($success, $data, $message);
            } //return "sku编号不能为空";
            if (empty($v['skCount'])) {
                $success = false;
                $message = "sku数量不能为空";
                return array($success, $data, $message);
            } //return "sku数量不能为空";
            if (empty($v['spCode'])) {
                $success = false;
                $message = "出仓单spu编号不能为空";
                return array($success, $data, $message);
            } //return "出仓单spu编号不能为空";
            if (empty($v['spCount'])) {
                $success = false;
                $message = "出仓单货品spu总数量不能为空";
                return array($success, $data, $message);
            } //return "出仓单货品spu总数量不能为空";
        }

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array
     * 入仓单管理/入仓单管理列表
     */
    public function receipt_search() {
        $data = array();
        $data['list'][] = array(
            "recCode" => "RE30606170641966",
            "recCtime" => "2018-07-25 13:51:18",
            "recUcode" => "WO30606170641966",
            "recUname" => "123",
            "recMark" => "111",
            "recStatus" => "1",
            "recStatMsg" => "已创建",
        );
        $data['list'][] = array("recCode" => "RE30606170641969",
            "recCtime" => "2018-07-25 13:51:18",
            "recUcode" => "WO30606170641966",
            "recUname" => "123",
            "recMark" => "111",
            "recStatus" => "2",
            "recStatMsg" => "已验货",
        );
        $data['list'][] = array("recCode" => "RE30606170641968",
            "recCtime" => "2018-07-25 13:51:18",
            "recUcode" => "WO30606170641966",
            "recUname" => "123",
            "recMark" => "111",
            "recStatus" => "3",
            "recStatMsg" => "已完成",
        );
        $data['list'][] = array("recCode" => "RE30606170641969",
            "recCtime" => "2018-07-25 13:51:18",
            "recUcode" => "WO30606170641966",
            "recUname" => "123",
            "recMark" => "111",
            "recStatus" => "4",
            "recStatMsg" => "已取消",
        );
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 入仓单管理/入仓单管理之修改(1)入仓单详情
     */
    public function receipt_detail() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
//            return "入仓单编号不能为空";
        } else {
            $data['list'][] = array(
                "gbCode" => "GB30606170641628",
                "skName" => "绿宝非转调和(5升)",
                "skCode" => "SK30606170641628",
                "skNorm" => "4桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "spBprice" => "0.00",
                "spCode" => "SP30606170641628",
                "spCount" => "16",
                "spUnit" => "桶",
                "posCode" => "PO30606170641628",);
            $data['list'][] = array("gbCode" => "GB30606170641579",
                "skName" => "绿宝非转大豆（10升）",
                "skCode" => "SK30606170641579",
                "skNorm" => "2桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "spBprice" => "4.00",
                "spCode" => "SP30606170641579",
                "spCount" => "8",
                "spUnit" => "桶",
                "posCode" => "PO30606170641579",);
            $data['list'][] = array("gbCode" => "GB30606170641671",
                "skName" => "谷盟(5升)",
                "skCode" => "SK30606170641671",
                "skNorm" => "4桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "skpprice" => "4.00",
                "spCode" => "SP30606170641671",
                "spCount" => "16",
                "spUnit" => "桶",
                "posCode"
                => "PO30606170641671",);
            $success = true;
            $message = '';
            return array($success, $data, $message);
        }
    }


    /**
     * @return array|string
     * 入仓单管理/入仓单管理之修改(2)修改入仓单数量
     */
    public function receipt_goods_count_update() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        if (empty($_POST['data']['gbCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单货品编号不能为空";
        if (empty($_POST['data']['skCount'])) {
            $success = false;
            $message = '入仓单货品sku数量不能为空';
            return array($success, $data, $message);
        } //return "入仓单货品sku数量不能为空";
        if (empty($_POST['data']['spBprice'])) {
            $success = false;
            $message = '入仓单货品sku成本不能为空';
            return array($success, $data, $message);
        } //return "入仓单货品sku成本不能为空";
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 入仓单管理之修改（3）增加入仓单货品
     */
    public function receipt_goods_create() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        $list = $_POST['data']['list'];
        foreach ($list as $k => $v) {
            if (empty($v['skCode'])) {
                $success = false;
                $message = 'sku编号不能为空';
                return array($success, $data, $message);
            } //return "sku编号不能为空";
            if (empty($v['skCount'])) {
                $success = false;
                $message = 'sku数量不能为空';
                return array($success, $data, $message);
            } //return "sku数量不能为空";
            if (empty($v['spCode'])) {
                $success = false;
                $message = 'spu编号不能为空';
                return array($success, $data, $message);
            } //return "spu编号不能为空";
            if (empty($v['spBprice'])) {
                $success = false;
                $message = '入仓单货品spu价格不能为空';
                return array($success, $data, $message);
            } //return "入仓单货品spu价格不能为空";
            if (empty($v['count'])) {
                $success = false;
                $message = 'spu总数量不能为空';
                return array($success, $data, $message);
            } //return "spu总数量不能为空";
        }
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }


    /**
     * @return array|string
     * 入仓单管理之修改（4）删除入仓单货品
     */
    public function receipt_goods_delete() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        if (empty($_POST['data']['gbCode'])) {
            $success = false;
            $message = '入仓单货品编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }


    /**
     * @return array|string
     * 入仓单管理/入仓单管理之查看轨迹
     */
    public function receipt_trace_search() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        else {
            $data['list'][] = array(
                "stime" => "2018-07-01 00:00:00",
                "code" => "RE30606170641628",
                "mark" => "创建入仓"
            );
            $data['list'][] = array(
                "stime" => "2018-07-01 06:00:00",
                "code" => "RE30606170641628",
                "mark" => "上架并完成入仓单"
            );
            $success = true;
            $message = '';
            return array($success, $data, $message);
        }

    }

    /**
     * @return array|string
     * 入仓单管理/入仓单管理之删除
     */
    public function receipt_delete() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        else {
            $success = true;
            $message = '';
            return array($success, $data, $message);
        }
    }

    /**
     * @return array|string
     * 完成入仓单
     */
    public function receipt_finish() {
        $data = array();
        if (empty($_POST['data']['recCode'])) {
            $success = false;
            $message = '入仓单编号不能为空';
            return array($success, $data, $message);
        } //return "入仓单编号不能为空";
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }
}