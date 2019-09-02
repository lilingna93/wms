// pages/order/order.js
import Venus from '../../common/request.js'
Page({
  /**
   * 页面的初始数据
   */
  
  data: {
    currentTabsIndex:0,
    scrollTop: 0,
    scrollHeight: 0,
    orderList:[],
    pageCurrent: 0,
    status: null,
    loadShow: false,
    loadTxt: '',
    totalCount: 0,
  },
  onTabsItemTap(event) {
    var index = event.currentTarget.dataset.index;
    var that = this;
    var hide = false;
    that.setData({
      currentTabsIndex: index,
      pageCurrent: 0,
      orderList: [],
      loadShow: hide,
      scrollTop: 0,
      loadTxt: '',
      status: index,
    })
    index = index ? index : null;
    if(!index){
      index=null
    }else if(index==1){
      index=5
    }else{
      index=7
    }
    var param = {
      pageCurrent:0,
      status: index
    };
    Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_LIST, param, function (result) {
      that.setData({
        orderList: result.data.list,
        totalCount: result.data.totalCount
      })
      if (result.data.totalCount > result.data.pageSize) {
        that.setData({
          loadTxt: '上拉加载更多',
        })
      } else {
        that.setData({
          loadTxt: '暂无更多数据',
        })
      }
    });
  },

  loadMore: function () {
    var show = true
    var that = this;
    this.setData({
      loadShow: show,
      loadTxt: '加载中',
      pageCurrent: that.data.pageCurrent + 1
    })
    var param = {
      pageCurrent: that.data.pageCurrent,
      status: that.data.status
    };
    if (that.data.orderList.length < that.data.totalCount) {
    Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_LIST, param, function (result) {
      if (result.data.list.length>0) {
        var pageOrderList = that.data.orderList.concat(result.data.list);
        that.setData({
          orderList: pageOrderList,
          scrollTop: event.detail.scrollTop
        })
        } 
     })
    } else {
      var hide = false
      that.setData({
        loadShow: hide,
        loadTxt: '暂无更多数据',
      })
    }
  },
  goOrderListDetail:function(event) {
    console.log(event.currentTarget.dataset.code)
    var code = event.currentTarget.dataset.code
    wx.navigateTo({
      url: '../applyDetail/applyDetail?code=' + code,
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    that.setData({
      currentTabsIndex: 0
    })
    var param = {
      pageCurrent: 0,
      status: null
    };
    Venus.request(Venus.api.VENUS_WMS_APPLY_ORDER_LIST, param, function (result) {
      that.setData({
        orderList: result.data.list,
        totalCount: result.data.totalCount
      })
      if (result.data.totalCount > result.data.pageSize) {
        that.setData({
          loadTxt: '上拉加载更多',
        })
      } else {
        that.setData({
          loadTxt: '暂无更多数据',
        })
      }
    });
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          scrollHeight:res.windowHeight
        })
      },
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