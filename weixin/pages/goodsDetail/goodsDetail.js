// pages/applySearch/applySearch.js
const common = require('../../common/common.js');
const Venus = require('../../common/request.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    reasonIdx:0,
    reasons:[
      "商品包装破损",
      "实际到货商品与页面描述不符",
      "商品保质期已过半",
      "下错单（数量、品牌、规格）",
      "其它（电话沟通）"
    ],
    isReturnOper:false,
    goodsCode:"",//货品编号
    goodsCount:0,//退货数量
    goods:{
      name:"",
      brand:"",
      norm:"",
      price:"",
      unit:"",
      count:"",
      rcount:""
    }

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var params = options.params;
    params = JSON.parse(params);
    console.log(params);
    this.setData(
      {
        goods: {
          name: params["spName"],
          brand: params["skBrand"],
          norm: params["skNorm"],
          price: params["skPrice"],
          unit: params["skUnit"],
          count: params["skNum"],
          rcount: params["skCount"],
        },
        goodsCode: params["gCode"]||"GOODSCODE"
      }
    )
  },


/**
   * 打开退货开关
   */
  onClickEventForReturn:function(event){
    console.log(event)
    this.setData({
      isReturnOper: event.detail.value
    })
  },
/**
   * 选择原因
   */
  onClickEventForReason:function(event){
    console.log(event)
    this.setData({
      reasonIdx: event.detail.value
    })
  },
  onInputEventForCount:function(event){
    this.data.goodsCount = event.detail.value;
  },
  onClickEventForSubmit:function(event){
    var I = this;
    var returnCount = I.data.goodsCount;
    var count = I.data.goods.count;
    if (isNaN(returnCount) || returnCount == 0) {
      wx.showModal({
        title: '提示',
        content: '申请退货数量( ' + returnCount + ' )不可为 “空、0、非数字“',
        showCancel: false
      });
      return;
    }
    if (returnCount > count) {
      wx.showModal({
        title: '提示',
        content: '申请退货数量( ' + returnCount + ' ),不可以大于当时采购数量( ' + count+' )',
        showCancel: false
      });
      return;
    }
    wx.showModal({
      title: '确认',
      content: '确认申请退货吗？',
      success: function (res) {
        if (res.confirm) {
          I.submitForCreateRetureOrder();
        }
      }
    })
  },
  submitForCreateRetureOrder(){
    var I = this;
    var params = {
      "gcode": I.data.goodsCode,
      "type": parseInt(I.data.reasonIdx) + 2,
      "count": I.data.goodsCount,
      "rname": getApp().globalData.userInfo.rname,
    };
    Venus.request(Venus.api.VENUS_WMS_RETURN_RETUENGOODS_CREATE, params, function (result) {
      if (true == result.success) {
        wx.showModal({
          title: '申请提交成功',
          content: '所申请退货的数量已经从库存中临时减去，等待仓库管理员进一步确认，如需查询退货申请单，可到“我的”，“退货申请”中查询。',
          showCancel:false,
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