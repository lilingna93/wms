<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>系统账户管理</BreadcrumbItem>
      <BreadcrumbItem>仓库账户管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Button type="warning" style="margin-bottom: 10px;" @click="handleClick('add')">添加账户</Button>
        <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data"
               :loading="loading"></Table>
        <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total
              :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
        <Modal
          v-model="modalData.accountAdd"
          title="添加账户"
          @on-ok="addAccount" :width="600"
          @on-visible-change="handleReset('addAccountData')">
          <Form :label-width="100" inline ref="addAccountData" :model="addAccountData">
            <FormItem label="账户名称：" prop="woName">
              <Input type="text" v-model="addAccountData.woName"></Input>
            </FormItem>
            <FormItem label="账户密码：" prop="woPwd">
              <Input type="text" v-model="addAccountData.woPwd"></Input>
            </FormItem>
            <FormItem label="真实姓名：" prop="realName">
              <Input type="text" v-model="addAccountData.realName"></Input>
            </FormItem>
            <FormItem label="账户权限：" prop="woAuth">
              <Checkbox v-for="item in authData" :key="item.authCode" v-model="item.checked">{{item.authName}}
              </Checkbox>
            </FormItem>
          </Form>
        </Modal>
        <Modal v-model="modalData.accountEdit" title="修改账户" @on-ok="editAccount" :width="600">
          <Form :label-width="100" inline>
            <FormItem label="账户名称：">
              <Input type="text" disabled v-model="editAccountData.woName"></Input>
            </FormItem>
            <FormItem label="账户密码：">
              <Input type="text" v-model="editAccountData.woPwd"></Input>
            </FormItem>
            <FormItem label="真实姓名：">
              <Input type="text" v-model="editAccountData.realName"></Input>
            </FormItem>
            <FormItem label="账户TOKEN:">
              <Input type="text" disabled v-model="editAccountData.woToken"></Input>
            </FormItem>
            <FormItem label="账户权限：">
              <Checkbox v-for="item in authData" :key="item.authCode" v-model="item.checked">{{item.authName}}
              </Checkbox>
            </FormItem>
          </Form>
        </Modal>
      </div>
    </Card>
  </Content>
