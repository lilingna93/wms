//服务器地址
const host = "wms.shijijiaming.cn";
const service = "https://" + host + "/wms/service/mapi";
//API定义
const apis = {
  //账户相关
  VENUS_WMS_AUTH_WXLOGIN:   "venus.wms.auth.wxlogin",
  VENUS_WMS_AUTH_WXACTIVE:  "venus.wms.auth.wxactive",
  VENUS_WMS_AUTH_WXLOGOUT:  "venus.wms.auth.wxlogout",

  
  //sku data
  VENUS_WMS_SKU_LATESTSKU: "venus.wms.sku.latestsku",
  //拉取最新的小仓SKU数据
  VENUS_WMS_MINI_SKU_LATESTSKU: "venus.wms.sku.latestminisku",
  //采购下单
  VENUS_WMS_PURCHASE_ORDER_CREATE:"venus.wms.purchase.order.create",
  //申领首页商品列表
  VENUS_WMS_APPLY_GOODS_LIST:"venus.wms.apply.goods.list",
  //申领下单
  VENUS_WMS_APPLY_ORDER_CREATE:"venus.wms.apply.order.create",
  //采购单
  VENUS_WMS_PURCHASE_ORDER_LIST: "venus.wms.purchase.order.list",
  VENUS_WMS_PURCHASE_ORDER_DETAIL:"venus.wms.purchase.order.detail",
  VENUS_WMS_PURCHASE_ORDER_STATUS_UPDATE:"venus.wms.purchase.order.status.update",
  VENUS_WMS_PURCHASE_ORDER_CANCEL:"venus.wms.purchase.order.cancel",
  VENUS_WMS_PURCHASE_ORDER_SPLIT_SEARCH:"venus.wms.purchase.order.split.search",
  VENUS_WMS_PURCHASE_ORDER_GOODS_RECEIPT:"venus.wms.purchase.order.goods.receipt",
  VENUS_WMS_PURCHASE_ORDER_DELETE: "venus.wms.purchase.order.delete",
  //采购车列表
  VENUS_WMS_PURCHASE_PURCHASING_CAR_LIST:"venus.wms.purchase.purchasing.car.list",
  //申领单
  VENUS_WMS_APPLY_ORDER_LIST: "venus.wms.apply.order.list",
  VENUS_WMS_APPLY_ORDER_DETAIL:"venus.wms.apply.order.detail",
  VENUS_WMS_APPLY_ORDER_CANCEL:"venus.wms.apply.order.cancel",
  //申领车列表
  VENUS_WMS_APPLY_APPLY_CAR_LIST:"venus.wms.apply.apply.car.list",
  //退回已经申领货品
  VENUS_WMS_APPLY_RETURN_GOODS: "venus.wms.apply.return.goods",
  //提交退货申请
  VENUS_WMS_RETURN_RETUENGOODS_CREATE: "venus.wms.return.returngoods.create",
  VENUS_WMS_RETURN_RETUENGOODS_SEARCHS: "venus.wms.return.returngoods.searchs",
  //下载链接
  ORDEREXPORT_PURCHASE_ORDER_EXPORT:"orderexport.purchase.order.export"
}
//网络请求
function request(api, data, success, fail) {
  //console.log({ service: api, data: data });
  var header = {
    'Content-type': 'application/x-www-form-urlencoded'
  };
  if (getApp().globalData.sess) {
    header["Cookie"] = 'PHPSESSID=' + getApp().globalData.sess;
  }
  //console.log(getApp().globalData.sess);
  wx.showNavigationBarLoading();
  wx.request({
    url: service,
    method: 'POST',
    dataType: 'json',
    data: {
      service: api,
      data: JSON.stringify(data)
    },
    header: header,
    success: function(res) {
      wx.hideNavigationBarLoading();
      console.log(res);
      if(res.data.success==false){
        wx.showModal({
          title: '提示',
          content: res.data.message,
          showCancel: false
        });
      }
      if (res.statusCode == 200) {
        var result = res.data;
        if (result.error == 0) {
          getApp().globalData.sess = result.sess;
          success && success(result);
        } else {
          //业务错误
          if (fail) {
            fail(result);
          } else {
            wx.showModal({
              title: '提示',
              content: result.msg,
              showCancel: false
            });
          }
        }
      } else {
        //服务错误
        wx.showModal({
          title: '提示',
          content: '当前服务器异常,请稍后尝试！',
          showCancel: false
        });
      }
    },
    fail: function(result) {
      wx.hideNavigationBarLoading();
      //网络错误
      wx.showModal({
        title: '提示',
        content: '当前网络环境不稳定，请稍后尝试！',
        showCancel: false
      });
    },
    complete: function(res) {
      wx.hideNavigationBarLoading();
    }
  });
}





module.exports = {
  host: host,
  request: request,
  api: apis,
  service: service
}