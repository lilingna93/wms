// pages/order/order.js
import Venus from '../../common/request.js'
Page({

  /**
   * 页面的初始数据
   */
  data: {
    baseImgPath: 'https://wms.shijijiaming.cn/static/spuimg/',
    currentTabsIndex: 0,
    scrollTop: 0,
    scrollHeight: 0,
    orderList: [],
    pageCurrent: 0,
    oStatus: null,
    loadShow: false,
    loadTxt: '',
    totalCount: 0,
  },
  getDetail(event) {
    const that = this;
    let param = {
      oCode: event.currentTarget.dataset.code
    };
    const index = event.currentTarget.dataset.index;
    const value = wx.getStorageSync(event.currentTarget.dataset.code)
    let orderList = that.data.orderList;
    if (value && !orderList[index].show) {
      orderList[index].show=true;
      orderList[index].orderDetail = value;
      that.setData({
        orderList: orderList,
      })

    } else if (!orderList[index].show) {
      Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_DETAIL, param, function(result) {
        wx.setStorageSync(event.currentTarget.dataset.code, result.data.list)
        orderList[index].show = true;
        orderList[index].orderDetail = result.data.list;
        that.setData({
          orderList: orderList,
        })
      });
    } else {
      orderList[index].show = false;
      that.setData({
        orderList: orderList
      })
    }
  },
  onTabsItemTap: function(event) {
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
      oStatus: index,
    })

    index = index ? index : null;
    var param = {
      pageCurrent: that.data.pageCurrent,
      oStatus: index
    };
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_LIST, param, function(result) {
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
  loadMore: function() {
    var show = true
    var that = this;
    this.setData({
      loadShow: show,
      loadTxt: '加载中',
      pageCurrent: that.data.pageCurrent + 1
    })
    var param = {
      pageCurrent: that.data.pageCurrent,
      oStatus: that.data.oStatus
    };
    if (that.data.orderList.length < that.data.totalCount) {
      Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_LIST, param, function(result) {
        if (result.data.list) {
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
  goOrderDetail: function(event) {
    var code = event.currentTarget.dataset.code
    wx.navigateTo({
      url: '../purchaseDetail/purchaseDetail?code=' + code,
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    that.setData({
      currentTabsIndex: 0,
    })
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          scrollHeight: res.windowHeight
        })
      },
    })
    var param = {
      pageCurrent: 0,
      oStatus: null
    };
    Venus.request(Venus.api.VENUS_WMS_PURCHASE_ORDER_LIST, param, function(result) {
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

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

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