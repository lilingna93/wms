import Venus from '../../common/request.js'
const {
  $Message
} = require('../../iview/base/index');
//获取应用实例
Page({
  /**
   * 页面的初始数据
   */
  data: {
    skuData: {
      "R": [],
      "D": {}
    },
    percent: 0,
    status:"active",
    sec:0
  },
  checkHandler:null,
  checkLoadStatus:function(){
    var I = this;
    I.data.sec++;
    console.log(I.data.sec)
    if (I.data.sec <= 60) {
      I.setData({
        percent: parseInt(100 * I.data.sec / 60)
      });
    }else{
      clearInterval(I.checkHandler);
      $Message({
        content: '数据更新超时，请关闭小程序重新打开',
        type: 'error',
        duration: 100
      });
      wx.showModal({
        title: '提示',
        content: '重新加载数据',
        success(res) {
          if (res.confirm) {
            console.log('用户点击确定')
            I.initLoginStatus();
          } else if (res.cancel) {
            console.log('用户点击取消')
          }
        }
      })
    }
  },
  //打开后首次初始化登录状态，并获得必要数据
  initLoginStatus: function () {
    var I = this;
    I.checkHandler = setInterval(I.checkLoadStatus,1000);
    wx.login({
      success: function (res) {
        var param = {
          code: res.code
        }
        Venus.request(Venus.api.VENUS_WMS_AUTH_WXLOGIN, param, function (result) {
          if (true == result.success) {
            getApp().globalData.userInfo = result.data;
            var skuver = result.data["skuver"];
            var imgver = result.data["imgver"];
            var warcode = result.data["warcode"];
            var miniskuver = result.data["miniskuver"];
            getApp().globalData.isexternal = result.data.isexternal || 1;
            I.checkAndInitSkuData(skuver, function () { 
              //更新大仓sku
              I.checkAndInitMiniSkuData(miniskuver, function () {
                //更新小仓sku
                clearInterval(I.checkHandler);
                I.setData({percent: 98});
                I.chechAndInitImgData(warcode, imgver, function () {
                  I.setData({percent: 100});
                  I.data.sec = 0;
                  wx.reLaunch({
                    url: '../purchase/purchase'
                  });
                });
              });
            });
          } else {
            wx.reLaunch({
              url: '../active/active'
            });
          }
        });
      }
    });
  },
  checkAndInitSkuData: function (skuver, onComplete) {
    console.log(skuver);
    var I = this;
    var ver = wx.getStorageSync("SKU_VER");
    if (ver == skuver) {
      //版本一致，当前缓存中SKU是最新数据
      onComplete && onComplete();
    } else {
      $Message({
        content: '发现最新货品数据字典，准备更新 ...',
        duration:12
      });
      //版本不一致，需要重新加载最新数据
      Venus.request(Venus.api.VENUS_WMS_SKU_LATESTSKU, {}, function (result) {
        console.log(result.data);
        $Message({
          content: '数据更新中，请稍后 ...',
          duration: 12
        });
        var data = JSON.parse(result.data);
        wx.setStorageSync("SKU_VER", skuver);
        wx.setStorageSync('SKU_DATA', data.R);
        wx.setStorageSync('SKU_DATA_DICT', data.D);
        //更新购物车缓存数据，去除无效SKU
        I.checkAndUpdateCartData(data.D);
        onComplete && onComplete();
      });
    }
  },
  checkAndInitMiniSkuData: function (miniskuver, onComplete) {
    //onComplete && onComplete();//忽略掉小仓字典
    //return;
    console.log(miniskuver);
    var I = this;
    var ver = wx.getStorageSync("MINI_SKU_VER");
    if (ver == miniskuver) {
      //版本一致，当前缓存中SKU是最新数据
      onComplete && onComplete();
    } else {
      $Message({
        content: '发现最新项目组货品数据字典，准备更新 ...',
        duration: 12
      });
      //版本不一致，需要重新加载最新数据
      Venus.request(Venus.api.VENUS_WMS_MINI_SKU_LATESTSKU, { version: getApp().globalData.version}, function (result) {
        //console.log(result.data);
        $Message({
          content: '数据更新中，请稍后 ...',
          duration: 12
        });
        var data = JSON.parse(result.data);
        wx.setStorageSync("MINI_SKU_VER", miniskuver);
        wx.setStorageSync('MINI_SKU_DATA', data.R);
        //wx.setStorageSync('MINI_SKU_DATA_DICT', data.D);
        //更新购物车缓存数据，去除无效SKU
        //I.checkAndUpdateCartData(data.D);
        onComplete && onComplete();
      });
    }
  },
  chechAndInitImgData: function (warcode, imgver, onComplete) {
    //临时去掉图片异步加载
    onComplete && setTimeout(onComplete, 2000);
    return;

    var I = this;
    var ver = wx.getStorageSync("IMG_VER");
    if (ver == imgver && true) {
      onComplete && onComplete();
    } else {
      var timeHandler = setInterval(function () {
        if (!this.index || this.index > 3) {
          this.index = 0
        }
        
        var flag = ['','.', '. .', '. . .'][this.index];
        this.index++;
        $Message({
          content: '正在更新商品物料数据，请稍后 ' + flag,
          type: 'warning'
        });
      }, 600);
     

      wx.downloadFile({
        url: 'https://' + Venus.host+'1/static/spuimage/spuimg.' + warcode + '.zip',
        success: function (res) {
          if (res.statusCode == 200) {
            //console.log(res);
            var filePath = res.tempFilePath;
            var imgDir = wx.env.USER_DATA_PATH + "/sku";
            var fsm = wx.getFileSystemManager();
            // console.log(fsm.accessSync(imgDir));
            // return;
            var imgFileDir = imgDir + "/spuimg/";
            fsm.mkdir({
              dirPath: imgDir,
              complete: function (res) {
                fsm.unzip({
                  zipFilePath: filePath,
                  targetPath: imgDir,
                  complete: function (res) {
                    //console.log(res);
                    fsm.readdir({
                      dirPath: imgFileDir,
                      success: function (res) {
                        clearInterval(timeHandler);
                        $Message({
                          content: '更新完毕'
                        });
                        wx.setStorageSync("IMG_VER", imgver);
                        onComplete && setTimeout(onComplete, 2000);
                        console.log(wx.env);
                      }
                    })
                  },
                });
              }
            });
          }
        }
      });
    }
  },

  checkAndUpdateCartData:function(skuDict){
    var cartSkuData = wx.getStorageSync("P_CART_COUNT_DICT");
    var cartSkuDataUpadted = false;
    for(var scode in cartSkuData){
      if (!skuDict[scode]){
        delete cartSkuData[scode];
        cartSkuDataUpadted = true;
      }
    }
    if (cartSkuDataUpadted){
      wx.setStorageSync("P_CART_COUNT_DICT", cartSkuData);
    }
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.initLoginStatus();

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