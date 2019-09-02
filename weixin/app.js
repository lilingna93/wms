//app.js
App({
  onLaunch: function () {
    // 登录
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
      }
    })
    
  },
  globalData: {
    userInfo: null,
    //记录登录状态
    sess:'',
    skuver:'',
    version:27,
    isexternal:''
  }
})