</template>
<script>
  export default {
    methods: {
      addAccount() {
        this.initChecked('add');
        var params = {
          'service': this.Api.VENUS_WMS_WORKER_WORKER_ADD,
          'data': this.addAccountData
        }
        this.$http.post(this.Api.VENUS_WMS_WORKER_WORKER_ADD, params).then(res => {
          if (res.success) {
            this.$Modal.success({
              title: '提示',
              content: res.message
            })
            this.queryTable();
          }
        })
      },
      editAccount() {
        this.initChecked('edit');
        var params = {
          'service': this.Api.VENUS_WMS_WORKER_WORKER_UPDATE,
          'data': this.editAccountData
        }
        this.$http.post(this.Api.VENUS_WMS_WORKER_WORKER_UPDATE, params).then(res => {
          if (res.success) {
            this.$Modal.success({
              title: '提示',
              content: res.message
            })
          }
          this.queryTable();
        })
      },
      initChecked(act) {
        if (act == 'add') {
          for (let i = 0; i < this.authData.length; i++) {
            if (this.authData[i].checked) {
              this.addAccountData.woAuth += parseInt(this.authData[i].authCode);
            }
          }
        } else {
          this.editAccountData.woAuth = 0;
          for (let i = 0; i < this.authData.length; i++) {
            if (this.authData[i].checked) {
              this.editAccountData.woAuth += parseInt(this.authData[i].authCode);
            }
          }
        }

      },
      queryTable(curPage) {
        curPage = curPage ? curPage : 0;
        var params = {
          "service": this.Api.VENUS_WMS_WORKER_WORKER_LIST,
          "data": {
            "pageCurrent": curPage
          }
        }
        this.$http.post(this.Api.VENUS_WMS_WORKER_WORKER_LIST, params).then(res => {
          this.loading = false;
          this.pageData.data = res.data.list;
          this.pageData.totalCount = parseInt(res.data.totalCount);
          this.pageData.pageSize = parseInt(res.data.pageSize);
          this.pageData.pageCurrent = parseInt(res.data.pageCurrent);

        })
      },
      handleReset(name) {
        this.$refs[name].resetFields();
      },
      handleClick(act) {
        if (act == 'add') {
          this.modalData.accountAdd = true;
          for (var i = 0; i < this.authData.length; i++) {
            this.authData[i].checked = false;
          }
        } else {
          this.modalData.accountEdit = true;
        }
      },
      handleChange(count) {
        count = count - 1;
        this.queryTable(count);
      },
      deleteWorker(woCode) {
        var params = {
          'service': this.Api.VENUS_WMS_WORKER_WORKER_DELETE,
          'data': {
            'woCode': woCode
          }
        }
        this.$http.post(this.Api.VENUS_WMS_WORKER_WORKER_DELETE, params).then(res => {
          if (res.success) {
            this.$Message.info(res.message);
          }
          this.queryTable();
        })
      }
    },
    mounted() {
      this.queryTable();
      this.tableHeight = Number(window.innerHeight - 268);
      this.contentHeight = Number(window.innerHeight - 160);
    },
    data() {
      return {
        contentHeight: 0,
        tableHeight: 0,
        loading: false,
        authData: [
          {
            authCode: 1,
            authName: 'SPU管理',
            checked: false
          },
          {
            authCode: 2,
            authName: 'SKU管理',
            checked: false
          },
          {
            authCode: 4,
            authName: '供货商管理',
            checked: false
          },
          {
            authCode: 8,
            authName: '自动上下架',
            checked: false
          },
          {
            authCode: 16,
            authName: '订单管理',
            checked: false
          },
          {
            authCode: 32,
            authName: '账户管理',
            checked: false
          },
          {
            authCode: 64,
            authName: '退货单管理(运营)',
            checked: false
          },
          {
            authCode: 128,
            authName: '退货单管理(仓配)',
            checked: false
          },
          {
            authCode: 256,
            authName: '创建入仓单',
            checked: false
          },
          {
            authCode: 512,
            authName: '入仓单管理',
            checked: false
          },
          {
            authCode: 1024,
            authName: '创建出仓单',
            checked: false
          },
          {
            authCode: 2048,
            authName: '出仓单管理',
            checked: false
          },
          {
            authCode: 4096,
            authName: '库存管理',
            checked: false
          },
          {
            authCode: 8192,
            authName: '工单管理',
            checked: false
          },
          {
            authCode: 16384,
            authName: '报表管理',
            checked: false
          },
          {
            authCode: 32768,
            authName: '仓库账户管理',
            checked: false
          },
          {
            authCode: 65536,
            authName: '市场报表',
            checked: false
          },
          {
            authCode: 131072,
            authName: '仓配报表',
            checked: false
          },
          {
            authCode: 262144,
            authName: '采购报表',
            checked: false
          },
          {
            authCode: 524288,
            authName: '财务报表',
            checked: false
          },
          {
            authCode: 1048576,
            authName: '品控报表',
            checked: false
          }
        ],
        pageData: {
          columns: [
            {
              title: '人员编码',
              key: 'woCode'
            },
            {
              title: '账户名称',
              key: 'woName'
            },
            {
              title: '真实姓名',
              key: 'realName'
            },
            {
              title: '账户TOKEN',
              key: 'woToken'
            },
            {
              title: '操作',
              key: 'action',
              width: 250,
              align: 'center',
              render: (h, params) => {
                return h('div', [
                  h('Button', {
                    props: {
                      type: 'warning',
                      size: 'small'
                    },
                    style: {
                      marginRight: '10px'
                    },
                    on: {
                      click: () => {
                        this.editAccountData.woName = params.row.woName;
                        this.editAccountData.woCode = params.row.woCode;
                        this.editAccountData.woAuth = parseInt(params.row.woAuth);
                        for (let i = 0; i < this.authData.length; i++) {
                          if ((this.authData[i].authCode & this.editAccountData.woAuth) > 0) {
                            this.authData[i].checked = true;
                          } else {
                            this.authData[i].checked = false;
                          }
                        }
                        this.editAccountData.realName = params.row.realName;
                        this.editAccountData.woToken = params.row.woToken;
                        this.handleClick('edit');
                      }
                    }
                  }, '修改'),
                  h('Button', {
                    props: {
                      type: 'error',
                      size: 'small'
                    },
                    on: {
                      click: () => {

                        this.$Modal.confirm({
                          title: '提示',
                          content: '确定要删除吗？',
                          onOk: () => {
                            this.deleteWorker(params.row.woCode);
                          }
                        })
                      }
                    }
                  }, '删除')
                ]);
              }

            }
          ],
          data: [],
          totalCount: 0,
          pageCurrent: 0,
          pageSize: 0
        },
        modalData: {
          accountAdd: false,
          accountEdit: false,
        },
        addAccountData: {
          woName: '',
          woAuth: 0,
          woPwd: '',
          realName: ''
        },
        editAccountData: {
          woName: '',
          woAuth: 0,
          woPwd: '',
          woName: '',
          realName: '',
          woToken: '',
          woCode: '',
        }
      }
    }
  }
</script>
