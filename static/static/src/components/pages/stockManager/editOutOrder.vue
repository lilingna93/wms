<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem to="outOrderManage">出仓单管理</BreadcrumbItem>
      <BreadcrumbItem>出仓单详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Modal
          v-model="modal"
          title="添加货品"
          ok-text="确认添加"
          cancel-text="取消添加"
          @on-ok="confirmAdd">
          <Form :label-width="70" inline ref="formInline2" :rules="rules" :model="skuMsg">
            <FormItem label="名称:" :label-width="94">
              <AutoComplete
                v-model="keywords"
                :data="showList"
                @on-search="handleSearch"
                @on-select="handleSelect"
                placeholder="input here"
                style="width:390px"></AutoComplete>
            </FormItem>
            <br/>
            <FormItem label="SKU规格:" :label-width="94">
              <Input v-model="skuMsg.skNorm" :disabled="true" style="width: 180px"></Input>
              <span v-if="goodsSpcount>0" style="color: red"><!--v-if="!houseType&&goodsSpcount>0"-->
                (当前库存剩余{{goodsSpcount}}{{skuMsg.skUnit}})
              </span>
              <!--<span v-else-if="houseType&&goodsSpcount>0" style="color: red">-->
                 <!--(当前库存剩余{{goods}}{{skuMsg.spUnit}})-->
              <!--</span>-->
            </FormItem>
            <br/>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px"
               ><!--   v-if="!houseType"-->
              <FormItem label="SKU数量:" style="margin-right: 0" :label-width="94">
                 <AutoComplete
                   v-model="skuMsg.skCount"
                   @on-change="handleSet" style="width: 110px"></AutoComplete>
              </FormItem>
              {{skuMsg.skUnit}}
            </span>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px">
              <FormItem label="SPU数量:" prop="spCount" style="margin-right: 0" :label-width="94">
                  <AutoComplete
                    v-model="skuMsg.spCount"
                    style="width: 110px" :disabled="true"></AutoComplete><!--:disabled="!houseType"-->
              </FormItem>
              {{skuMsg.spUnit}}
            </span>
          </Form>
        </Modal>
        <Modal
          v-model="editModal"
          title="修改货品"
          ok-text="确定修改"
          cancel-text="取消修改"
          @on-ok="confirmEdit">
          <Form :label-width="100" inline ref="formInline" :model="rowMsg">
            <FormItem label="名称:">
              <Input placeholder="" :disabled="true" v-model="rowMsg.skName" style="width: 390px"></Input>
            </FormItem>
            <br/>
            <FormItem label="SKU规格:">
              <Input v-model="rowMsg.skNorm" :disabled="true"></Input>
            </FormItem>
            <br/>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px"
                 ><!-- v-if="!houseType"-->
              <FormItem label="SKU数量:" style="margin-right: 0">
                <AutoComplete
                  v-model="rowMsg.skCount"
                  @on-change="handleSetSk" style="width: 110px"></AutoComplete>
              </FormItem>
              {{rowMsg.skUnit}}
            </span>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px">
              <FormItem label="SPU数量:" style="margin-right: 0" :label-width="100">
                  <AutoComplete
                    v-model="rowMsg.count"
                    style="width: 110px" :disabled="true"></AutoComplete><!-- :disabled="!houseType"-->
              </FormItem>
              {{rowMsg.spUnit}}
            </span>
          </Form>
        </Modal>
        <Button type="primary" @click="modal = true" v-if="$route.query.invStatus==1" style="margin-bottom: 20px">添加货品
        </Button>
        <div v-else style="padding-bottom: 12px;font-size: 16px;font-weight: bold">
          出仓单详情
        </div>
        <Table border ref="selection" :height="tableHeight" :columns="orderTitle" :data="list"></Table>
        <div class="page"
             style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0;">
          <Page :total="totalCount" style="float: right" :current="pageCurrent+1" :page-size="pageSize"
                @on-change="pageChange" show-total
                show-elevator></Page>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "create-out-order",
    data() {
      return {
        houseType: Boolean(houseType),
        tableHeight: 0,
        contentHeight: 0,
        goodsSpcount: 0,
        goods: 0,
        rowMsg: {},
        ruleInline: {
          receiver: [
            {required: true, message: '请输入客户名称', trigger: 'blur'}
          ],
          phone: [
            {required: true, message: '请输入客户电话', trigger: 'blur'},
          ],
          eCode: [
            {required: true, message: '请输入电子单号', trigger: 'blur'},
          ],
          address: [
            {required: true, message: '请输入客户地址', trigger: 'blur'},
          ],
          mark: [
            {required: true, message: '请输入备注信息', trigger: 'blur'},
          ]
        },
        rules: {
          spCount: [
            {required: true, message: '请输入SPU数量', trigger: 'blur'}
          ]
        },
        isFast: false,
        msg: {},
        showList: [],
        keywords: null,
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        params: {
          category: ''
        },
        skuList: [],
        skuMsg: {
          mark: null,
          spCount: null,
        },
        list: [],
        keyword: null,
        modal: false,
        editModal: false,
        orderTitle: [
          {
            title: 'SKU编号',
            key: 'skCode',
            align: 'center',
          },
          /*{
            title: 'SPU编码',
            key: 'spCode',
            align: 'center',
          },*/
          {
            title: '货品名称',
            key: 'skName',
            align: 'center',
          },
          {
            title: 'SKU规格',
            key: 'skNorm',
            align: 'center',
          },
          {
            title: '数量',
            key: 'skCount',
            align: 'center',
            width: 80,
          },
          {
            title: 'SKU单位',
            key: 'skUnit',
            align: 'center',
            width: 100,
          },
          /*{
            title: 'SPU总数量',
            key: 'count',
            align: 'center',
            width: 100,
          },
          {
            title: 'SPU单位',
            key: 'spUnit',
            align: 'center',
            width: 100,
          },*/
          // {
          //   title: '操作',
          //   key: 'action',
          //   width: 140,
          //   align: 'center',
          //   render: (h, params) => {
          //     return h('div', [
          //       h('Button', {
          //         props: {
          //           type: 'primary',
          //           size: 'small'
          //         },
          //         style: {
          //           marginRight: '5px'
          //         },
          //         on: {
          //           click: () => {
          //             this.edit(params.row)
          //           }
          //         }
          //       }, '修改'),
          //       h('Button', {
          //         props: {
          //           type: 'error',
          //           size: 'small'
          //         },
          //         on: {
          //           click: () => {
          //             const _this = this
          //             // const invStatus =_this.$route.query.invStatus
          //             _this.remove(params.row, params.index)
          //           }
          //         }
          //       }, '删除'),
          //     ]);
          //   }
          // }
        ],
        getParams: {},
        setSpcount: 0,
        setSkCount: 0,
        setCounts: 0,
      }
    },
    methods: {
      isInteger(obj) {
        return Math.round(obj) == obj   //是整数，则返回true，否则返回false
      },
      handleSearch(value) {
        this.showList = []
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_GET_SKU,
          data: {
            sku: value
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_GET_SKU, params).then(res => {
          this.$Message.destroy();
          if (value && res.data && res.data.list) {
            _this.skuList = res.data.list
            if (_this.skuList.length == 1 && _this.skuList[0].skCode != null) {
              _this.keywords = value
              _this.skuList[0].spBprice = _this.skuMsg.spBprice
              _this.skuList[0].skCount = 1
              _this.skuList[0].goodsCode = _this.skuMsg.goodsCode
              _this.goodsSpcount = _this.skuList[0].goods / _this.skuList[0].spCount
              _this.goods = _this.skuList[0].goods
              _this.skuMsg = _this.skuList[0]
              _this.setSpcount = _this.skuMsg.spCount
            }
            _this.skuList.forEach(function (item) {
              let showMsg = item.skName
              if (item.skNorm && item.mark) {
                showMsg = item.skName + "【" + "规格：" + item.skNorm + "、备注：" + item.mark + "、编码："+item.skCode+"】"
              } else if (item.skNorm && !item.mark) {
                showMsg = item.skName + "【" + "规格：" + item.skNorm +"、编码："+item.skCode+"】"
              } else if (!item.skNorm && item.mark) {
                showMsg = item.skName + "【" + "备注：" + item.mark + "、编码："+item.skCode+"】"
              }
              _this.showList.push(showMsg)
            })
          }
        })
      },
      handleSelect(value) {
        const _this = this
        if (value) {
          var valList = value.split("【");
          var valCode = value.substr(value.length-10,9);
          _this.skuList.forEach(function (item) {
            if ((item.skName==valList[0] && item.skCode == valCode) || item.skCode.indexOf(valList[0]) > -1) {
              // item.mark = _this.skuMsg.mark
              _this.skuMsg = item
              _this.skuMsg.skCount = 1
              _this.setSpcount = item.spCount
              _this.goodsSpcount = item.goods / item.spCount
              _this.goods = item.goods
            }
          })
        }
      },
      addgood() {
        const _this = this
        let skuMsg = JSON.parse(JSON.stringify(_this.skuMsg));
        skuMsg.count = _this.skuMsg.spCount
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_CREATE,
          data: {
            invCode: _this.$route.query.invCode,
            list: [
              {
                skCode: skuMsg.skCode,
                skCount: skuMsg.skCount,
                spCode: skuMsg.spCode,
                count: skuMsg.count
              }
            ]
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_CREATE, params).then(res => {
          if (res.success) {
          _this.getDetail();
          _this.$Message.info('添加成功');
          _this.keywords = '  '
          _this.skuMsg = {}
          _this.goodsSpcount=0;
        }
      })
      },
      confirmAdd() {
        const _this = this
        if (_this.skuMsg.skName != null || _this.skuMsg.skCode != null) {
          _this.$refs.formInline2.validate((valid) => {
            if (valid && _this.skuMsg.spCount > 0) {
              if(_this.skuMsg.spCunit==1){
                if (_this.isInteger(_this.skuMsg.skCount) == true) {
                  _this.addgood();
                } else {
                  this.$Modal.warning({
                    title: '提示',
                    content: '数量格式不正确',
                  });
                }
              }else{
                _this.addgood();
              }

            } else {
              this.$Modal.warning({
                title: '提示',
                content: '请核对货品信息',
              });
            }
          })
        } else {
          this.$Modal.warning({
            title: '提示',
            content: '请输入名称或SKU编码',
          });
        }
      },
      handleSet() {
        const _this = this;
        if (_this.skuMsg.skCount) {
          _this.skuMsg.spCount = _this.setSpcount;
          _this.skuMsg.spCount = _this.skuMsg.skCount * _this.skuMsg.spCount
        } else {
          _this.skuMsg.skCount = null
          _this.skuMsg.spCount = 0
        }
      },
      handleSetSk() {
        const _this = this;
        if (_this.rowMsg.skCount) {
          _this.rowMsg.count = 0
          _this.rowMsg.count = (_this.setCounts / _this.setSkCount) * _this.rowMsg.skCount
        } else {
          _this.rowMsg.count = null
          _this.rowMsg.spCount = 0
        }
      },
      edit(params) {
        const _this = this
        _this.editModal = true;
        _this.rowMsg = JSON.parse(JSON.stringify(params))
        _this.getParams.params = params
        _this.setSkCount = _this.rowMsg.skCount
        _this.setCounts = _this.rowMsg.count
      },
      editgood() {
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_COUNT_UPDATE,
          data: {
            invCode: _this.$route.query.invCode,
            igoCode: _this.rowMsg.igoCode,
            skCode: _this.rowMsg.skCode,
            skCount: _this.rowMsg.skCount,
            spCode: _this.rowMsg.spCode,
            count: _this.rowMsg.count,
            spCunit:_this.getParams.params.spCunit
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_COUNT_UPDATE, params).then(res => {
          if (res.success) {
          _this.getDetail();
          _this.$Message.info('修改成功');
        }
      })
      },
      confirmEdit() {
        const _this = this;
        let skNorm = _this.getParams.params.count / _this.getParams.params.skCount
        _this.rowMsg.skCount = _this.rowMsg.count / skNorm

        if (_this.rowMsg.count > 0) {
          if( _this.getParams.params.spCunit==1){
            if (_this.isInteger(_this.rowMsg.skCount) == true ) {
                _this.editgood();
            }else {
              this.$Modal.warning({
                title: '提示',
                content: '数量格式不正确',
              });
            }
          }else{
            _this.editgood();
          }
        } else {
          this.$Modal.warning({
            title: '提示',
            content: '请补全货品信息',
          });
        }
      },
      remove(row, index) {
        const _this = this;
        this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_DELETE,
              data: {
                igoCode: row.igoCode,
                invCode: _this.$route.query.invCode
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_GOODS_DELETE, params).then(res => {
              if (res.success) {
                _this.getDetail();
              }
            })
          }
        })
      },
      pageChange: function (page) {
        let pageCurrent = page
        this.getDetail(pageCurrent)
      },
      getDetail(page) {
        const _this = this;
        _this.list = [];
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_DETAIL,
          data: {
            invCode: _this.$route.query.invCode,
            pageCurrent: page
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_DETAIL, params).then(res => {
          if (res.data && res.data.list) {
            _this.pageCurrent = Number(res.data.pageCurrent);
            _this.totalCount = Number(res.data.totalCount);
            _this.pageSize = Number(res.data.pageSize)
            _this.list = res.data.list
          }
        })
      }
    },
    mounted() {
      this.getDetail();
      if (this.$route.query.invStatus !=1) {
        this.orderTitle.pop();
      }
      // if (!this.houseType) {
        this.rules = {}
      // }
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 264)
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 264)
        }
      }
    }
  }
</script>

