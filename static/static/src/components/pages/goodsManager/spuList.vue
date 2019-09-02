<style>
  .operate {
    margin-bottom: 10px;
    overflow: hidden;
  }
</style>
<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>货品数据管理</BreadcrumbItem>
      <BreadcrumbItem>SPU管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="80" ref="formInline" inline>
          <!--<FormItem label="客户">
                    <Select style="width:160px;" v-model="searchData.exwCode">
                        <Option v-for="item in warehouse" :value="item.war_code" :key="item.war_code">{{item.war_name}}</Option >
                    </Select >
                </FormItem>-->
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
          <FormItem label="名称">
            <Input type="text" placeholder="" v-model="searchData.spName"></Input>
          </FormItem>
          <Button type="primary" @click="queryTable()">查询</Button>
        </Form>
        <div class="operate">
          <Button type="warning" style="float: left;" @click="exportSpuData">下载全部SPU数据</Button>
          <Upload
            :show-upload-list="false"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
            :on-success="handleSuccess"
            :action="this.$http.baseUrl.host1"
            :data="{'service': this.Api.VENUS_WMS_SPU_PERCENT_IMPORT}"
            style="float: left; margin-left: 10px;">
            <Button type="primary">导入客户利润率</Button>
          </Upload>
          <Upload
            :show-upload-list="false"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
            :on-success="handleSuccess"
            :action="this.$http.baseUrl.host1"
            :data="{'service': this.Api.VUNUS_WMS_SPU_SPRICE_IMPORT}"
            style="float: left;margin-left: 10px;">
            <Button type="primary">导入内部销售价</Button>
          </Upload>
          <Upload
            :show-upload-list="false"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
            :on-success="handleSuccess"
            :action="this.$http.baseUrl.host1"
            :data="{'service': this.Api.VUNUS_WMS_SPU_BPRICE_IMPORT}"
            style="float: left;margin-left: 10px;">
            <Button type="primary">导入内部采购价</Button>
          </Upload>
          <Upload
            :show-upload-list="false"
            :format="['xlsx']"
            :on-format-error="handleFormatError"
            :on-success="handleSuccess"
            :action="this.$http.baseUrl.host1"
            :data="{'service': this.Api.VUNUS_WMS_SPU_SUPPLIER_IMPORT}"
            style="float: left;margin-left: 10px;">
            <Button type="primary">导入供货商设置</Button>
          </Upload>
        </div>
        <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data"
               :loading="loading"></Table>
        <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
              :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
        <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
          <input type="hidden" name="fname" v-model="formData.fname"/>
          <input type="hidden" name="tname" v-model="formData.tname"/>
          <input type="hidden" name="sname" v-model="formData.sname"/>
        </form>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    methods: {
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        var params = {
          "service": this.Api.VENUS_WMS_SPU_SPU_SEARCH,
          "data": {
            "spName": this.searchData.spName,
            "spType": this.searchData.spType,
            "spSubtype": this.searchData.spSubtype,
            "exwCode": this.searchData.exwCode,
            "pageCurrent": curPage

          }
        }
        this.$http.post(this.Api.VENUS_WMS_SPU_SPU_SEARCH, params).then(res => {
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
      exportSpuData() {
        // if(this.searchData.exwCode==0){
        // 	this.$Modal.warning({
        // 	title:'提示',
        // 	content:'请先选择客户再进行下载。'
        // })
        // }else{
        var params = {
          "service": this.Api.VENUS_WMS_SPU_SPU_EXPORT,
          "data": {
            'exwCode': this.searchData.exwCode,
          }
        }
        this.$http.post(this.Api.VENUS_WMS_SPU_SPU_EXPORT, params).then(res => {
          if (res.success) {
            this.formData.fname = res.data;
            var I = this;
            setTimeout(function () {
              I.$refs.submitForm.submit();
            }, 200)

          }
        })
        // }

      },
      handleFormatError() {
        this.$Modal.warning({
          title: '提示',
          content: '文件格式不符，请重新选择.xlsx的文件上传！'
        });
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
      }
    },
    mounted() {
      this.queryTable();
      this.tableHeight = Number(window.innerHeight - 320);
      this.contentHeight = Number(window.innerHeight - 170);
    },
    data() {
      return {
        contentHeight: 0,
        tableHeight: 0,
        warehouse: warehouse,
        type: type,
        triggerData: subType,
        subType: {"0": "全部"},
        loading: true,
        searchData: {
          spName: "",
          spType: "0",
          spSubtype: "0",
          exwCode: "0",
        },
        pageData: {
          columns: [
            {
              title: 'SPU编码',
              key: 'spCode'
            },
            {
              title: '名称',
              key: 'spName'
            },
            {
              title: '品牌',
              key: 'spBrand'
            },
            {
              title: '单位规格',
              key: 'spNorm'
            },
            {
              title: '单位',
              key: 'spUnit'
            },
            {
              title: '内部采购价',
              key: 'spBprice'
            },
            {
              title: '内部销售价',
              key: 'spSprice'
            },
            /*{
                          title:'客户利润率',
                          key:'cltP ercent'
                      },*/
            {
              title: '客户利润价',
              key: 'cltProfit'
            },
            {
              title: '客户销售价',
              key: 'cltSprice'
            },
            {
              title: '供货商编码',
              key: 'supCode'
            },
            {
              title: '供货商名称',
              key: 'supName'
            }
          ],
          data: [],
          totalCount: 0,
          pageSize: 0,
          pageCurrent: 0
        },
        formData: {
          fname: '',
          tname: '001',
          sname: '全部SPU数据表.xlsx'
        }
      }

    }
  }
</script>
