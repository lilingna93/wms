<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>仓库任务管理 </BreadcrumbItem>
      <BreadcrumbItem>工单管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="100" inline>
          <FormItem label="工单类型">
            <Select v-model="params.type" style="width: 140px">
              <Option value="">全部</Option>
              <Option value="1">入仓业务-入仓</Option>
              <Option value="2">入仓业务-验货</Option>
              <Option value="3">入仓业务-上架</Option>
              <Option value="4">仓内业务-补货移区</Option>
              <Option value="5">出仓业务-拣货捡单</Option>
              <Option value="6">出仓业务-验货出仓</Option>
              <Option value="7">出仓业务-异常</Option>
            </Select>
          </FormItem>
          <FormItem label="工单状态">
            <Select v-model="params.status">
              <Option value="">全部</Option>
              <Option value="1">已创建</Option>
              <Option value="2">跟进中</Option>
              <Option value="3">已完成</Option>
              <Option value="4">已取消</Option>
            </Select>
          </FormItem>
          <FormItem label="操作人员编号">
            <Input v-model="params.worCode"></Input>
          </FormItem>
          <Button @click="search()" type="primary">查询</Button>
        </Form>
        <div class="goodsList">
          <Table :height="tableHeight" border ref="selection" :columns="orderTitle" :data="list"></Table>
          <div class="page"
               style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
            <Page :total="totalCount" :current="pageCurrent+1" style="float: right" :page-size="pageSize" @on-change="pageChange" show-total
                  show-elevator></Page>
          </div>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "work-list-manage",
    data() {
      return {
        contentHeight: 0,
        tableHeight: 0,
        totalCount: 0,
        pageSize: 0,
        pageCurrent: 0,
        params: {
          type: null,
          status: null,
          worCode: null,
          useDate: false
        },
        list: [],
        orderTitle: [
          {
            title: '工单编号',
            align: 'center',
            key: 'tCode'
          },
          {
            title: '创建时间',
            align: 'center',
            key: 'tCtime'
          },
          {
            title: '完成日期',
            align: 'center',
            key: 'tFtime'
          },
          {
            title: '类型',
            align: 'center',
            key: 'tType'
          },
          {
            title: '状态',
            align: 'center',
            key: 'tStatMsg',
            width: 110,
          },
          {
            title: '操作员',
            align: 'center',
            key: 'worName',
            width: 110,
          },
          {
            title: '所属单号（入仓/出仓）',
            align: 'center',
            key: 'code'
          },
          {
            title: '操作',
            width: 100,
            align: 'center',
            render: (h, params) => {
              let actionBtn = [];
              if (params.row.tStatus == 2 || params.row.worName != null) {
                actionBtn = [
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small'
                    },
                    on: {
                      click: () => {
                        const _this = this;
                        _this.cancel(params.row.tCode)
                      }
                    }
                  }, '取消'),
                ]
              } else {
                actionBtn = [
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small',
                      disabled: true
                    },
                    on: {
                      click: () => {
                        const _this = this;
                        _this.cancel(params.row.tCode)
                      }
                    }
                  }, '取消'),
                ]
              }
              return h('div', actionBtn);
            }
          }
        ],
      }
    },
    methods: {
      cancel(tCode) {
        let _this = this;
        _this.$Modal.confirm({
          title: '提示',
          content: `确定要取消吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_TASK_TASK_CANCEL,
              data: {
                tCode: tCode
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_TASK_TASK_CANCEL, params).then(res => {
              if (res.success) {
                _this.search();
                _this.$Message.success('取消成功');
              }
            })
          }
        })
      },
      search(page) {
        let _this = this
        _this.list = [];
        _this.totalCount = 0;
        _this.pageCurrent = 0;
        _this.pageSize = 0;
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_TASK_TASK_SEARCH,
          data: {
            type: _this.params.type,
            status: _this.params.status,
            worCode: _this.params.worCode,
            pageCurrent: page,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_TASK_TASK_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.list = res.data.list;
            _this.totalCount = parseInt(res.data.totalCount);
            _this.pageCurrent = parseInt(res.data.pageCurrent);
            _this.pageSize = parseInt(res.data.pageSize);
          }
        })

      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.search(pageCurrent)
      },
    },
    mounted() {
      this.search()
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 278);
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 278);
        }
      }
    }

  }
</script>

<style scoped>

</style>
