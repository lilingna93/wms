const menu = [
  {
    path: '/',
    name: '首页',
    children: [],
  },
  {
    path: '/goodsManager',
    name: '货品数据管理',
    permissions:15,
    children: [
      {
        path: '/spuList',
        name: 'SPU管理',
        permissions:1
      },
      {
        path: '/skuList',
        name: 'SKU管理',
        permissions:2
      },
      {
        path: '/supplierList',
        name: '供货商管理',
        permissions:4
      },
      {
        path: '/autoUpDown',
        name: '自动上下架',
        permissions:8
      }
    ]
  },
  {
    path: '/orderManager',
    name: '外部订单管理',
    permissions:240,
    children: [
      {
        path: '/purchaseOrder',
        name: '订单管理',
        permissions:16
      },
      {
        path: '/accountList',
        name: '账户管理',
        permissions:32
      },
      {
        path: '/returnGoods',
        name: '退货单管理(运营)',
        permissions:64
      },
      {
        path: '/returnGoodsExtend',
        name: '退货单管理(仓配)',
        permissions:128
      }
    ]
  },
  {
    path: '/stockManage',
    name: '库存货品管理',
    permissions:7936,
    children: [
      {
        path: '/createEnterOrder',
        name: '创建入仓单',
        permissions:256
      },
      {
        path: '/enterOrderManage',
        name: '入仓单管理',
        permissions:512
      },
      {
        path: '/createOutOrder',
        name: '创建出仓单',
        permissions:1024
      },
      {
        path: '/outOrderManage',
        name: '出仓单管理',
        permissions:2048
      },
      {
        path: '/stockManage',
        name: '库存管理',
        permissions:4096
      }
    ]
  },
  {
    path: '/taskManage',
    name: '仓库任务管理',
    permissions:8192,
    children: [
      {
        path: '/workListManage',
        name: '工单管理',
        permissions:8192
      }
    ]
  },
  {
    path: '/reportManage',
    name: '报表数据管理',
    permissions:16384,
    children: [
      {
        path: '/reportFormManage',
        name: '报表管理',
        permissions:16384
      }
    ]
  },
  {
    path: '/accountManager',
    name: '系统账户管理',
    permissions:32768,
    children: [
      {
        path: '/wareAccountList',
        name: '仓库账户管理',
        permissions:32768
      }
    ]
  },
  {
    path: '',
    name: '报表数据平台',
    permissions:2031616,
    children: [
      {
        path: '/marketReport',
        name: '市场报表',
        permissions:65536
      },
      {
        path: '/stockReport',
        name: '仓配报表',
        permissions:131072
      },
      {
        path: '/purchaseReport',
        name: '采购报表',
        permissions:262144
      },
      {
        path: '/financeReport',
        name: '财务报表',
        permissions:524288
      },
      {
        path: '/qaReport',
        name: '品控报表',
        permissions:1048576
      }
    ]
  },
]

export default menu
