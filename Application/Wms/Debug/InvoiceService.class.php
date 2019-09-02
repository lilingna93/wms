<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/7/16
 * Time: 23:10
 */

namespace Wms\Debug;


class InvoiceService {
    /**
     * @return array|string
     * 创建入仓单／获取sku
     */
    public function invoice_get_sku() {
        $data = array();
        if (empty($_POST['data']['sku'])) {
            $success = false;
            $message = "sku为空";
            return array($success, $data, $message);
//            return "sku为空";
        } else {
            $data['list'][] = array(
                "skName" => "绿宝非转调和",
                "skCode" => "SK30606170641628",
                "skNorm" => "4桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641628",
                "spCount" => "4",
                "spUnit" => "桶",
                "goods" => 100
            );
            $data['list'][] = array(
                "skName" => "绿宝非转大豆",
                "skCode" => "SK30606170641579",
                "skNorm" => "2桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641579",
                "spCount" => "2",
                "spUnit" => "桶",
                "goods" => 100
            );
            $data['list'][] = array(
                "skName" => "谷盟",
                "skCode" => "SK30606170641671",
                "skNorm" => "4桶/箱",
                "skUnit" => "箱",
                "spCode" => "SP30606170641671",
                "spCount" => "4",
                "spUnit" => "桶",
                "goods" => 100
            );
            $success = true;
            $message = '';
            return array($success, $data, $message);
        }
    }

