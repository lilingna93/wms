<?php
/**
 * Created by PhpStorm.
 * User: lilingna
 * Date: 2018/11/6
 * Time: 15:20
 * app仓内流程服务
 */

namespace Wms\Service;

use Common\Service\PassportService;
use Wms\Dao\GoodsbatchDao;
use Wms\Dao\GoodsDao;
use Wms\Dao\GoodstoredDao;
use Wms\Dao\IgoodsDao;
use Wms\Dao\IgoodsentDao;
use Wms\Dao\InvoiceDao;
use Wms\Dao\ReceiptDao;
use Wms\Dao\SubgoodsbatchDao;
use Wms\Dao\TaskDao;
use Wms\Dao\WarehouseDao;
use Wms\Dao\WorkerDao;

class BeeService
{
    static private $TASK_STATUS_CREATE = "1";//工单创建状态/未领取
    static private $TASK_STATUS_UNDERWAY = "2";//underway工单进行中状态/领取后处理中
    static private $TASK_STATUS_FINISH = "3";//工单完成状态
    static private $TASK_STATUS_CANCEL = "4";//工单取消状态

    static private $TASK_TYPE_RECEIPT = "1";//工单类型:入仓业务-入仓
    static private $TASK_TYPE_INSPECTION = "2";//工单类型:入仓业务-验货
    static private $TASK_TYPE_PUTAWAY = "3";//工单类型:入仓业务-上架
    static private $TASK_TYPE_UPTPOS = "4";//工单类型:仓内业务-补货移区
    static private $TASK_TYPE_INVPICKORDER = "5";//工单类型:出仓业务-拣货捡单
    static private $TASK_TYPE_INVINSPECTION = "6";//工单类型:出仓业务-验货出仓
    static private $TASK_TYPE_INVUNUAUAL = "7";//工单类型:出仓业务-异常

    static private $RECEIPT_STATUS_CREATE = "1";//入仓单创建状态
    static private $RECEIPT_STATUS_INSPECTION = "2";//inspection入仓单验货状态
    static private $RECEIPT_STATUS_FINISH = "3";//入仓单完成状态
    static private $RECEIPT_STATUS_CANCEL = "4";//入仓单取消状态

    static private $INVOICE_STATUS_FORECAST = "1";//出仓单已预报状态
    static private $INVOICE_STATUS_CREATE = "2";//出仓单已创建状态
    static private $INVOICE_STATUS_PICK = "3";//inspection出仓单已拣货状态
    static private $INVOICE_STATUS_INSPECTION = "4";//inspection出仓单已验货状态
    static private $INVOICE_STATUS_FINISH = "5";//inspection出仓单已出仓状态
    static private $INVOICE_STATUS_RECEIPT = "6";//出仓单已收货状态
    static private $INVOICE_STATUS_CANCEL = "7";//出仓单已取消状态

    static private $GOODSBATCH_STATUS_CREATE = "1";//货品批次创建状态
    static private $GOODSBATCH_STATUS_INSPECTION = "2";//货品批次验货状态
    static private $GOODSBATCH_STATUS_PUTAWAY = "3";//Putaway货品批次上架状态
    static private $GOODSBATCH_STATUS_FINISH = "4";//货品批次使用完状态

    static private $SUBGOODSBATCH_STATUS_INSPECTION = "1";//Putaway货品批次未上架状态
    static private $SUBGOODSBATCH_STATUS_FINISH = "2";//货品批次已上架状态

    static private $IGOODSENT_STATUS_CREATE = "1";//出仓批次已创建状态
    static private $IGOODSENT_STATUS_PICK = "2";//出仓批次已拣货状态
    static private $IGOODSENT_STATUS_INSPECTION = "3";//inspection出仓批次已验货状态
    static private $IGOODSENT_STATUS_FINISH = "4";//inspection出仓批次已出仓状态
    static private $IGOODSENT_STATUS_CANCEL = "5";//出仓单已取消状态

    public $warCode;
    public $worCode;

    public function __construct()
    {
//        $workerData = PassportService::getInstance()->loginUser();
//        if (empty($workerData)) {
//            venus_throw_exception(110);
//        }
//
//        $this->warCode = $workerData["war_code"];
//        $this->worcode = $workerData["wor_code"];
    }


    /**
     *
     * 获取系统信息
     * @return array
     */
    public function system_information(/*$param*/)
    {
        //$param = (!isset($param)) ? $_POST : $param;
        $success = true;
        $data = array(
            "app_version" => C("app_version"),
            "update_config" => C("update_config"),
        );
        $message = "";
        return array($success, $data, $message);
    }

    /**
     *
     * 产生入仓批次码
     * @param $param
     * @return array
     */
    public function batchcode_generate($param)
    {

        $param = (!isset($param)) ? $_POST["data"] : $param["data"];
        $param = json_decode($param, 1);
        $count = intval($param["count"]);//产生数量

        $list = array();
        while ($count-- > 0) {
            $list[] = venus_unique_code("S");
        }
        $success = true;
        $data["list"] = $list;
        $message = json_encode($_POST);
        return array($success, $data, $message);
    }


    public function truckcode_generate($param)
    {

        $param = (!isset($param)) ? $_POST["data"] : $param["data"];
        $param = json_decode($param, 1);
        $count = intval($param["count"]);//产生数量

        $list = array();
        while ($count-- > 0) {
            $list[] = venus_unique_code("B");
        }
        $success = true;
        $data["list"] = $list;
        $message = json_encode($_POST);
        return array($success, $data, $message);
    }

    public function stockcode_generate($param)
    {

        $param = (!isset($param)) ? $_POST["data"] : $param["data"];
        $param = json_decode($param, 1);
        $count = intval($param["count"]);//产生数量

        $list = array();
        while ($count-- > 0) {
            $list[] = venus_unique_code("P");
        }
        $success = true;
        $data["list"] = $list;
        $message = json_encode($_POST);
        return array($success, $data, $message);
    }


    /**************************************************************************************************/

    /**
     * @param $param
     * @return array
     * 入仓单出仓单实际货品清单
     */
    public function task_goods_detail($param)
    {
        $param = (!isset($param)) ? $_POST["data"] : $param["data"];
        $param = json_decode($param, 1);
        $warCode = "WA000001";
        $code = $param['code'];
        $data = array();
        if (empty($code)) {
            $success = false;
            $message = '请输入查询单号';
            return array($success, $data, $message);
        } else {
            if (substr($code, 0, 2) == "RE") {
                $recModel = ReceiptDao::getInstance($warCode);
                $gbModel = GoodsbatchDao::getInstance($warCode);
                $recInfo = $recModel->queryByCode($code);
                if (!in_array($recInfo['rec_status'], array(self::$RECEIPT_STATUS_CREATE, self::$RECEIPT_STATUS_CANCEL))) {
                    $warInfoRec = WorkerDao::getInstance($recInfo['war_code'])->queryByCode($recInfo['wor_code']);
                    $data['info'] = array(
                        "code" => $recInfo['rec_code'],
                        "ctime" => $recInfo['rec_ctime'],
                        "warName" => $warInfoRec['war_name'],
                        "worName" => $warInfoRec['wor_rname'],
                    );
                    $gbData = $gbModel->queryListByRecCode($code);
                    foreach ($gbData as $gbDatum) {
                        $skuList = array(
                            "name" => $gbDatum["spu_name"],
                            "brand" => $gbDatum["spu_brand"],
                            "skuCount" => floatval($gbDatum['sku_count']),
                            "skuProCount" => floatval($gbDatum['promote_skucount']),
                            "skuNorm" => $gbDatum["sku_norm"],
                            "from" => $gbDatum["spu_from"],
                            "unit" => $gbDatum["sku_unit"],
                            "cunit" => $gbDatum["spu_cunit"],
                            "skuCode" => $gbDatum["sku_code"],
                        );
                        $data['list'][] = $skuList;
                    }
                }
            } else {
                $invModel = InvoiceDao::getInstance($warCode);
                $igoModel = IgoodsDao::getInstance($warCode);
                $invInfo = $invModel->queryByCode($code);
//                if (!in_array($invInfo['inv_status'], array(self::$INVOICE_STATUS_CREATE, self::$INVOICE_STATUS_PICK, self::$INVOICE_STATUS_CANCEL, self::$INVOICE_STATUS_FORECAST))) {
                $data['info'] = array(
                    "code" => $invInfo['inv_code'],
                    "ctime" => $invInfo['inv_ctime'],
                    "warName" => $invInfo['war_name'],
                    "worName" => $invInfo['wor_rname'],
                );
                $igoData = $igoModel->queryListByInvCode($code);
                foreach ($igoData as $igoDatum) {
                    $skuList = array(
                        "name" => $igoDatum["spu_name"],
                        "brand" => $igoDatum["spu_brand"],
                        "skuCount" => floatval($igoDatum['sku_count']),
                        "skuNorm" => $igoDatum["sku_norm"],
                        "from" => $igoDatum["spu_from"],
                        "unit" => $igoDatum["sku_unit"],
                        "cunit" => $igoDatum["spu_cunit"],
                        "skuCode" => $igoDatum["sku_code"],
                    );
                    $data['list'][] = $skuList;
                }
//                }
            }
        }
        if (!empty($data['list'])) {
            $success = true;
            $message = '';
            return array($success, $data, $message);
        } else {
            $success = false;
            $message = '货品信息为空';
            return array($success, $data, $message);
        }
    }

