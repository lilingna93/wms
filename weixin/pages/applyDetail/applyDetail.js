// pages/applyDetail/applyDetail.js
import Venus from '../../common/request.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    orderDetail: {},
    calcelModal: false,
    options: ''
  },
  cancelTap: function () {
    this.setData({
      calcelModal: true
    })

  },
  handleClose: function () {
    this.setData({
      calcelModal: false
    })
  },
  handleCancel() {
    var that = this;
    var param = {
      invCode: that.data.options.code,
    };
    that.setData({
      calcelModal: false
    })
    Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_CANCEL, param, function (result) {
      console.log(result)
      if (result.success) {
        that.loadData();
      }
    });
  },
  goReturnGoods:function(e) {
    const index = e.currentTarget.dataset.index;
    const igoCode = this.data.orderDetail.list[index].igoCode;
    const skCode = this.data.orderDetail.list[index].skCode;
    const skName = this.data.orderDetail.list[index].skName;
    const spBrand = this.data.orderDetail.list[index].spBrand;
    const skNorm = this.data.orderDetail.list[index].skNorm;
    const spCunit = this.data.orderDetail.list[index].spCunit;
    const spCode = this.data.orderDetail.list[index].spCode;
    const skUnit = this.data.orderDetail.list[index].skUnit;
    const skCount = this.data.orderDetail.list[index].skCount;
    const count = this.data.orderDetail.list[index].count;
    const invcode = this.data.options.code;
    let params = {
      "igoCode":igoCode,
      "skCode": skCode,
      "skName": skName,
      "spBrand": spBrand,
      "skNorm": skNorm,
      "spCunit": spCunit,
      "skUnit": skUnit,
      "spCode": spCode,
      "skCount": skCount,
      "count":count,
      "invcode": invcode
    }
    let param = JSON.stringify(params)
    console.log(params);
    wx.navigateTo({
      url: '../applyGoodsDetail/applyGoodsDetail?params=' + param,
    });
  },

  /**
   * 生命周期函数--监听页面加载
   */
  loadData: function () {
    var that = this;
    var userInfo = getApp().globalData.userInfo;
    that.setData({
      //baseImgPath: wx.env.USER_DATA_PATH + "/sku/spuimg/",
      phoneNumber: userInfo["callcenter"],
    }
    )
    var param = {
      invCode: that.data.options.code
    }
    Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_DETAIL, param, function (result) {
      //无数据的情况
      if (result.data.list.length == 0) {
        wx.navigateBack({
          delta: 1
        })
        return;
      }
      that.setData({
        orderDetail: result.data
      })
    });
  },
  onLoad: function (options) {
    this.setData({
      options: options
    })
    this.loadData();
  },
  callContact: function () {
    wx.makePhoneCall({
      phoneNumber: this.data.phoneNumber
    })
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
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    var that = this;
    var pages = getCurrentPages(); // 当前页面  
    var beforePage = pages[pages.length - 2]; // 前一个页面  
    beforePage.onLoad(); // 执行前一个页面的onLoad方法  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

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