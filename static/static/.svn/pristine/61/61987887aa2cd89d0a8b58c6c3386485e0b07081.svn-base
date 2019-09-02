<style type="text/css" media="screen">
  i {
    font-style: normal;
  }

  .mr_20 {
    display: inline-block;
    margin-right: 30px;
  }

  .ivu-table .red td {
    background: #ff4144;
    color: #fff;
  }

  .ivu-table .bgColor td {
    background: #fed6f5;
    color: #000000;
  }
</style>
<template>
  <Content :style="{padding: '0 10px 10px'}">
    <Breadcrumb :style="{margin: '8px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem to="purchaseOrder">订单管理</BreadcrumbItem>
      <BreadcrumbItem>订单详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div style="background:#eee;padding:8px;margin-bottom: 8px;">
          <div style="background: #fff;padding: 10px">
            <span class="mr_20">下单客户：{{info.warName}}</span>
            <span class="mr_20">联系人：{{info.uName}}</span>
            <span class="mr_20">订单编号：{{info.oCode}}</span>
            <span class="mr_20">订单状态：{{info.oStatusMsg}}</span>
            <span class="mr_20">送达日期：{{info.oPdate}}</span>
          </div>
        </div>
        <div style="padding: 8px 0 12px">
          备注： <Input v-model="mark" placeholder="请输入备注" style="width: 20%" />  <Button type="info" style="float: right;margin-right: 2%" @click="postMark">修改备注</Button>

        </div>
        <Table border ref="selection" :height="tableHeight" :columns="detailTit" :data="detailList"
               :row-class-name="orderClassName"></Table>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    name: "purchase-detail",
    methods: {
      orderClassName(row, index) {   //表格tr背景
        if (row.own == 1) {
          return 'bgColor';
        }
        return '';
      },
      isInteger(obj) {
        return Math.round(obj) == obj   //是整数，则返回true，否则返回false
      },
      handleEdit(row) {
        this.$set(row, '$isEdit', true);
      },
      handleSave(row) {
        this.$set(row, 'skCount', parseFloat(parseFloat(row.skCount).toFixed(2)));
        if (row.skCount >= 0) {
          if (row.spCunit == 1) {
            if (this.isInteger(parseFloat(row.skCount)) == true) {
              this.editCount(row);
            } else {
              this.$Modal.warning({
                title: '提示',
                content: '数量格式不正确',
              });
            }
          } else {
            this.editCount();
            this.$set(row, '$isEdit', false);
          }
        } else {
          this.$Modal.warning({
            title: '提示',
            content: '数量格式不正确',
          });
        }
      },
      postMark() {
        let params = {
          service: this.Api.VENUS_WMS_ORDER_ORD_MARK_UPDATE,
          data: {
            oMark: this.mark,
            oCode: this.info.oCode
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDER_ORD_MARK_UPDATE, params).then(res => {
          if(res.success){
            this.loadDetailData();
            this.$Message.info('修改备注成功');
          }
        })
      },
      editCount(row) {
        let params = {
          service: this.Api.VENUS_WMS_ORDERTASK_GOODS_UPDATE,
          data: this.editParams
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_GOODS_UPDATE, params).then(res => {
          if (res.success) {
            this.$Message.info('货品数量修改成功');
            this.$set(row, '$isEdit', false);
          }
          this.loadDetailData()
        })
      },
      loadDetailData() {
        let params = {
          service: this.Api.VENUS_WMS_ORDERTASK_ORDER_DETAIL,
          data: {
            oCode: this.$route.query.oCode
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_ORDER_DETAIL, params).then(res => {
          this.info = res.data.info
          this.mark = this.info.oMark
          this.detailList = res.data.list
        })
      }
    },
    mounted() {
      this.tableHeight = Number(window.innerHeight - 256);
      this.contentHeight = Number(window.innerHeight - 140);
      this.loadDetailData();
    },
    data() {
      return {
        mark:"",
        tableHeight: '',
        contentHeight: '',
        editParams: {
          goodsCode: '',
          spCount: '',
          spCunit: '',
          oCode: ''
        },
        info: {
          oCode: "O30610225623983",
          oStatus: "1",
          oPdate: "2018-07-24",
          warName: "市政党校",
          uName: "rousi",
          uPhone: "13900000000",
          oMark: "订单备注",
          uCode: "U30727133327955",
        },
        detailTit: [
          {
            title: '货品编号',
            key: 'goodsCode',
            align: 'center'
          },
          {
            title: '货品名称',
            key: 'spName',
            align: 'center'
          },
          {
            title: '规格',
            key: 'skNorm',
            align: 'center'
          },
          {
            title: '品牌',
            key: 'skBrand',
            align: 'center'
          },
          {
            title: '产地',
            key: 'skForm',
            align: 'center'
          },
          {
            title: '备注',
            key: 'skMark',
            align: 'center'
          },
          {
            title: '采购数量',
            key: 'skInit',
            align: 'center'
          },
          {
            title: '实际数量',
            key: 'skCount',
            align: 'center',
            render: (h, params) => {
              if (params.row.$isEdit) {
                return h('input', {
                  domProps: {
                    value: params.row.skCount
                  },
                  style: {
                    width: '50px',
                    textAlign: 'center',
                    outline: 'none'
                  },
                  on: {
                    input: function (event) {
                      params.row.skCount = event.target.value
                    }
                  }
                });
              } else {
                return h('div', params.row.skCount);
              }
            }
          },
          {
            title: '单位',
            key: 'skUnit',
            align: 'center'
          },
/*          {
            title: '操作',
            align: 'center',
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: params.row.$isEdit ? 'success' : 'info',
                    size: 'small',
                    disabled: Number(params.row.goodsStatus) == 3 ? true : false
                  },
                  on: {
                    click: () => {
                      if (params.row.$isEdit) {
                        this.editParams.goodsCode = params.row.goodsCode;
                        this.editParams.skCount = params.row.skCount;
                        this.editParams.spCount = params.row.spCount;
                        this.editParams.spCunit = params.row.spCunit;
                        this.editParams.oCode = this.$route.query.oCode;
                        this.handleSave(params.row)
                      } else {
                        this.handleEdit(params.row)
                      }
                    }
                  }
                }, params.row.$isEdit ? '保存' : '编辑')
              ]);
            }
          }*/
        ],
        detailList: []
      }
    }
  }
</script>
