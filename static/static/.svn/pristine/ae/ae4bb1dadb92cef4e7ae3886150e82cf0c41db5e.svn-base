<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>货品数据管理</BreadcrumbItem>
      <BreadcrumbItem to="/skuList">SKU管理</BreadcrumbItem>
      <BreadcrumbItem>外部客户SKU设置</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="80" ref="formInline" inline>
          <FormItem label="选择客户">
            <Select style="width:160px;" placeholder="请选择" v-model="warCode" @on-change="changeCustomer">
              <Option v-for="item in customerList" :value="item.warCode" :key="item.warCode">{{item.warName}}</Option>
            </Select>
          </FormItem>
          <Button type="primary" @click="searchCustomer">查询</Button>
        </Form>
        <div v-show="isShow">
          <Form :label-width="80" ref="formInline" inline>
            <FormItem label="一级分类">
              <Select style="width:160px;" v-model="searchData.spType" @on-change="changeType(searchData.spType)">
                <Option v-for="(item,key) in type" :value="key" :key="key">{{item}}</Option>
              </Select>
            </FormItem>
            <FormItem label="二级分类">
              <Select style="width:160px;" v-model="searchData.spSubtype">
                <Option v-for="(item,key) in subType" :value="key" :key="key">{{item}}</Option>
              </Select>
            </FormItem>
            <FormItem label="状态">
              <Select style="width:160px;" v-model="searchData.skStatus">
                <Option value="0">全部</Option>
                <Option value="1">已上线</Option>
                <Option value="2">已下线</Option>
              </Select>
            </FormItem>
            <FormItem label="产品名称">
              <Input type="text" placeholder="" v-model="searchData.spName"></Input>
            </FormItem>
            <Button type="primary" @click="queryTable()">查询</Button>
          </Form>
          <div style="margin-bottom: 20px;overflow: hidden">
            <Button type="warning" style="float: left;" @click="downTemplate()">导出客户销售方案</Button>
            <Upload
              :show-upload-list="false"
              :format="['xlsx']"
              :on-format-error="handleFormatError"
              :on-success="handleSuccess"
              :action="this.$http.baseUrl.host1"
              :data="{'service': this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_IMPORT,'data':warCode}"
              style="float: left; margin-left: 10px;">
              <Button type="primary">导入客户销售方案</Button>
            </Upload>
            <Button type="success" style="margin-left: 20px" @click="searchSkuData()">新发地定价</Button>
          </div>
          <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data"
                 :loading="loading"></Table>
          <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
                :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>

          <Modal
            v-model="skuDataModal"
            title="查询"
            ok-text="关闭"
            :styles="{top: '20px'}"
            width="1280">
            <Form inline >
    <!--          <FormItem label="发布日期:" style="margin-bottom: 6px">
                <DatePicker type="date" placeholder="选择发布日期"  format="yyyy-MM-dd"
                            @on-change="choosepDate" style="width:136px"></DatePicker>
              </FormItem>
              <FormItem style="margin-bottom: 6px">
                <Button type="primary" @click="searchSkuData()">查询</Button>
              </FormItem>-->
              <FormItem style="margin-bottom: 6px">
                <Button type="success" @click="exportExcel">导出新发地SKU数据</Button>
              </FormItem>
            </Form>
            <Table border :columns="insTit" :data="insList" :height="insHeight"></Table>
            <Page style="text-align: right;margin-top: 10px;" :total="pageDatas.totalCount" show-elevator show-total
                  :page-size="pageDatas.pageSize" :current="pageDatas.pageCurrent+1"
                  @on-change="handlePageChange"></Page>
          </Modal>
          <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "threshold-warning",
    data() {
      return {
        insHeight: 0,
        skuDataModal: false,
        isShow: false,
        contentHeight: null,
        tableHeight: 0,
        warCode: '',
        customerList: [],
        loading: true,
        type: type,
        triggerData: subType,
        subType: {"0": "全部"},
        formData: {
          fname: '',
          tname: '001',
          sname: ''
        },
        searchData: {
          spName: '',
          spType: '0',
          spSubtype: '0',
          skStatus: '0',
        },
        pageDatas: {
          totalCount: 0,
          pageSize: 0,
          pageCurrent: 0
        },
        order: {
          reTime: ''
        },
        insTit: [
          {
            title: 'SKU编码',
            key: 'skCode',
            align: 'center'
          },
          {
            title: 'SKU名称',
            key: 'skName',
            align: 'center'
          },
          {
            title: 'SPU编码',
            key: 'spCode',
            align: 'center'
          },
          {
            title: 'SPU名称',
            key: 'spName',
            align: 'center'
          },
          {
            title: '规格',
            key: 'skNorm',
            align: 'center'
          },
          {
            title: '单位',
            key: 'skUnit',
            align: 'center'
          },
          {
            title: '最低价',
            key: 'mPrice',
            align: 'center'
          },
          {
            title: '平均价',
            key: 'aPrice',
            align: 'center'
          },
          {
            title: '最高价',
            key: 'maPrice',
            align: 'center'
          },
          {
            title: '利润率',
            key: 'pPercent',
            align: 'center'
          },
          {
            title: '发布日期',
            key: 'reTime',
            align: 'center'
          },
        ],
        insList: [],
        pageData: {
          columns: [
            {
              title: 'SKU编码',
              key: 'skCode'
            },
            {
              title: '所属SPU编码',
              key: 'spCode'
            },
            {
              title: '产品名称',
              key: 'spName'
            },
            {
              title: '规格',
              key: 'skNorm'
            },
            {
              title: '规格数量',
              key: 'spCount'
            },
            {
              title: '采购单位',
              key: 'skUnit'
            },
            {
              title: '客户销售价',
              key: 'skEprice'
            },
            {
              title: '状态',
              key: 'skStatus',
              width: 150,
              align: 'center',
              render: (h, params) => {
                return h('i-switch', {
                  props: {
                    'size': 'large',
                    'true-value': '1',
                    'false-value': '2',
                    'value': params.row.skStatus
                  },
                  on: {
                    'on-change': () => {
                      this.loading = true;
                      this.switchStatus(params.row.skCode, params.row.skStatus);
                    },
                  }
                }, [
                  h('span', {
                    slot: 'open',
                    domProps: {
                      innerHTML: '上线'
                    }
                  }),
                  h('span', {
                    slot: 'close',
                    domProps: {
                      innerHTML: '下线'
                    }
                  })
                ])
              }

            }],
          data: [],
          totalCount: 0,
          pageSize: 0,
          pageCurrent: 0
        }
      }
    },
    methods: {
      getNowDate() {
        var date = new Date();
        var month = date.getMonth() + 1;
        var strDate = date.getDate();
        if (month >= 1 && month <= 9) {
          month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
          strDate = "0" + strDate;
        }
        var currentdate = date.getFullYear()  + month  + strDate;
        return currentdate;
      },
      exportExcel() {
        const _this = this;
        _this.formData.sname=`新发地SKU数据.xlsx`;
        let params={
          "service":_this.Api.VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_EXPORT,
          "data":{}
        }
        _this.$http.post(_this.Api.VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_EXPORT,params).then(res =>{
          if(res.success){
            _this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      choosepDate(date) {
        this.order.reTime = date;
      },
      searchSkuData(curPage) {
        this.skuDataModal =  true
        curPage=curPage?curPage:0;
        let params = {
          "service": this.Api.VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_SEARCH,
          "data": {
            "reTime": this.order.reTime,
            "pageCurrent":curPage
          }
        }
       this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_XINFADI_SKU_SEARCH, params).then(res => {
          this.insList = res.data.list;
          this.pageDatas.totalCount=parseInt(res.data.totalCount);
          this.pageDatas.pageSize=parseInt(res.data.pageSize);
          this.pageDatas.pageCurrent=parseInt(res.data.pageCurrent);
        })
      },
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        var params = {
          "service": this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_LIST,
          "data": {
            "warCode": this.warCode,
            "spName": this.searchData.spName,
            "spType": this.searchData.spType,
            "spSubtype": this.searchData.spSubtype,
            "skStatus": this.searchData.skStatus,
            "pageCurrent": curPage
          }
        }
        this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_LIST, params).then(res => {
          this.loading = false;
          this.pageData.data = res.data.list;
          this.pageData.totalCount = parseInt(res.data.totalCount);
          this.pageData.pageSize = parseInt(res.data.pageSize);
          this.pageData.pageCurrent = parseInt(res.data.pageCurrent);
        })
      },
      changeType(typeCode) {
        this.searchData.spSubtype = "0";
        if (typeCode == "0") {
          this.subType = {"0": "全部"};
        } else {
          for (let item in this.triggerData) {
            if (item == typeCode) {
              this.subType = this.triggerData[typeCode];

            }
          }
        }
      },
      handlePageChange(count) {
        count = count - 1;
        this.searchSkuData(count);
      },
      handleChange(count) {
        count = count - 1;
        this.queryTable(count);
      },
      switchStatus(skCode, skStatus) {
        if (skStatus == '1') {
          var params = {
            "service": this.Api.VENUS_WMS_SKUEXTERNAL_STATUS_OFFLINE,
            "data": {
              "skCode": skCode,
              "skStatus": '2',
              "warCode": this.warCode
            }
          }
          this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_STATUS_OFFLINE, params).then(res => {
            if (res.success) {
              this.loading = false;
              this.queryTable(this.pageData.pageCurrent)
              this.$Modal.success({
                title: '提示',
                content: res.message
              })
            }
          })
        } else {
          var params = {
            "service": this.Api.VENUS_WMS_SKUEXTERNAL_STATUS_ONLINE,
            "data": {
              "skCode": skCode,
              "skStatus": '1',
              "warCode": this.warCode
            }

          }
          this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_STATUS_ONLINE, params).then(res => {
            if (res.success) {
              this.loading = false;
              this.queryTable(this.pageData.pageCurrent)
              this.$Modal.success({
                title: '提示',
                content: res.message
              })
            }
          })
        }
      },
      searchCustomer() {
        if (this.warCode == '') {
          this.$Modal.warning({
            title: '提示',
            content: '请先选择客户再进行查询。'
          })
        } else {
          this.isShow = true;
          this.queryTable();
        }
      },
      getCustomerList() {
        var params = {
          "service": this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_CUSTOMER,
          "data": {}
        }
        this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_CUSTOMER, params).then(res => {
          if (res.success) {
            this.customerList = res.data.customer;
          }
        })

      },
      handleSuccess(res, file) {
        if (res.error == 0) {
          if (res.success) {
            this.$Modal.success({
              title: '提示',
              content: res.message
            })
            this.queryTable();
          } else {
            this.$Modal.warning({
              title: '提示',
              content: res.message
            })
          }
        } else {
          this.$Modal.warning({
            title: '提示',
            content: res.msg
          })
        }
      },
      handleFormatError() {
        this.$Modal.warning({
          title: '提示',
          content: '文件格式不符，请重新选择.xlsx的文件上传！'
        });
      },
      downTemplate() {
        var _this = this;
        this.formData.sname = '客户销售方案.xlsx';
        var params = {
          "service": this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_EXPORT,
          "data": {"warCode": this.warCode}
        }
        this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_ESKU_EXPORT, params).then(res => {
          if (res.success == true) {
            this.formData.fname = res.data;
            setTimeout(function () {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      changeCustomer() {
        this.searchData.spType = '0';
        this.searchData.spSubtype = '0';
        this.searchData.skStatus = '0';
      }
    },
    mounted() {
      this.tableHeight = Number(window.innerHeight - 278);
      this.contentHeight = Number(window.innerHeight - 164);
      this.insHeight = Number(window.innerHeight - 260);
      this.getCustomerList();
    },

  }
</script>

<style>
</style>
