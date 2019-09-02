// import axios from 'axios';
// import Qs from 'qs';
// import Vue from 'vue'
/*
* axios封装
* */
var instance = axios.create({});
instance.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
const baseUrl = {
  host1: 'https://' + config.host + '/wms/service/api',
  host2: 'https://' + config.host + '/wms/service/file',
  host3: 'https://' + config.host + '/wms/service/filezip',
}

// 添加请求拦截器
axios.interceptors.request.use(
  config => {
    //post传参序列化
    if (config.method === 'post') {
      // config.data['token'] = '3dfeb4f20158697a5a96837898ab590a';
      config.data = Qs.stringify(config.data);
    }
    return config;
  },
  err => {
    return Promise.reject(err);
  });

// http response 添加响应拦截器
axios.interceptors.response.use(
  response => {
    if (response.data.error == 110) {
      window.location.href = "//" + config.host + "/manage/login";
    }
    return response;
  },
  error => {
    if (error.response) {
      switch (error.response.status) {
        case 404:

      }
    }
    return Promise.reject(error.response.data)   // 返回接口返回的错误信息
  });

/**
 * post 请求方法
 * @param url
 * @param data
 * @returns {Promise}
 */
export function post(service, data = {}) {
  var _url = baseUrl.host1;
  return new Promise((resolve, reject) => {
    axios.post(_url, data).then(response => {
      var res = response.data;
      if (res.error == 0) {
        if (!res.success) {
          Vue.prototype.$Message.warning({
            content: res.message,
            duration: 3,
            closable: true
          });
        }
      } else {
        Vue.prototype.$Message.warning({
          content: res.msg,
          duration: 3,
          closable: true
        });
      }
      resolve(response.data);
    }, err => {
      reject(err);
    })
  })
}

