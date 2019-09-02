var base='';
//购物车数量缓存数据{code:count}
var cartCountDict={};
//初始化变量名
function initBase(str){
  base=str;
}
//更新购物车数量缓存数据
function setCountStorage(){
  wx.setStorageSync(base+'CART_COUNT_DICT', cartCountDict);
}
//初始化购物车数量缓存数据
function getCountStorage(){
  var dict = wx.getStorageSync(base+'CART_COUNT_DICT');
  if(dict){
    cartCountDict=dict
  }else{
    clearCountStorage();
  }
}
//清空购物车数量缓存数据
function clearCountStorage(){
  cartCountDict = {};
  setCountStorage();
}
//根据skuCode设置数量
function setCountBySkuCode(skCode,count){
  if(undefined==count || 0==count){
    delete cartCountDict[skCode];
  }else {
    cartCountDict[skCode] = count;
  }
  setCountStorage();
  
}
//根据skuCode获取数量
function getCountBySkuCode(skcode) {
  return cartCountDict[skcode];
}
//获取cartCount字典
function getCartCountDict() {
  return cartCountDict;
}
/**
 * 购物车加操作
 */

function onSkuCountAddEvent(that,event) {
  var skCode = event.currentTarget.dataset.code;
  var count = getCountBySkuCode(skCode);
  var spStock = event.currentTarget.dataset.stock;
  var skCunit = event.currentTarget.dataset.skcunit;
  if(spStock){
    if (count == spStock) {
      wx.showModal({
        title: '提示',
        content: '请输入小于或等于库存的数量',
        showCancel: false
      })
      count = spStock-1
    }
  }else{
    count = (undefined == count) ? 0 : count;
  }
  let counts = ++count;
  if (skCunit == 0.10 && count.toString().indexOf('.') > -1) {
    counts = parseFloat(Number(counts).toFixed(1))
  } else if (skCunit == 0.01 && count.toString().indexOf('.') > -1) {
    counts = parseFloat(Number(counts).toFixed(2))
  } else {
    counts = parseInt(counts);
  }
  setCountBySkuCode(skCode, counts);
  that.data.cartCountDict[skCode] = counts;
  that.updateTotalCount && that.updateTotalCount();
  that.updateTotalPrice && that.updateTotalPrice();
  if (that.data.totalCount != undefined){
    that.setData({
      totalCount:that.data.totalCount
    });
  }
  if (that.data.totalPrice) {
    that.setData({
      totalPrice: that.data.totalPrice
    });
  }
  that.setData({
    cartCountDict: that.data.cartCountDict
  });
  playPopSound();
}
/**
* 购物车减操作
*/
function onSkuCountSubEvent(that,event) {
  var skCode = event.currentTarget.dataset.code;
  var count = getCountBySkuCode(skCode);
  var skCunit = event.currentTarget.dataset.skcunit;
  if (undefined == count || 0 == count) return;
  if (count == 1 || count < 1) {
    wx.showModal({
      title: '提示',
      content: '确认删除该商品？',
      success: function (res) {
        if (res.confirm) {
          setCountBySkuCode(skCode, 0);
          delete that.data.cartCountDict[skCode];
          that.requestList && that.requestList();
          if (that.data.totalCount!=undefined) {
            that.setData({
              totalCount: that.data.totalCount
            });
          }
          if (that.data.totalPrice) {
            that.setData({
              totalPrice: that.data.totalPrice
            });
          }
          that.setData({
            cartCountDict: that.data.cartCountDict
          });
        }
      }
    })
    return;
  }
  let counts = --count;
  if (skCunit == 0.10 && count.toString().indexOf('.')> -1) {
    counts = parseFloat(Number(counts).toFixed(1))
  } else if (skCunit == 0.01 && count.toString().indexOf('.') > -1) {
    counts = parseFloat(Number(counts).toFixed(2))
  } else {
    counts = parseInt(counts);
  }

  setCountBySkuCode(skCode, counts);
  
  that.data.cartCountDict[skCode] = counts;
  that.updateTotalCount && that.updateTotalCount();
  that.updateTotalPrice && that.updateTotalPrice();
  if (that.data.totalCount != undefined) {
    that.setData({
      totalCount: that.data.totalCount
    });
  }
  if (that.data.totalPrice) {
    that.setData({
      totalPrice: that.data.totalPrice
    });
  }
  that.setData({
    cartCountDict: that.data.cartCountDict
  });
  playPopSound();
}
/**
* 购物车编辑数量
*/
function onSkuCountEditEvent(that, event) {
  console.log(event)
  var skCode = event.currentTarget.dataset.code;
  var spStock = event.currentTarget.dataset.stock;
  var count;
  var skCunit = event.currentTarget.dataset.skcunit;
  if (event.detail.value){
    if (skCunit == 0.10 && event.detail.value.indexOf('.') > -1) {
      count = parseFloat(Number(event.detail.value).toFixed(1))
    } else if (skCunit == 0.01 && event.detail.value.indexOf('.')>-1) {
      count = parseFloat(Number(event.detail.value).toFixed(2))
    } else {
      count = parseInt(event.detail.value);
    }
    if (spStock) {
      if (count > spStock) {
          wx.showModal({
          title: '提示',
          content: '请输入小于或等于库存的数量',
          showCancel: false
        })
        count = spStock
      }
    } else {
      count = (count < 0) ? 0 : count;
    }
  }else{
    count = 0;
  }
  setCountBySkuCode(skCode, count);
  that.data.cartCountDict[skCode] = count;
  if (count == 0) {
    delete that.data.cartCountDict[skCode];
  }
  that.updateTotalCount && that.updateTotalCount();
  that.updateTotalPrice && that.updateTotalPrice();
  if (that.data.totalCount != undefined) {
    that.setData({
      totalCount: that.data.totalCount
    });
  }
  if (that.data.totalPrice) {
    that.setData({
      totalPrice: that.data.totalPrice
    });
  }
  that.setData({
    cartCountDict: that.data.cartCountDict
  });
}

