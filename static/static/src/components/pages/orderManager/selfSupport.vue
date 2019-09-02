<style type="text/css" media="screen">
  i {
    font-style: normal;
  }

  .mr_20 {
    display: inline-block;
    margin-right: 30px;
  }
</style>
<template>
  <Content :style="{padding: '0 10px 10px'}">
    <Breadcrumb :style="{margin: '8px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem to="purchaseOrder">订单管理</BreadcrumbItem>
      <BreadcrumbItem>自营货品列表</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div style="background:#eee;padding:8px;margin-bottom: 8px;">
          <div style="background: #fff;padding: 8px">
            <span class="mr_20">任务编号：{{$route.query.otCode}}</span>
            <span class="mr_20">自营货品：{{router.ownStatusMsg}}</span>
            <span style="float: right" v-if="router.ownStatusMsg=='未处理'">
              <!--<span style="margin-right: 16px"><Button type="primary" @click="outStock"
                                                       size="small">2、确认出库</Button></span>-->
               <span style="display: inline-block">
                 <Upload
                   :show-upload-list="false"
                   :format="['xlsx']"
                   :on-format-error="handleFormatError"
                   :on-success="handleSuccess"
                   :action="this.$http.baseUrl.host1"
                   :data="{'service': this.Api.VENUS_WMS_ORDERBATCH_OWNLIST_FINISH,'otCode':this.$route.query.otCode}"
                 >
              <Button type="primary" size="small">2、回传备货单，并确认出库</Button>
             </Upload>
               </span>
            </span>
          </div>
        </div>
        <div style="margin: 8px">
          所含采购单列表
        </div>
        <Table border :columns="orderTit" :height="orderHeight" :data="orderList"
        ></Table>
        <div style="margin: 8px">
          所含货品列表
          <span class="mr_20">
            <!--<Button type="primary" style="margin-left: 30px" size="small" @click="exportOrder">1、按项目分类导出备货单</Button>-->
            <Button type="primary" style="margin-left: 30px" size="small" @click="exportselfOrder">1、导出自营备货单</Button>
          </span>
        </div>
        <Table border :columns="goodsTit" :height="goodsHeight" :data="goodsList"
        ></Table>
        <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
          <input type="hidden" name="fname" v-model="formData.fname">
          <input type="hidden" name="tname" v-model="formData.tname">
          <input type="hidden" name="sname" v-model="formData.sname">
        </form>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    name: "selfSupport",
    methods: {
      getNowFormatDate() {
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
            _this.searchTask()
            _this.searchPurchase();
            _this.loadGoodsList();
            _this.$Modal.success({
              title: '提示',
              content: res.message,
            })
          }
        } else {
          _this.$Modal.warning({
            title: '提示',
            content: res.msg,
          })
        }
      },
      exportselfOrder() {
        const _this = this;
        this.formData.sname = `备货单${_this.getNowFormatDate()}_${_this.$route.query.otCode}.xlsx`;
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERBATCH_OWNLIST_EXPORT,
          "data": {
            'otCode': _this.$route.query.otCode,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_OWNLIST_EXPORT, params).then(res => {
          if (res.success) {
            this.formData.fname = res.data;
            setTimeout(function () {
              _this.$refs.submitForm.submit();
            }, 200)
          } else {
            _this.searchTask();
            _this.searchPurchase();
            _this.loadGoodsList();
          }
        })
      },
      exportOrder() {
        const _this = this;
        this.formData.sname = "分单(自营货品).xlsx";
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERTASK_EXPORT_SUP,
          "data": {
            'otCode': _this.$route.query.otCode,
            'type': _this.$route.query.type,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_EXPORT_SUP, params).then(res => {
          if (res.success) {
            this.formData.fname = res.data;
            setTimeout(function () {
              _this.$refs.submitForm.submit();
            }, 200)
          }
        })
      },
      searchPurchase() {      //搜索采购单
        let params = {
          "service": this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            "otCode": this.$route.query.otCode,
            "type": this.$route.query.type
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          for (let i in res.data.list) {
            if (res.data.list[i].otStatus != 1) {
              res.data.list[i]._disabled = true
            }
          }
          this.orderList = res.data.list;
        })
      },
      loadGoodsList() {      //搜索货品列表
        let params = {
          "service": this.Api.VENUS_WMS_ORDERTASK_TASK_DETAIL,
          "data": {
            "otCode": this.$route.query.otCode,
            "type": this.$route.query.type
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_DETAIL, params).then(res => {
          this.goodsList = res.data.list;
        })
      },
      outStock() { //出库
        const _this = this;
        _this.selection = []
        for (let i = 0; i < _this.orderList.length; i++) {
          _this.selection.unshift(_this.orderList[i].oCode);
        }
        _this.$Modal.confirm({
          title: '提示',
          content: `确定出库吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              "service": _this.Api.VENUS_WMS_ORDERTASK_OWN_INV_CREATE,
              "data": {
                "otCode": _this.$route.query.otCode,
                "oCodes": _this.selection,
                "isAllow": 0
              }
            }
            this.$http.post(this.Api.VENUS_WMS_ORDERTASK_OWN_INV_CREATE, params).then(res => {
              if (res.success) {
                _this.$Message.info('确认出库成功');
                _this.searchTask();
              } else {
                setTimeout(() => {
                  _this.confirmAgain(_this.selection, res.message)
                }, 500)
              }
            })
          }
        })
      },
      confirmAgain(selection, msg) {
        const _this = this;
        _this.$Modal.confirm({
          title: '提示',
          content: `${msg}确定要出库吗`,
          cancelText: `取消`,
          onOk() {
            let params = {
              "service": _this.Api.VENUS_WMS_ORDERTASK_OWN_INV_CREATE,
              "data": {
                "otCode": _this.$route.query.otCode,
                "oCodes": selection,
                "isAllow": 1
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_OWN_INV_CREATE, params).then(res => {
              if (res.success) {
                _this.$Message.info('确认出库成功');
                _this.searchTask();
              }
            })
          }
        })
      },
      searchTask() {
        let param = {
          "service": this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH,
          "data": {
            "otCode": this.$route.query.otCode
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH, param).then(res => {
          this.router.ownStatus = res.data.list[0].ownStatus;
          this.router.ownStatusMsg = res.data.list[0].ownStatusMsg;
        })
      }
    },
    mounted() {
      this.searchTask()
      this.searchPurchase();
      this.loadGoodsList();
      this.contentHeight = Number(window.innerHeight - 150);
      this.orderHeight = Number(window.innerHeight - 520);
      this.goodsHeight = Number(window.innerHeight - 440);

    },
    data() {
      return {
        formData: {
          fname: '',
          tname: '008',
          sname: ''
        },
        router: {
          ownStatus: "",
          ownStatusMsg: ""
        },
        selection: [],
        contentHeight: 0,
        orderHeight: 0,
        goodsHeight: 0,
        goodsTit: [
          {
            title: '货品编号',
            key: 'code',
            align: 'center'
          },
          {
            title: '货品名称',
            key: 'spName',
            align: 'center'
          },
          {
            title: '规格',
            key: 'spNorm',
            align: 'center'
          },
          {
            title: '品牌',
            key: 'spBrand',
            align: 'center'
          },
          {
            title: '产地',
            key: 'spForm',
            align: 'center'
          },
          {
            title: '备注',
            key: 'spMark',
            align: 'center'
          },
          {
            title: '采购数量',
            key: 'skCount',
            align: 'center'
          },
          {
            title: '单位',
            key: 'unit',
            align: 'center'
          }],
        goodsList: [],
        orderTit: [
          {
            title: '订单编号',
            key: 'oCode',
            align: 'center'
          },
          {
            title: '创建时间',
            key: 'oCtime',
            align: 'center'
          },
          {
            title: '下单客户',
            key: 'warName',
            align: 'center'
          },
          {
            title: '操作',
            align: 'center',
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: 'success',
                    size: 'small'
                  },
                  on: {
                    click: () => {
                      this.$router.push({
                        name: 'purchaseDetail',
                        query: {
                          oCode: params.row.oCode,
                        }
                      });
                    }
                  }
                }, '订单详情')
              ]);
            }
          }],
        orderList: [],
      }
    }
  }
</script>
