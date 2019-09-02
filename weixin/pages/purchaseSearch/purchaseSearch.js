// pages/purchaseSearch/purchaseSearch.js
const common = require('../../common/common.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    cartCountDict:{},
    keyWord:"",
    skuDict:{},
    goodsCodes:[],

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
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var keyWord = getCurrentPages()[0].data.keyWord;
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    this.data.skuDict = wx.getStorageSync('SKU_DATA_DICT');
    for (var item in this.data.skuDict){
      if (!(/^[\u4e00-\u9fa5]+$/).test(keyWord)){
        if (this.data.skuDict[item].spAbName.indexOf("#"+keyWord) != -1){
          this.data.goodsCodes.push(item);
        }
      }else{
        if (this.data.skuDict[item].spName.indexOf(keyWord) != -1) {
          this.data.goodsCodes.push(item);
        }
      }
      
    }
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