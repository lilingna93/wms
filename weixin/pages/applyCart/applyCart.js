// pages/applyCart/applyCart.js
const utils = require('../../utils/util.js');
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    cartCountDict: {},
    cartCodes:[],
    totalChecked: '',
    singleChecked: '',
    totalCount:0,
    rooms: [],
    roomsIdx: -1,
    cartList:[]
  },
  bindRoomsChange: function (e) {
    this.setData({
      roomsIdx: e.detail.value
    })
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

  // 购物车单选操作
  singleCheck: function (e) {
    var checked = e.currentTarget.dataset.checked;
    var spCode = e.currentTarget.dataset.code;
    var index = e.currentTarget.dataset.index;
    this.data.cartList[index].checked = !this.data.cartList[index].checked
    this.data.totalChecked = this.isTotalChecked();
    this.data.singleChecked = this.isSingleChecked();
    this.updateTotalCount();
    this.setData({
      singleChecked: this.data.singleChecked,
      totalChecked: this.data.totalChecked,
      totalCount: this.data.totalCount,
      cartList: this.data.cartList
    })
  },

  // 购物车全选操作
  totalCheck: function (e) {
    var checked = e.currentTarget.dataset.checked;
    checked = !checked;
    if (checked) {
      for (var i = 0; i < this.data.cartList.length; i++) {
        this.data.cartList[i].checked = true;
      }
    } else {
      for (var i = 0; i < this.data.cartList.length; i++) {
        this.data.cartList[i].checked = false;
      }
    }
    this.data.singleChecked = this.isSingleChecked();
    this.data.totalChecked = checked;
    this.updateTotalCount();
    this.setData({
      singleChecked: this.data.singleChecked,
      totalChecked: this.data.totalChecked,
      totalCount: this.data.totalCount,
      cartList: this.data.cartList
    })
  },
  // 是否全选(true/false)
  isTotalChecked: function () {
    var sys = true;
    for (var i = 0; i < this.data.cartList.length; i++) {
      sys = sys && this.data.cartList[i].checked;
    }
    return sys;
  },

  // 是否单选(true/false判断是否可以下单)
  isSingleChecked: function () {
    var sys = false;
    for (var i = 0; i < this.data.cartList.length; i++) {
      if (this.data.cartList[i].checked) {
        sys = true;
      }
    }
    return sys;
  },
  // 删除购物车里的商品
  deleteCart() {
    if (!this.isSingleChecked()) {
      wx.showModal({
        title: '提示',
        content: '请选择要删除的商品',
        showCancel: false
      })
    } else {
      var I = this;
      wx.showModal({
        title: '提示',
        content: '确认删除该商品？',
        success: function (res) {
          if (res.confirm) {
            I.updateCartCount();
            I.requestList();
          }
        }
      })
    }

  },
  //删除申领车时更新cartCountDict
  updateCartCount() {
    for (var i = 0; i < this.data.cartList.length; i++) {
      var code = this.data.cartList[i].spCode;
      if (this.data.cartList[i].checked) {
        common.setCountBySkuCode(code, 0);
        delete this.data.cartCountDict[code];
      }
    }
    this.setData({
      cartCountDict: this.data.cartCountDict
    })
  },
  //更新申领车申领数量
  updateTotalCount(){
    this.data.totalCount=0;
    for (var i = 0; i < this.data.cartList.length; i++) {
      if (this.data.cartList[i].checked) {
        this.data.totalCount += Number(this.data.cartCountDict[this.data.cartList[i].spCode]);
      }
    }
    this.setData({
      totalCount: this.data.totalCount
    })
  },
  // 确认下单
  confirmOrder() {
    var I = this;
    if (I.data.rooms.length > 1 && I.data.roomsIdx == -1){
      wx.showModal({
        title: '提示',
        content: '请选择申领餐厅',
        showCancel: false
      });
      return;
    }
    


    wx.showModal({
      title: '提示',
      content: '是否确认下单？',
      success: function (res) {
        if (res.confirm) {
          var userInfo = getApp().globalData.userInfo;
          var list = [];
          for (var i = 0; i < I.data.cartList.length; i++) {
            if (I.data.cartList[i].checked == true) {
              list.push({
                "spCode": I.data.cartList[i].spCode,
                "count": I.data.cartCountDict[I.data.cartList[i].spCode],
                "spCunit": I.data.cartList[i].spCunit
              })
            }
          }
          var room = (I.data.rooms.length == 0 ? "" : I.data.rooms[I.data.roomsIdx]);
          var params = {
            "receiver": userInfo.name,
            "phone": userInfo.phone,
            "address": userInfo.waraddress,
            "postal": userInfo.warpostal,
            "mark": '',
            "list": list,
            "room": room
          }
          Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_CREATE, params, function (result) {
            if (true == result.success) {
              I.updateCartCount();
              let data = {
                "target": 2,
              }
              let params = JSON.stringify(data);
              wx.navigateTo({
                url: '../successNotice/successNotice?params=' + params,
              })
            } else {
              wx.showModal({
                title: '提示',
                content: result.message,
                showCancel: false
              })
            }
          });
        }
      }
    })

  },
  //请求申领车列表数据
  requestList() {
    let params = {
      list: []
    }
    for (var item in this.data.cartCountDict) {
      params.list.push(item)
    }
    var I = this;
    Venus.request(Venus.api.VENUS_WMS_APPLY_APPLY_CAR_LIST, params, function (result) {
      if (true == result.success) {
        I.data.cartList = result.data;
        //给cartList增加checked属性并判断库存数量
        for (var i = 0; i < I.data.cartList.length; i++) {
          I.data.cartList[i]['checked'] = true;
          I.data.cartList[i]['isStockChange'] = false;
          var code = I.data.cartList[i].spCode;
          var count = I.data.cartCountDict[code];
          var stock = I.data.cartList[i].spCount;
          if (count > stock) {
            I.data.cartList[i]['isStockChange']= true;
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
        //初始化申领车总数
        I.updateTotalCount();
        //初始化全选状态
        I.data.totalChecked = I.isTotalChecked();
        //初始化单选状态（判断是否可以下单）
        I.data.singleChecked = I.isSingleChecked();
        I.setData({
          cartList: I.data.cartList,
          totalChecked: I.data.totalChecked,
          singleChecked: I.data.singleChecked,
        })
      } else {
        wx.showModal({
          title: '提示',
          content: result.message,
          showCancel: false
        })
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //获取缓存数据
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    this.setData({
      cartCountDict: this.data.cartCountDict
    })
    //初始化图片路径
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
    var userInfo = getApp().globalData.userInfo;
    var rooms = userInfo["rooms"];
    if (rooms.length > 1){
      this.setData({
        rooms: rooms,
        roomsIdx: -1
      })
    } else if (rooms.length == 1 && rooms[0] != ''){
      this.setData({
        rooms: rooms,
        roomsIdx: 0
      })
    }
  },
  //跳转去申领首页
  linkToPurchase: function () {
    wx.switchTab({
      url: '../apply/apply',
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
    this.requestList();
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