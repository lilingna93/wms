// pages/successNotice/successNotice.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    target:"",
    message:""
  },
  //跳转至"我的"页面
  linkMine:function(){
    wx.switchTab({
      url: '../mine/mine',
    })
  },
  //跳转至采购首页
  linkTarget:function(){
    if (this.data.target == 1){
      wx.switchTab({
        url: '../purchase/purchase',
      })
    } else if (this.data.target == 2){
      wx.switchTab({
        url: '../apply/apply',
      })
    }
    
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let option = JSON.parse(options.params);
    this.setData({
      target: option.target,
      message: option.message,
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