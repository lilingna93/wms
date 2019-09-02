import Venus from '../../common/request.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    profile: {
      name: "",
      code: "",
      phone: "",
      warname: "",
      warehouse: "",
      address: "",
      postal: "",
      isexternal: ""
    },
    callcenter: "",
    appver: "",
    skuver: ""
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var userInfo = getApp().globalData.userInfo;
    var version = getApp().globalData.version;
    const isexternal = getApp().globalData.isexternal
    this.setData({
      profile: {
        name: userInfo["rname"],
        code: userInfo["worcode"],
        phone: userInfo["phone"],
        warname: userInfo["warname"],
        warehouse: userInfo["warehousecode"],
        address: userInfo["waraddress"],
        postal: userInfo["warpostal"]
      },
      callcenter: userInfo["callcenter"],
      appver: userInfo["appver"] + "." + version,
      skuver: userInfo["skuver"].toUpperCase(),
      isexternal: isexternal
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  onOpenBuyerListEvent: function() {
    wx.navigateTo({
      url: '../purchaseOrder/purchaseOrder',
    });
  },
  onOpenApplyListEvent: function() {
    wx.navigateTo({
      url: '../applyOrder/applyOrder',
    });
  },
  onOpenReturnListEvent: function() {
    wx.navigateTo({
      url: '../returnGoods/returnGoods',
    });
  },
  onCallCenterEvent: function() {
    wx.makePhoneCall({
      phoneNumber: this.data.callcenter
    })
  },
  onRestartEvent: function() {
    wx.showModal({
      title: '确认重新启动?',
      content: '重启后，商品字典将会更新。',
      showCancel: true,
      success: function(res) {
        if (res.confirm) {
          wx.clearStorageSync("SKU_VER");
          wx.clearStorageSync("SKU_DATA");
          wx.clearStorageSync("SKU_DATA_DICT");
          wx.clearStorageSync("MINI_SKU_VER");
          wx.clearStorageSync("MINI_SKU_DATA");
          wx.clearStorageSync("MINI_SKU_DATA_DICT");
          wx.clearStorageSync("P_CART_COUNT_DICT");
          wx.clearStorageSync("A_CART_COUNT_DICT");
          wx.clearStorageSync("SPU_DATA_DICT");

          wx.reLaunch({
            url: '../index/index',
          });
        }
      }
    });





  },
  onLogoutEvent: function() {
    wx.showModal({
      title: '确认退出?',
      content: '退出后，重新登录需重新激活手机号',
      showCancel: true,
      success: function(res) {
        console.log(res);
        if (res.confirm) {
          Venus.request(Venus.api.VENUS_WMS_AUTH_WXLOGOUT, {}, function(result) {
            if (true == result.success) {
              wx.clearStorageSync("SKU_VER");
              wx.clearStorageSync("SKU_DATA");
              wx.clearStorageSync("SKU_DATA_DICT");
              wx.clearStorageSync("MINI_SKU_VER");
              wx.clearStorageSync("MINI_SKU_DATA");
              wx.clearStorageSync("MINI_SKU_DATA_DICT");
              wx.clearStorageSync("P_CART_COUNT_DICT");
              wx.clearStorageSync("A_CART_COUNT_DICT");
              wx.clearStorageSync("SPU_DATA_DICT");
              wx.reLaunch({
                url: '../index/index',
              });
            }
          });
          return;

        }
      }
    });
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: '至味，让团餐进销存如此简单',
      path: '/pages/index/index',
      imageUrl: '/images/shareimage.jpg'
    }
  }
})