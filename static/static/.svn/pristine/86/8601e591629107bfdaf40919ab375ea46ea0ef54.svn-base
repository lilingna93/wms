<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem to="returnGoods">退货单管理</BreadcrumbItem>
      <BreadcrumbItem>退货单详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div style="display: flex;justify-content: space-between;margin-bottom: 16px">
          <span>
            <Button type="primary" style="margin-left: 20px" @click="exportTodayChoice">下载自营退货单</Button>
            <Button type="success" style="margin-left: 20px" @click="exportSelfToDirect">下载直采退货单</Button>
            <Button type="warning" style="margin-left: 20px" @click="exportTodaySale">下载直采（奶制品及鲜鱼水菜）退货单</Button>
            <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
          </span>
          <span style="display: flex">
            <div style="display:flex;align-content: center;margin-left: 20px;">
              <div style="width: 20px;height: 20px;border-radius: 4px;background: #2d8cf0;display: inline-block;"></div>
              自营货品
            </div>
            <div style="display:flex;align-content: center;margin-left: 20px;">
              <div style="width: 20px;height: 20px;border-radius: 4px;background: #19be6b;display: inline-block;"></div>
              直采货品
            </div>
            <div style="display:flex;align-content: center;margin-left: 20px;">
              <div style="width: 20px;height: 20px;border-radius: 4px;background: #f90;display: inline-block;"></div>
              直采(奶制品及鲜鱼水菜)
            </div>
            <div style="display:flex;align-content: center;margin-left: 20px;">
              <div style="width: 20px;height: 20px;border-radius: 4px;background: #2db7f5;display: inline-block;"></div>
              缺货直采
            </div>
          </span>
        </div>
        <Table :width="tableWidth" :height="tableHeight" border ref="selection" :columns="pageData.columns"
               :data="pageData.data"
               :loading="loading" :row-class-name="rowClassName"></Table>
        <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
              :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "return-goods-list",
    methods: {
      rowClassName(row, index) {   //表格tr背景
        const selectObj = {
          1: () => {
            return 'info';
          },
          2: () => {
            return 'blue';
          },
          3: () => {
            return 'warning';
          },
          4: () => {
            return 'success';
          }
        };
        return selectObj[row.color]();
      },
      exportTodayChoice() {
        const _this = this;
        _this.formData.sname = `自营退货单.xlsx`;
        let params = {
          "service": _this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_SELFSUPPORT_EXPORT,
          "data": {
            "rtCode": this.$route.query.rtCode,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_SELFSUPPORT_EXPORT, params).then(res => {
          if (res.success) {
            _this.formData.fname = res.data;
            setTimeout(() => {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      exportSelfToDirect() {
        const _this = this;
        _this.formData.sname = `直采退货单.xlsx`;
        let params = {
          "service": _this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_DIRECTMINING_EXPORT,
          "data": {
            "rtCode": this.$route.query.rtCode,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_DIRECTMINING_EXPORT, params).then(res => {
          if (res.success) {
            _this.formData.fname = res.data;
            setTimeout(() => {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      exportTodaySale() {
        const _this = this;
        _this.formData.sname = `直采（奶制品及鲜鱼水菜）退货单.xlsx`;
        let params = {
          "service": _this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_ISSUP_EXPORT,
          "data": {
            "rtCode": this.$route.query.rtCode,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS_ISSUP_EXPORT, params).then(res => {
          if (res.success) {
            _this.formData.fname = res.data;
            setTimeout(() => {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      handleEdit(row) {
        this.$set(row, '$isEdit', true);
      },
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        let params = {
          "service": this.Api.VENUS_WMS_RETURNTASK_DETAILS_LIST,
          "data": {
            "pageCurrent": curPage,
            "pageSize": this.pageData.pageSize,
            "isTwarehouse": 1
          }
        }
        this.$http.post(this.Api.VENUS_WMS_RETURNTASK_DETAILS_LIST, params).then(res => {
          if (res.success) {
            this.loading = false;
            this.pageData.data = res.data.list;
            this.pageData.totalCount = parseInt(res.data.totalCount);
            this.pageData.pageSize = parseInt(res.data.pageSize);
            this.pageData.pageCurrent = parseInt(res.data.pageCurrent);
          }
        })
      },
      handleChange(count) {
        count = count - 1;
        this.queryTable(count);
      },
      handleSave(row) {
        this.$set(row, 'warMark', row.warMark);
        this.operation("确定要保存当前备注吗？", row, 1);
      },
      operation(content, params, btnCode,isTwarehouse ) {
        this.$Modal.confirm({
          title: '提示',
          content: content,
          onOk: () => {
            let param = {
              "service": this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS,
              "data": {
                "ogrCode": params.ogrCode,
                "ogrNode": params.ogrNode,
                "ogrType": params.ogrType,
                "supCode": params.supCode,
                "buttonCode": btnCode,
                "aCount": params.aCount,
                "warMark": params.warMark,
                "isTwarehouse": isTwarehouse
              }
            }
            this.$http.post(this.Api.VENUS_WMS_RETURNTASKGOODS_RETURNTASKGOODS, param).then(res => {
              if (res.success) {
                this.$Message.success(res.message);
                this.queryTable(this.pageData.pageCurrent);
              }
            })
          }
        })
      }
    },
    mounted() {
      this.queryTable();
      this.tableHeight = Number(window.innerHeight - 248);
      this.tableWidth = Number(window.innerWidth - 268);
      this.contentHeight = Number(window.innerHeight - 156);
    },
    data() {
      return {
        formData: {
          fname: '',
          tname: '001',
          sname: ''
        },
        contentHeight: '',
        tableHeight: '',
        tableWidth: '',
        loading: false,
        returnGoods: {},
        editParams: {
          warMark: "",
          aCount: "",
          ogrCode: ""
        },
        pageData: {
          columns: [
            {
              title: '项目组',
              key: 'warName',
              align: 'center',
              fixed: 'left',
              width: 140
            },
            {
              title: '申请人',
              key: 'userName',
              align: 'center',
              fixed: 'left',
              width: 100
            },
            {
              title: '采购单号',
              key: 'oCode',
              align: 'center',
              width: 140
            },
            {
              title: '货品编码',
              key: 'ogrCode',
              align: 'center',
              width: 140
            },
            {
              title: '品名',
              key: 'spName',
              align: 'center',
              width: 134
            },
            {
              title: '规格',
              key: 'skNorm',
              align: 'center',
              width: 108
            },
            {
              title: '品牌',
              key: 'spBrand',
              align: 'center',
              width: 100
            },
            {
              title: '总金额',
              key: 'totalSprice',
              align: 'center',
              width:100
            },
            {
              title: '备注',
              key: 'spMark',
              align: 'center',
              width: 140
            },
            {
              title: '退货原因',
              key: 'typeName',
              align: 'center',
              width: 128
            },
            {
              title: '退货数量',
              key: 'gCount',
              align: 'center',
              width: 70
            },
            {
              title: '单位',
              key: 'skUnit',
              align: 'center',
              width: 46
            },
            {
              title: '状态',
              key: 'ogrStatus',
              align: 'center',
              width: 60
            },
            {
              title: '仓库说明',
              key: 'warMark',
              align: 'center',
              width: 130,
              fixed: 'right',
              render: (h, params) => {
                if (params.row.$isEdit) {
                  return h('input', {
                    domProps: {
                      value: params.row.warMark
                    },
                    style: {
                      width: '50px',
                      textAlign: 'center',
                      outline: 'none'
                    },
                    on: {
                      input: function (event) {
                        params.row.warMark = event.target.value
                      }
                    }
                  });
                } else {
                  return h('div', params.row.warMark);
                }
              }
            },
            {
              title: '实退数量',
              key: 'aCount',
              align: 'center',
              width: 70,
              fixed: 'right',
            },
            {
              title: '操作',
              key: 'action',
              align: 'center',
              fixed: 'right',
              width: 220,
              render: (h, params) => {
                let actionBtn = []
                actionBtn = [
                  h('Button', {
                    props: {
                      size: 'small',
                      disabled: params.row.isEditCount == 2 ? true : false
                    },
                    style: {
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        if (params.row.$isEdit) {
                          this.editParams.warMark = params.row.warMark;
                          this.editParams.aCount = params.row.aCount;
                          this.editParams.ogrCode = params.row.ogrCode;
                          this.handleSave(params.row)
                        } else {
                          this.handleEdit(params.row)
                        }
                      }
                    }
                  }, params.row.$isEdit ? '保存' : '编辑备注'),
                  h('Button', {
                    props: {
                      size: 'small',
                    },
                    style: {
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        this.operation('确定要回库吗？', params.row, 3, 1);
                      }
                    }
                  }, '回库操作'),
                  h('Button', {
                    props: {
                      size: 'small',
                    },
                    style: {
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        this.operation('确定要入库吗？', params.row, 5, 1);
                      }
                    }
                  }, '入库操作'),
                  h('Button', {
                    props: {
                      size: 'small',
                    },
                    style: {
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        this.operation('确定要转运营吗？', params.row, 8, 2);
                      }
                    }
                  }, '转运营'),
                ]
                const btnObj = {
                  1: () => {
                    actionBtn[2] = ''
                  },
                  7: () => {
                    actionBtn[1] = ''
                  },
                  8: () => {
                    actionBtn[1] = ''
                  }
                };
                btnObj[params.row.buttonType]();
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

<style>
  .ivu-table .blue td {
    background: #2db7f5;
    color: #fff;
  }

  .ivu-table .info td {
    background: #2d8cf0;
    color: #fff;
  }

  .ivu-table .success td {
    background: #19be6b;
    color: #fff;
  }

  .ivu-table .warning td {
    background: #f90;
    color: #fff;
  }
</style>
