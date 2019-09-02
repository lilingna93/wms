// import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'index',
      component: (resolve) => require(['@/components/common/index'], resolve),
      children: [
        {
          path: '/',
          name: 'default',
          component: (resolve) => require(['@/components/pages/default'], resolve)
        },
        {
          path: '/skuList',
          name: 'skuList',
          component: (resolve) => require(['@/components/pages/goodsManager/skuList'], resolve)

        },
        {
          path: '/spuList',
          name: 'spuList',
          component: (resolve) => require(['@/components/pages/goodsManager/spuList'], resolve)
        },
        {
          path: '/supplierList',
          name: 'supplierList',
          component: (resolve) => require(['@/components/pages/goodsManager/supplierList'], resolve)
        },
        {
          path: '/autoUpDown',
          name: 'autoUpDown',
          component: (resolve) => require(['@/components/pages/goodsManager/autoUpDown'], resolve)
        },
        /*{
          path: '/autoUpDown1',
          name: 'autoUpDown1',
          component: (resolve) => require(['@/components/pages/goodsManager/autoUpDown1'], resolve)
        },*/
        {
          path: '/externalSku',
          name: 'externalSku',
          component: (resolve) => require(['@/components/pages/goodsManager/externalSku'], resolve)
        },
        {
          path: '/orderList',
          name: 'orderList',
          component: (resolve) => require(['@/components/pages/orderManager/orderList'], resolve)
        },
        {
          path: '/orderDetail',
          name: 'orderDetail',
          component: (resolve) => require(['@/components/pages/orderManager/orderDetail'], resolve)
        },
        {
          path: '/accountList',
          name: 'accountList',
          component: (resolve) => require(['@/components/pages/orderManager/accountList'], resolve)
        },
        {
          path: '/purchaseOrder',
          name: 'purchaseOrder',
          component: (resolve) => require(['@/components/pages/orderManager/purchaseOrder'], resolve)
        },
        {
          path: '/purchaseDetail',
          name: 'purchaseDetail',
          component: (resolve) => require(['@/components/pages/orderManager/purchaseDetail'], resolve)
        },
        {
          path: '/selfSupport',
          name: 'selfSupport',
          component: (resolve) => require(['@/components/pages/orderManager/selfSupport'], resolve)
        },
        {
          path: '/directPurchase',
          name: 'directPurchase',
          component: (resolve) => require(['@/components/pages/orderManager/directPurchase'], resolve)
        },
        {
          path: '/returnGoodsManage',
          name: 'returnGoodsManage',
          component: (resolve) => require(['@/components/pages/orderManager/returnGoodsManage'], resolve)
        },
        {
          path: '/returnGoods',
          name: 'returnGoods',
          component: (resolve) => require(['@/components/pages/orderManager/returnGoods'], resolve)
        },
        {
          path: '/returnGoodsList',
          name: 'returnGoodsList',
          component: (resolve) => require(['@/components/pages/orderManager/returnGoodsList'], resolve)
        },
        {
          path: '/returnGoodsExtend',
          name: 'returnGoodsExtend',
          component: (resolve) => require(['@/components/pages/orderManager/returnGoodsExtend'], resolve)
        },
        {
          path: '/wareAccountList',
          name: 'wareAccountList',
          component: (resolve) => require(['@/components/pages/accountManager/wareAccountList'], resolve)
        },
        {
          path: '/createEnterOrder',
          name: 'createEnterOrder',
          component: (resolve) => require(['@/components/pages/stockManager/createEnterOrder'], resolve)
        },
        {
          path: '/enterOrderManage',
          name: 'enterOrderManage',
          component: (resolve) => require(['@/components/pages/stockManager/enterOrderManage'], resolve)
        },
        {
          path: '/createOutOrder',
          name: 'createOutOrder',
          component: (resolve) => require(['@/components/pages/stockManager/createOutOrder'], resolve)
        },
        {
          path: '/outOrderManage',
          name: 'outOrderManage',
          component: (resolve) => require(['@/components/pages/stockManager/outOrderManage'], resolve)
        },
        {
          path: '/stockManage',
          name: 'stockManage',
          component: (resolve) => require(['@/components/pages/stockManager/stockManage'], resolve)
        },
        {
          path: '/thresholdWarning',
          name: 'thresholdWarning',
          component: (resolve) => require(['@/components/pages/stockManager/thresholdWarning'], resolve)

        },
        {
          path: '/editEnterOrder',
          name: 'editEnterOrder',
          component: (resolve) => require(['@/components/pages/stockManager/editEnterOrder'], resolve)
        },
        {
          path: '/editOutOrder',
          name: 'editOutOrder',
          component: (resolve) => require(['@/components/pages/stockManager/editOutOrder'], resolve)
        },
        {
          path: '/workListManage',
          name: 'workListManage',
          component: (resolve) => require(['@/components/pages/taskManage/workListManage'], resolve)
        },
        {
          path: '/reportFormManage',
          name: 'reportFormManage',
          component: (resolve) => require(['@/components/pages/reportManage/reportFormManage'], resolve)
        },
        {
          path: '/stockDetail',
          name: 'stockDetail',
          component: (resolve) => require(['@/components/pages/stockManager/stockDetail'], resolve)
        },
        {
          path: '/warehouseBatch',
          name: 'warehouseBatch',
          component: (resolve) => require(['@/components/pages/stockManager/warehouseBatch'], resolve)
        },
        {
          path: '/financeReport',
          name: 'financeReport',
          component: (resolve) => require(['@/components/pages/reportData/financeReport'], resolve)
        },
        {
          path: '/marketReport',
          name: 'marketReport',
          component: (resolve) => require(['@/components/pages/reportData/marketReport'], resolve)
        },
        {
          path: '/purchaseReport',
          name: 'purchaseReport',
          component: (resolve) => require(['@/components/pages/reportData/purchaseReport'], resolve)
        },
        {
          path: '/stockReport',
          name: 'stockReport',
          component: (resolve) => require(['@/components/pages/reportData/stockReport'], resolve)
        },
        {
          path: '/qaReport',
          name: 'qaReport',
          component: (resolve) => require(['@/components/pages/reportData/qaReport'], resolve)
        }
      ]
    }

  ]
})
