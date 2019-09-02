<template>
  <div :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem to="stockManage">库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>出仓批次详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div class="goodsList">
          <div style="padding-bottom: 12px;font-size: 16px;font-weight: bold">
            订单详情
          </div>
          <Table :height="tableHeight" border ref="selection" :columns="columns" :data="goodsList"></Table>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
  export default {
    name: "edit-enter-order",
    data() {
      return {
        columns: [
          {
            title: '出仓批次',
            key: 'igsCode',
            align: 'center',
          },
          {
            title: '出仓单号',
            key: 'invCode',
            align: 'center',
          },
          {
            title: '对应订单编号',
            key: 'invEcode',
            align: 'center',
          },
          {
            title: '出仓数量',
            key: 'igsSkuCount',
            align: 'center',
            width:100
          },
          {
            title: '创建时间',
            key: 'invCtime',
            align: 'center',
          },
          {
            title: '状态',
            key: 'invStatMsg',
            align: 'center',
            width:100
          },
          {
            title: '创建人',
            key: 'invUname',
            align: 'center',
            width:100
          },
          {
            title: '客户单位',
            key: 'invReceiver',
            align: 'center',
          },
          {
            title: '类型',
            key: 'invType',
            align: 'center',
            width:100
          },
          {
            title: '备注',
            key: 'invMark',
            align: 'center',
          },
        ],
        goodsList: [],
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        gsCode:''
      }
    },
    methods: {
      getDetail(page) {
        const _this = this;
        page = page ? page : 0
        _this.goodsList = [];
        _this.gsCode = _this.$route.query.gsCode
        let params = {
          service: _this.Api.VENUS_WMS_GOODS_GOODS_IGOODSENT,
          data: {
            gsCode: _this.gsCode,
            pageCurrent: page
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_IGOODSENT, params).then(res => {
          if (res.data && res.data.list) {
            _this.pageSize = Number(res.data.pageSize);
            _this.totalCount = Number(res.data.totalCount);
            _this.pageCurrent = Number(res.data.pageCurrent);
            _this.goodsList = res.data.list;
          }
        })
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.getDetail(pageCurrent)
      },
    },
    mounted() {
      this.getDetail();
    },
    watch: {
      goodsList: function () {
        if (this.goodsList.length > 0) {
          this.tableHeight = Number(window.innerHeight - 220)
        }
      }
    }
  }
</script>
