import Venus from '../../common/request.js'
const { $Message } = require('../../iview/base/index');
Page({
  /**
   * 页面的初始数据
   */
  data: {
    vcode: "",
    phone: ""
  },

  //编辑手机号
  onPhoneEditEvent: function(e) {
    this.data.phone = e.detail.value;
  },

  //发送验证码
  onSendValidCodeEvent: function() {
    this.data.vcode = 123456;
    this.setData(this.data);
  },

  //登录并激活
  onLoginAndActiveEvent: function() {
    var phone = this.data.phone;
    var vcode = this.data.vcode;
    if (!(/^1[34578]\d{9}$/.test(phone))) {
      $Message({ content: '请确认手机号填写正确', type: 'warning' });
      return;
    } 
    if (vcode == "") {
      $Message({ content: '请确认验证码填写正确', type: 'warning' });
      return;
    }
    wx.login({
      success: function(res) {
        console.log(res);
        var param = {
          code: res.code,
          phone: phone,
          vcode: vcode
        };
        Venus.request(Venus.api.VENUS_WMS_AUTH_WXACTIVE, param, function(result) {
          if (true == result.success) {
            $Message({ content: result.message });
            setTimeout(function () {
              wx.reLaunch({
                url: '../index/index'
              })},1000);
          }else{
            $Message({ content: result.message, type: 'error' });
          }
        });
      }
    });
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    console.log('onLoad');
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {
    console.log('onReady');
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    console.log('onShow');
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {
    console.log('onHide');
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {
    console.log('onUnload');
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