// pages/purchaseDetail/purchaseDetail.js
import Venus from '../../common/request.js'
const { $Message } = require('../../iview/base/index');
const common = require('../../common/common.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    orderDetail:{},
    phoneNumber:'',
    calcelModal:false,
    options:'',
    editModal:false,
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
  },
  copyText: function (e) {
    wx.setClipboardData({
      data: `https://${Venus.host}/wms/service/externalApi?service=${Venus.api.ORDEREXPORT_PURCHASE_ORDER_EXPORT}&code=${e.currentTarget.dataset.text}`,
      success: function (res) {
        wx.getClipboardData({
          success: function (res) {
            wx.showToast({
              title: '复制成功'
            })
          }
        })
      }
    })
  },
  cancelTap:function() {
    this.setData({
      calcelModal:true
    })
   
  },
  handleClose:function(){
    this.setData({
      calcelModal: false
    })
  },
  handleCancel() {
    var that = this;
    var param = {
      oCode: that.data.orderDetail.oCode,
      oStatus: 3
    };
    that.setData({
      calcelModal: false
    })
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_CANCEL, param, function (result) {
      if (result.success){
        that.loadData();
      }
    });
  },
  editTap:function(){
    this.setData({
      editModal: true
    })
  },
  handleEdit:function() {
    var that = this;
    var param = {
      oCode: that.data.orderDetail.oCode,
    };
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_DELETE, param, function (result) {
      common.clearCountStorage()
      for (var i = 0; i < that.data.orderDetail.list.length;i++){
        common.setCountBySkuCode(that.data.orderDetail.list[i].skCode, that.data.orderDetail.list[i].skNum)
      }
      if(result.success){
        wx.switchTab({
          url: '../purchase/purchase' 
        });
      }else{
        $Message({
          content: '修改订单失败',
          type: 'warning'
        });
      }
    });
  
  },
  closeEdit:function() {
    this.setData({
      editModal: false
    })
  },
  callContact: function () {
    wx.makePhoneCall({
      phoneNumber: this.data.phoneNumber
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  loadData:function() {
    var that = this;
    var userInfo = getApp().globalData.userInfo;
    that.setData({
      //baseImgPath: wx.env.USER_DATA_PATH + "/sku/spuimg/",
      phoneNumber: userInfo["callcenter"]
    }
    )
    var param = {
      oCode: that.data.options.code
    };
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_DETAIL, param, function (result) {
      that.setData({
        orderDetail: result.data
      })
    });
  },
  onLoad: function (options) {
    if(options){
      this.setData({
        options: options
      })
    }
    this.loadData();
  },
  goodsOperation() {
    var params = {
      "code": this.data.orderDetail.oCode,
      "oStatus": this.data.orderDetail.oStatus
    }
    var param = JSON.stringify(params)
    wx.navigateTo({
      url: '../orderAcceptance/orderAcceptance?params=' + param,
    })
  },
  goReturnGoods:function(e) {
    const index = e.currentTarget.dataset.index;
    const skCode = this.data.orderDetail.list[index].skCode;
    const spName = this.data.orderDetail.list[index].spName;
    const skBrand = this.data.orderDetail.list[index].skBrand;
    const skNorm = this.data.orderDetail.list[index].skNorm;
    const skPrice = this.data.orderDetail.list[index].skPrice;
    const skNum = this.data.orderDetail.list[index].skNum;
    const skUnit = this.data.orderDetail.list[index].skUnit;
    const gCode = this.data.orderDetail.list[index].goodscode;
    const skCount = this.data.orderDetail.list[index].skCount;
    let params = {
      "skCode": skCode,
      "spName": spName,
      "skBrand": skBrand,
      "skNorm": skNorm,
      "skPrice": skPrice,
      "skNum": skNum,
      "skUnit": skUnit,
      "gCode": gCode,
      "skCount": skCount
    }
    let param = JSON.stringify(params)
    wx.navigateTo({
      url: '../goodsDetail/goodsDetail?params=' + param,
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