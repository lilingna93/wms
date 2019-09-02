<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem to="stockManage">库存管理</BreadcrumbItem>
      <BreadcrumbItem>库存货品详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="140" inline>
          <FormItem label="编号：">
            {{spCode}}
          </FormItem>
          <FormItem label="名称：">
            {{spName}}
          </FormItem>
          <FormItem label="规格：">
            {{spNorm}}
          </FormItem>
          <FormItem label="单位：">
            {{spUnit}}
          </FormItem>
          <FormItem label="库存总数量：">
            {{spCount}}
            {{spUnit}}
          </FormItem>
          <FormItem label="保质期：">
            {{qgPeriod}}
          </FormItem>
        </Form>
        <Table :height="tableHeight" border ref="selection" :columns="stockList" :data="list"></Table>
        <div class="page"
             style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
          <Page :total="totalCount" style="float: right" :current="currentPage+1" :page-size="pageSize"
                @on-change="pageChange" show-total
                show-elevator></Page>
        </div>
        <Modal
          v-model="modal"
          title="退货"
          ok-text="确认退货"
          cancel-text="取消"
          @on-ok="confirmEdit('editForm')"
          @on-visible-change="handleReset('editForm')">
          <Form :label-width="100" inline ref="editForm" :model="goodsMsg" :rules="ruleInline"><!--  -->
            <FormItem label="批次编号：">
              <Input placeholder="" :disabled="true" v-model="goodsMsg.gsCode"></Input>
            </FormItem>
            <FormItem label="当前数量：">
              <Input placeholder="" :disabled="true" v-model="goodsMsg.count"></Input>
            </FormItem>
            <FormItem label="退货数量：" prop="skCount">
              <Input placeholder="请输入退货数量" v-model="goodsMsg.skCount"></Input>
            </FormItem>
            <FormItem label="收件人：" prop="receiver">
              <Input placeholder="请输入收件人" v-model="goodsMsg.receiver"></Input>
            </FormItem>
            <FormItem label="退货原因：" prop="returnmark">
              <Input placeholder="请输入退货原因" v-model="goodsMsg.returnmark"></Input>
            </FormItem>
            <FormItem label="备注：" >
              <Input placeholder="请输入备注" v-model="goodsMsg.mark"></Input>
            </FormItem>
          </Form>
        </Modal>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "stock-detail",
    data() {
      return {
        ruleInline: {
          skCount: [
            {required: true, message: '请输入退货数量', trigger: 'blur'}
          ],
          receiver: [
            {required: true, message: '请输入收件人', trigger: 'blur'}
          ],
          returnmark: [
            {required: true, message: '请输入退货原因', trigger: 'blur'}
          ],
        },
        modal: false,
        contentHeight: null,
        tableHeight: 0,
        goodsMsg: {
          gsCode: '',
          count: '',
          skCount: '',
          receiver:'',
          returnmark:'',
          mark:''
        },
        spCode: null,
        spName: null,
        spNorm: null,
        spUnit: null,
        spCount: null,
        qgPeriod:null,
        pageSize: 0,
        currentPage: 0,
        totalCount: 0,
        list: [],
        stockList: [
          {
            title: '货品批次编号',
            key: 'gsCode',
            align: 'center',
          },
          {
            title: '所属入仓单号',
            key: 'recCode',
            align: 'center',
          },
          {
            title: '入仓时间',
            key: 'recCtime',
            align: 'center',
          },
          {
            title: '生产日期',
            key: 'pdDate',
            align: 'center',
          },
          {
            title: '批次规格和数量',
            key: 'num',
            align: 'center',
          },
          {
            title: '入仓单价',
            key: 'sprice',
            align: 'center',
            width: 80,
          },
          {
            title: '批次数量',
            key: 'init',
            align: 'center',
            width: 80,
          },
          {
            title: '当前数量',
            key: 'count',
            align: 'center',
            width: 80,
          },
          {
            title: '操作',
            key: 'action',
            width: 270,
            align: 'center',
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: 'success',
                    size: 'small',
                  },
                  style: {
                    marginRight: '4px'
                  },
                  on: {
                    click: () => {
                      const recCode = params.row.recCode;
                      this.$router.push({
                        name: 'editEnterOrder',
                        query: {
                          recCode: recCode
                        }
                      });
                    }
                  }
                }, '入仓批次详情'),
                h('Button', {
                  props: {
                    type: 'info',
                    size: 'small',
                  },
                  style: {
                    marginRight: '4px'
                  },
                  on: {
                    click: () => {
                      const gsCode = params.row.gsCode;
                      this.$router.push({
                        name: 'warehouseBatch',
                        query: {
                          gsCode: gsCode
                        }
                      });
                    }
                  }
                }, '出仓批次详情'),
                h('Button', {
                  props: {
                    type: 'primary',
                    size: 'small',
                    disabled:params.row.isReturn ? false : true
                  },
                  style: {
                    marginRight: '4px'
                  },
                  on: {
                    click: () => {
                      this.edit(params.row);
                    }
                  }
                }, '退货'),
              ]);
            }
          }
        ],
      }
    },
    methods: {
      edit(params) {
        this.modal = true;
        this.goodsMsg.count = params.count;
        this.goodsMsg.gsCode = params.gsCode;
      },
      confirmEdit(name) {
        let _this = this
        _this.$refs[name].validate((valid) => {
          if (valid) {
            if (_this.goodsMsg.count >= _this.goodsMsg.skCount && _this.goodsMsg.skCount > 0) {
              let params = {
                service: _this.Api.VENUS_WMS_GOODS_GOODS_RETURN,
                data: {
                  gsCode: _this.goodsMsg.gsCode,
                  skCount: _this.goodsMsg.skCount,
                  receiver: _this.goodsMsg.receiver,
                  returnmark:_this.goodsMsg.returnmark,
                  mark: _this.goodsMsg.mark
                }
              }
              _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_RETURN, params).then(res => {
                if (res.success) {
                  _this.$Message.success(res.message);
                  _this.getStockDetail(_this.currentPage);
                }
              })
            } else {
              _this.$Message.error('请重新输入退货数量');
            }
          } else {
            _this.$Message.error('请补全退货信息');
          }
        })
      },
      handleReset(name) {
        this.$refs[name].resetFields();
      },
      getStockDetail(page) {
        let _this = this
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_GOODS_GOODS_STORED,
          data: {
            code: _this.$route.query.spCode,
            pageCurrent: page,
          }
        }

        _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_STORED, params).then(res => {
          if (res.data && res.data.list) {
            _this.totalCount = parseInt(res.data.totalCount);
            _this.pageSize = parseInt(res.data.pageSize);
            _this.pageCurrent = parseInt(res.data.pageCurrent);
            _this.spCode = res.data.code
            _this.spCount = res.data.count
            _this.spName = res.data.name
            _this.spNorm = res.data.norm
            _this.spUnit = res.data.unit;
            _this.qgPeriod = res.data.qgPeriod;
            _this.list = res.data.list
          }
        })
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.getStockDetail(pageCurrent);
      },

    },
    mounted() {
      this.getStockDetail()
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 264);
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 274);
        }
      }
    }

  }
</script>

<style scoped>

</style>
