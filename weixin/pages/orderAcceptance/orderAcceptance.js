// pages/orderAcceptance/orderAcceptance.js
import Venus from '../../common/request.js';
const utils = require('../../utils/util.js');
const {
  $Message
} = require('../../iview/base/index');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    disabled: false,
    oStatus: '',
    oCode: '',
    orderList: [],
    eventIndex: '',
    options: '',
    finshModal: false,
    modal: false,
    confirmModal: false,
    date:"",
    planEDate:"",
    actionsTxt: [{
      name: '继续下单',
      color: '#19be6b'
    },
    {
      name: '返回首页',
      color: '#2d8cf0',
    },
    {
      name: '取消',
      color: '#ff9900'
    }
    ],
    paramsList: [],
    confirmNum: 0,
    isFastModal: false,
    fastList: [],
    userIsuprectime:'1'
  },
  bindDateChange: function (e) {
    this.setData({
      date: e.detail.value
    })
  },
  finshOrder: function () {
    const that = this;
    let fList = [];
    for (var i = 0; i < that.data.orderList.length; i++) {
      var leng = that.data.orderList[i].list.length;
      for (var j = 0; j < leng; j++) {
        if (that.data.orderList[i].list[j].checked) {
          fList.push(that.data.orderList[i].list[j]);
          this.setData({
            finshModal: false,
            isFastModal: true,
            fastList: fList
          })
        }
        if(fList.length<1){
          this.setData({
            finshModal:true,
            isFastModal:false
          })
        }
      }
    }
  },
  handleClose: function () {
    this.setData({
      finshModal: false,
      fastList: []
    })
  },
  handleCloFast: function () {
    this.setData({
      isFastModal: false,
      fastList: []
    })
  },
  handleFast: function () {
    this.setData({
      finshModal: true,
      isFastModal: false
    })
  },
  handleFinsh: function () {
    var that = this;
    const list = [];
    for (var i = 0; i < that.data.orderList.length; i++) {
      var leng = that.data.orderList[i].list.length;
      for (var j = 0; j < leng; j++) {
        list.push({
          "isFast": that.data.orderList[i].list[j].checked,
          "skCode": that.data.orderList[i].list[j].skCode
        })
      }
    }
    var param = {
      oCode: that.data.oCode,
      oStatus: 2,
      list: list,
      date:that.data.date
    };
    that.setData({
      finshModal: false,
      disabled: true
    })
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_STATUS_UPDATE, param, function (result) {
      if (result.success) {
        that.setData({
          modal: true,
          confirmNum: 0
        })
      }
    });
  },
  actionsTap: function ({
    detail
  }) {
    const index = detail.index;
    if (index === 0) {
      wx.switchTab({
        url: '../purchase/purchase',
      });
    } else if (index === 1) {
      wx.switchTab({
        url: '../mine/mine',
      });
    } else if (index === 2) {
      this.setData({
        confirmNum: 0
      });
    }
    this.setData({
      modal: false
    });
  },
  onSkuCountSub: function (e) {
    const index = e.currentTarget.dataset.index;
    const idx = e.currentTarget.dataset.idx;
    let orderList = this.data.orderList;
    let num = orderList[idx].list[index].skNum;
    if (num <= 0) {
      return;
    }


    num = num - 1
    let spCunit = e.currentTarget.dataset.spcunit
    if (spCunit == 0.1 && num.toString().indexOf('.') > -1) {
      num = parseFloat(Number(num).toFixed(1))
    } else if (spCunit == 0.01 && num.toString().indexOf('.') > -1) {
      num = parseFloat(Number(num).toFixed(2))
    } else {
      num = parseInt(num)
    }
    orderList[idx].list[index].skNum = num
    this.setData({
      orderList: orderList
    });
  },
  onSkuCountEdit: function (e) {
    const index = e.currentTarget.dataset.index;
    const idx = e.currentTarget.dataset.idx;
    let orderList = this.data.orderList;
    let spCunit = e.currentTarget.dataset.spcunit
    let num;
    if (spCunit == 0.1 && e.detail.value.indexOf('.') > -1) {
      num = parseFloat(Number(e.detail.value).toFixed(1))
    } else if (spCunit == 0.01 && e.detail.value.indexOf('.') > -1) {
      num = parseFloat(Number(e.detail.value).toFixed(2))
    } else {
      num = parseInt(e.detail.value)
    }
    orderList[idx].list[index].skNum = num
    this.setData({
      orderList: orderList
    });
  },
  onSkuCountAdd: function (e) {
    const index = e.currentTarget.dataset.index;
    const idx = e.currentTarget.dataset.idx;
    let orderList = this.data.orderList;
    var num = orderList[idx].list[index].skNum;
    num = Number(num) + 1
    let spCunit = e.currentTarget.dataset.spcunit
    if (spCunit == 0.1 && num.toString().indexOf('.') > -1) {
      num = parseFloat(Number(num).toFixed(1))
    } else if (spCunit == 0.01 && num.toString().indexOf('.') > -1) {
      num = parseFloat(Number(num).toFixed(2))
    } else {
      num = parseInt(num)
    }
    orderList[idx].list[index].skNum = num
    this.setData({
      orderList: orderList
    });
  },
  confirmGoods: function (e) {
    const index = e.currentTarget.dataset.index;
    var list = this.data.orderList[index].list;
    var msg = [];
    for (var i = 0; i < list.length; i++) {
      msg.push({
        "skuCount": list[i].skNum,
        "spuCount": list[i].skNum * list[i].spCount,
        "goodsCode": list[i].goodscode,
        "skUnit": list[i].skUnit
      })
    }
    this.setData({
      confirmModal: true,
      paramsList: msg,
      eventIndex: e.currentTarget.dataset.index
    });
  },
  handleConfirm: function () {
    var that = this;
    var param = {
      "rname": getApp().globalData.userInfo.rname,
      "warCode": getApp().globalData.userInfo.warcode,
      "oCode": this.data.oCode,
      "list": this.data.paramsList
    };
    that.setData({
      confirmModal: false,
    })

    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_GOODS_RECEIPT, param, function (result) {
      if (result.success) {
        let orderList = that.data.orderList;
        var list = orderList[that.data.eventIndex].list;
        for (var i = 0; i < list.length; i++) {
          list[i].status = false
        }
        orderList[that.data.eventIndex].status = 1
        that.setData({
          confirmNum: that.data.confirmNum + 1,
          orderList: orderList
        })
        $Message({
          content: '确认收货成功',
          type: 'success'
        });
      } else {
        $Message({
          content: '确认收货失败',
          type: 'warning'
        });
      }
    });
  },
  check: function (e) {
    const that = this;
    let skCode = e.currentTarget.dataset.code;
    for (var i = 0; i < that.data.orderList.length; i++) {
      var leng = that.data.orderList[i].list.length;
      for (var j = 0; j < leng; j++) {
        if (that.data.orderList[i].list[j].skCode == skCode) {
          that.data.orderList[i].list[j].checked = !that.data.orderList[i].list[j].checked;
        }
      }
    }
    that.setData({
      orderList: that.data.orderList
    })
  },
  singleCheck: function (e) {
    let checked = e.currentTarget.dataset.checked;
    let skCode = e.currentTarget.dataset.code;
    let idx = e.currentTarget.dataset.idx;
    let index = e.currentTarget.dataset.index;
    this.data.orderList[idx].list[index].checked = !this.data.orderList[idx].list[index].checked;
    this.setData({
      orderList: this.data.orderList
    })
  },
  handleCancel: function () {
    this.setData({
      confirmModal: false
    });
  },
  loadData: function () {
    var that = this;
    that.setData({
      oCode: that.data.options.code,
      oStatus: that.data.options.oStatus,
    })
    var param = {
      oCode: that.data.oCode
    };
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_SPLIT_SEARCH, param, function (result) {
      var confirmNum = 0
      for (var i = 0; i < result.data.length; i++) {
        if (result.data[i].status == 1) {
          that.setData({
            confirmNum: ++confirmNum
          })
        }
        var leng = result.data[i].list.length;
        for (var j = 0; j < leng; j++) {
          result.data[i].list[j].checked = false
        }
        that.setData({
          orderList: result.data,
        })
      }
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let date = utils.formatTime(new Date);
    var params = JSON.parse(options.params);
    let userIsuprectime = getApp().globalData.userInfo.userIsuprectime;
    this.setData({
      options: params,
      date:date,
      planEDate: date,
      userIsuprectime: userIsuprectime
    })
    this.loadData();
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