    /**
     * @param $param
     * @return array
     * 搜索task列表之前操作
     */
    public function task_before_search($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $code = $param['code'];
        $substrCode = substr($code, 0, 1);
        //1未验货2已验货3未上架4已上架5未拣货6已拣货7补货未拣货8补货已拣货(补货未上架)9补货已上架

        $warCode = "WA000001";
        $taskModel = TaskDao::getInstance($warCode);
        if ($substrCode == "R" || $substrCode == "I" || $substrCode == "S") {
            $taskData = $taskModel->queryByExtra($code);
            if ($taskData) {
                $tCode = $taskData["task_code"];
                $type = $taskData["task_type"];
                $status = $taskData["task_status"];
            } else {
                $success = false;
                $message = '无相关信息';
                return array($success, array(), $message);
            }

        } else {
            if ($substrCode == "P") {
                $taskModel = TaskDao::getInstance($warCode);
                $taskData = $taskModel->queryByExtra($code);
                if (!empty($taskData)) {
                    $tCode = $taskData["task_code"];
                    $type = $taskData["task_type"];
                    $status = $taskData["task_status"];
                } else {
                    $issetPosCode = $this->task_detail(array("code" => $code, "status" => 7));
                    if (!empty($issetPosCode[1]['list'])) {
                        $tCode = "";
                        $create = true;
                        $type = self::$TASK_TYPE_UPTPOS;
                        $status = "";
                    } else {
                        $success = false;
                        $message = '此货架暂无货品';
                        return array($success, array(), $message);
                    }

                }
            } else {
                $success = false;
                $message = '此编号不符合格式';
                return array($success, array(), $message);
            }
        }

        $data = array(
            "code" => $code,
            "tCode" => $tCode,
            "type" => $type,
            "status" => $status
        );
        $data['create'] = isset($create) ? $create : false;
        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @param $param
     * @return array
     * 工单列表
     */
    public function task_search($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $worCode = $warInfo['worCode'];

        $type = $param['type'];
        $status = $param['status'];
        $oCode = $param['code'];
        $tCode = $param['tCode'];


        $pageCurrent = $param['pageCurrent'];//当前页数

        $clause = array();

        if (!empty($type) && $type != 1) {
            $clause['type'] = $type;
        }
        if (!empty($oCode)) {
            $clause['ocode'] = $oCode;
        }
        if (!empty($tCode)) {
            $clause['code'] = $tCode;
        }

        if (!empty($status)) {
            $clause['status'] = $status;
            if ($status != 1) {
                $clause['worcode'] = $worCode;
            }
        }

        if (empty($pageCurrent)) {
            $pageCurrent = 0;
        }

        $taskModel = \Common\Service\TaskService::getInstance();
        $workerModel = WorkerDao::getInstance($warCode);
        $totalCount = $taskModel->query_task_count_by_search($warCode, $clause);
        $pageLimit = pageLimit($totalCount, $pageCurrent, 10);
        $taskData = $taskModel->query_task_list_by_search($warCode, $clause, $pageLimit['page'], $pageLimit['pSize']);
        $data = array();
        $data = array(
            "pageCurrent" => $pageCurrent,
            "pageSize" => $pageLimit['pageSize'],
            "totalCount" => $totalCount,
        );
        $data['list'] = array();
        foreach ($taskData as $value) {
            $taskDatum = array(
                "tCode" => $value['task_code'],
                "ctime" => $value['task_ctime'],
                "type" => $value['task_type'],
                "typeMsg" => venus_task_type_desc($value['task_type']),
                "status" => $value['task_status'],
                "statusMsg" => venus_task_status_desc($value['task_status']),
                "user" => $value['worname'],
                "oCode" => $value['task_ocode']
            );
            $data['list'][] = $taskDatum;
        }

        $success = true;
        $message = '';
        return array($success, $data, $message);
    }

    /**
     * @param $param
     * @return array|bool
     * 工单详情
     */
    public function task_detail($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;

        $warInfo = $this->getUserInfo();

        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];//data下
        $status = $param['status'];//data下1未验货2已验货3未上架4已上架5未拣货6已拣货
        $taskModel = \Common\Service\TaskService::getInstance();
        if ($status == 7) {
            if (!empty($tCode)) {
                $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
                $user = $taskInfo['wor_rname'];
                $taskData = $taskInfo['task_data'];
                $dataBefo = array(
                    "ctime" => $taskInfo['task_ctime'],
                    "tCode" => $tCode,
                );
                $posCode = $taskInfo['task_extra'];
            } else {
                $posCode = $param['code'];
            }
            $list = $this->task_uptpos_unpick_detail($warCode, $posCode);
        } else {
            if (empty($tCode)) {
                $success = false;
                $message = '工单编号不能为空';
                return array($success, array(), $message);
            }
            $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
            $type = $taskInfo['task_type'];
            $oCode = $taskInfo['task_ocode'];
            $dataBefo = array(
                "code" => $oCode,
                "ctime" => $taskInfo['task_ctime'],
                "tCode" => $tCode,
            );
            $user = $taskInfo['wor_rname'];
            if (!empty($oCode)) {
                if (substr($oCode, 0, 2) == "RE") {
                    //1未验货2已验货3未上架4已上架
                    if ($status == 1) {
                        $list = $this->task_gb_detail($warCode, $tCode, self::$GOODSBATCH_STATUS_CREATE);
                    } elseif ($status == 2) {
                        $code = $param['code'];//data下筛选
                        $list = $this->task_sgb_detail($warCode, $tCode, self::$SUBGOODSBATCH_STATUS_INSPECTION, $code);
                    } elseif ($status == 3) {
                        $code = $taskInfo['task_extra'];
                        $list = $this->task_sgb_detail($warCode, $tCode, self::$SUBGOODSBATCH_STATUS_INSPECTION, $code);
                    } elseif ($status == 4) {
                        $code = $param['code'];//data下筛选
                        $list = $this->task_sgb_detail($warCode, $tCode, self::$SUBGOODSBATCH_STATUS_FINISH, $code);
                    }
                } else {
                    if ($status == 5) {
                        $list = $this->task_inv_detail($warCode, $tCode, true);
                    } elseif ($status == 6) {
                        $code = $param['code'];//data下筛选
                        $list = $this->task_inv_detail($warCode, $tCode, false, $code);

                    }
                }
            } elseif ($status == 8) {
                $code = $param['code'];//data下筛选
                $list = $this->task_uptpos_pick_detail($warCode, $tCode, $code);
            } elseif ($status == 9) {
                $code = $param['code'];//data下筛选
                $list = $this->task_uptpos_putaway_detail($warCode, $tCode, $code);
            } else {
                return array("", array(), "");
            }
        }

        $dataBefo["user"] = $user;
        if (!empty($list['list'])) {
            $data = array_merge($dataBefo, $list);
        } else {
            $data = $dataBefo;
        }

        return array("", $data, "");
    }

