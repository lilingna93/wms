// pages/apply/apply.js
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    typeIndex:0,
    skuData:[],
    goodsList:[],
    totalCount:0,
    cartCountDict:{},
    stockCountDict:{},
    isexternal: ""
  },
  // 请求库存列表数据
  requestData: function(params){
    var I = this;
    Venus.request(Venus.api.VENUS_WMS_APPLY_GOODS_LIST, params, function (result) {
      if (true == result.success) {
        I.data.goodsList=result.data.list;
        I.setData({ goodsList: I.data.goodsList })
        for(var i=0;i<I.data.goodsList.length;i++){
          var code = I.data.goodsList[i].spCode;
          var stock = I.data.goodsList[i].spCount;
          var count=I.data.cartCountDict[code];
          if(count>stock){
            common.setCountBySkuCode(code, stock);
            I.data.cartCountDict[code] = stock;
            if (stock == 0) {
              delete I.data.cartCountDict[code];
            }
            I.setData({
              cartCountDict: I.data.cartCountDict
            })
          }
        }
      } else {
        wx.showModal({
          title: '提示',
          content: result.message,
          showCancel: false
        })
      }
    });
  },
  // 根据一级分类获取商品数据
  triggerGoodsData:function(e){
    var tCode = e.currentTarget.dataset.code;
    var idx = e.currentTarget.dataset.index;
    this.data.typeIndex = idx;
    var data={
      "pageCurrent": 0, 
      "tCode": tCode, 
      "cgCode": 0
    }
    this.requestData(data);
    this.setData({typeIndex:this.data.typeIndex});
  },
  // 购物车加操作
  onSkuCountAddEvent: function (event) {
    common.onSkuCountAddEvent(this, event);
  },

  // 购物车减操作
  onSkuCountSubEvent: function (event) {
    common.onSkuCountSubEvent(this, event);
  },

  // 购物车编辑数量
  onSkuCountEditEvent: function (event) {
    common.onSkuCountEditEvent(this, event);
  },
  // 更新购物车总数量
  updateTotalCount() {
    var countDict = this.data.cartCountDict;
    var count = 0;
    for (var key in countDict) {
      count = common.accAdd(count, parseFloat(countDict[key]))
    }
    this.data.totalCount = count;
  },
  // 跳转至申领车页
  linkToCar() {
    //将当前已经记录库存数据写入缓存
    wx.navigateTo({
      url: '../applyCart/applyCart',
    })
  },
  //输入关键词实时更新
  inputWords: function (e) {
    this.data.keyWord = e.detail.value;
  }, 
  //跳转到搜索结果页
  searchGoods: function (e) {
    var key = this.data.keyWord;
    if (key == '') {
      wx.showModal({
        title: '提示',
        content: '请输入商品名称',
        showCancel: false
      });
      return false;
    }
    wx.navigateTo({
      url: '../applySearch/applySearch?keyWord=' + key,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.data.skuData = wx.getStorageSync("MINI_SKU_DATA");
    const isexternal = getApp().globalData.isexternal
    this.setData({
      skuData: this.data.skuData,
      isexternal: isexternal
    })
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
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
    common.initBase('A_');
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    var typeIndex = this.data.typeIndex;
    var tCode = this.data.skuData[typeIndex].tCode;
    var data = { "pageCurrent": 0, "tCode": tCode, "cgCode": 0 }
    this.requestData(data);
    this.updateTotalCount();
    this.setData({
      totalCount: this.data.totalCount,
      cartCountDict: this.data.cartCountDict
    });
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