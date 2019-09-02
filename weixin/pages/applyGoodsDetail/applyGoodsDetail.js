// pages/applySearch/applySearch.js
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isReturnOper: false,
    goodsCode: "",//货品编号
    goodsCount: 0,//退货数量
    goods: {
      name: "",
      brand: "",
      norm: "",
      price: "",
      unit: "",
      count: "",
      rcount: ""
    },
    invcode:""
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var params = options.params;
    params = JSON.parse(params);
    this.setData(
      {
        goods: {
          name: params["skName"],
          brand: params["spBrand"],
          norm: params["skNorm"],
          price: params["skPrice"],
          unit: params["skUnit"],
          count: parseFloat(params["count"]),
        },
        goodsCode: params["igoCode"] || "GOODSCODE",
        invcode: params["invcode"]
      }
    )
  },


  /**
     * 打开退货开关
     */
  onClickEventForReturn: function (event) {
    this.setData({
      isReturnOper: event.detail.value
    })
  },
  
  onClickEventForSubmit: function (event) {
    var I = this;
    wx.showModal({
      title: '确认',
      content: '确认取消该货品申领吗？',
      success: function (res) {
        if (res.confirm) {
          I.submitForCreateRetureOrder();
        }
      }
    })
  },
  submitForCreateRetureOrder() {
    var I = this;
    var params = {
      "igocode": I.data.goodsCode
    };
    Venus.request(Venus.api.VENUS_WMS_APPLY_RETURN_GOODS, params, function (result) {
      if (true == result.success) {
        wx.showModal({
          title: '取消申领成功',
          content: '取消申领的货品已经还原到库存中。',
          showCancel: false,
          success: function (res) {
            wx.navigateBack({
              delta: 1
            })
          }
        })
      } else {
        wx.showModal({
          title: '提示',
          content: result.message,
          showCancel: false
        })
      }
    });

  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },
  /**
     * 生命周期函数--监听页面卸载
     */
  onUnload: function () {
    var I = this;
    var pages = getCurrentPages(); // 当前页面  
    var beforePage = pages[pages.length - 2]; // 前一个页面  
    beforePage.onLoad({ code: I.data.invcode}); // 执行前一个页面的onLoad方法  
  },
  /**
  * 用户点击右上角分享
  */
  onShareAppMessage: function () {
    return {
      title: '至味，让团餐进销存如此简单',
      path: '/pages/index/index',
      imageUrl: '/images/shareimage.jpg'
    }
  }
})