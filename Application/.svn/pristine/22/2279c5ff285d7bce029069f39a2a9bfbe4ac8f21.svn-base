<?php

use Wms\Dao\ReportDao;
use Wms\Dao\WarehouseDao;

static $REPORT_TYPE_RECEIPT = "2";//入仓单
static $REPORT_TYPE_INVOICE = "4";//出仓单
static $REPORT_TYPE_RECEIPT_COLLECT = "6";//入库汇总
static $REPORT_TYPE_INVOICE_COLLECT = "8";//出库汇总
static $REPORT_TYPE_GOODSTROED_COLLECT = "10";//库存汇总
static $REPORT_TYPE_GOODSTROED_ACCOUNT = "12";//台账登记表
static $REPORT_TYPE_APPLY = "14";//申领单
static $REPORT_TYPE_PURCHUSE = "16";//采购单

static $REPORT_STATUS_CREATE = "1";//报表状态已创建
static $REPORT_STATUS_UNDERWAY = "2";//报表状态处理中
static $REPORT_STATUS_FINISH = "3";//报表状态已生成
static $REPORT_STATUS_DATANULL = "4";//报表状态无数据
static $REPORT_STATUS_INVUNUAUAL = "5";//报表状态异常

static $INVOICE_STATUS_FORECAST = "1";//出仓单已预报状态
static $INVOICE_STATUS_CREATE = "2";//出仓单已创建状态
static $INVOICE_STATUS_PICK = "3";//inspection出仓单已拣货状态
static $INVOICE_STATUS_INSPECTION = "4";//inspection出仓单已验货状态
static $INVOICE_STATUS_FINISH = "5";//inspection出仓单已出仓状态
static $INVOICE_STATUS_RECEIPT = "6";//出仓单已收货状态
static $INVOICE_STATUS_CANCEL = "7";//出仓单已取消状态

static $RECEIPT_STATUS_CREATE = "1";//入仓单创建状态
static $RECEIPT_STATUS_INSPECTION = "2";//inspection入仓单验货状态
static $RECEIPT_STATUS_FINISH = "3";//入仓单完成状态
static $RECEIPT_STATUS_CANCEL = "4";//入仓单取消状态

static $GOODSBATCH_STATUS_CREATE = "1";//货品批次创建状态
static $GOODSBATCH_STATUS_INSPECTION = "2";//货品批次验货状态
static $GOODSBATCH_STATUS_PUTAWAY = "3";//Putaway货品批次上架状态
static $GOODSBATCH_STATUS_FINISH = "4";//货品批次使用完状态


$reportModel = ReportDao::getInstance("WA000001");
$warehouseModel = WarehouseDao::getInstance("WA000001");
$clause = array(
    "status" => $REPORT_STATUS_CREATE
);
$reportData = $reportModel->queryListByConditionWithoutWarehouse($clause, 0, 100000);

$repDataByType = array();
foreach ($reportData as $v) {
    $data = json_decode($v['rep_data'], true);
    $warehouseModel = WarehouseDao::getInstance($data['warCode']);
    $dataList = array(
        'repCode' => $v['rep_code'],
        'name' => $v['rep_name'],
        'stime' => $data['stime'],
        'etime' => $data['etime'],
        'warCode' => $data['warCode'],
        'warName' => $warehouseModel->query()['war_name']
    );
    if (isset($data['spCode'])) {
        $dataList['spCode'] = $data['spCode'];
    }
    $repDataByType[$v['rep_type']][] = $dataList;
    unset($data);
    unset($dataList);
}
if (empty($reportData)) {
    echo "暂无可创建数据";
    return false;
}