    /**
     * @return array|bool
     * 工单申领
     */
    public function task_apply($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $tCode = $param['tCode'];
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $worCode = $warInfo['worCode'];
        if (empty($tCode)) {
            if (isset($param["create"]) && $param["create"] == 1) {
                $pCode = $param['pCode'];
                $insertTaskData = array(
                    "oldPosCode" => $pCode,
                );
                $ocode = null;
                $type = self::$TASK_TYPE_UPTPOS;
                $status = self::$TASK_STATUS_CREATE;
                $tCode = \Common\Service\TaskService::getInstance()->task_create($warCode, $insertTaskData, $ocode, $type, $status, $pCode);
                if (!$tCode) {
                    $success = false;
                    $message = '创建工单失败';
                    return array($success, array(), $message);
                }
            } else {
                $success = false;
                $message = '工单编号不能为空';
                return array($success, array(), $message);
            }
        }

        $taskModel = \Common\Service\TaskService::getInstance();
        $statusTask = $taskModel->query_status_by_taskcode($warCode, $tCode);
        if ($statusTask == self::$TASK_STATUS_CREATE) {
            $status = self::$TASK_STATUS_UNDERWAY;
            $applyRes = $taskModel->task_update_status_and_worcode_by_taskcode($warCode, $tCode, $status, $worCode);
            if (!$applyRes) {
                $success = false;
                $message = '领取工单失败';
                return array($success, "", $message);
            } else {
                $success = true;
                $message = '领取工单成功';
                $data = array(
                    "tCode" => $tCode,
                );
                return array($success, $data, $message);
            }
        } else {
            $success = false;
            $message = '此工单不能进行此操作';
            return array($success, "", $message);
        }
    }

