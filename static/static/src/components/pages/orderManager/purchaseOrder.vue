<style>
  .ivu-card-body {
    padding: 10px;
  }

  .ivu-table .red td {
    background: #FF6B57;
    color: #fff;
  }

  .ivu-table .green td {
    background: #e7f7df;
    color: #000000;
  }


  .ivu-table .gray td {
    background: #808695;
    color: #fff;
  }

</style>
<template>
  <Content :style="{padding: '0 10px 10px'}">
    <Breadcrumb :style="{margin: '10px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem>订单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Modal
          v-model="modal"
          title="查询订单（PS:从2019年4月25日订单开始可以搜索）"
          ok-text="关闭"
          :styles="{top: '20px'}"
          width="1180">
          <Form inline :label-width="66">
            <FormItem label="客户单位:" style="margin-bottom: 6px">
              <Select style="width:176px" :transfer="true" v-model="order.warCode">
                <Option v-for="(item,key) in  warehouse" :value="item.war_code" :key="key">{{item.war_name}}
                </Option>
              </Select>
            </FormItem>
            <FormItem label="送达时间:" style="margin-bottom: 6px">
              <DatePicker type="date" placeholder="选择送达时间"  v-model="order.pDate" format="yyyy-MM-dd"   @on-change="choosepDate" style="width:136px" :options="disDate"> </DatePicker>
            </FormItem>
            <FormItem label="是否是最终销售单:" style="margin-bottom: 6px" :label-width="112">
              <Select  :transfer="true" v-model="order.isFinalOrder" @on-change="selectisFinalOrder">
                <Option value="0">全部</Option>
                <Option value="2">是</Option>
                <Option value="1">否</Option>
              </Select>
            </FormItem>
            <FormItem label="是否已处理:" style="margin-bottom: 6px" :label-width="76">
              <Select  :transfer="true" v-model="order.isDownload" @on-change="selectDownload">
                <Option value="0">全部</Option>
                <Option value="2">已处理</Option>
                <Option value="1">未处理</Option>
              </Select>
            </FormItem>
            <FormItem style="margin-bottom: 6px" :label-width="16">
              <Button type="primary" @click="queryOrderTask(0)">查询</Button>
            </FormItem>
            <FormItem style="margin-bottom: 6px" :label-width="16">
              <Button type="success" @click="handleDownload">批量下载</Button>
            </FormItem>
          </Form>
          <Table border :columns="insTit" :data="insList" :height="insHeight" @on-selection-change="selectRows"></Table>
          <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total  :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
        </Modal>

        <Form :label-width="80" inline>
          <FormItem label="创建日期：" style="margin-bottom: 10px">
            <DatePicker type="date" placeholder="创建日期" format="yyyy-MM-dd" :transfer="true" v-model="orderSearch.cDate"
                        @on-change="chooseDate"
                        style="width: 150px"></DatePicker>
          </FormItem>
          <FormItem :label-width="20" style="margin-bottom: 10px">
            <Button type="primary" @click="search">查询</Button>
          </FormItem>
          <FormItem :label-width="20" style="margin-bottom: 10px">
            <Button type="primary" @click="searchNoIns(0)">查询全部订单</Button>
          </FormItem>
        </Form>
        <div style="margin-bottom: 8px;display: flex;justify-content: space-between;align-items: center">
          当日任务列表
          <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
          <div style="display: inline-block" >
            <!--<Button style="margin-left: 20px" @click="exportTest">test专用</Button>-->

            <Button type="info" style="margin-left: 20px" @click="exportTodayChoice">导出当天总备货单</Button>
            <Button type="primary" style="margin-left: 20px" @click="exportTodaySelf">导出当天总直采单</Button>
            <Button type="warning" style="margin-left: 20px" @click="exportTodaySale">导出当天总销售单</Button>
            <Button type="success" style="margin-left: 20px" @click="exportall">导出大餐酸奶</Button>
          </div>
        </div>
        <Table border :columns="taskTit" :row-class-name="rowClassName" :height="taskHeight" :data="taskList"></Table>
        <div style="margin: 8px 0">
          <Form inline :label-width="80">
            <FormItem label="客户单位:" style="margin-bottom: 6px">
              <Select style="width:176px" :transfer="true" v-model="orderSearch.warCode">
                <Option v-for="(item,key) in  wars" :value="item.code" :key="key">{{item.name}}
                </Option>
              </Select>
            </FormItem>
            <FormItem label="订单状态：" style="margin-bottom: 6px">
              <Select style="width:150px" :transfer="true" v-model="orderSearch.status" @on-change="selectStatus">
                <Option value="0">全部</Option>
                <Option value="1">待处理</Option>
                <Option value="2">处理中</Option>
                <Option value="3">已处理</Option>
              </Select>
            </FormItem>
            <FormItem label="送达时间：" style="margin-bottom: 6px">
              <Select @on-change="selectPdate" :label-in-value="true" v-model="selectDate" :transfer="true"
                      :style="{width:'150px'}"><!--:disabled="isSelect"-->
                <Option v-for="(item,key) in  plandates" :value="item" :key="key">{{item}}
                </Option>
              </Select>
            </FormItem>
            <FormItem style="margin-bottom: 6px">
              <Button type="primary" @click="searchOrder()">查询</Button>
            </FormItem>
            <FormItem :label-width="68" style="margin-bottom: 6px">
              <span style="font-size: 20px">待处理 <span style="color: #ed4014;">{{state.count1}}</span></span>
            </FormItem>
            <FormItem :label-width="20" style="margin-bottom: 6px">
              <span style="font-size: 20px">处理中 <span style="color: #ed4014;">{{state.count2}}</span></span>
            </FormItem>
            <FormItem :label-width="20" style="margin-bottom: 6px">
              <span style="font-size: 20px">已处理 <span style="color: #ed4014;">{{state.count3}}</span></span>
            </FormItem>
          </Form>
        </div>
        <div style="margin: 8px 0">
          <span style="display: inline-block;margin-right: 12px">采购单列表</span>
          <span v-if="orderSearch.otCode!=null">所属<b>{{orderSearch.otCode}}</b>的采购单</span>
          <span style="float: right"><Button type="info" size="small" @click="createTask">创建分单任务</Button></span>
        </div>
        <Table border :columns="orderTit" :height="orderHeight" :data="orderList"
               @on-selection-change="selectRow" :row-class-name="orderClassName"></Table>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    name: "purchase-order",
    methods: {
      queryOrderTask(curPage) {
        const _this = this;
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            "pDate": _this.order.pDate,
            "warCode": _this.order.warCode,
            "isFinalOrder": _this.order.isFinalOrder,
            "isDownload":_this.order.isDownload,
            "stime":"2019-04-25",
            "page":curPage
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          _this.insList = res.data.list;
          this.pageData.totalCount=parseInt(res.data.totalCount);
          this.pageData.pageSize=parseInt(res.data.pageSize);
          this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
        })
      },
      handleChange(count){
        count=count-1;
        this.searchNoIns(count);
      },
      searchNoIns(curPage) {
        let _this = this
        _this.modal = true;
        curPage=curPage?curPage:0;
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            "otCode": "",
            "status": "",
            "cDate": "",
            "stime":"2019-04-25",
            "page":curPage
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          _this.insList = res.data.list;
          this.pageData.totalCount=parseInt(res.data.totalCount);
          this.pageData.pageSize=parseInt(res.data.pageSize);
          this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
        })
      },
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
      exportTodayChoice() {
        const _this = this;
        if (_this.testDate()) {
          _this.formData.sname=`总备货单_${_this.getNowDate()}.xlsx`;
          _this.formData.tname = '002';
          if (_this.date == '') {
            _this.orderSearch.cDate =window.sessionStorage.getItem("date") ||_this.getNowFormatDate()
          }
          let params={
            "service":_this.Api.VENUS_WMS_ORDERBATCH_OWNLIST_EXPORTALL,
            "data":{
              'pDate':_this.orderSearch.cDate,
            }
          }
          _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_OWNLIST_EXPORTALL,params).then(res =>{
            if(res.success){
              _this.formData.fname=res.data;
              setTimeout(function(){
                _this.$refs.submitForm.submit();
              },200)
            }
          })
        }else {
          _this.$Message.warning('此功能将在16:00到24:00开放');
        }
      },
      exportTodaySelf() {
        const _this = this;
        if (_this.testDate()){
          _this.formData.sname=`总直采单_${_this.getNowDate()}.xlsx`;
          _this.formData.tname = '002';
          if (_this.date == '') {
            _this.orderSearch.cDate =  window.sessionStorage.getItem("date") || _this.getNowFormatDate()
          }
          let params={
            "service":_this.Api.VENUS_WMS_ORDERBATCH_SUPLIST_EXPORTALL,
            "data":{
              'pDate':_this.orderSearch.cDate,
            }
          }
          _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_SUPLIST_EXPORTALL,params).then(res =>{
            if(res.success){
              _this.formData.fname=res.data;
              setTimeout(function(){
                _this.$refs.submitForm.submit();
              },200)
            }
          })
        }else{
          _this.$Message.warning('此功能将在16:00到24:00开放');
        }
      },
      /*   exportTest() {
           const _this = this;
           _this.formData.sname=`测试_${_this.getNowDate()}.xlsx`;
           _this.formData.tname = '002';
           if (_this.date == '') {
             _this.orderSearch.cDate = _this.getNowFormatDate()
           }
           let params={
             "service":_this.Api.VENUS_WMS_ORDERBATCH_ORDERS_EXPORT,
             "data":{
               'pDate':_this.orderSearch.cDate,
             }
           }
           _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_ORDERS_EXPORT,params).then(res =>{
             if(res.success){
               _this.formData.fname=res.data;
               setTimeout(function(){
                 _this.$refs.submitForm.submit();
               },200)
             }
           })
         },*/
      exportTodaySale() {
        const _this = this;
        if (_this.testDate()){
          _this.formData.sname=`总销售单_${_this.getNowDate()}.xlsx`;
          _this.formData.tname = '007';
          if (_this.date == '') {
            _this.orderSearch.cDate =  window.sessionStorage.getItem("date") || _this.getNowFormatDate()
          }
          let params={
            "service":_this.Api.VENUS_WMS_ORDERBATCH_ORDER_EXPORT,
            "data":{
              'pDate':_this.orderSearch.cDate,
            }
          }
          _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_ORDER_EXPORT,params).then(res =>{
            if(res.success){
              _this.formData.fname=res.data;
              setTimeout(function(){
                _this.$refs.submitForm.submit();
              },200)
            }
          })
        }else {
          _this.$Message.warning('此功能将在16:00到24:00开放');
        }
      },
      exportall() {
        const _this = this;
        _this.formData.sname=`大餐酸奶_${_this.getNowDate()}.xlsx`;
        _this.formData.tname = '002';
        if (_this.date == '') {
          _this.orderSearch.cDate =  window.sessionStorage.getItem("date") || _this.getNowFormatDate()
        }
        let params={
          "service":_this.Api.VENUS_WMS_ORDERBATCH_SUPLIST_YOGURT_EXPORTALL,
          "data":{
            'pDate':_this.orderSearch.cDate,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_SUPLIST_YOGURT_EXPORTALL,params).then(res =>{
          if(res.success){
            _this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      time_range(beginTime, endTime, nowTime) {
        let strb = beginTime.split(":");
        if (strb.length != 2) {
          return false;
        }

        let stre = endTime.split(":");
        if (stre.length != 2) {
          return false;
        }

        let strn = nowTime.split(":");
        if (stre.length != 2) {
          return false;
        }
        let b = new Date();
        let e = new Date();
        let n = new Date();

        b.setHours(strb[0]);
        b.setMinutes(strb[1]);
        e.setHours(stre[0]);
        e.setMinutes(stre[1]);
        n.setHours(strn[0]);
        n.setMinutes(strn[1]);


        if (n.getTime() - b.getTime() > 0 && n.getTime() - e.getTime() < 0) {
          return true;
        } else {
          return false;
        }
      },
      testDate() {
        let nowTime = new Date().getHours() + ":" + new Date().getMinutes();
        return this.time_range("15:59", "23:59", nowTime);
      },
      selectStatus() {
        this.selectDate = null;
        this.plandates = []
      },
      selectPdate(param) {
        if (param != undefined) {
          this.selectDate = param.value;
        }
      },
      searchOrder() {
        this.orderSearch.otCode = null
        this.firstLoad();
      },
      rowClassName(row, index) {   //表格tr背景
        if (row.search==true){
          return 'gray';
        }
        if ((parseFloat(row.supStatus) == 1 || parseFloat(row.ownStatus) == 1)) {
          return 'red';
        }
        return '';
      },
      orderClassName(row, index) {   //表格tr背景
        if (parseFloat(row.otStatus) != 1) {
          return 'green';
        }
        return '';
      },
      search() {
        this.taskSearch();
        this.firstLoad();
      },
      createTask() {    //创建分单任务
        if (this.selection.length > 0) {
          let params = {
            "service": this.Api.VENUS_WMS_ORDERTASK_TASK_CREATE,
            "data": {
              "oCodes": this.selection
            }
          }
          this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_CREATE, params).then(res => {
            if (res.success == true) {
              this.$Message.info('创建分单后将会切换到当日任务');
              this.searchPurchase();
              this.taskSearch();
            }
          })
        } else {
          this.$Modal.warning({
            title: '提示',
            content: '请选择采购单',
          });
        }
      },
      taskSearch() {
        if (this.date == '') {
          this.orderSearch.cDate = window.sessionStorage.getItem("date") || this.getNowFormatDate()
        }
        let param = {
          "service": this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH,
          "data": {
            "cDate":   window.sessionStorage.getItem("date") || this.getNowFormatDate(),
            "otCode": ''
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH, param).then(res => {
          this.taskList = res.data.list;
          this.state = res.data.stat;
        })
      },
      selectRow(selection) {    //选择checbox
        const _this = this
        _this.selection = []
        if (selection.length == 1) {
          _this.selection.push(selection[0].oCode)
        } else if (selection.length > 1) {
          for (let item in selection) {
            _this.selection.push(selection[item].oCode);
          }
        }
      },
      handleDownload() {
        const _this = this;
        _this.formData.tname = '0071';
        _this.formData.sname = `最终销售单_${_this.getNowFormatDate()}.xlsx`;
        let param = {
          "service": _this.Api.VENUS_WMS_ORDERBATCH_ORDER_FINISH_EXPORT,
          "data": {
            "ocodes": _this.selections
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERBATCH_ORDER_FINISH_EXPORT, param).then(res => {
          if (res.success){
            _this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      selectRows(selection) {
        const _this = this
        _this.selections = []
        if (selection.length == 1) {
          _this.selections.push(selection[0].oCode)
        } else if (selection.length > 1) {
          for (let item in selection) {
            _this.selections.push(selection[item].oCode);
          }
        }
      },
      chooseDate(date) {      //选择时间
        window.sessionStorage.setItem("date",date);
        let cDate = window.sessionStorage.getItem("date");
        this.date = cDate;
        this.orderSearch.cDate = cDate
      },
      choosepDate(date) {
        this.order.pDate = date;
      },
      searchPurchase() {      //搜索采购单
        let _this = this
        _this.orderSearch.cDate = window.sessionStorage.getItem("date") ||  _this.getNowFormatDate();
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            "otCode": _this.orderSearch.otCode,
            "status": _this.orderSearch.status,
            "cDate": _this.orderSearch.cDate,
            "warCode": _this.orderSearch.warCode
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          for (let i in res.data.list) {
            if (res.data.list[i].otStatus == 1) {
              res.data.list[i]._disabled = false
            } else {
              res.data.list[i]._disabled = true
            }
          }
          _this.orderList = res.data.list;
        })
      },
      searchTaskList(row) { //查询当前任务采购单
        let params = {
          "service": this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            otCode: this.orderSearch.otCode
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          for (let i in res.data.list) {
            if (res.data.list[i].otStatus == 1) {
              res.data.list[i]._disabled = false
            } else {
              res.data.list[i]._disabled = true
            }
          }
          this.orderList = res.data.list;
          let index = row.index;
          for (let i = 0;i<this.taskList.length;i++){
            this.$set(this.taskList[i],'search',false);
          }
          this.$set(this.taskList[index],'search',true);
        })
      },
      deleteTask() {
        let params = {
          service: this.Api.VENUS_WMS_ORDERTASK_TASK_DELETE,
          data: {
            otCode: this.otCode,
          }
        }
        this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_DELETE, params).then(res => {
          if (res.success) {
            if (this.orderSearch.cDate.toString().indexOf("T") > -1) {
              this.orderSearch.cDate = window.sessionStorage.getItem("date") || this.getNowFormatDate();
            }
            let param = {
              "service": this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH,
              "data": {
                "cDate": this.orderSearch.cDate,
                "otCode": ''
              }
            }
            this.$http.post(this.Api.VENUS_WMS_ORDERTASK_TASK_SEARCH, param).then(res => {
              this.taskList = res.data.list;
              this.state = res.data.stat;
            })
            this.searchPurchase();
          }
        })
      },
      getNowFormatDate() {
        let date = new Date();
        let seperator1 = "-";
        let month = date.getMonth() + 1;
        let strDate = date.getDate();
        if (month >= 1 && month <= 9) {
          month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
          strDate = "0" + strDate;
        }
        let currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
        return currentdate;
      },
      firstLoad() {
        const _this = this
        if (_this.orderSearch.cDate.toString().indexOf("T") > -1) {
          this.orderSearch.cDate = window.sessionStorage.getItem("date") ||  this.getNowFormatDate();
        }
        let params = {
          "service": _this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH,
          "data": {
            status: _this.orderSearch.status,
            pDate: _this.selectDate,
            cDate: _this.orderSearch.cDate,
            warCode: _this.orderSearch.warCode
          }
        }
        if (_this.selectDate == "全部") {
          params.data.pDate = ''
        }
        _this.$http.post(_this.Api.VENUS_WMS_ORDERTASK_ORDER_SEARCH, params).then(res => {
          for (let i in res.data.list) {
            if (res.data.list[i].otStatus == 1) {
              res.data.list[i]._disabled = false
            } else {
              res.data.list[i]._disabled = true
            }
          }
          _this.orderList = res.data.list;
          _this.plandates = res.data.plandates;
          _this.wars = res.data.wars;
        })
      }
    },
    mounted() {
      this.orderSearch.status = "1";
      this.orderSearch.cDate =  window.sessionStorage.getItem("date") || this.getNowFormatDate();
      this.taskSearch();
      this.firstLoad();
      this.contentHeight = Number(window.innerHeight - 150);
      this.orderHeight = Number(window.innerHeight - 460);
      this.insHeight = Number(window.innerHeight - 260);
    },
    beforeDestroy() {
    },
    data() {
      return {
        wars:[],
        warehouse:warehouse,
        disDate: {
          disabledDate(date) {
            return date && date.valueOf() < 1556175762397;//(2019-04-25之前禁止选择)
          }
        },
        pageData: {
          totalCount:0,
          pageSize:0,
          pageCurrent:0
        },
        modal:false,
        formData: {
          fname: '',
          tname: '002',
          sname: ''
        },
        state:'',
        insTit: [
          {
            type: 'selection',
            width: 60,
            align: 'center'
          },
          {
            title: '订单编号',
            key: 'oCode',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'oCtime',
            align: 'center',
            width: 150
          },
          {
            title: '送达时间',
            key: 'oPdate',
            align: 'center',
            width: 100
          },
          {
            title: '下单客户',
            key: 'warName',
            align: 'center',
          },
          {
            title: '所属任务',
            key: 'otCode',
            align: 'center'
          },
          {
            title: '订单状态',
            key: 'statusMsg',
            align: 'center',
            width: 290,
          }
        ],
        insList:[],
        setInter: '',
        selectDate: '',
        plandates: [],
        date: '',
        order:{
          warCode:'',
          pDate:'',
          isFinalOrder:'',
          isDownload:''
        },
        orderHeight: 0,
        insHeight: 0,
        taskHeight: 200,
        selection: [],
        selections: [],
        orderSearch: {
          cDate: '',
          status: '',
          otCode: null,
          warCode:''
        },
        otCode: '',
        contentHeight: '',
        orderTit: [
          {
            type: 'selection',
            width: 60,
            align: 'center'
          },
          {
            title: '订单编号',
            key: 'oCode',
            align: 'center',
          },
          {
            title: '创建时间',
            key: 'oCtime',
            align: 'center',
            width: 130
          },
          {
            title: '送达时间',
            key: 'oPdate',
            align: 'center',
            width: 90
          },
          {
            title: '下单客户',
            key: 'warName',
            align: 'center',
          },
          {
            title: '所属任务',
            key: 'otCode',
            align: 'center'
          },
          {
            title: '订单状态',
            key: 'statusMsg',
            align: 'center',
            width: 290,
          },
          {
            title: '操作',
            align: 'center',
            width: 100,
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: 'info',
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
          }
        ],
        orderList: [],
        taskTit: [
          {
            title: '任务编号',
            key: 'otCode',
            align: 'center'
          },
          {
            title: '创建时间',
            key: 'ctime',
            align: 'center'
          },
          {
            title: '自营货品订单',
            key: 'ownStatusMsg',
            align: 'center',
          },
          {
            title: '直采货品订单',
            key: 'supStatusMsg',
            align: 'center'
          },
          {
            title: '所含订单数量',
            key: 'orderCount',
            align: 'center',
          },
          {
            title: '操作',
            align: 'center',
            width: 360,
            render: (h, params) => {
              let actionBtn = [
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
                      this.orderSearch.otCode = params.row.otCode;
                      this.searchTaskList(params);
                    }
                  }
                }, '查询'),
                h('Button', {
                  props: {
                    type: 'warning',
                    size: 'small'
                  },
                  style: {
                    marginRight: '5px'
                  },
                  on: {
                    click: () => {
                      const _this = this;
                      _this.otCode = params.row.otCode;
                      _this.$Modal.confirm({
                        title: '提示',
                        content: `确定删除此条任务吗？`,
                        cancelText: `取消`,
                        onOk() {
                          _this.deleteTask();
                        }
                      })
                    }
                  }
                }, '删除'),
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
                      this.$router.push({
                        name: 'selfSupport',
                        query: {
                          otCode: params.row.otCode,
                          type: 1,
                          ownStatus: params.row.ownStatus,
                          ownStatusMsg: params.row.ownStatusMsg,
                          cDate: params.row.ctime,
                        }
                      });
                    }
                  }
                }, '自营货品列表'),
                h('Button', {
                  props: {
                    type: 'info',
                    size: 'small'
                  },
                  on: {
                    click: () => {
                      this.$router.push({
                        name: 'directPurchase',
                        query: {
                          otCode: params.row.otCode,
                          type: 2,
                          supStatus: params.row.supStatus,
                          supStatusMsg: params.row.supStatusMsg,
                          cDate: params.row.ctime,
                        }
                      });
                    }
                  }
                }, '直采货品列表'),
              ];
              if (params.row.supStatus == 0) {
                actionBtn[3] = '';
              }
              if (params.row.ownStatus == 0) {
                actionBtn[2] = '';
              }
              if (params.row.ownStatus == 2 || params.row.supStatus == 2) {
                actionBtn[1] = '';
              }
              return h('div', actionBtn);
            }
          }
        ],
        taskList: []
      }
    }
  }
</script>
