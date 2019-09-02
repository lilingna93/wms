<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>入仓单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="66" inline :model="params">
          <FormItem label="起始日期">
            <DatePicker type="date" placeholder="开始日期" v-model="params.stime"
                        @on-change='onselectstart' style="width: 140px"></DatePicker>
          </FormItem>
          <FormItem label="结束日期">
            <DatePicker type="date" placeholder="结束日期"
                        @on-change='onselectend' style="width: 140px"></DatePicker><!--v-model="params.etime"-->
          </FormItem>
          <FormItem label="状态">
            <Select style="width: 80px" v-model="params.status">
              <Option value="">全部</Option>
              <Option value="1">已创建</Option>
              <Option value="3">已完成</Option>
              <Option value="2">已验货</Option>
              <Option value="4">已取消</Option>
            </Select>
          </FormItem>
          <FormItem label="供货商">
            <AutoComplete
              v-model="params.supplier"
              :data="showSupplier"
              @on-search="searchSupplier"
              @on-select="selectSupplier"
              placeholder="请输入查询供货商（关键字）"
              style="width:220px"></AutoComplete>
          </FormItem>
          <FormItem label="单号">
            <Input v-model="params.code"></Input>
          </FormItem>
          <Button @click="search()" type="primary">查询</Button>
          <FormItem>
            <Upload
              :show-upload-list="false"
              :format="['xlsx']"
              :on-format-error="handleFormatError"
              :on-success="handleSuccess"
              :action="this.$http.baseUrl.host1"
              :data="{'service': this.Api.VENUS_WMS_RECEIPT_REC_IMPORT}"
            >
              <Button type="primary">导入入仓单</Button>
            </Upload>
          </FormItem>
        </Form>
        <div class="goodsList">
          <Modal v-model="modal" cancel-text=""
                 title="订单轨迹">
            <Table border :columns="trackTitle" :data="trackList"></Table>
          </Modal>
          <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
          <Table :height="tableHeight" border ref="selection" :columns="orderTitle" :data="list"></Table>
        </div>
        <div class="page" style="margin-top: 14px;position: absolute;bottom: 10px;right: 14px">
          <Page :total="totalCount" :current="pageCurrent+1" :page-size="pageSize" @on-change="pageChange" show-total
                show-elevator></Page>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "enter-order-manage",
    data() {
      return {
        showSupplier:[],
        warehouse: warehouse,
        contentHeight: 0,
        tableHeight: 0,
        temData: [],
        arr: [],
        keyword: null,
        pageSize: 1,
        pageCurrent: 1,
        totalCount: 0,
        params: {
          stime: '',
          etime: '',
          status: '',
          code: '',
          supplier: '',
          supcode:''
        },
        list: [],
        modal: false,
        formData: {
          fname: '',
          tname: '004',
          sname: ''
        },
        orderTitle: [
          {
            title: '入仓单号',
            key: 'recCode',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'recCtime',
            align: 'center',
          },
          {
            title: '下单员',
            key: 'recUname',
            align: 'center',
          },
          {
            title: '类型',
            key: 'recTypeMsg',
            align: 'center',
          },
          {
            title: '当前状态',
            key: 'recStatMsg',
            align: 'center',
          },
          {
            title:'供货商',
            key: 'recSupName',
            align: 'center'
          },
          {
            title: '备注',
            key: 'recMark',
            align: 'center',
          },
          {
            title: '操作',
            key: 'action',
            width: 280,
            align: 'center',
            render: (h, params) => {
              let actionBtn = [];
              // if (params.row.recStatus != 1) {
              actionBtn = [
                h('Button', {
                  props: {
                    type: 'primary',
                    size: 'small'
                  },
                  style: {
                    marginRight: '10px',
                    marginLeft: '10px'
                  },
                  on: {
                    click: () => {
                      const recCode = params.row.recCode;
                      const recStatus = params.row.recStatus;
                      this.$router.push({
                        name: 'editEnterOrder',
                        query: {
                          recCode: recCode,
                          recStatus: recStatus
                        }
                      });
                    }
                  }
                }, '查看详情'),
                h('Button', {
                  props: {
                    type: 'success',
                    size: 'small'
                  },
                  style: {
                    marginRight: '10px'
                  },
                  on: {
                    click: () => {
                      this.searchTrace(params.row.recCode)
                    }
                  }
                }, '查看轨迹'),
                h('Button', {
                  props: {
                    type: 'primary',
                    size: 'small'
                  },
                  on: {
                    click: () => {
                      this.exportOrder(params.row.recCode)
                    }
                  }
                }, '下载入仓单'),
              ]
              /*  } else {
                  actionBtn = [
                    h('Button', {
                      props: {
                        type: 'primary',
                        size: 'small'
                      },
                      style: {
                        marginRight: '5px'
                      },
                      on: {
                        click: () => {
                          const recCode = params.row.recCode;
                          const recStatus = params.row.recStatus;
                          this.$router.push({
                            name: 'editEnterOrder',
                            query: {
                              recCode: recCode,
                              recStatus: recStatus
                            }
                          });
                        }
                      }
                    }, '修改'),
                    h('Button', {
                      props: {
                        type: 'success',
                        size: 'small'
                      },
                      style: {
                        marginRight: '5px'
                      },
                      on: {
                        click: () => {
                          this.searchTrace(params.row.recCode)
                        }
                      }
                    }, '查看轨迹'),
                    h('Button', {
                      props: {
                        type: 'error',
                        size: 'small',
                      },
                      on: {
                        click: () => {
                          const _this = this;

                          _this.remove(params.row.recCode, params.index);
                        }
                      }
                    }, '删除'),
                  ]
                }*/
              return h('div', actionBtn);
            }
          }
        ],
        trackTitle: [
          {
            title: '开始时间',
            key: 'stime'
          },
          {
            title: '结束时间',
            key: 'etime'
          },
          {
            title: '所用编号',
            key: 'code'
          },
          {
            title: '信息',
            key: 'mark'
          },
        ],
        trackList: []
      }
    },
    methods: {
      searchSupplier(value) {
        const _this = this;
        _this.showSupplier = [];
        let params = {
          service: _this.Api.VENUS_WMS_SUPPLIER_SUP_SEARCH,
          data: {
            suName: value
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_SUPPLIER_SUP_SEARCH, params).then(res => {
          if (res.success) {
            for (let i=0;i<res.data.list.length;i++) {
              _this.showSupplier.push(res.data.list[i].suName+"【编码："+res.data.list[i].suCode+"】")
            }
            console.log(_this.showSupplier)
          }
        })
      },
      selectSupplier(val) {
        this.params.supplier = val;
        if(this.params.supplier) {
          let  val = this.params.supplier.split("：")[1];
          this.params.supcode = val.substr(0,val.length-1);
        }
      },
      exportOrder(code) {
        const _this = this;
        this.formData.sname = `入仓单${code}.xlsx`;
        let params = {
          "service": _this.Api.VENUS_WMS_RECEIPT_REC_EXPORT,
          "data": {
            'code': code,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_REC_EXPORT, params).then(res => {
          if (res.success) {
            this.formData.fname = res.data;
            setTimeout(function () {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      handleFormatError() {
        this.$Modal.warning({
          title: '提示',
          content: '文件格式不符，请重新选择.xlsx的文件上传！'
        });
      },
      handleSuccess(res, file) {
        const _this = this;
        if (res.error == 0) {
          if (!res.success) {
            _this.$Modal.warning({
              title: '提示',
              content: res.message,
            })
          } else {
            _this.search();
            _this.$Modal.success({
              title: '提示',
              content: '导入成功',
            })
          }
        } else {
          _this.$Modal.warning({
            title: '提示',
            content: res.msg,
          })
        }
      },
      search(page) {
        const _this = this
        _this.list = [];
        _this.totalCount = 0;
        _this.pageCurrent = 0;
        _this.pageSize = 0;
        page = page ? page : 0
        _this.params.pageCurrent = page;
        let params = {
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_SEARCH,
          data: _this.params,
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.pageSize = Number(res.data.pageSize)
            _this.pageCurrent = Number(res.data.pageCurrent)
            _this.totalCount = Number(res.data.totalCount)
            _this.list = res.data.list;
            _this.params.supcode =""
          }
        })
      },
      searchTrace(recCode) {
        const _this = this
        let params = {
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_TRACE_SEARCH,
          data: {
            recCode: recCode
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_TRACE_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.modal = true;
            _this.trackList = res.data.list;
          }
        })
      },
      remove(recCode) {
        let _this = this;
        _this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_DELETE,
              data: {
                recCode: recCode
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_DELETE, params).then(res => {
              if (res.success) {
                _this.search();
              }
            })
          }
        })
      },
      onselectstart(val) {
        this.params.stime = val
      },
      onselectend(val) {
        if (val) {
          this.params.etime = ''
          let date = new Date(Date.parse(val) + 24 * 60 * 60 * 1000)
          this.params.etime = date.toISOString().replace(/T/, " - ")
        }
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.search(pageCurrent)
      },
    },
    mounted() {
      this.search();
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 310);
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 310);
        }
      }
    }
  }
</script>

<style>

</style>
