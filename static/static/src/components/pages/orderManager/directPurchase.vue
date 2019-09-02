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
      <BreadcrumbItem>直采货品列表</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <div style="background:#eee;padding:8px;margin-bottom: 8px;">
          <div style="background: #fff;padding: 10px">
            <span class="mr_20">任务编号：{{$route.query.otCode}}</span>
            <span class="mr_20">直采货品：{{router.supStatusMsg}}</span>
          </div>
        </div>
        <div style="margin: 8px">
          <span class="mr_20">所含采购单列表</span>
          <span style="float: right"> <Button type="info" size="small" @click="outStock"
                                              v-if="router.supStatusMsg!='已处理'">3、确认出库</Button></span>
        </div>
        <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
          <input type="hidden" name="fname" v-model="formData.fname">
          <input type="hidden" name="tname" v-model="formData.tname">
          <input type="hidden" name="sname" v-model="formData.sname">
        </form>
        <Table border :columns="orderTit" :height="orderHeight" :data="orderList"
              ></Table>
        <div style="height: 40px;line-height: 40px">
          <span class="mr_20">所含货品列表</span>
          <span class="mr_20"><Button type="primary" size="small" @click="exportOrder">1、按供货商导出分单</Button></span>
           <span style="display: inline-block;float: right">
             <span class="mr_20" >
            选择供应商：
            <Select @on-change="selectSupCode" :label-in-value="true" v-model="supCode" :transfer="true"
                    :style="{width:'160px'}">
               <Option v-for="(item,key) in  suppliers" :value="key" :key="key">{{item}}</Option>
            </Select>
          </span>
          <span><Button type="primary" size="small" @click="saveData()">2、保存实际数据及当前采购价</Button></span>
           </span>
        </div>
        <Table border :columns="goodsTit" :height="goodsHeight" :data="goodsList"
        ></Table>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    name: "direct-purchase",
    methods: {
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
          this.selectList =  res.data.list;
          let supList = res.data.supList
          this.suppliers = supList
        })
      },
      saveData() {
        const _this = this
        this.$Modal.confirm({
          title: '提示',
          content: '确定要保存当前采购价吗？',
          onOk: () => {
            let spuList = [];
            let orderList = [];
            for (let i in _this.goodsList) {
              let obj = {}
              obj[_this.goodsList[i].spCode] = _this.goodsList[i].spBprice
              spuList.push(obj);
            }
            for (let i in _this.orderList) {
              orderList.push(_this.orderList[i].oCode);
            }
            let params = {
              "service": _this.Api.VENUS_WMS_ORDERTASK_SUP_BPRICE_UPDATE,
              "data": {
                "otCode":this.$route.query.otCode,
                "spuList": spuList,
                "orderList": orderList
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_SUP_BPRICE_UPDATE, params).then(res => {
              if (res.success) {
                this.loadGoodsList();
                this.$Message.info('货品价格修改成功');
              }
            })
          }
        })
      },
      exportOrder() {
        const _this = this;
        this.formData.sname="分单(直采货品).xlsx";
        let params={
          "service":_this.Api.VENUS_WMS_ORDERTASK_EXPORT_SUP,
          "data":{
            'otCode':_this.$route.query.otCode,
            'type':_this.$route.query.type,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_EXPORT_SUP,params).then(res =>{
          if(res.success){
            this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      selectSupCode(param) {
        if(param.value==0){
          this.goodsList = this.selectList
        }else {
          let list = [];
          for (let i = 0; i < this.selectList.length; i++) {
            if (this.selectList[i].supCode == param.value) {
              list.push(this.selectList[i]);
            }
          }
          this.goodsList = list
        }
      },
      outStock() { //出库
        let _this = this;
        _this.selection = []
        for (let i = 0;i< _this.orderList.length;i++){
          _this.selection.push(_this.orderList[i].oCode);
        }
        _this.$Modal.confirm({
          title: '提示',
          content: `确定出库吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              "service": _this.Api.VENUS_WMS_ORDERTASK_SUP_INV_CREATE,
              "data": {
                "otCode": _this.$route.query.otCode,
                "ctime": _this.selectTime,
                "oCodes": _this.selection
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_SUP_INV_CREATE, params).then(res => {
              if (res.success) {
                _this.$Message.info('出库成功');
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
          this.router.supStatusMsg=res.data.list[0].supStatusMsg;
          this.router.supStatus= res.data.list[0].supStatus;
        })
      }
    },
    mounted() {
      this.searchTask();
      this.contentHeight = Number(window.innerHeight - 150);
      this.orderHeight = Number(window.innerHeight - 520);
      this.goodsHeight = Number(window.innerHeight - 450);
      this.searchPurchase();
      this.loadGoodsList();
    },
    data() {
      return {
        router:{
          supStatus:"",
          supStatusMsg:""
        },
        selectList:[],
        formData:{
          fname:'',
          tname:'002',
          sname:''
        },
        supName: '',
        supCode: '',
        suppliers: [],
        contentHeight: '',
        orderHeight: 0,
        goodsHeight: 0,
        selection: [],
        selectTime: [],
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
            align: 'center',
            width: 80
          },
          {
            title: '单位',
            key: 'unit',
            align: 'center',
            width: 60
          },
          {
            title: '采购价格',
            key: 'spBprice',
            align: 'center',
            width: 100,
            render: (h, params) => {
                var _this = this
                return h('input', {
                  domProps: {
                    value: params.row.spBprice
                  },
                  style: {
                    width: '50px',
                    textAlign: 'center',
                    outline: 'none'
                  },
                  on: {
                    input: function (event) {
                      if (parseFloat(event.target.value) < 0 || event.target.value == '') {
                        params.row.spBprice = 0
                        _this.goodsList[params.index] = params.row
                      } else {
                        let value = parseFloat(parseFloat(event.target.value).toFixed(2))
                        params.row.spBprice = value;
                        _this.goodsList[params.index] = params.row
                      }
                    },
                  }
                });
            }
          },
        ],
        spBprice: '',
        goodsList: [],
      }
    }
  }
</script>
