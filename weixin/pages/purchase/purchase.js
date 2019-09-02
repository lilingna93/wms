// pages/purchase/purchase.js
const common = require('../../common/common.js');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    //一级类目 二级类目索引值
    typeIndex: 0,
    cgIndex: 0,
    //sku数据(根据分类)
    skuData: [],
    //sku字典数据(根据code)
    skuDict: {},
    //二级分类数据
    cgData: [],
    //分类下的商品code(全部)
    allGoodsCodes: [],
    //分类下的商品code(分页)
    goodsCodes: [],
    //购物车商品数量字典数据(根据code)
    cartCountDict: {},
    //购物车商品总数
    totalCount: 0,
    //搜索关键词
    keyWord: ''
  },

  // 初始化sku数据
  initSku: function(event) {
    var typeIndex = this.data.typeIndex;
    var tCode = this.data.skuData[typeIndex].tCode;
    this.data.cgData = this.data.skuData[typeIndex][tCode];
    var cgIndex = this.data.cgIndex;
    this.data.allGoodsCodes = this.data.cgData[cgIndex].list;
    this.data.goodsCodes = this.data.allGoodsCodes.slice(0, 10);
    this.setData(this.data);
  },

  // 根据一级分类获取二级分类
  triggerCgData: function(event) {
    var tCode = event.currentTarget.dataset.code;
    var idx = event.currentTarget.dataset.index;
    this.data.typeIndex = idx;
    this.data.cgIndex = 0;
    this.data.cgData = this.data.skuData[idx][tCode];
    var cgIndex = this.data.cgIndex;
    this.data.allGoodsCodes = this.data.cgData[cgIndex].list;
    this.data.goodsCodes = this.data.allGoodsCodes.slice(0, 10);
    this.setData(this.data);
  },

  // 根据二级分类获取商品数据
  triggerGoodsData: function(event) {
    var idx = event.currentTarget.dataset.index;
    this.data.cgIndex = idx;
    this.data.allGoodsCodes = this.data.cgData[this.data.cgIndex].list;
    this.data.goodsCodes = this.data.allGoodsCodes.slice(0, 10);
    this.setData(this.data);
  },

  // 购物车加操作
  onSkuCountAddEvent: function(event) {
    common.onSkuCountAddEvent(this, event);
  },

  // 购物车减操作
  onSkuCountSubEvent: function(event) {
    common.onSkuCountSubEvent(this, event)
  },

  // 购物车编辑数量
  onSkuCountEditEvent: function(event) {
    common.onSkuCountEditEvent(this, event)
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

  //跳转至采购车页
  linkToCar() {
    wx.navigateTo({
      url: '../purchaseCart/purchaseCart',
    })
  },

  // 滑动到底部触发(模拟下拉加载)
  toLower(e) {
    var codes = this.data.goodsCodes;
    var allCodes = this.data.allGoodsCodes;
    var len = codes.length;
    var length = allCodes.length;
    codes = codes.concat(allCodes.slice(len, len + 10));
    if (len > length) {
      codes = codes.concat(allCodes.slice(len, length));
      return;
    }
    this.data.goodsCodes = codes;
    this.data.allGoodsCodes = allCodes;
    this.setData(this.data);
  },

  //输入关键词实时更新
  inputWords: function(e) {
    this.data.keyWord = e.detail.value;
  },

  //跳转到搜索结果页
  searchGoods: function(e) {
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
      url: '../purchaseSearch/purchaseSearch?keyWord=' + key,
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    //获取sku数据
    this.data.skuData = wx.getStorageSync('SKU_DATA');
    this.data.skuDict = wx.getStorageSync('SKU_DATA_DICT');
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
    this.initSku();
    if(getApp().globalData.isexternal==2) {
      wx.setNavigationBarTitle({
        title: ' 采购（外部）'//页面标题为路由参数
      })
      wx.setNavigationBarColor({
        frontColor: '#ffffff',//前景颜色值，包括按钮、标题、状态栏的颜色，仅支持 #ffffff 和 #000000
        backgroundColor: '#000000',
        animation: {
          duration: 1000,
          timingFunc: 'easeInOut'
        }
      })
    }else {
      wx.setNavigationBarTitle({
        title: ' 采购'//页面标题为路由参数
      })
      wx.setNavigationBarColor({
        frontColor: '#ffffff',//前景颜色值，包括按钮、标题、状态栏的颜色，仅支持 #ffffff 和 #000000
        backgroundColor: '#2d8cf0',
        animation: {
          duration: 1000,
          timingFunc: 'easeInOut'
        }
      })
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    common.initBase('P_');
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    this.updateTotalCount();
    this.setData(this.data);
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