export const Api = {
  "VENUS_WMS_SPU_SPU_SEARCH": "venus.wms.spu.spu.search",
  "VENUS_WMS_SPU_PERCENT_IMPORT": "venus.wms.spu.percent.import",
  "VUNUS_WMS_SPU_SPRICE_IMPORT": "venus.wms.spu.sprice.import",
  "VUNUS_WMS_SPU_BPRICE_IMPORT": "venus.wms.spu.bprice.import",
  "VUNUS_WMS_SPU_SUPPLIER_IMPORT": "venus.wms.spu.supplier.import",
  "VENUS_WMS_SPU_SPU_EXPORT": "venus.wms.spu.spu.export",
  "VENUS_WMS_SKU_SKU_SEARCH": "venus.wms.sku.sku.search",
  "VENUS_WMS_SKU_STATUS_ONLINE": "venus.wms.sku.status.online",
  "VENUS_WMS_SKU_STATUS_OFFLINE": "venus.wms.sku.status.offline",
  "VUENS_WMS_SKU_SKU_PUBLISH": "venus.wms.sku.sku.publish",
  "VENUS_WMS_SUPPLIER_SUP_SEARCH": "venus.wms.supplier.sup.search",
  "VENUS_WMS_SUPPLIER_SUP_ADD": "venus.wms.supplier.sup.add",
  "VENUS_WMS_SUPPLIER_SUP_UPDATE": "venus.wms.supplier.sup.update",
  "VENUS_WMS_SUPPLIER_SUP_DELETE": "venus.wms.supplier.sup.delete",
  "VENUS_WMS_ORDER_ORD_EXPORT": "venus.wms.order.ord.export",
  "VENUS_WMS_ORDER_ORD_INV_EXPORT": "venus.wms.order.ord.inv.export",
  "VENUS_WMS_ORDER_STATUS_UPDATE": "venus.wms.order.status.update",
  "VENUS_WMS_ORDER_ORD_SEARCH": "venus.wms.order.ord.search",
  "VENUS_WMS_ORDER_ORD_CANCEL": "venus.wms.order.ord.cancel",
  "VENUS_WMS_ORDER_GOODS_ADD": "venus.wms.order.goods.add",
  "VENUS_WMS_ORDER_SKU_SEARCH": "venus.wms.order.sku.search",
  "VENUS_WMS_ORDER_SKUCOUNT_UPDATE": "venus.wms.order.skucount.update",
  "VENUS_WMS_ORDER_MARK_UPDATE": "venus.wms.order.mark.update",
  "VENUS_WMS_ORDER_DETAILEDLIST_EXPORT": "venus.wms.order.detailedList.export",
  "VENUS_WMS_ORDER_DETAILS_LIST": "venus.wms.order.details.list",
  "VENUS_WMS_ORDER_GOODS_DELETE": "venus.wms.order.goods.delete",
  "VENUS_WMS_ORDER_USER_LIST": "venus.wms.order.user.list",
  "VENUS_WMS_ORDER_USER_ADD": "venus.wms.order.user.add",
  "VENUS_WMS_ORDER_USER_UPDATE": "venus.wms.order.user.update",
  "VENUS_WMS_ORDER_USER_DELETE": "venus.wms.order.user.delete",
  "VENUS_WMS_WORKER_WORKER_LIST": "venus.wms.worker.worker.list",
  "VENUS_WMS_WORKER_WORKER_ADD": "venus.wms.worker.worker.add",
  "VENUS_WMS_WORKER_WORKER_UPDATE": "venus.wms.worker.worker.update",
  "VENUS_WMS_WORKER_WORKER_DELETE": "venus.wms.worker.worker.delete",
  "VENUS_WMS_ORDER_NOTTHEIROWN_ORD_EXPORT": "venus.wms.order.nottheirown.ord.export",


  "VENUS_WMS_AUTH_LOGOUT": 'venus.wms.auth.logout',
  "VENUS_WMS_RECEIPT_RECEIPT_GET_SKU": 'venus.wms.receipt.receipt.get.sku',
  "VENUS_WMS_RECEIPT_RECEIPT_CREATE": 'venus.wms.receipt.receipt.create',
  "VENUS_WMS_RECEIPT_RECEIPT_SEARCH": 'venus.wms.receipt.receipt.search',
  "VENUS_WMS_RECEIPT_RECEIPT_TRACE_SEARCH": 'venus.wms.receipt.receipt.trace.search',
  "VENUS_WMS_RECEIPT_RECEIPT_DELETE": 'venus.wms.receipt.receipt.delete',
  "VENUS_WMS_RECEIPT_RECEIPT_DETAIL": 'venus.wms.receipt.receipt.detail',
  "VENUS_WMS_RECEIPT_RECEIPT_GOODS_CREATE": 'venus.wms.receipt.receipt.goods.create',
  "VENUS_WMS_RECEIPT_RECEIPT_GOODS_DELETE": 'venus.wms.receipt.receipt.goods.delete﻿﻿',
  "VENUS_WMS_RECEIPT_RECEIPT_GOODS_COUNT_UPDATE": 'venus.wms.receipt.receipt.goods.count.update',
  "VENUS_WMS_INVOICE_INVOICE_GET_SKU": 'venus.wms.invoice.invoice.get.sku',
  "VENUS_WMS_INVOICE_INVOICE_CREATE": 'venus.wms.invoice.invoice.create',
  "VENUS_WMS_INVOICE_INVOICE_SEARCH": 'venus.wms.invoice.invoice.search',
  "VENUS_WMS_INVOICE_INVOICE_TRACE_SEARCH": 'venus.wms.invoice.invoice.trace.search',
  "VENUS_WMS_INVOICE_INVOICE_DELETE": 'venus.wms.invoice.invoice.delete',
  "VENUS_WMS_INVOICE_INVOICE_DETAIL": 'venus.wms.invoice.invoice.detail',
  "VENUS_WMS_INVOICE_INVOICE_CONFIRM": 'venus.wms.invoice.invoice.confirm﻿﻿﻿﻿﻿',
  "VENUS_WMS_INVOICE_INVOICE_IMPORT": 'venus.wms.invoice.invoice.import',
  "VENUS_WMS_INVOICE_INVOICE_GOODS_COUNT_UPDATE": 'venus.wms.invoice.invoice.goods.count.update﻿﻿﻿﻿﻿',
  "VENUS_WMS_INVOICE_INVOICE_GOODS_DELETE": 'venus.wms.invoice.invoice.goods.delete﻿﻿﻿﻿﻿﻿﻿',
  "VENUS_WMS_INVOICE_INVOICE_GOODS_CREATE": 'venus.wms.invoice.invoice.goods.create',
  "VENUS_WMS_GOODS_GOODS_SEARCH": 'venus.wms.goods.goods.search',
  "VENUS_WMS_GOODS_GOODS_STORED": 'venus.wms.goods.goods.stored',
  "VENUS_WMS_TASK_TASK_SEARCH": 'venus.wms.task.task.search',
  "VENUS_WMS_REPORT_REPORT_SEARCH": 'venus.wms.report.report.search',
  "VENUS_WMS_REPORT_REPORT_CREATE": 'venus.wms.report.report.create',
  "VENUS_WMS_REPORT_REPORT_DELETE": 'venus.wms.report.report.delete',
  "VENUS_WMS_REPORT_REPORT_MONTH_SPUTYPE": 'venus.wms.report.report.month.sputype',
  "VENUS_WMS_TASK_TASK_CANCEL": 'venus.wms.task.task.cancel',


  "VENUS_WMS_ORDERTASK_ORDER_SEARCH": 'venus.wms.ordertask.order.search',
  "VENUS_WMS_ORDERTASK_TASK_SEARCH": 'venus.wms.ordertask.task.search',
  "VENUS_WMS_ORDERTASK_TASK_DELETE": 'venus.wms.ordertask.task.delete',
  "VENUS_WMS_ORDERTASK_ORDER_DETAIL": 'venus.wms.ordertask.order.detail',
  "VENUS_WMS_ORDERTASK_GOODS_UPDATE": 'venus.wms.ordertask.goods.update',
  "VENUS_WMS_ORDERTASK_TASK_CREATE": 'venus.wms.ordertask.task.create',
  "VENUS_WMS_ORDERTASK_SUP_BPRICE_UPDATE": 'venus.wms.ordertask.sup.bprice.update',
  "VENUS_WMS_ORDERTASK_OWN_INV_CREATE": 'venus.wms.ordertask.own.inv.create',
  "VENUS_WMS_ORDERTASK_SUP_INV_CREATE": 'venus.wms.ordertask.sup.inv.create',
  "VENUS_WMS_ORDERTASK_EXPORT_SUP": 'venus.wms.ordertask.export.sup',
  "VENUS_WMS_ORDERTASK_TASK_DETAIL": 'venus.wms.ordertask.task.detail',


  "VENUS_WMS_RETURN_RETURNGOODS_SEARCH": 'venus.wms.return.returngoods.search',
  "VENUS_WMS_RETURN_RETURNGOODS_CONFIRM": 'venus.wms.return.returngoods.confirm',
  "VENUS_WMS_RETURN_RETURNGOODS_REJECT": 'venus.wms.return.returngoods.reject',
  "VENUS_WMS_ORDER_ORD_MARK_UPDATE": 'venus.wms.order.ord.mark.update',
  "VENUS_WMS_RECEIPT_REC_IMPORT": 'venus.wms.receipt.rec.import',
  "VENUS_WMS_RECEIPT_REC_EXPORT": 'venus.wms.receipt.rec.export',
  "VENUS_WMS_GOODS_GOODS_EXPORT": 'venus.wms.goods.goods.export',
  "VENUS_WMS_INVOICE_INV_EXPORT": 'venus.wms.invoice.inv.export',
  "VENUS_WMS_RETURNTASK_RETURNTASK_SEARCH": 'venus.wms.returntask.returntask.search',
  "VENUS_WMS_RETURNTASK_DETAILS_LIST": 'venus.wms.returntask.details.list',
  "VENUS_WMS_RETURNTASK_RETURNGOODS_UPDATE": 'venus.wms.returntask.returngoods.update',
  "VENUS_WMS_RETURNTASK_RETURNTASK_CONFIRM": 'venus.wms.returntask.returngoods.confirm',
  "VENUS_WMS_RETURNTASK_RETURNTASK_REJECT": 'venus.wms.returntask.returngoods.reject',
  "VENUS_WMS_ORDERBATCH_OWNLIST_EXPORT": 'venus.wms.orderbatch.ownlist.export',
  "VENUS_WMS_ORDERBATCH_OWNLIST_WAREHOUSE_EXPORT": 'venus.wms.orderbatch.ownlist.warehouse.export',
  "VENUS_WMS_ORDERBATCH_OWNLIST_EXPORTALL": 'venus.wms.orderbatch.ownlist.exportall',
  "VENUS_WMS_ORDERBATCH_OWNLIST_FINISH": 'venus.wms.orderbatch.ownlist.finish',
  "VENUS_WMS_ORDERBATCH_SUPLIST_EXPORTALL": 'venus.wms.orderbatch.suplist.exportall',
  "VENUS_WMS_ORDERBATCH_ORDER_EXPORT": 'venus.wms.orderbatch.order.export',
  "VENUS_WMS_ORDERBATCH_ORDERS_EXPORT": 'venus.wms.orderbatch.orders.export',
  "VENUS_WMS_GOODS_GOODS_RETURN": 'venus.wms.goods.goods.return',
  "VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS": 'venus.wms.returntask.returngoods',
  "VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_SELFSUPPORT_EXPORT": 'venus.wms.returntask.returngoods.selfsupport.export',
  "VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_DIRECTMINING_EXPORT": 'venus.wms.returntask.returngoods.directmining.export',
  "VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_ISSUP_EXPORT": 'venus.wms.returntask.returngoods.issup.export',


  "VENUS_WMS_STATUS_GWARNING_IMPORT":"venus.wms.status.gwarning.import",
  "VENUS_WMS_STATUS_GWARNING_EXPORT":"venus.wms.status.gwarning.export",
  "VENUS_WMS_STATUS_GWARNING_EMAIL":"venus.wms.status.gwarning.email",

  "VENUS_WMS_SKUEXTERNAL_ESKU_CUSTOMER":"venus.wms.skuexternal.esku.customer",
  "VENUS_WMS_SKUEXTERNAL_ESKU_LIST":"venus.wms.skuexternal.esku.list",
  "VENUS_WMS_SKUEXTERNAL_ESKU_IMPORT":"venus.wms.skuexternal.esku.import",
  "VENUS_WMS_SKUEXTERNAL_ESKU_EXPORT":"venus.wms.skuexternal.esku.export",
  "VENUS_WMS_SKUEXTERNAL_STATUS_OFFLINE":"venus.wms.skuexternal.status.offline",
  "VENUS_WMS_SKUEXTERNAL_STATUS_ONLINE":"venus.wms.skuexternal.status.online",
  "VENUS_WMS_ORDERBATCH_ORDER_FINISH_EXPORT":"venus.wms.orderbatch.order.finish.export",
  "VENUS_WMS_INVOICE_INVOICE_SEARCHE_LIKE":"venus.wms.invoice.invoice.search.like",
  "VENUS_WMS_GOODS_GOODS_IGOODSENT":"venus.wms.goods.goods.igoodsent",
  "VENUS_WMS_REPORTDOWNLOAD_REPORTDOWNLOAD_LIST":"venus.wms.reportdownload.reportdownload.list",
  "VENUS_WMS_REPORTDOWNLOAD_DOWNLOAD_FILE":"venus.wms.reportdownload.download.file",

  "VENUS_WMS_SKUEXTERNAL_AUTO_IMPORT":"venus.wms.skuexternal.auto.import",
  "VENUS_WMS_SKUEXTERNAL_AUTO_EXPLODE":"venus.wms.skuexternal.auto.explode",
  "VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_SEARCH":"venus.wms.skuexternal.xinfadi.sku.search",
  "VENUS_WMS_ORDERBATCH_SUPLIST_YOGURT_EXPORTALL":"venus.wms.orderbatch.suplist.yogurt.exportall",
  "VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_EXPORT":"venus.wms.skuexternal.xinfadi.sku.export",


}
export default {
  post, baseUrl
}
