<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem>退货单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="100" ref="formInline" inline>
          <FormItem label="退货状态：">
            <Select style="width:150px" :transfer="true" v-model="returnGoods.ogrStatus" @on-change="selectOgrStatus">
              <Option value="0">全部</Option>
              <Option value="1">申请中</Option>
              <Option value="2">已处理</Option>
              <Option value="3">已拒绝</Option>
            </Select>
          </FormItem>
          <FormItem label="退货原因：">
            <Select style="width:200px" :transfer="true" v-model="returnGoods.ogrType" @on-change="selectOgrType">
              <Option value="0">全部</Option>
              <Option value="1">实收不足,产生退货</Option>
              <Option value="2">商品包装破损</Option>
              <Option value="3">实际到货商品与页面描述不符</Option>
              <Option value="4">商品保质期已过半</Option>
              <Option value="5">下错单（数量、品牌、规格）</Option>
              <Option value="6">其它（电话沟通）</Option>
            </Select>
          </FormItem>
          <FormItem :label-width="40">
            <Button type="primary" @click="queryTable()">查询</Button>
          </FormItem>
        </Form>

        <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data"
               :loading="loading"></Table>
        <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
              :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "return-goods-manage",
    methods: {
      selectOgrStatus(val) {
        this.returnGoods.ogrStatus = val;
      },
      selectOgrType(val) {
        this.returnGoods.ogrType = val;
      },
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        let params = {
          "service": this.Api.VENUS_WMS_RETURN_RETURNGOODS_SEARCH,
          "data": {
            "ogrStatus": this.returnGoods.ogrStatus,
            "ogrType": this.returnGoods.ogrType,
            "pageCurrent": curPage
          }
        }
        this.$http.post(this.Api.VENUS_WMS_RETURN_RETURNGOODS_SEARCH, params).then(res => {
          this.loading = false;
          this.pageData.data = res.data.list;
          this.pageData.totalCount = parseInt(res.data.totalCount);
          this.pageData.pageSize = parseInt(res.data.pageSize);
          this.pageData.pageCurrent = parseInt(res.data.pageCurrent);
        })
      },
      handleChange(count) {
        count = count - 1;
        this.queryTable(count);
      },
      confirmReturnGoods() {
        let params = {
          "service": this.Api.VENUS_WMS_RETURN_RETURNGOODS_CONFIRM,
          "data": {
            "ogrCode": this.rowData.ogrCode,
          }
        }
        this.$http.post(this.Api.VENUS_WMS_RETURN_RETURNGOODS_CONFIRM, params).then(res => {
          if (res.success) {
            this.$Message.success('退货成功');
            this.queryTable(this.pageData.pageCurrent);
          }
        })
      },
      rejectRetrunGoods() {
        let params = {
          "service": this.Api.VENUS_WMS_RETURN_RETURNGOODS_REJECT,
          "data": {
            "ogrCode": this.rowData.ogrCode,
          }
        }
        this.$http.post(this.Api.VENUS_WMS_RETURN_RETURNGOODS_REJECT, params).then(res => {
          if (res.success) {
            this.$Message.success('拒绝成功');
            this.queryTable(this.pageData.pageCurrent);
          }
        })
      }
    },
    mounted() {
      this.queryTable();
      this.tableHeight = Number(window.innerHeight - 278);
      this.contentHeight = Number(window.innerHeight - 170);
    },
    data() {
      return {
        contentHeight: 0,
        tableHeight: 0,
        loading: true,
        returnGoods: {
          ogrStatus: '',
          ogrType: '',
        },
        rowData: {
          ogrCode: '',
        },
        pageData: {
          columns: [
            {
              title: '项目组',
              key: 'warName',
              align: 'center',
            },
            {
              title: '采购单号',
              key: 'oCode',
              align: 'center',
            },
            {
              title: '订单编号',
              key: 'ogrCode',
              align: 'center',
            },
            {
              title: '货品名称',
              key: 'spName',
              align: 'center',
            },
            {
              title: '规格',
              key: 'skNorm',
              align: 'center',
            },
            {
              title: '品牌',
              key: 'spBrand',
              align: 'center',
            },
            {
              title: '产地',
              key: 'spFrom',
              align: 'center',
            },
            {
              title: '备注',
              key: 'spMark',
              align: 'center',
            },
            {
              title: '退货原因',
              key: 'typeName',
              align: 'center',
              width: 180
            },
            {
              title: '退货数量',
              key: 'gCount',
              align: 'center',
            },
            {
              title: '单位',
              key: 'skUnit',
              align: 'center',
              width: 50
            },
            {
              title: '状态',
              key: 'statusName',
              align: 'center',
            },
            {
              title: '操作',
              key: 'action',
              width: 150,
              align: 'center',
              render: (h, params) => {
                let actionBtn = []
                if (params.row.statusName == "申请中") {
                  actionBtn = [
                    h('Button', {
                      props: {
                        type: 'primary',
                        size: 'small',
                      },
                      style: {
                        marginRight: '5px'
                      },
                      on: {
                        click: () => {
                          this.rowData.ogrCode = params.row.ogrCode;
                          this.$Modal.confirm({
                            title: '提示',
                            content: '确定要退货吗？',
                            onOk: () => {
                              this.confirmReturnGoods();
                            }
                          })
                        }
                      }
                    }, '确认退货'),
                    h('Button', {
                      props: {
                        type: 'error',
                        size: 'small',
                      },
                      on: {
                        click: () => {
                          this.rowData.ogrCode = params.row.ogrCode;
                          this.$Modal.confirm({
                            title: '提示',
                            content: '确定要拒绝退货吗？',
                            onOk: () => {
                              this.rejectRetrunGoods();
                            }
                          })
                        }
                      }
                    }, '拒绝'),
                  ]
                } else {
                  actionBtn = [
                    h('Button', {
                      props: {
                        type: 'primary',
                        size: 'small',
                        disabled: true
                      },
                      style: {
                        marginRight: '5px'
                      },
                      on: {
                        click: () => {
                          this.rowData.ogrCode = params.row.ogrCode;
                          this.$Modal.confirm({
                            title: '提示',
                            content: '确定要退货吗？',
                            onOk: () => {
                              this.confirmReturnGoods();
                            }
                          })
                        }
                      }
                    }, '确认退货'),
                    h('Button', {
                      props: {
                        type: 'error',
                        size: 'small',
                        disabled: true
                      },
                      on: {
                        click: () => {
                          this.rowData.ogrCode = params.row.ogrCode;
                          this.$Modal.confirm({
                            title: '提示',
                            content: '确定要拒绝退货吗？',
                            onOk: () => {
                              this.rejectRetrunGoods();
                            }
                          })
                        }
                      }
                    }, '拒绝'),

                  ]
                }
                return h('div', actionBtn);
              }
            }
          ],
          data: [],
          totalCount: 0,
          pageSize: 100,
          pageCurrent: 0
        }
      }
    }
  }
</script>

<style scoped>

</style>