//操作声音

function playPopSound(){
  const iac = wx.createInnerAudioContext();
  iac.autoplay = true;
  iac.src = '/images/res/pop.mp3'
  iac.onPlay(() => {})
}
function accAdd(arg1, arg2) {
  var r1, r2, m, c;
  try {
    r1 = arg1.toString().split(".")[1].length;
  } catch (e) {
    r1 = 0;
  }
  try {
    r2 = arg2.toString().split(".")[1].length;
  } catch (e) {
    r2 = 0;
  }
  c = Math.abs(r1 - r2);
  m = Math.pow(10, Math.max(r1, r2));
  if (c > 0) {
    var cm = Math.pow(10, c);
    if (r1 > r2) {
      arg1 = Number(arg1.toString().replace(".", ""));
      arg2 = Number(arg2.toString().replace(".", "")) * cm;
    } else {
      arg1 = Number(arg1.toString().replace(".", "")) * cm;
      arg2 = Number(arg2.toString().replace(".", ""));
    }
  } else {
    arg1 = Number(arg1.toString().replace(".", ""));
    arg2 = Number(arg2.toString().replace(".", ""));
  }
  return (arg1 + arg2) / m;
}



module.exports = {
  initBase: initBase,
  setCountStorage: setCountStorage,
  getCountStorage: getCountStorage,
  clearCountStorage: clearCountStorage,
  setCountBySkuCode: setCountBySkuCode,
  getCountBySkuCode: getCountBySkuCode,
  getCartCountDict: getCartCountDict,
  onSkuCountAddEvent: onSkuCountAddEvent,
  onSkuCountSubEvent: onSkuCountSubEvent,
  onSkuCountEditEvent: onSkuCountEditEvent,
  accAdd: accAdd
}