    /**
     * @return array|bool
     * 取消工单
     */
    public
    function task_cancel($param)
    {
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $worCode = $warInfo['worCode'];

        $param = (!isset($param)) ? $_POST['data'] : $param;

        $data = array();
        $tCode = $param['tCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $statusTaskInfo = $taskModel->query_status_by_taskcode($warCode, $tCode);
        if ($statusTaskInfo == self::$TASK_STATUS_UNDERWAY) {
            $status = self::$TASK_STATUS_CREATE;
            $uptTask = $taskModel->task_update_status_and_worcode_by_taskcode($warCode, $tCode, $status, '');
            if ($uptTask) {
                $success = true;
                $message = "取消工单成功";
                return array($success, $data, $message);
            } else {
                $success = false;
                $message = '取消工单失败';
                return array($success, "", $message);
            }
        } else {
            $success = false;
            $message = '此工单不能取消';
            return array($success, "", $message);
        }


    }

    /**
     * @param $param
     * @return array|bool
     * 设为已验货
     */
    public function task_sgb_create($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $subgbCode = $param['subgbCode'];
        $tCode = $param['tCode'];
        $list = $param['list'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($subgbCode) || substr($subgbCode, 0, 1) != "S") {
            $message = "请选择验货批次编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($list)) {
            $message = "验货货品列表不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $gbModel = GoodsbatchDao::getInstance($warCode);

        $oCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $listData = array();
        foreach ($list as $listuam) {
            if (empty($listuam['skCount'])) {
                venus_db_rollback();
                $message = "sku数量不能为空";
                $success = false;
                return array($success, "", $message);
            }
            if (empty($listuam['gbCode'])) {
                venus_db_rollback();
                $message = "一级批次编号不能为空";
                $success = false;
                return array($success, "", $message);
            }
            $listData[$listuam['gbCode']]["skCount"] += $listuam['skCount'];
        }
        $isSuccess = true;
        $errorSgbData = array();
        foreach ($listData as $gbCode => $listDatum) {
            $gbData = $gbModel->queryByCode($gbCode);
            if ($gbData['promote_skucount'] + $listDatum['skCount'] <= $gbData['sku_count']) {
                $count = $listDatum['skCount'] * $gbData['spu_count'];
                $promoteCount = $gbData['promote_skucount'] + $listDatum['skCount'];
                $isSuccess = $isSuccess && $gbModel->updatePromoteSkuCountByCode($gbCode, $promoteCount);

                $insertSgbData = array(
                    "code" => $subgbCode,
                    "status" => self::$SUBGOODSBATCH_STATUS_INSPECTION,
                    "count" => $count,
                    "bprice" => $gbData['gb_bprice'],
                    "spucode" => $gbData['spu_code'],
                    "skucode" => $gbData['sku_code'],
                    "skucount" => $listDatum['skCount'],
                    "gbcode" => $gbCode,
                    "reccode" => $gbData['rec_code'],
                );
                $isSuccess = $isSuccess && $sgbModel->insert($insertSgbData);
            } else {
                if (!in_array($gbCode, $errorSgbData)) {
                    $errorSgbData[] = $gbCode;
                }

            }
        }
        if (!empty($errorSgbData)) {
            venus_db_rollback();
            $message = "验货列表含已验完货品，请重新选择";
            $success = false;
            return array($success, "", $message);
        } else {
            if (!$isSuccess) {
                venus_db_rollback();
                $message = "验货失败";
                $success = false;
                return array($success, "", $message);
            } else {
                venus_db_commit();
                $data = $this->task_detail(array("tCode" => $tCode, "status" => 1));
                $success = true;
                $message = "验货成功";
                return array($success, $data[1], $message);
            }

        }
    }

    /**
     * @param $param
     * @return array|bool
     * 设为未验货
     */
    public function task_sgb_delete($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $subgbCode = $param['subgbCode'];
        $tCode = $param['tCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($subgbCode) || substr($subgbCode, 0, 1) != "S") {
            $message = "请选择验货批次编号";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $gbModel = GoodsbatchDao::getInstance($warCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $sgbClause = array("tCode" => $tCode, "status" => 2, "code" => $subgbCode);
        $sgbDataList = $this->task_detail($sgbClause);
        if (!empty($sgbDataList[1])) {
            $isSuccess = true;
            foreach ($sgbDataList[1]['list'] as $sgbData) {
                $gbCode = $sgbData['gbCode'];
                $gbData = $gbModel->queryByCode($gbCode);
                $promoteCount = $gbData['promote_skucount'] - $sgbData['skuCount'];
                $isSuccess = $isSuccess && $gbModel->updatePromoteSkuCountByCode($gbCode, $promoteCount);
            }
            $isSuccess = $isSuccess && $sgbModel->deleteBySubgbCode($subgbCode);
            if (!$isSuccess) {
                venus_db_rollback();
                $success = false;
                $message = "操作失败";
                $data = array();
            } else {
                venus_db_commit();
                $success = true;
                $message = "操作成功";
                $data = $this->task_detail(array("tCode" => $tCode, "status" => 2));
            }
        } else {
            venus_db_commit();
            $success = true;
            $message = "操作失败,此批次不处于已验货状态";
            $data = array();
        }

        return array($success, $data[1], $message);
    }

    /**
     * @param $param
     * @return array|bool
     * 验货完成
     */
    public function task_inspection_finish($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $tCode = $param['tCode'];
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, array(), $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $gbModel = GoodsbatchDao::getInstance($warCode);
        $recModel = ReceiptDao::getInstance($warCode);
        $statusTask = $taskModel->query_status_by_taskcode($warCode, $tCode);
        if ($statusTask == self::$TASK_STATUS_UNDERWAY) {
            $oCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
            venus_db_starttrans();
            $gbUptStatusRes = $gbModel->updateStatusByRecCode($oCode, self::$GOODSBATCH_STATUS_INSPECTION);
            $recUptStatusRes = $recModel->updateStatusByCode($oCode, self::$RECEIPT_STATUS_INSPECTION);
            $taskUptStatusRes = $taskModel->update_task_finish_status_by_code($warCode, $tCode, self::$TASK_STATUS_FINISH);
            $subgbCodeList = $sgbModel->queryListByCondition(array("reccode" => $oCode));
            $subgbCodeDataArr = array();
            foreach ($subgbCodeList as $subgbCodeData) {
                if (!in_array($subgbCodeData['subgb_code'], $subgbCodeDataArr)) {
                    $subgbCodeDataArr[] = $subgbCodeData['subgb_code'];
                }
            }
            foreach ($subgbCodeDataArr as $subgbCode) {
                $taskData = array("code" => $oCode, "subgbCode" => $subgbCode);
                $issetTask = $taskModel->query_task_by_data($warCode, $taskData);
                if (empty($issetTask)) {
                    $insertTaskRes = $taskModel->task_create($warCode, $taskData, $oCode, self::$TASK_TYPE_PUTAWAY, self::$TASK_STATUS_CREATE, $subgbCode);
                    if (!$insertTaskRes) {
                        venus_db_rollback();
                        $success = false;
                        $message = "创建工单失败";
                        return array($success, array(), $message);
                    }
                } else {
                    continue;
                }

            }
            if (!$gbUptStatusRes) {
                venus_db_rollback();
                $success = false;
                $message = "一级批次操作失败";
            } elseif (!$recUptStatusRes) {
                venus_db_rollback();
                $success = false;
                $message = "入仓单操作失败";
            } elseif (!$taskUptStatusRes) {
                venus_db_rollback();
                $success = false;
                $message = "工单操作失败";
            } else {
                venus_db_commit();
                $success = true;
                $message = "验货成功";
            }
        } elseif ($statusTask == self::$TASK_STATUS_FINISH) {
            $success = true;
            $message = "已确认验货完成";
        } elseif ($statusTask == self::$TASK_STATUS_CREATE) {
            $success = false;
            $message = "请先领取工单";
        } else {
            $success = false;
            $message = "工单已取消";
        }

        return array($success, array(), $message);
    }

    //设为已上架
    public function task_sgb_putaway($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $posCode = $param['posCode'];
        $list = $param['list'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($posCode) || substr($posCode, 0, 1) != "P") {
            $message = "货架编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($list)) {
            $message = "上架货品列表不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $listData = array();
        foreach ($list as $sgbData) {
            $listData[$sgbData['sgbCode']] += $sgbData['skCount'];
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $gbModel = GoodsbatchDao::getInstance($warCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $isSuccess = true;
        foreach ($listData as $sgbCode => $skuCount) {
            $sgbDataList = $sgbModel->queryByCode($sgbCode);
            if ($sgbDataList['sku_count'] >= $skuCount) {
                $count = $sgbDataList['spu_count'] * $skuCount;
                $insertSgbData = array(
                    "status" => self::$SUBGOODSBATCH_STATUS_FINISH,
                    "count" => $count,
                    "bprice" => $sgbDataList["sgb_bprice"],
                    "code" => $sgbDataList["subgb_code"],
                    "spucode" => $sgbDataList['spu_code'],
                    "skucode" => $sgbDataList["sku_code"],
                    "skucount" => $skuCount,
                    "gbcode" => $sgbDataList["gb_code"],
                    "reccode" => $sgbDataList["rec_code"],
                    "poscode" => $posCode
                );
                $isSuccess = $isSuccess && $sgbModel->insert($insertSgbData);
                $isSuccess = $isSuccess && $sgbModel->updateInitAndCountAndSkucountByCode($sgbCode, $sgbDataList["sgb_count"] - $count, $sgbDataList["sgb_count"] - $count, $sgbDataList["sku_count"] - $skuCount);
            } else {
                venus_db_rollback();
                $success = false;
                $message = "上架失败,此货品剩余" . $sgbDataList['sku_count'] . $sgbDataList['sku_unit'];
                $data = array();
                return array($success, $data, $message);
            }
        }
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "上架失败";
            $data = array();
        } else {
            venus_db_commit();
            $success = true;
            $message = "上架成功";
            $data = $this->task_detail(array("tCode" => $tCode, "status" => 3));
        }
        return array($success, $data[1], $message);
    }

    //设为未上架
    public function task_sgb_unputaway($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $posCode = $param['posCode'];
        $tCode = $param['tCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($posCode) || substr($posCode, 0, 1) != "P") {
            $message = "请选择货架编号";
            $success = false;
            return array($success, "", $message);
        }
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $taskModel = \Common\Service\TaskService::getInstance();
        $recCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $subgbCode = $taskInfo['task_data']['subgbCode'];
        $isSuccess = true;
        $clauseSgbData = array("poscode" => $posCode, "reccode" => $recCode, "subgbcode" => $subgbCode);
        $sgbDataList = $sgbModel->queryListByCondition($clauseSgbData);
        $sgbDataArr = array();
        foreach ($sgbDataList as $sgbDatum) {
            $sgbDataArr[$sgbDatum['spu_code']]['count'] += $sgbDatum['sgb_count'];
            $sgbDataArr[$sgbDatum['spu_code']]['skuCount'] += $sgbDatum['sku_count'];
            $sgbDataArr[$sgbDatum['spu_code']]['list'][] = $sgbDatum['sgb_code'];
        }
        foreach ($sgbDataArr as $spuCode => $sgbData) {
            $subgbData = $sgbModel->queryBySpuCodeAndStatusAndReccode($spuCode, self::$SUBGOODSBATCH_STATUS_INSPECTION, $recCode);
            if (!empty($subgbData)) {
                $sgbCode = $subgbData['sgb_code'];
                $sgbCount = $subgbData["sgb_count"] + $sgbData['count'];
                $sgbSkuCount = $subgbData["sku_count"] + $sgbData['skuCount'];
                $isSuccess = $isSuccess && $sgbModel->updateInitAndCountAndSkucountByCode($sgbCode, $sgbCount, $sgbCount, $sgbSkuCount);
                $isSuccess = $isSuccess && $sgbModel->deleteByCodes($sgbData['list']);
            }
        }
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "操作失败";
            $data = array();
            return array($success, $data, $message);
        } else {
            venus_db_commit();
            $success = true;
            $data = $this->task_detail(array("tCode" => $tCode, "status" => 4));
            $message = "操作成功";
            return array($success, $data[1], $message);
        }
    }

    //确认上架完成
    public function task_putaway_finish($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $tCode = $param['tCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $taskModel = \Common\Service\TaskService::getInstance();
        $sgbModel = SubgoodsbatchDao::getInstance($warCode);
        $gbModel = GoodsbatchDao::getInstance($warCode);
        $recModel = ReceiptDao::getInstance($warCode);
        $gsModel = GoodstoredDao::getInstance($warCode);
        $goodsModel = GoodsDao::getInstance($warCode);
        $statusTask = $taskModel->query_status_by_taskcode($warCode, $tCode);
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $isSuccess = true;
        if ($statusTask == self::$TASK_STATUS_UNDERWAY) {
            $oCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
            $taskInfo = $taskModel->query_taskdata_by_taskcode($warCode, $tCode);
            $subgbCode = $taskInfo['subgbCode'];
            venus_db_starttrans();
            $insertGoodsDataArr = array();
            $issetUnputwayByGbcode = $sgbModel->queryListByStatusAndGbcode($oCode, self::$SUBGOODSBATCH_STATUS_INSPECTION);
            $issetUnputwayByOcode = $sgbModel->queryListByStatusAndReccode($oCode, self::$SUBGOODSBATCH_STATUS_INSPECTION);
            if (empty($issetUnputwayByGbcode)) {
                $isSuccess = $isSuccess && $gbModel->updateStatusByRecCode($oCode, self::$GOODSBATCH_STATUS_PUTAWAY);
            }
            if (empty($issetUnputwayByOcode)) {
                $isSuccess = $isSuccess && $recModel->updateStatusByCode($oCode, self::$RECEIPT_STATUS_FINISH);
            }

            $subgbDataUnputway = $this->task_detail(array("tCode" => $tCode, "status" => 3));

            if (!empty($subgbDataUnputway) && isset($subgbDataUnputway[1]) && !empty($subgbDataUnputway[1]['list'])) {
                venus_db_rollback();
                $success = false;
                $message = "还有" . count($subgbDataUnputway[1]['list']) . "项货品未上架";
            } else {
                $subgbData = $sgbModel->queryListByCondition(
                    array(
                        "reccode" => $oCode,
                        "subgbcode" => $subgbCode,
                        "status" => self::$SUBGOODSBATCH_STATUS_FINISH
                    )
                );

                foreach ($subgbData as $subgbDatum) {

                    $insertGsData = array(
                        "init" => $subgbDatum["sgb_init"],
                        "count" => $subgbDatum["sgb_count"],
                        "bprice" => $subgbDatum["sgb_bprice"],
                        "gbcode" => $subgbDatum["gb_code"],
                        "sgbcode" => $subgbDatum["sgb_code"],
                        "poscode" => $subgbDatum["pos_code"],
                        "spucode" => $subgbDatum["spu_code"],
                        "skucode" => $subgbDatum["sku_code"],
                        "skucount" => $subgbDatum["sku_count"],
                    );
                    $isSuccess = $isSuccess && $gsModel->insert($insertGsData);
                    $insertGoodsDataArr[$subgbDatum["spu_code"]] += $subgbDatum["sgb_count"];
                }

                foreach ($insertGoodsDataArr as $spuCode => $insertGoods) {
                    $issetGoods = $goodsModel->queryBySpuCode($spuCode);
                    if ($issetGoods) {
                        $goodsCode = $issetGoods['goods_code'];
                        $init = $issetGoods['goods_init'] + $insertGoods;
                        $count = $issetGoods['goods_count'] + $insertGoods;
                        $isSuccess = $isSuccess && $goodsModel->updateCountAndInitByCode($goodsCode, $init, $count);
                    } else {
                        $insertGoodsData = array(
                            "init" => $insertGoods,
                            "count" => $insertGoods,
                            "spucode" => $spuCode,
                        );
                        $isSuccess = $isSuccess && $goodsModel->insert($insertGoodsData);
                    }

                }

                $isSuccess = $isSuccess && $taskModel->update_task_finish_status_by_code($warCode, $tCode, self::$TASK_STATUS_FINISH);
                if (!$isSuccess) {
                    venus_db_rollback();
                    $success = false;
                    $message = "操作失败";
                } else {
                    venus_db_commit();
                    $success = true;
                    $message = "上架完成";
                }
            }


        } elseif ($statusTask == self::$TASK_STATUS_FINISH) {
            $success = true;
            $message = "已确认上架完成";
        } elseif ($statusTask == self::$TASK_STATUS_CREATE) {
            $success = false;
            $message = "请先领取工单";
        } else {
            $success = false;
            $message = "工单已取消";
        }

        return array($success, array(), $message);
    }

    //设为已拣货
    public function task_igs_pick($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $bcode = $param['bCode'];
        $list = $param['list'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($bcode) || substr($bcode, 0, 1) != "B") {
            $message = "拣货车编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($list)) {
            $message = "拣货货品不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $igsModel = IgoodsentDao::getInstance($warCode);
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        venus_db_starttrans();
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $isSuccess = true;
        $listData = array();
        foreach ($list as $listuam) {
            if (empty($listuam['igsCode'])) {
                venus_db_rollback();
                $message = "拣货编号不能为空";
                $success = false;
                return array($success, "", $message);
            }
            if (empty($listuam['skCount'])) {
                venus_db_rollback();
                $message = "拣货数量不能为空";
                $success = false;
                return array($success, "", $message);
            }
            $listData[$listuam['igsCode']] += $listuam['skCount'];
        }
        foreach ($listData as $igsCode => $skCount) {
            $igsData = $igsModel->queryByCode($igsCode);
            $insertData = array();
            $count = $skCount * $igsData["spu_count"];
            $insertData = array(
                "count" => $count,
                "bprice" => $igsData["igs_bprice"],
                "spucode" => $igsData["spu_code"],
                "gscode" => $igsData["gs_code"],
                "igocode" => $igsData["igo_code"],
                "skucode" => $igsData["sku_code"],
                "skucount" => $skCount,
                "invcode" => $igsData["inv_code"],
                "bcode" => $bcode,
            );
            $isSuccess = $isSuccess && $igsModel->insert($insertData);
            $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsData["igs_count"] - $count, $igsData["sku_count"] - $skCount);
            unset($insertData);
        }
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "设为已拣货失败";
            $data = array();
            return array($success, $data, $message);
        } else {
            venus_db_commit();
            $success = true;
            $message = "设为已拣货成功";
            $data = $this->task_detail(array("tCode" => $tCode, "status" => 5));
            return array($success, $data[1], $message);
        }
    }

    //设为未拣货
    public function task_igs_unpick($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $bCode = $param['bCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($bCode)) {
            $message = "拣货车编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $igsModel = IgoodsentDao::getInstance($warCode);
        $taskModel = \Common\Service\TaskService::getInstance();
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $isSuccess = true;
        $invCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
        $clauseIgsData = array("bcode" => $bCode, "invcode" => $invCode);
        $igsDataList = $igsModel->queryListByCondition($clauseIgsData);
        $igsDataArr = array();
        foreach ($igsDataList as $igsDatum) {
            $igsDataArr[$igsDatum['spu_code']]['count'] += $igsDatum['igs_count'];
            $igsDataArr[$igsDatum['spu_code']]['skuCount'] += $igsDatum['sku_count'];
            $igsDataArr[$igsDatum['spu_code']]['list'][] = $igsDatum['igs_code'];
        }
        foreach ($igsDataArr as $spuCode => $igsData) {
            $clauseIgsDataNew = array("spucode" => $spuCode, "bcode" => array("exp", "is null"), "invcode" => $invCode);
            $igsDataInfo = $igsModel->queryListByCondition($clauseIgsDataNew, 0, 1)[0];
            if (!empty($igsDataInfo)) {
                $igsCode = $igsDataInfo['igs_code'];
                $igsCount = $igsDataInfo["igs_count"] + $igsData['count'];
                $igsSkuCount = $igsDataInfo["sku_count"] + $igsData['skuCount'];
                $isSuccess = $isSuccess && $igsModel->updateCountAndSkuCountByCode($igsCode, $igsCount, $igsSkuCount);
                $isSuccess = $isSuccess && $igsModel->deleteByCodes($igsData['list']);
            }
        }

        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "设为未拣货失败";
            $data = array();
            return array($success, $data, $message);
        } else {
            venus_db_commit();
            $success = true;
            $message = "设为未拣货成功";
            $data = $this->task_detail(array("tCode" => $tCode, "status" => 6));
            return array($success, $data[1], $message);
        }
    }

    //确认拣货完成
    public function task_pick_finish($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $pCode = $param['pCode'];
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($pCode)) {
            $message = "目的地编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $igsModel = IgoodsentDao::getInstance($warCode);
        $invModel = InvoiceDao::getInstance($warCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $invCode = $taskModel->query_ocode_by_taskcode($warCode, $tCode);
        $isSuccess = true;
        $isSuccess = $isSuccess && $igsModel->updatePorterCodeByInvCodeAndBenchCode($invCode, array('exp', 'is not null'), $pCode);
        $issetUnpick = $this->task_detail(array("tCode" => $tCode, "status" => 5));
        if (!empty($issetUnpick) && isset($issetUnpick[1]) && !empty($issetUnpick[1]['list'])) {
            venus_db_rollback();
            $success = false;
            $message = "还有" . count($issetUnpick[1]['list']) . "项货品未拣货";
        } else {
            $isSuccess = $isSuccess && $invModel->updateStatusByCode($invCode, self::$INVOICE_STATUS_PICK);
            $isSuccess = $isSuccess && $taskModel->update_task_finish_status_by_code($warCode, $tCode, self::$TASK_STATUS_FINISH);
            $clauseIgsDataNew = array("bcode" => array("exp", "is null"), "invcode" => $invCode, "pcode" => array("exp", "is null"));
            $igsDataInfo = $igsModel->queryListByCondition($clauseIgsDataNew, 0, 100);
            $igsCodeArr = array();
            foreach ($igsDataInfo as $igsData) {
                $igsCodeArr[] = $igsData["igs_code"];
            }
            $isSuccess = $isSuccess && $igsModel->deleteByCodes($igsCodeArr);

            if (!$isSuccess) {
                venus_db_rollback();
                $success = false;
                $message = "确认拣货完成失败";
            } else {
                venus_db_commit();
                $success = true;
                $message = "确认拣货完成成功";
            }
        }

        return array($success, array(), $message);
    }

    /**
     * @param $param
     * @return array
     * 补货设为已拣货
     */
    public function task_uptpos_pick($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $bCode = $param['bCode'];
        $list = $param['list'];
        $data = array();
        if (empty($tCode)) {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($bCode)) {
            $message = "拣货车编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $gsModel = GoodstoredDao::getInstance($warCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $listData = array();
        foreach ($list as $listuam) {
            if (empty($listuam['gsCode'])) {
                venus_db_rollback();
                $message = "货品库存批次编号不能为空";
                $success = false;
                return array($success, "", $message);
            }
            if (empty($listuam['skCount'])) {
                venus_db_rollback();
                $message = "拣货数量不能为空";
                $success = false;
                return array($success, "", $message);
            }
            $listData[$listuam['gsCode']] += $listuam['skCount'];
        }
        $isSuccess = true;
        $taskData = $taskInfo['task_data'];
        foreach ($listData as $gsCode => $skCount) {
            $gsData = $gsModel->queryByCode($gsCode);
            $insertData = array();
            $count = $skCount * $gsData["spu_count"];
            if ($skCount < $gsData['sku_count'] || $skCount == $gsData['sku_count']) {
                $insertData = array(
                    "init" => $count,
                    "count" => $count,
                    "bprice" => $gsData["gb_bprice"],
                    "sgbcode" => $gsData["sgb_code"],
                    "gbcode" => $gsData["gb_code"],
                    "poscode" => $bCode,
                    "spucode" => $gsData["spu_code"],
                    "skucode" => $gsData["sku_code"],
                    "skucount" => $skCount,
                );
                $gsCodeNew = $gsModel->insert($insertData);
                $isSuccess = $isSuccess && (empty($gsCodeNew) ? false : true);
                $isSuccess = $isSuccess && $gsModel->updateInitAndCountByCode($gsCode, $gsData["gs_init"] - $count, $gsData["gs_count"] - $count);
                $isSuccess = $isSuccess && $gsModel->updateSkuCountByCode($gsCode, $gsData["sku_count"] - $skCount);
                $isSuccess = $isSuccess && $gsModel->updateSkuInitByCode($gsCode, $gsData["sku_init"] - $skCount);
                $taskData["list"][$gsCodeNew]['skCount'] = $skCount;
                $taskData["list"][$gsCodeNew]['count'] = $count;
                $taskData["list"][$gsCodeNew]['oldGsCode'] = $gsCode;
                $taskData["list"][$gsCodeNew]['bCode'] = $bCode;
                unset($insertData);
            } else {
                venus_db_rollback();
                $success = false;
                $message = "请重新选择货品数量";
                return array($success, $data, $message);
            }
        }
        $isSuccess = $isSuccess && $taskModel->update_data_by_taskcode($warCode, $tCode, $taskData, false);
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "补货设为已拣货失败";
        } else {
            venus_db_commit();
            $success = true;
            $message = "";
            $taskDetailData = $this->task_detail(array("tCode" => $tCode, "status" => 7));
            $data = $taskDetailData[1];
        }
        return array($success, $data, $message);
    }

    /**
     * @param $param
     * @return array
     * 补货设为未拣货
     */
    public function task_uptpos_unpick($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $bCode = $param['bCode'];
        $data = array();
        $gsModel = GoodstoredDao::getInstance($warCode);
        venus_db_starttrans();
        $taskData = \Common\Service\TaskService::getInstance()->query_taskdata_by_taskcode($warCode, $tCode);
        $gsDataArr = $taskData['list'];
        $removeGsData = array();
        $updateGsData = array();
        $updateTaskData = $taskData;
        foreach ($gsDataArr as $gsCode => $gsData) {
            if ($gsData['bCode'] == $bCode) {
                if (!in_array($gsCode, $removeGsData)) {
                    $removeGsData[] = $gsCode;
                }
                $updateGsData[$gsData['oldGsCode']]['count'] += $gsData['count'];
                $updateGsData[$gsData['oldGsCode']]['skCount'] += $gsData['skCount'];
                unset($updateTaskData['list'][$gsCode]);
            }
        }
        $isSuccess = true;
        foreach ($updateGsData as $gsCode => $updateGsDatum) {
            $gsData = $gsModel->queryByCode($gsCode);
            $count = $updateGsDatum['count'];
            $skCount = $updateGsDatum['skCount'];
            $isSuccess = $isSuccess && $gsModel->updateInitAndCountByCode($gsCode, $gsData["gs_init"] + $count, $gsData["gs_count"] + $count);
            $isSuccess = $isSuccess && $gsModel->updateSkuCountByCode($gsCode, $gsData["sku_count"] + $skCount);
            $isSuccess = $isSuccess && $gsModel->updateSkuInitByCode($gsCode, $gsData["sku_init"] + $skCount);
        }
        if (!empty($removeGsData)) {
            $isSuccess = $isSuccess && $gsModel->deleteByCodes($removeGsData);
        }
        $isSuccess = $isSuccess && \Common\Service\TaskService::getInstance()->update_data_by_taskcode($warCode, $tCode, $updateTaskData, false);
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "补货设为未拣货失败";
        } else {
            venus_db_commit();
            $success = true;
            $message = "补货设为未拣货成功";
            $taskDetailData = $this->task_detail(array("tCode" => $tCode, "status" => 8));
            $data = $taskDetailData[1];
        }
        return array($success, $data, $message);

    }

    /**
     * @param $param
     * @return array
     * 补货设为已上架
     */
    public function task_uptpos_putaway($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $pCode = $param['pCode'];
        $list = $param['list'];
        $data = array();
        if (empty($tCode) || substr($tCode, 0, 1) != "T") {
            $message = "工单编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        if (empty($pCode) || substr($pCode, 0, 1) != "P") {
            $message = "货品编号不能为空";
            $success = false;
            return array($success, "", $message);
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $gsModel = GoodstoredDao::getInstance($warCode);
        venus_db_starttrans();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        if ($taskStatus == self::$TASK_STATUS_CANCEL || $taskStatus == self::$TASK_STATUS_FINISH) {
            venus_db_rollback();
            $message = "此工单不能进行此操作";
            $success = false;
            return array($success, "", $message);
        }
        $listData = array();
        foreach ($list as $listuam) {
            if (empty($listuam['gsCode'])) {
                venus_db_rollback();
                $message = "货品库存批次编号不能为空";
                $success = false;
                return array($success, "", $message);
            }
            if (empty($listuam['skCount'])) {
                venus_db_rollback();
                $message = "拣货数量不能为空";
                $success = false;
                return array($success, "", $message);
            }
            $listData[$listuam['gsCode']] += $listuam['skCount'];
        }

        $isSuccess = true;
        $taskData = $taskInfo['task_data'];
        foreach ($listData as $gsCode => $skCount) {
            $gsData = $gsModel->queryByCode($gsCode);
            $insertData = array();
            $count = $skCount * $gsData["spu_count"];
            if ($skCount == $gsData["sku_count"]) {
                $isSuccess = $isSuccess && $gsModel->updatePosCodeByCode($gsCode, $pCode);
                $taskData["list"][$gsCode]['posCode'] = $pCode;
                $taskData["list"][$gsCode]['parentGsCode'] = $gsCode;
            } else {
                $insertData = array(
                    "init" => $count,
                    "count" => $count,
                    "bprice" => $gsData["gb_bprice"],
                    "sgbcode" => $gsData["sgb_code"],
                    "gbcode" => $gsData["gb_code"],
                    "poscode" => $pCode,
                    "spucode" => $gsData["spu_code"],
                    "skucode" => $gsData["sku_code"],
                    "skucount" => $skCount,
                );
                $gsCodeNew = $gsModel->insert($insertData);
                $taskData["list"][$gsCodeNew]['posCode'] = $pCode;
                $taskData["list"][$gsCodeNew]['skCount'] = $skCount;
                $taskData["list"][$gsCodeNew]['count'] = $count;
                $taskData["list"][$gsCodeNew]['parentGsCode'] = $gsCode;
                $taskData["list"][$gsCodeNew]['bCode'] = $taskData["list"][$gsCode]['bCode'];
                $taskData["list"][$gsCodeNew]['oldGsCode'] = $taskData["list"][$gsCode]['oldGsCode'];
                $taskData["list"][$gsCode]['skCount'] = $taskData["list"][$gsCode]['skCount'] - $skCount;
                $taskData["list"][$gsCode]['count'] = $taskData["list"][$gsCode]['count'] - $count;
                $isSuccess = $isSuccess && (empty($gsCodeNew) ? false : true);
                $isSuccess = $isSuccess && $gsModel->updateInitAndCountByCode($gsCode, $gsData["gs_init"] - $count, $gsData["gs_count"] - $count);
                $isSuccess = $isSuccess && $gsModel->updateSkuCountByCode($gsCode, $gsData["sku_count"] - $skCount);
                $isSuccess = $isSuccess && $gsModel->updateSkuInitByCode($gsCode, $gsData["sku_init"] - $skCount);
            }
            unset($insertData);
        }
        $isSuccess = $isSuccess && $taskModel->update_data_by_taskcode($warCode, $tCode, $taskData, false);
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "补货设为已上架失败";
        } else {
            venus_db_commit();
            $success = true;
            $message = "补货设为已上架成功";
            $taskDetailData = $this->task_detail(array("tCode" => $tCode, "status" => 8));
            $data = $taskDetailData[1];
        }
        return array($success, $data, $message);
    }

    /**
     * @param $param
     * @return array
     * 补货设为未上架
     */
    public function task_uptpos_unputaway($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $pCode = $param['pCode'];
        $data = array();
        $gsModel = GoodstoredDao::getInstance($warCode);
        venus_db_starttrans();
        $taskData = \Common\Service\TaskService::getInstance()->query_taskdata_by_taskcode($warCode, $tCode);
        $gsDataArr = $taskData['list'];
        $removeGsData = array();
        $updateGsData = array();
        $updateTaskData = $taskData;
        foreach ($gsDataArr as $gsCode => $gsData) {
            if ($gsData['posCode'] == $pCode) {
                if ($gsData['parentGsCode'] == $gsCode) {
                    $updateGsData[$gsData['parentGsCode']]['count'] = 0;
                    $updateGsData[$gsData['parentGsCode']]['skCount'] = 0;
                    $updateGsData[$gsData['parentGsCode']]['bCode'] = $gsData['bCode'];
                    $updateTaskData['list'][$gsCode]['posCode'] = "";
                } else {
                    if (!in_array($gsCode, $removeGsData)) {
                        $removeGsData[] = $gsCode;
                    }
                    $updateGsData[$gsData['parentGsCode']]['count'] += $gsData['count'];
                    $updateGsData[$gsData['parentGsCode']]['skCount'] += $gsData['skCount'];
                    $updateGsData[$gsData['parentGsCode']]['bCode'] = $gsData['bCode'];
                    unset($updateTaskData['list'][$gsCode]);
                }

            }
        }
        $isSuccess = true;
        foreach ($updateGsData as $gsCode => $updateGsDatum) {
            if ($updateGsDatum['count'] != 0 && $updateGsDatum['skCount'] != 0) {
                $gsData = $gsModel->queryByCode($gsCode);
                $count = $updateGsDatum['count'];
                $skCount = $updateGsDatum['skCount'];
                $isSuccess = $isSuccess && $gsModel->updateInitAndCountByCode($gsCode, $gsData["gs_init"] + $count, $gsData["gs_count"] + $count);
                $isSuccess = $isSuccess && $gsModel->updateSkuCountByCode($gsCode, $gsData["sku_count"] + $skCount);
                $isSuccess = $isSuccess && $gsModel->updateSkuInitByCode($gsCode, $gsData["sku_init"] + $skCount);
            }
            $isSuccess = $isSuccess && $gsModel->updatePosCodeByCode($gsCode, $updateGsDatum['bCode']);

        }
        if (!empty($removeGsData)) {
            $isSuccess = $isSuccess && $gsModel->deleteByCodes($removeGsData);
        }
        $isSuccess = $isSuccess && \Common\Service\TaskService::getInstance()->update_data_by_taskcode($warCode, $tCode, $updateTaskData, false);
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            $message = "补货设为未上架失败";
        } else {
            venus_db_commit();
            $success = true;
            $message = "补货设为未上架成功";
            $taskDetailData = $this->task_detail(array("tCode" => $tCode, "status" => 9));
            $data = $taskDetailData[1];
        }
        return array($success, $data, $message);
    }

    /**
     * @param $param
     * @return array
     * 确认补货完成
     */
    public function task_uptpos_finish($param)
    {
        $param = (!isset($param)) ? $_POST['data'] : $param;
        $warInfo = $this->getUserInfo();
        $warCode = $warInfo['warCode'];
        $tCode = $param['tCode'];
        $taskData = \Common\Service\TaskService::getInstance()->query_taskdata_by_taskcode($warCode, $tCode);
        $gsDataArr = $taskData['list'];
        $errorGs=array();
        $isSuccess = true;
        foreach ($gsDataArr as $gsCode => $gsData) {
            if (!isset($gsData['posCode']) || empty($gsData['posCode'])) {
                $isSuccess = $isSuccess && false;
                $errorGs[] = $gsCode;
            }
        }
        $taskModel = \Common\Service\TaskService::getInstance();
        $isSuccess = $isSuccess && $taskModel->update_task_finish_status_by_code($warCode, $tCode, self::$TASK_STATUS_FINISH);
        if (!$isSuccess) {
            venus_db_rollback();
            $success = false;
            if (!empty($errorGs)) {
                $message = "确认补货完成失败,有" . count($errorGs) . "项未上架";
            } else {
                $message = "确认补货完成操作失败";
            }

        } else {
            $success = true;
            $message = "确认补货完成成功";
        }
        return array($success, array(), $message);
    }








//------------------------------------------------------------------------------------------------------------------
//    以下为内部使用方法
    /**
     * @param $warCode
     * @param $tCode
     * @param $status 1未验货2已验货
     * @return mixed
     * 批次详情
     */
    private
    function task_gb_detail($warCode, $tCode, $status)
    {
        $data = array();
        $taskModel = \Common\Service\TaskService::getInstance();
        $taskDetailData = $taskModel->query_gb_detail_by_taskcode($warCode, $tCode, $status);
        if (!empty($taskDetailData)) {
            foreach ($taskDetailData as $taskDetailDatum) {
                if ($taskDetailDatum["sku_count"] - $taskDetailDatum["promote_skucount"] > 0) {
                    $skuList = array(
                        "name" => $taskDetailDatum["spu_name"],
                        "skuCount" => floatval($taskDetailDatum["sku_count"] - $taskDetailDatum["promote_skucount"]),
                        "brand" => $taskDetailDatum["spu_brand"],
                        "skuNorm" => $taskDetailDatum["sku_norm"],
                        "from" => $taskDetailDatum["spu_from"],
                        "unit" => $taskDetailDatum["sku_unit"],
                        "cunit" => $taskDetailDatum["spu_cunit"],
                        "skuCode" => $taskDetailDatum["sku_code"],
                        "gbCode" => $taskDetailDatum['gb_code'],
                        "count" => 0
                    );
                    $data["list"][] = $skuList;
                }
            }
        }
        return $data;

    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $status
     * @param string $code
     * @return array
     * 详细批次列表及详细批次code和货架code筛选
     */
    private
    function task_sgb_detail($warCode, $tCode, $status, $code = '')
    {

        $data = array();
        $taskModel = \Common\Service\TaskService::getInstance();
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskType = $taskInfo['task_type'];
        $taskStatus = $taskInfo['task_status'];
        $taskDetailData = $taskModel->query_sgb_detail_by_taskcode($warCode, $tCode, $status, $code);
        if (!empty($taskDetailData)) {
            $sgbCodeList = array();
            $posCodeList = array();
            foreach ($taskDetailData as $taskDetailDatum) {
                if (floatval($taskDetailDatum['sku_count']) > 0) {
                    $skuList = array(
                        "name" => $taskDetailDatum["spu_name"],
                        "brand" => $taskDetailDatum["spu_brand"],
                        "skuCount" => floatval($taskDetailDatum['sku_count']),
                        "skuNorm" => $taskDetailDatum["sku_norm"],
                        "from" => $taskDetailDatum["spu_from"],
                        "unit" => $taskDetailDatum["sku_unit"],
                        "cunit" => $taskDetailDatum["spu_cunit"],
                        "skuCode" => $taskDetailDatum["sku_code"],
                        "gbCode" => $taskDetailDatum["gb_code"],
                        "sgbCode" => $taskDetailDatum["sgb_code"],
                        "count" => 0
                    );
                }

                if (empty($code)) {
                    if ($taskType == self::$TASK_TYPE_INSPECTION && $taskStatus == self::$TASK_STATUS_UNDERWAY) {
                        if (!in_array($taskDetailDatum["subgb_code"], $sgbCodeList)) {
                            $data["subgbCodeList"][0] = array("subgbCode" => "全部");
                            $data["subgbCodeList"][] = array("subgbCode" => $taskDetailDatum["subgb_code"]);
                            $sgbCodeList[] = $taskDetailDatum["subgb_code"];
                        }
                        if (floatval($taskDetailDatum['sku_count']) > 0) {
                            $skuList["subgbCode"] = $taskDetailDatum["subgb_code"];
                        }
                    }

                    if ($taskType == self::$TASK_TYPE_PUTAWAY && $taskStatus == self::$TASK_STATUS_UNDERWAY) {
                        if (!in_array($taskDetailDatum["pos_code"], $posCodeList)) {
                            $data["posCodeList"][0] = array("posCode" => "全部");
                            $data["posCodeList"][] = array("posCode" => $taskDetailDatum["pos_code"]);
                            $posCodeList[] = $taskDetailDatum["pos_code"];
                        }
                        if (floatval($taskDetailDatum['sku_count']) > 0) {
                            $skuList["posCode"] = $taskDetailDatum["pos_code"];
                        }
                    }
                } else {
                    if (substr($code, 0, 1) == "S") {
                        $data['subgbCode'] = $code;
                    }
                    if (substr($code, 0, 1) == "P") {
                        $data['posCode'] = $code;
                    }
                }
                if (floatval($taskDetailDatum['sku_count']) > 0) {
                    $data["list"][] = $skuList;
                }
            }
        }
        if (!empty($data["list"])) {
            $data['status'] = $taskStatus;
        } else {
            $data = array(
                'status' => $taskStatus
            );
        }
        return $data;
    }

    /**
     * @param $warCode
     * @param $tCode
     * @param $isNullBcode
     * @param string $code
     * @return array
     *
     */
    private
    function task_inv_detail($warCode, $tCode, $isNullBcode, $code = '')
    {
        $data = array();
        $taskModel = \Common\Service\TaskService::getInstance();
        $invModel = InvoiceDao::getInstance($warCode);
        if ($isNullBcode) {
            $bcode = array('exp', 'is null');
        } else {
            if (empty($code)) {
                $bcode = array('exp', 'is not null');
            } else {
                $bcode = $code;
            }
        }
        $taskInfo = $taskModel->query_taskinfo_by_taskcode($warCode, $tCode);
        $taskStatus = $taskInfo['task_status'];
        $taskDetailData = $taskModel->query_igs_detail_by_taskcode_and_benchcode($warCode, $tCode, $bcode);
        if (!empty($taskDetailData)) {
            $bCodeList = array();
            foreach ($taskDetailData as $taskDetailDatum) {
                if (floatval($taskDetailDatum['sku_count']) > 0) {
                    $skuList = array(
                        "name" => $taskDetailDatum["spu_name"],
                        "brand" => $taskDetailDatum["spu_brand"],
                        "skuCount" => floatval($taskDetailDatum['sku_count']),
                        "skuNorm" => $taskDetailDatum["sku_norm"],
                        "from" => $taskDetailDatum["spu_from"],
                        "unit" => $taskDetailDatum["sku_unit"],
                        "cunit" => $taskDetailDatum["spu_cunit"],
                        "skuCode" => $taskDetailDatum["sku_code"],
                        "posCode" => $taskDetailDatum["pos_code"],
                        "igsCode" => $taskDetailDatum["igs_code"],
                        "count" => 0
                    );

                    if (!$isNullBcode) {
                        if (empty($code)) {
                            if (!in_array($taskDetailDatum["bench_code"], $bCodeList)) {
                                $data["bCodeList"][0] = array("bCode" => "全部");
                                $data["bCodeList"][] = array("bCode" => $taskDetailDatum["bench_code"]);
                                $bCodeList[] = $taskDetailDatum["bench_code"];
                            }
                        } else {
                            if (substr($code, 0, 1) == "B") {
                                $data['bCode'] = $code;
                            }
                        }
                        $skuList["bCode"] = $taskDetailDatum["bench_code"];
                    }
                    $data["list"][] = $skuList;
                }
            }
        }
        if (!empty($data["list"])) {
            $data['status'] = $taskStatus;
        } else {
            $data = array(
                'status' => $taskStatus
            );
        }
        return $data;

    }

    private function getUserInfo()
    {
        $workerData = PassportService::getInstance()->loginUser();
        if (empty($workerData)) {
            venus_throw_exception(110);
        }
        return array(
            'warCode' => $workerData["war_code"],
            'worCode' => $workerData["wor_code"],
            'worName' => $workerData["wor_name"],
            'worRname' => $workerData["wor_rname"],
        );
    }

    //补货之未拣货
    private function task_uptpos_unpick_detail($warCode, $posCode)
    {
        $data = array();
        $gsModel = GoodstoredDao::getInstance($warCode);
        $posData = $gsModel->queryListByPosCode($posCode, 0, 1000);
        $data['posCode'] = $posCode;
        foreach ($posData as $posDatum) {
            if (floatval($posDatum['sku_count']) > 0) {
                $skuList = array(
                    "name" => $posDatum["spu_name"],
                    "brand" => $posDatum["spu_brand"],
                    "skuCount" => floatval($posDatum['sku_count']),
                    "skuNorm" => $posDatum["sku_norm"],
                    "from" => $posDatum["spu_from"],
                    "unit" => $posDatum["sku_unit"],
                    "cunit" => $posDatum["spu_cunit"],
                    "skuCode" => $posDatum["sku_code"],
                    "posCode" => $posDatum["pos_code"],
                    "gsCode" => $posDatum["gs_code"],
                    "count" => 0
                );
                $data["list"][] = $skuList;
            }
        }
        return $data;
    }

    //补货之已拣货
    private function task_uptpos_pick_detail($warCode, $tCode, $pCode = '')
    {
        $taskModel = \Common\Service\TaskService::getInstance();
        $taskData = $taskModel->query_taskdata_by_taskcode($warCode, $tCode);
        $data = array();
        if (isset($taskData['list']) && !empty($taskData['list'])) {
            $gsCodeData = array();
            foreach ($taskData['list'] as $gsCode => $taskDatum) {
                if (empty($taskDatum['posCode'] && !empty($taskDatum['bCode']))) {
                    $gsCodeData[] = $gsCode;
                }
            }
            if (!empty($gsCodeData)) {
                $gsClause = array(
                    "codes" => $gsCodeData,
                );
                if (!empty($pCode)) {
                    $gsClause['poscode'] = $pCode;
                }
                $gsData = GoodstoredDao::getInstance($warCode)->queryListByCondition($gsClause, 0, 1000);
                $bCodeList = array();
                foreach ($gsData as $gsDatum) {
                    if (floatval($gsDatum['sku_count']) > 0) {
                        if (empty($code)) {
                            if (!in_array($gsDatum["pos_code"], $bCodeList)) {
                                $data["bCodeList"][0] = array("bCode" => "全部");
                                $data["bCodeList"][] = array("bCode" => $gsDatum["pos_code"]);
                                $bCodeList[] = $gsDatum["pos_code"];
                            }
                        } else {
                            if (substr($pCode, 0, 1) == "B") {
                                $data['bCode'] = $pCode;
                            }
                        }
                        $skuList = array(
                            "name" => $gsDatum["spu_name"],
                            "brand" => $gsDatum["spu_brand"],
                            "skuCount" => floatval($gsDatum['sku_count']),
                            "skuNorm" => $gsDatum["sku_norm"],
                            "from" => $gsDatum["spu_from"],
                            "unit" => $gsDatum["sku_unit"],
                            "cunit" => $gsDatum["spu_cunit"],
                            "skuCode" => $gsDatum["sku_code"],
                            "posCode" => $gsDatum["pos_code"],
                            "gsCode" => $gsDatum["gs_code"],
                            "count" => 0
                        );
                        $data["list"][] = $skuList;
                    }
                }
            } else {
                $data['list'] = array();
            }
        }


        return $data;
    }

    //补货之已上架
    private function task_uptpos_putaway_detail($warCode, $tCode, $pCode = '')
    {
        $taskModel = \Common\Service\TaskService::getInstance();
        $taskData = $taskModel->query_taskdata_by_taskcode($warCode, $tCode);
        $gsCodeData = array();
        $data = array();
        foreach ($taskData['list'] as $gsCode => $taskDatum) {
            if (!empty($taskDatum['posCode'])) {
                $gsCodeData[] = $gsCode;
            }
        }
        if (!empty($gsCodeData)) {
            $gsClause = array(
                "codes" => $gsCodeData,
            );

            if (!empty($pCode)) {
                $gsClause['poscode'] = $pCode;
            }
            $gsData = GoodstoredDao::getInstance($warCode)->queryListByCondition($gsClause, 0, 1000);
            $bCodeList = array();
            foreach ($gsData as $gsDatum) {
                if (floatval($gsDatum['sku_count']) > 0) {
                    if (empty($code)) {
                        if (!in_array($gsDatum["pos_code"], $bCodeList)) {
                            $data["pCodeList"][0] = array("pCode" => "全部");
                            $data["pCodeList"][] = array("pCode" => $gsDatum["pos_code"]);
                            $bCodeList[] = $gsDatum["pos_code"];
                        }
                    } else {
                        if (substr($pCode, 0, 1) == "B") {
                            $data['pCode'] = $pCode;
                        }
                    }
                    $skuList = array(
                        "name" => $gsDatum["spu_name"],
                        "brand" => $gsDatum["spu_brand"],
                        "skuCount" => floatval($gsDatum['sku_count']),
                        "skuNorm" => $gsDatum["sku_norm"],
                        "from" => $gsDatum["spu_from"],
                        "unit" => $gsDatum["sku_unit"],
                        "cunit" => $gsDatum["spu_cunit"],
                        "skuCode" => $gsDatum["sku_code"],
                        "posCode" => $gsDatum["pos_code"],
                        "gsCode" => $gsDatum["gs_code"],
                        "count" => 0
                    );
                    $data["list"][] = $skuList;
                }
            }

        }
        return $data;
    }
}