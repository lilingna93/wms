// pages/applySearch/applySearch.js
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    cartCountDict: {},
    keyWord: "",
    goodsList:[]
  },
  // 购物车加操作
  onSkuCountAddEvent: function (event) {
    common.onSkuCountAddEvent(this, event);
  },

  // 购物车减操作
  onSkuCountSubEvent: function (event) {
    common.onSkuCountSubEvent(this, event)
  },

  // 购物车编辑数量
  onSkuCountEditEvent: function (event) {
    common.onSkuCountEditEvent(this, event)
  },
  // 请求库存列表数据
  requestData: function (params) {
    var I = this;;
    Venus.request(Venus.api.VENUS_WMS_APPLY_GOODS_LIST, params, function (result) {
      if (true == result.success) {
        I.data.goodsList = result.data.list;
        I.setData({
          goodsList: I.data.goodsList,
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var keyWord = getCurrentPages()[0].data.keyWord;
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    var data = { "spName": keyWord }
    this.requestData(data);
    this.setData(this.data);
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