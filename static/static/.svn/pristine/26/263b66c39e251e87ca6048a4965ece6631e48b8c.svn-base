<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>报表数据管理</BreadcrumbItem>
      <BreadcrumbItem>报表管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div style="width:100%;background: #e3e8ee;padding:10px;" class="tabs-item">
          <Tabs type="card" @on-click="handlerClick">
            <TabPane label="入仓单">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="date" :transfer="true" :options="disDate" placement="top-end" split-panels
                              placeholder="Select date" style="width: 200px"
                              @on-change='onselectDate' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="出仓单">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="date" placement="top-end" :transfer="true" :options="disDate" split-panels
                              placeholder="Select date" style="width: 200px"
                              @on-change='onselectDate' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="入库汇总">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="month" :transfer="true" placement="top-end" :options="disMonth" split-panels
                              placeholder="Select date"
                              style="width: 200px" @on-change='onselectMonth' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="出库汇总">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="month" placement="top-end" :transfer="true" :options="disMonth" split-panels
                              placeholder="Select date"
                              style="width: 200px" @on-change='onselectMonth' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="库存汇总">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="month" placement="top-end" :transfer="true" :options="disMonth" split-panels
                              placeholder="Select date"
                              style="width: 200px" @on-change='onselectMonth' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="台账登记表">
              <Form inline :label-width="90">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期" :label-width="56">
                  <DatePicker type="month" :transfer="true" placement="top-end" :options="disMonth" split-panels
                              placeholder="Select date"
                              style="width: 200px" @on-change='onselectMonth' v-model="isDates"></DatePicker>
                </FormItem>
                <FormItem label="货品名称" :label-width="100">
                  <AutoComplete
                    v-model="keywords"
                    :data="showList"
                    @on-search="handleSearch"
                    @on-select="handleSelect"
                    placeholder="请输入SPU货品名称"
                    style="width:280px" :transfer="true" placement="bottom"></AutoComplete>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
            <TabPane label="月度毛利统计表">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <!--<DatePicker type="daterange" placement="top-end" :transfer="true"  split-panels
                              placeholder="Select date"
                              style="width: 200px" @on-change='selectDate'></DatePicker>-->
                  <DatePicker type="month" :transfer="true" placement="top-end" :options="disMon" split-panels
                              placeholder="Select date"
                              style="width: 200px"   @on-change='selectMonth' v-model="isMonth"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="downReport">创建并下载报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>
          <!--  <TabPane label="申领单" v-if="houseType">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="date" :transfer="true" placement="top-end" :options="disDate" split-panels
                              placeholder="Select date" style="width: 200px"
                              @on-change='onselectDate' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>-->
         <!--   <TabPane label="采购单" v-if="houseType">
              <Form inline :label-width="80">
                <FormItem label="客户单位：">
                  <Input v-model="warname" placeholder="" :disabled="true" style="width: 160px"/>
                </FormItem>
                <FormItem label="日期">
                  <DatePicker type="date" :transfer="true" placement="top-end" :options="disDate" split-panels
                              placeholder="Select date" style="width: 200px"
                              @on-change='onselectDate' v-model="isDates"></DatePicker>
                </FormItem>
                <span>
                <Button type="primary" @click="createReport">创建报表</Button>
              </span>
                <span
                  style="display: inline-block;width:100%;text-align:right;margin-left: 14px;font-size: 12px;color: gray">(创建报表后一般会在十分钟内生成)</span>
              </Form>
            </TabPane>-->
          </Tabs>
        </div>
        <div style="padding: 10px 0 10px 0">
          <Button type="primary" style="margin-bottom: 10px" @click="baleDown">打包下载</Button>
          <div class="goodsList">
            <Table border ref="selection" :height="tableHeight" @on-selection-change="selectRow" :columns="orderTitle"
                   :data="list"></Table>
            <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
              <input type="hidden" name="fname" v-model="downData.repFname"/>
              <input type="hidden" name="tname" v-model="downData.type"/>
              <input type="hidden" name="sname" v-model="downData.repName"/>
            </form>
            <form ref="baleForm" :action="this.$http.baseUrl.host3" enctype="multipart/form-data" method="POST">
              <input type="hidden" name="fname" v-model="baleData.repFname"/>
              <input type="hidden" name="tname" v-model="baleData.type"/>
              <input type="hidden" name="sname" v-model="baleData.repName"/>
            </form>

            <form ref="reportForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
              <input type="hidden" name="fname" v-model="reportData.fname"/>
              <input type="hidden" name="sname" v-model="reportData.sname"/>
              <input type="hidden" name="tname" v-model="reportData.tname"/>
            </form>
          </div>
        </div>
        <div class="page"
             style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
          <Page :total="totalCount" style="float: right" :current="pageCurrent+1" :page-size="pageSize"
                @on-change="pageChange"
                show-total
                show-elevator></Page>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "report-form-manage",
    data() {
      return {
        reportDate:[],
        houseType: Boolean(houseType),
        type: '',
        selection: [],
        reportData: {
          fname:'',
          sname:'',
          tname:'052'
        },
        baleData: {
          repFname: [],
          type: '',
          repName: ''
        },
        downData: {
          repFname: '',
          type: '',
          repName: ''
        },
        isDates: '',
        warname: config.user.warname,
        tableHeight: 0,
        contentHeight: 0,
        spuList: [],
        reportType: '入仓单',
        keywords: '',
        showList: [],
        warehouse: warehouse,
        params: {
          warCode: config.user.warcode,
          type: 2,
          stime: '',
          etime: '',
          spCode: '',
          repName: '',
        },
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        list: [],
        disDate: {
          disabledDate(date) {
            return date && date.valueOf() > Date.now() - 86400000;
          }
        },
        disMonth: {
          disabledDate(date) {
            return date && date.valueOf() > Date.now() - new Date().getDate() * (24 * 60 * 60 * 1000)
          }
        },
        disMon: {
          disabledDate(date) {
            return date && date.valueOf() > Date.now()
          }
        },
        isMonth:'',
        orderTitle: [
          {
            type: 'selection',
            width: 60,
            align: 'center',
          },
          {
            title: '报表单号',
            key: 'code',
            align: 'center',
          },
          {
            title: '报表名称',
            key: 'repName',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'repCtime',
            align: 'center',
          },
          {
            title: '创建人',
            key: 'worName',
            align: 'center',
          },
          {
            title: '当前状态',
            key: 'repStatMsg',
            align: 'center',
          },
          {
            title: '操作',
            key: 'action',
            width: 200,
            align: 'center',
            render: (h, params) => {
              let actionBtn = [];
              if (params.row.repStatus == 3) {
                actionBtn = [
                  h('Button', {
                    props: {
                      type: 'success',
                      size: 'small',
                    },
                    style: {
                      marginRight: '5px'
                    },
                    on: {
                      click: () => {
                        this.down(params.row.repFname, params.row.repName);
                      }
                    }
                  }, '下载报表'),
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small',
                    },
                    on: {
                      click: () => {
                        this.remove(params.row.code);
                      }
                    }
                  }, '删除'),
                ]
              } else {
                actionBtn = [
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small',
                    },
                    on: {
                      click: () => {
                        this.remove(params.row.code);
                      }
                    }
                  }, '删除'),]
              }
              return h('div', actionBtn);
            }
          }
        ],
      }
    },
    methods: {
      selectMonth(date) {
        this.isMonth = date
      },
      downReport() {
        const _this = this;
          let params = {
            service: _this.Api.VENUS_WMS_REPORT_REPORT_MONTH_SPUTYPE,
            data: {
              time:_this.isMonth,
            }
          }
          _this.$http.post(_this.Api.VENUS_WMS_REPORT_REPORT_MONTH_SPUTYPE, params).then(res => {
            if(res.success) {
              _this.isMonth='';
              _this.$Message.success('正在下载报表');
              _this.reportData.fname = res.data.fname;
              _this.reportData.sname = res.data.sname;
              setTimeout(function () {
                _this.$refs.reportForm.submit();
              }, 200)
            }
          })
      },
      baleDown() {
        var I = this;
        if(I.selection.length<1){
          this.$Modal.warning({
            title: '提示',
            content: '请选择报表后下载',
          });
        }else {
          I.baleData.repFname = encodeURI(JSON.stringify(I.selection));
          I.baleData.repName = I.reportType + '.zip'
          I.baleData.type = I.setType();
          if (I.baleData.repFname.length > 0) {
            setTimeout(function () {
              I.$refs.baleForm.submit();
            }, 200)
          }
        }
      },
      setType() {
        const _this = this;
        const selectType = {
          2: () => {
            _this.type = '010'
          },
          4: () => {
            _this.type = "020"
          },
          6: () => {
            _this.type = "011"
          },
          8: () => {
            _this.type = "021"
          },
          10: () => {
            _this.type = "030"
          },
          12: () => {
            _this.type = "040"
          },
          14: () => {
            _this.type = '010'
          },
          16: () => {
            _this.type = "020"
          },
        }
        selectType[_this.params.type]();
        return _this.type
      },
      selectRow(selection) {
        const _this = this
        _this.selection = []
        if (selection.length == 1) {
          let fname = selection[0].repName;
          let fnames = selection[0].repFname + ".xlsx";
          let params ={}
         params[fname]=fnames
          _this.selection.push(params)
        } else if (selection.length > 1) {
          for (let item in selection) {
            let fname = selection[item].repName;
            let fnames = selection[item].repFname + ".xlsx"
            let params ={}
            params[fname]=fnames
            _this.selection.push(params);
          }
        }
      },
      down(repFname, repName) {
        var I = this;
        this.downData.repFname = repFname + ".xlsx"
        this.downData.repName = repName + ".xlsx"
        this.downData.type = this.setType();
        setTimeout(function () {
          I.$refs.submitForm.submit();
        }, 200)
      },
      handleSearch(value) {
        this.showList = []
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_GOODS_GOODS_SEARCH,
          data: {
            spName: _this.keywords,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.spuList = res.data.list;
            if (value) {
              _this.spuList.forEach(function (item) {
                if (item.code && item.name) {
                  if (item.name.indexOf(value) > -1) {
                    _this.showList.push(item.name + "【"+"规格：" + item.norm +"、编码："+item.code+ "】");
                  }
                }
              })
            }
          }
        })
      },
      handleSelect(value) {
        const _this = this
        var valList = value.split("【");
        let val = value.split("：")
        let vals = val[val.length-1]
        var valCode = val[val.length-1].substr(0,vals.length-1)
        if (value) {
          _this.spuList.forEach(function (item) {
            if (item.name==valList[0] && item.code == valCode) {
              _this.params.spCode = item.code
            }
          })
        }

      },
      createReport() {
        const _this = this;
        if (_this.params.type == 6 || _this.params.type == 8 || _this.params.type == 10 || _this.params.type == 12) {
          var repName = _this.params.stime.substr(0, 7) + "-" + _this.reportType
        } else {
          var repName = _this.params.stime.substr(0, 10) + "-" + _this.reportType
        }
        let params = {
          service: _this.Api.VENUS_WMS_REPORT_REPORT_CREATE,
          data: {
            warCode: _this.params.warCode,
            type: _this.params.type,
            stime: _this.params.stime,
            etime: _this.params.etime,
            code: _this.params.spCode,
            otherMsg: {
              repName: repName,
              spCode: _this.params.spCode,
            }
          }
        }
        if(this.params.type==12){
          params.data.all = 1;
        }
        if (this.params.type==12&&_this.params.spCode==''){
          this.$Modal.warning({
            title: '提示',
            content: '请选择货品',
          });
        }else {
          _this.$http.post(_this.Api.VENUS_WMS_REPORT_REPORT_CREATE, params).then(res => {
            if (res.success) {
            _this.getDetail();
            _this.isDates = ''
            _this.params.stime = ''
            _this.params.spCode = ''
            _this.keywords = ''
            _this.$Message.success('创建报表成功');
          }
        })
        }
      },
      getDetail(page) {
        const _this = this;
        _this.list = [];
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_REPORT_REPORT_SEARCH,
          data: {
            type: _this.params.type,
            pageCurrent: page
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_REPORT_REPORT_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.pageCurrent = Number(res.data.pageCurrent);
            _this.totalCount = Number(res.data.totalCount);
            _this.pageSize = Number(res.data.pageSize);
            for (let i in res.data.list) {
              if (res.data.list[i].repStatus != 3) {
                res.data.list[i]._disabled = true
              }
            }
            _this.list = res.data.list
          }
        })
      },
      remove(code) {
        const _this = this;
        this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_REPORT_REPORT_DELETE,
              data: {
                repCode: code
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_REPORT_REPORT_DELETE, params).then(res => {
              if (res.success) {
                _this.getDetail();
                _this.$Message.success('此报表已删除');
              }
            })
          }
        })
      },
      onselectDate(val) {
        if (val) {
          this.params.stime = val + ' 00:00:00'
          this.params.etime = ''
          let date = new Date(Date.parse(val) + 24 * 60 * 60 * 1000)
          this.params.etime = date.toISOString().replace(/T/, " ").substr(0, 19);
        }
      },
      onselectMonth(val) {
        if (val) {
          this.params.stime = val + '-01 00:00:00'
          this.params.etime = ''
          var last = Number(val.charAt(val.length - 1)) + 1;
          var str = val.substr(0, val.length - 1);
          this.params.etime = str + "" + last + '-01 00:00:00'
        }
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.getDetail(pageCurrent)
      },
      handlerClick(name) {
        const _this = this;
        _this.params.type = (name + 1) * 2
        let tabPane = document.querySelector('.ivu-tabs-tabpane');
        const selectObj = {
          2: () => {
            _this.reportType = '入仓单'
          },
          4: () => {
            _this.reportType = '出仓单'
          },
          6: () => {
            _this.reportType = '入库汇总'
          },
          8: () => {
            _this.reportType = '出库汇总'
          },
          10: () => {
            _this.reportType = '库存汇总'
          },
          12: () => {
            _this.reportType = '台账登记'
          },
          14: () => {
            _this.reportType = '月度毛利统计表'
          },
          16: () => {
            _this.reportType = '采购单'
          },
        }
        selectObj[_this.params.type]();
        if (!_this.params.type == 12) {
          tabPane.style.height = "auto"
        }
        // _this.getDetail()
      }
    },
    mounted() {
      // this.getDetail();
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 410)
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 410)
        }
      }
    }
  }
</script>

<style>
  .tabs-item .ivu-form-item {
    margin-bottom: 8px;
  }

  .tabs-item .ivu-form {
    padding-bottom: 8px;
  }

  .ivu-tabs-card > .ivu-tabs-content > .ivu-tabs-tabpane {
    background: #fff;
    margin-top: -16px;
    padding: 20px 20px 0px 20px;
  }

  .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab {
    border-color: transparent;
  }

  .ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active {
    border-color: #fff;
  }

  .ivu-picker-panel-content-right {
    display: none;
  }
  .ivu-picker-panel-body {
    min-width: auto !important;
  }
</style>
