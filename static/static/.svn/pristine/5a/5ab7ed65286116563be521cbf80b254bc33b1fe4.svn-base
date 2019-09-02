<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>出仓单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="66" inline>
          <FormItem label="起始日期">
            <DatePicker type="date" v-model="params.stime" @on-change="onselectstart" placeholder="开始日期"
                        style="width: 140px"></DatePicker>
          </FormItem>
          <FormItem label="结束日期">
            <DatePicker type="date" @on-change="onselectend" placeholder="结束日期" style="width: 140px"></DatePicker>
          </FormItem>
          <FormItem label="状态">
            <Select v-model="params.status" style="width: 100px">
              <Option value="">全部</Option>
              <Option value="1">已预报</Option>
              <Option value="2">已创建</Option>
              <Option value="3">已拣货</Option>
              <Option value="4">已验货</Option>
              <Option value="5">已出仓</Option>
              <Option value="6">已收货</Option>
              <Option value="7">已取消</Option>
            </Select>
          </FormItem>
          <FormItem label="类型">
            <Select :style="{width:'180px'}" v-model="params.type">
              <Option value="">全部</Option>
              <Option v-for="(item,index) in  invoice_type" :value="item.code" :key="index">{{item.label}}</Option>
            </Select>
          </FormItem>
          <FormItem label="出仓单号">
            <Input v-model="params.code" style="width: 100px  "></Input>
          </FormItem>
          <FormItem label="订单号">
            <Input v-model="params.ecode" style="width: 100px  "></Input>
          </FormItem>
          <Button @click="search()" type="primary">查询</Button>
        </Form>
        <div class="goodsList">
