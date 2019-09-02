// pages/purchaseCart/purchaseCart.js
const utils = require('../../utils/util.js');
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    cartCountDict:{},
    planSDate:'',
    planEDate:'',
    totalPrice:0,
    totalChecked:'',
    singleChecked:'',
    cartList: [],
    rooms: [],
    roomsIdx: -1,
  },
  bindRoomsChange: function (e) {
    this.setData({
      roomsIdx: e.detail.value
    })
  },
  // 送货日期更新
  bindPickerChange: function (e) {
    this.data.planSDate = e.detail.value;
    this.setData(this.data);
  },

  // 购物车加操作
  onSkuCountAddEvent: function (event) {
    common.onSkuCountAddEvent(this,event);
  },

  // 购物车减操作
  onSkuCountSubEvent: function (event) {
    common.onSkuCountSubEvent(this,event);
  },
  
  // 购物车编辑数量
  onSkuCountEditEvent: function (event) {
    common.onSkuCountEditEvent(this, event);
  },

  // 购物车单选操作
  singleCheck:function(e){
    var checked = e.currentTarget.dataset.checked;
    var skCode = e.currentTarget.dataset.code;
    var index = e.currentTarget.dataset.index;
    this.data.cartList[index].checked = !this.data.cartList[index].checked
    this.data.totalChecked = this.isTotalChecked();
    this.data.singleChecked = this.isSingleChecked();
    this.updateTotalPrice();
    this.setData({
      singleChecked:this.data.singleChecked,
      totalChecked: this.data.totalChecked,
      totalPrice: this.data.totalPrice,
      cartList: this.data.cartList
    })
  },
   
  // 购物车全选操作
  totalCheck:function(e){
    var checked = e.currentTarget.dataset.checked;
    checked=!checked;
    if(checked){
      for (var i = 0; i < this.data.cartList.length; i++) {
        this.data.cartList[i].checked = true;
      }
    }else{
      for (var i = 0; i < this.data.cartList.length; i++) {
        this.data.cartList[i].checked = false;
      }
    }
    this.data.singleChecked = this.isSingleChecked();
    this.data.totalChecked=checked;
    this.updateTotalPrice();
    this.setData({
      singleChecked:this.data.singleChecked,
      totalChecked:this.data.totalChecked,
      totalPrice: this.data.totalPrice,
      cartList: this.data.cartList
    })
  },
  // 是否全选(true/false)
  isTotalChecked:function(){
    var sys=true;
    for (var i = 0; i < this.data.cartList.length; i++) {
      sys = sys && this.data.cartList[i].checked;
    }
    return sys;
  },

  // 是否单选(true/false判断是否可以下单)
  isSingleChecked: function () {
    var sys=false;
    for (var i = 0; i < this.data.cartList.length; i++) {
      if (this.data.cartList[i].checked) {
        sys = true;
      }
    }
    return sys;
  },

  // 更新购物车商品的总价
  updateTotalPrice() {
    var totalPrice=0;
    for (var i = 0; i < this.data.cartList.length; i++) {
      if (this.data.cartList[i].checked) {
        totalPrice += this.data.cartList[i].skTotalPrice * this.data.cartCountDict[this.data.cartList[i].skCode];
      }
    }
    this.data.totalPrice = totalPrice.toFixed(2);
    this.setData({
      totalPrice: this.data.totalPrice
    })
  },

  // 删除购物车里的商品
  deleteCart(){
    var I=this;
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
  // 确认下单
  confirmOrder(){
    var I = this;
    if (I.data.rooms.length > 1 && I.data.roomsIdx == -1) {
      wx.showModal({
        title: '提示',
        content: '请选择采购餐厅',
        showCancel: false
      });
      return;
    }
    wx.showModal({
      title: '提示',
      content: '是否确认下单？',
      success: function (res) {
        if (res.confirm) {
          var list = [];
          for(var i=0;i<I.data.cartList.length;i++){
            if (I.data.cartList[i].checked==true){
              list.push({
                "skCode": I.data.cartList[i].skCode,
                "skNum": I.data.cartCountDict[I.data.cartList[i].skCode],
                "sprice": I.data.cartList[i].skTotalPrice
              })
            }
          }
          var room = I.data.rooms.length == 0 ? "" : I.data.rooms[I.data.roomsIdx];
          var params = {
            "oMark": '',
            "oPlan": I.data.planSDate,
            "list": list,
            "room": room
          }

          // if ((new Date(I.data.planSDate)).getDay()==6 && false){
          //   wx.showModal({
          //     title: '提示',
          //     content: "周六不支持配送",
          //     showCancel: false
          //   })
          //   return;
          // }


          Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_CREATE, params, function (result) {
            if (true == result.success) {
              I.updateCartCount();
              let data = {
                "target":1,
                "message":result.message
              }
              let params = JSON.stringify(data);
              wx.navigateTo({
                url: '../successNotice/successNotice?params='+params,
              })
            } else {
              wx.showModal({
                title: '提示',
                content: result.message,
                showCancel: false
              })
            }
          })
        }
      }
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    
    //获取购物车数量缓存数据
    common.getCountStorage();
    this.data.cartCountDict = common.getCartCountDict();
    //初始化送货日期
    this.data.planSDate = utils.formatStartTime(new Date);
    this.data.planEDate = utils.formatEndTime(new Date);
    //初始化图片路径
    //this.data.baseImgPath = wx.env.USER_DATA_PATH + "/sku/spuimg/";
    var userInfo = getApp().globalData.userInfo;
    var rooms = userInfo["rooms"];
    console.log(rooms)
    if (rooms.length > 1) {
      this.setData({
        rooms: rooms,
        roomsIdx: -1
      })
    } else if (rooms.length == 1 && rooms[0] != '') {
      this.setData({
        rooms: rooms,
        roomsIdx: 0
      })
    }
    this.setData(this.data);
  },
  //请求购物车列表数据
  requestList(){
    let params = {
      list:[]
    }
    for (var item in this.data.cartCountDict){
      params.list.push(item);
    }
    var I=this;
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_PURCHASING_CAR_LIST, params, function (result) {
      if (true == result.success) {
        I.data.cartList=result.data.list;
        //给cartList增加checked属性
        for (var i=0;i<I.data.cartList.length;i++) {
          I.data.cartList[i]['checked'] = true;
        }
        console.log(I.data.cartList)
        //初始化购物车总价格
        I.updateTotalPrice();
        //初始化全选状态
        I.data.totalChecked = I.isTotalChecked();
        //初始化单选状态（判断是否可以下单）
        I.data.singleChecked = I.isSingleChecked();
        I.setData({
          cartList: I.data.cartList,
          totalChecked: I.data.totalChecked,
          singleChecked: I.data.singleChecked
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
  //删除购物车时更新cartCountDict
  updateCartCount(){
    for (var i = 0; i < this.data.cartList.length; i++) {
      var code = this.data.cartList[i].skCode;
      if (this.data.cartList[i].checked) {
        common.setCountBySkuCode(code, 0);
        delete this.data.cartCountDict[code];
      }
    }
    this.setData({
      cartCountDict: this.data.cartCountDict
    })
  },
  //跳转去采购首页
  linkToPurchase:function(){
    wx.switchTab({
      url: '../purchase/purchase',
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
    //初始化购物车列表数据
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