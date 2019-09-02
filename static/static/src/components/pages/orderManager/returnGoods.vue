<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>订单管理</BreadcrumbItem>
      <BreadcrumbItem>退货单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="100" ref="formInline" inline>
          <FormItem label="项目组：">
            <Select :style="{width:'180px'}" v-model="returnGoods.warcode">
              <Option v-for="(item,index) in  warehouse" :value="item.war_code" :key="index">{{item.war_name}}</Option>
            </Select>
          </FormItem>
          <FormItem label="退货单状态：">
            <Select style="width:150px" :transfer="true" v-model="returnGoods.rtStatus">
              <Option value="1">申请中</Option>
              <Option value="2">已处理</Option>
            </Select>
          </FormItem>
          <FormItem label="申请日期：">
            <DatePicker type="daterange"  placeholder="请选择申请日期" style="width: 200px" @on-change="selectDate"></DatePicker>
          </FormItem>
          <FormItem :label-width="40">
            <Button type="primary" @click="queryTable()">查询</Button>
          </FormItem>
        </Form>

        <Table :height="tableHeight" border ref="selection" :row-class-name="rowClassName" :columns="pageData.columns" :data="pageData.data"
               :loading="loading"></Table>
        <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
              :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "return-goods",
    methods: {
      rowClassName(row, index) {   //表格tr背景
        if ((parseFloat(row.rtStatus) == 1)) {
          return 'red';
        }
        return '';
      },
      selectDate(val) {
        this.returnGoods.sTime=val[0];
        this.returnGoods.eTime=val[1];
      },
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        let params = {
          "service": this.Api.VENUS_WMS_RETURNTASK_RETURNTASK_SEARCH,
          "data": {
            "rtStatus": this.returnGoods.rtStatus,
            "sTime":this.returnGoods.sTime,
            "eTime":this.returnGoods.eTime,
            "warCode":this.returnGoods.warcode,
            "pageCurrent": curPage
          }
        }
        console.log(params)
        this.$http.post(this.Api.VENUS_WMS_RETURNTASK_RETURNTASK_SEARCH, params).then(res => {
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
    },
    mounted() {
      this.queryTable();
      this.tableHeight = Number(window.innerHeight - 262);
      this.contentHeight = Number(window.innerHeight - 154);
    },
    data() {
      return {
        warehouse: warehouse,
        contentHeight: 0,
        tableHeight: 0,
        loading: true,
        returnGoods: {
          rtStatus: "1",
          sTime:'',
          eTime:'',
          warcode:''
        },
        startDate:'',
        endDate:'',
        rowData: {
          ogrCode: '',
        },
        pageData: {
          columns: [
            {
              title: '退货单编码',
              key: 'rtCode',
              align: 'center',
            },
            {
              title: '项目组名称',
              key: 'warName',
              align: 'center',
            },
            {
              title: '创建日期',
              key: 'rtAddtime',
              align: 'center',
            },
            {
              title: '当前状态',
              key: 'rtStatusName',
              align: 'center',
              width: 180
            },
            {
              title: '操作',
              key: 'action',
              width: 150,
              align: 'center',
              render: (h, params) => {
                let actionBtn = []
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
                          const rtCode = params.row.rtCode;
                          this.$router.push({
                            name: 'returnGoodsList',
                            query: {
                              rtCode: rtCode,
                            }
                          });
                        }
                      }
                    }, '退货单详情')
                  ]
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
  .ivu-table .red td {
    background: #FF6B57;
    color: #fff;
  }
</style>