<!--          <Upload
            :show-upload-list="false"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
            :on-success="handleSuccess"
            :action="this.$http.baseUrl.host1"
            :data="{'service': this.Api.VENUS_WMS_INVOICE_INVOICE_IMPORT}"
            style="float: left; margin-right: 10px;" >
            <Button type="primary">导入出仓单</Button>
          </Upload>
          <Button type="success" @click="confirmPrediction" >确认预报</Button>-->
          <Table style="margin-top: 14px" :height="tableHeight" border ref="selection" @on-selection-change="selectRow"
                 :columns="orderTitle"
                 :data="list"></Table>
          <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
          <Modal v-model="modal" cancel-text=""
                 title="订单轨迹">
            <Table border :columns="trackTitle" :data="trackList"></Table>
          </Modal>
          <div class="page"
               style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0;">
            <Page :total="totalCount" style="float: right" :current="pageCurrent+1" :page-size="pageSize"
                  @on-change="pageChange" show-total
                  show-elevator></Page>
          </div>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "out-order-manage",
    props: {
      data: Array
    },
    data() {
      return {
        invoice_type: invoice_type,
        houseType: Boolean(houseType),
        contentHeight: 0,
        tableHeight: 0,
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        params: {
          stime: '',
          etime: '',
          status: '',
          type: '',
          code: '',
          ecode: ''
        },
        list: [],
        keyword: null,
        modal: false,
        orderTitle: [
          /*{
            type: 'selection',
            width: 60,
            align: 'center'
          },*/
          {
            title: '出仓单号',
            key: 'invCode',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'invCtime',
            align: 'center',
            width:146
          },
          {
            title: '下单员',
            key: 'invUname',
            align: 'center',
            width:110
          },
          {
            title: '类型',
            key: 'invType',
            align: 'center',
            width:60
          },
          {
            title:'订单号',
            key:"invEcode",
            align:'center'
          },
          {
            title: '备注',
            key: 'invMark',
            align: 'center',
          },
          {
            title: '当前状态',
            key: 'invStatMsg',
            align: 'center',
            width:90
          },
          {
            title: '操作',
            key: 'action',
            width: 280,
            align: 'center',
            render: (h, params) => {
              let actionBtn = [];
              // if (params.row.invStatus  != 1) {
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
                        const invCode = params.row.invCode;
                        const invStatus = params.row.invStatus;
                        this.$router.push({
                          name: 'editOutOrder',
                          query: {
                            invCode: invCode,
                            invStatus: invStatus,
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
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        this.seeTrajectory(params.row)
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
                        this.exportOrder(params.row.invCode)
                      }
                    }
                  }, '下载出仓单'),
                ]
/*              } else {
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
                        const invCode = params.row.invCode;
                        const invStatus = params.row.invStatus;
                        this.$router.push({
                          name: 'editOutOrder',
                          query: {
                            invCode: invCode,
                            invStatus: invStatus,
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
                        this.seeTrajectory(params.row)
                      }
                    }
                  }, '查看轨迹'),
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small'
                    },
                    on: {
                      click: () => {
                        const _this = this;
                        // if(params.row.invStatus!=1) {
                        //   _this.$Modal.warning({
                        //     title: '提示',
                        //     content: `当前状态不可删除`,
                        //   })
                        // }else {
                        this.remove(params.row, params.index)
                        // }
                      }
                    }
                  }, '删除'),
                  h('Button', {
                    props: {
                      type: 'primary',
                      size: 'small'
                    },
                    on: {
                      click: () => {
                        this.exportOrder(params.row.invCode)
                      }
                    }
                  }, '下载出仓单'),
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
        trackList: [],
        selection: [],
        formData:{
          fname:'',
          tname:'005',
          sname:''
        },
      }
    },
    methods: {
      exportOrder(code) {
        const _this = this;
        this.formData.sname=`出仓单${code}.xlsx`;
        let params={
          "service":_this.Api.VENUS_WMS_INVOICE_INV_EXPORT,
          "data":{
            'code':code,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INV_EXPORT,params).then(res =>{
          if(res.success){
            this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
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
        if(res.error==0){
          if(!res.success){
            _this.$Modal.warning({
              title: '提示',
              content: res.message,
            })
          }else {
            _this.search();
            _this.$Modal.success({
              title: '提示',
              content: '导入成功',
            })
          }
        }else {
          _this.$Modal.warning({
            title: '提示',
            content: res.msg,
          })
        }
      },
      seeTrajectory(row) {
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_TRACE_SEARCH,
          data: {
            invCode: row.invCode
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_TRACE_SEARCH, params).then(res => {
          if (res.data && res.data.length > 0) {
            _this.modal = true;
            _this.trackList = res.data
          }
        })
      },
      confirmPrediction() {
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_CONFIRM,
          data: {
            list: _this.selection
          }
        }
        if (params.data.list.length > 0) {
          _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_CONFIRM, params).then(res => {
            if (res.success) {
              _this.search();
              _this.$Message.info('确认预报成功')
            }
          })
        } else {
          this.$Modal.warning({
            title: '提示',
            content: '请选择预报单号',
          });
        }
      },
      remove(row, index) {
        const _this = this;
        _this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_INVOICE_INVOICE_DELETE,
              data: {
                invCode: row.invCode
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_DELETE, params).then(res => {
              if (res.success) {
                _this.search();
              }
            })
          }
        })
      },
      selectRow(selection) {
        const _this = this
        _this.selection = []
        if (selection.length == 1) {
          _this.selection.push(selection[0].invCode)
        } else if (selection.length > 1) {
          for (let item in selection) {
            _this.selection.push(selection[item].invCode);
          }
        }
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
      search(page) {
        const _this = this;
        _this.list = [];
        _this.totalCount = 0;
        _this.pageCurrent = 0;
        _this.pageSize = 0;
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_SEARCH,
          data: {
            stime: _this.params.stime,
            etime: _this.params.etime,
            status: _this.params.status,
            type: _this.params.type,
            code: _this.params.code,
            ecode: _this.params.ecode,
            pageCurrent: page,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.totalCount = parseInt(res.data.totalCount);
            _this.pageSize = parseInt(res.data.pageSize);
            _this.pageCurrent = parseInt(res.data.pageCurrent);
            for (let i in res.data.list) {
              if (res.data.list[i].invStatus != 1) {
                res.data.list[i]._disabled = true
              }
            }
            _this.list = res.data.list;
          }
        })
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.search(pageCurrent);
      },
    },
    mounted() {
      this.search();
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 308)
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 320)
        }
      }
    }
  }
</script>

<style>

</style>