    /**
     * @return array|string
     * 创建出仓单
     */
    public function invoice_create() {
        $data = array();
        if (empty($_POST['data']['receiver'])) {
            $message = "客户名称不能为空";
            venus_throw_exception(1, $message);
        } //return "客户名称不能为空";
        if (empty($_POST['data']['phone'])) {
            $message = "客户手机号不能为空";
            venus_throw_exception(1, $message);
        } //return "客户手机号不能为空";
        if (empty($_POST['data']['address'])) {
            $message = "客户地址不能为空";
            venus_throw_exception(1, $message);
        } //return "客户地址不能为空";
        if (empty($_POST['data']['postal'])) {
            $message = "客户邮编不能为空";
            venus_throw_exception(1, $message);
        }
        $list = $_POST['data']['list'];
        foreach ($list as $v) {
            if (empty($v['skCode'])) {
                $message = "sku编号不能为空";
                venus_throw_exception(1, $message);
            } //return "sku编号不能为空";
            if (empty($v['skCount'])) {
                $message = "sku数量不能为空";
                venus_throw_exception(1, $message);
            } //return "sku数量不能为空";
            if (empty($v['spCode'])) {
                $message = "出仓单spu编号不能为空";
                venus_throw_exception(1, $message);
            } //return "出仓单spu编号不能为空";
            if (empty($v['count'])) {
                $message = "出仓单货品spu总数量不能为空";
                venus_throw_exception(1, $message);
            } //return "出仓单货品spu总数量不能为空";
        }
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array
     * 出仓单管理
     */
    public function invoice_search() {
        $data = array();
        $data['pageCurrent'] = "1";
        $data['pageSize'] = "4";
        $data['totalCount'] = "4";
        $data['list'][] = array(
            "invCode" => "INV30606170641628",
            "invCtime" => "2018-07-01 00:00:00",
            "invUcode" => "U30606170641630",
            "invUname" => "肯定就是撒",
            "invMark" => "111",
            "invType" => "api",
            "invStatus" => "1",
            "invStatMsg" => "已创建",);
        $data['list'][] = array(
            "invCode" => "INV30606170641629",
            "invCtime" => "2018-07-01 00:00:00",
            "invUcode" => "U30606170641630",
            "invUname" => "肯定就是撒",
            "invMark" => "111",
            "invType" => "api",
            "invStatus" => "2",
            "invStatMsg" => "已预报",
        );
        $data['list'][] = array(
            "invCode" => "INV30606170641630",
            "invCtime" => "2018-07-01 00:00:00",
            "invUcode" => "U30606170641630",
            "invUname" => "肯定就是撒",
            "invMark" => "111",
            "invType" => "导入",
            "invStatus" => "3",
            "invStatMsg" => "已验货",
        );
        $data['list'][] = array(
            "invCode" => "INV30606170641631",
            "invCtime" => "2018-07-01 00:00:00",
            "invUcode" => "U30606170641630",
            "invUname" => "肯定就是撒",
            "invMark" => "111",
            "invType" => "导入",
            "invStatus" => "4",
            "invStatMsg" => "已完成",
        );

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理之修改（1）出仓单详情
     */
    public function invoice_detail() {
//        $_POST['data']['invCode'] = "111";
        $data = array();
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
            //return "请选择入仓单";
        } else {
            $data['list'][] = array(
                "igoCode" => "IGO30606170641628",
                "skName" => "绿宝非转调和",
                "skCode" => "S30606170641628",
                "skNorm" => "4桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "spCode" => "SP30606170641628",
                "count" => "16",
                "spUnit" => "桶",
            );
            $data['list'][] = array(
                "igoCode" => "IGO30606170641629",
                "skName" => "绿宝非转大豆",
                "skCode" => "S30606170641629",
                "skNorm" => "2桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "spCode" => "SP30606170641629",
                "count" => "8",
                "spUnit" => "桶",
            );
            $data['list'][] = array(
                "igoCode" => "IGO30606170641671",
                "skName" => "谷盟",
                "skCode" => "S30606170641671",
                "skNorm" => "4桶/箱",
                "skCount" => "4",
                "skUnit" => "箱",
                "spCode" => "SP30606170641671",
                "count" => "16",
                "spUnit" => "桶",
            );
            $success = true;
            $message = '';

        }
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理之修改（2）修改出仓单货品数量
     */
    public function invoice_goods_count_update() {
        $data = array();
        $list = $_POST['data']['list'];
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        if (empty($_POST['data']['invCode'])) venus_throw_exception(1, "出仓单编号不能为空");
        if (empty($_POST['data']['igoCode'])) venus_throw_exception(1, "出仓单货品编号不能为空");
        if (empty($_POST['data']['skCode'])) venus_throw_exception(1, "出仓单sku编号不能为空");
        if (empty($_POST['data']['skCount'])) venus_throw_exception(1, "出仓单sku数量不能为空");
        if (empty($_POST['data']['spCode'])) venus_throw_exception(1, "出仓单spu编号不能为空");
        if (empty($_POST['data']['count'])) venus_throw_exception(1, "出仓单货品spu总数量不能为空");


        $data = '';
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理之修改（3）增加出仓单货品
     */
    public function invoice_goods_create() {
        $list = $_POST['data']['list'];
        $data = array();
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        foreach ($list as $v) {
            if (empty($v['skCode'])) {
                $message = "出仓单sku编号不能为空";
                venus_throw_exception(1, $message);
            } //return "出仓单sku编号不能为空";
            if (empty($v['skCount'])) {
                $message = "出仓单sku数量不能为空";
                venus_throw_exception(1, $message);
            }// return "出仓单货品sku数量不能为空";
            if (empty($v['spCode'])) {
                $message = "出仓单spu编号不能为空";
                venus_throw_exception(1, $message);
            } //return "出仓单spu编号不能为空";
            if (empty($v['count'])) {
                $message = "出仓单货品spu总数量不能为空";
                venus_throw_exception(1, $message);
            } //return "出仓单货品spu总数量不能为空";
        }

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理之修改（4）删除出仓单货品
     */
    public function invoice_goods_delete() {
        $data = array();
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        if (empty($_POST['data']['igoCode'])) {
            $message = '出仓单货品编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单货品编号不能为空";
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理/出仓单管理之查看轨迹﻿﻿﻿
     */
    public function invoice_trace_search() {
        $data = array();
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        $data['list'][] = array(
            "stime" => "2018-07-01 00:00:00",
            "code" => "INV30606170641628",
            "mark" => "出仓"
        );
        $data['list'][] = array(
            "stime" => "2018-07-01 06:00:00",
            "code" => "GD30606170641628",
            "mark" => "拣货捡单"
        );
        $data['list'][] = array(
            "stime" => "2018-07-01 08:00:00",
            "code" => "GD30606170641629",
            "mark" => "验货出仓"
        );
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @return array|string
     * 出仓单管理之删除
     */
    public function invoice_delete() {
        $data = array();
        if (empty($_POST['data']['invCode'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }


    /**
     * @return array|string
     * 采购出仓单导入
     */
    public function invoice_import() {
        $data = array();
        if (empty($_FILES['file'])) {
            $success = false;
            $message = '请上传文件';
        } //return "请上传文件";
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }


    /**
     * @return array
     * 出仓单管理之确认预报
     */
    public function invoice_confirm() {
        $list = $_POST['data']['list'];
        if (empty($_POST['data']['list'])) {
            $message = '出仓单编号不能为空';
            venus_throw_exception(1, $message);
        } //return "出仓单编号不能为空";
        foreach ($list as $v) {
            if (empty($v)) {
                $message = '出仓单编号不能为空';
                venus_throw_exception(1, $message);
            } //return "出仓单编号不能为空";
        }
        $data = '';
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }
}