<template>
  <div :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem to="enterOrderManage">入仓单管理</BreadcrumbItem>
      <BreadcrumbItem>入仓单详情</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Modal
          v-model="modal"
          title="修改货品"
          ok-text="确认修改"
          cancel-text="取消修改"
          @on-ok="confirmEdit">
          <Form :label-width="72" inline :rules="ruleInline" ref="editForm" :model="goodsMsg">
            <FormItem label="名称">
              <Input placeholder="" :disabled="true" v-model="goodsMsg.skName" style="width: 390px"></Input>
            </FormItem>
            <span style="display:block;height: 38px;line-height: 38px;width: 260px;margin-bottom: 16px">
            <FormItem label="SKU规格:">
                <Input v-model="goodsMsg.skNorm" :disabled="true"></Input>
            </FormItem>
          </span>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px;">
              <FormItem label="采购:" prop="skCount" style="margin-right: 0" :label-width="74">
                <Input placeholder="请输入数量" v-model="goodsMsg.skCount" style="width:90px"></Input>
              </FormItem>
              {{goodsMsg.skUnit}}
            </span>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px">
              <FormItem label="价格:" prop="spBprice" style="margin-right: 0" :label-width="70">
                <Input placeholder="请输入价格" v-model="goodsMsg.spBprice" style="width: 90px"
                       @on-change="espBpriceChange"></Input>
              </FormItem>
              元 / {{goodsMsg.skUnit}}
               <span style="display: inline-block;" v-if="espuPrice>0">（<span style="color: red">¥{{espuPrice}} / {{goodsMsg.spUnit}}</span>）</span>
            </span>
            <FormItem label="仓内货架号" :label-width="72">
              <Input placeholder="" :disabled="true" v-model="goodsMsg.posCode"></Input>
            </FormItem>
          </Form>
        </Modal>
        <Modal
          v-model="addModal"
          title="添加货品"
          ok-text="确认添加"
          cancel-text="取消添加"
          @on-ok="confirmAdd">
          <Form ref="formInline" :label-width="70" :rules="ruleInline" inline :model="skuMsg">
            <FormItem label="名称/SKU编码:" :label-width="94">
              <AutoComplete
                v-model="keywords"
                :data="showList"
                @on-search="handleSearch"
                @on-select="handleSelect"
                placeholder="请输入商品名称或SKU编码"
                style="width:390px"></AutoComplete>
            </FormItem>
            <br/>
            <FormItem label="SKU规格:" :label-width="94">
              <Input v-model="skuMsg.skNorm" :disabled="true"></Input>
            </FormItem>
            <br/>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px;">
              <FormItem label="采购:" prop="skCount" style="margin-right: 0" :label-width="88">
                <Input placeholder="请输入数量" v-model="skuMsg.skCount" style="width:90px"></Input>
              </FormItem>
              {{skuMsg.skUnit}}
            </span>
            <span style="display: inline-block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px">
              <FormItem label="价格:" prop="spBprice" style="margin-right: 0" :label-width="70">
                <Input placeholder="请输入价格" v-model="skuMsg.spBprice" style="width: 80px"
                       @on-change="spBpriceChange"></Input>
              </FormItem>
              <span v-if="skuMsg.spBprice">
                   元 / {{skuMsg.skUnit}}
              </span>
               <span style="display: inline-block;" v-if="spuPrice>0">（<span style="color: red">¥{{spuPrice}} / {{skuMsg.spUnit}}</span>）</span>
            </span>
            <FormItem label="仓内货架号:" :label-width="90">
              <Input placeholder="请输入仓内货架号" style="width: 200px" v-model="skuMsg.posCode"></Input>
            </FormItem>
          </Form>
        </Modal>
        <div class="goodsList">
          <Button type="success" @click="addModal=true" style="margin-bottom: 20px" v-if="$route.query.recStatus==1">
            添加货品
          </Button>
          <div v-else style="padding-bottom: 12px;font-size: 16px;font-weight: bold">
            订单详情
          </div>
          <Table :height="tableHeight" border ref="selection" :columns="columns" :data="goodsList"></Table>
        </div>
        <div style="position:absolute;bottom:10px;right:14px;margin-top: 14px;">
          <Page :total="totalCount" :current="pageCurrent+1" :page-size="pageSize" @on-change="pageChange"
                show-total show-elevator></Page>
        </div>
      </div>
    </Card>
  </div>
</template>

<script>
  export default {
    name: "edit-enter-order",
    data() {
      return {
        espuPrice: 0,
        spuPrice: 0,
        tableHeight: 0,
        contentHeight: 0,
        modal: false,
        recCode: '',
        keywords: null,
        skuList: [],
        showList: [],
        skuMsg: {
          spBprice: null,
          count: null,
          skCount: null,
          posCode: null
        },
        addModal: false,
        goodsMsg: {
          gbCode: '',
          skName: '',
          skCode: '',
          skNorm: '',
          skCount: '',
          skUnit: '',
          spBprice: '',
          posCode: '',
          spCode: '',
          spCount: '',
          spUnit: ''
        },
        editGoods: {},
        columns: [
          {
            title: '货品编码',
            key: 'gbCode',
            align: 'center',
          },
          {
            title: '货品名称',
            key: 'skName',
            align: 'center',
          },
          {
            title: '品牌',
            key: 'spBrand',
            align: 'center',
          },
          {
            title: 'SKU编码',
            key: 'skCode',
            align: 'center',
            width: 110,
          },
          {
            title: 'SKU规格',
            key: 'skNorm',
            align: 'center',
            width: 116,
          },

          {
            title: '数量', /*采购*/
            key: 'skCount',
            align: 'center',
            width: 80,
          },
          /* {
             title: '实收数量',
             key: 'skuProCount',
             align: 'center',
             width: 80,
           },*/
          {
            title: 'SKU单位',
            key: 'skUnit',
            align: 'center',
            width: 80,
          },
          /*{
            title: 'SPU编号',
            key: 'spCode',
            align: 'center',
            width: 98,
          },
          {
            title: 'SPU总数量',
            key: 'spCount',
            align: 'center',
            width: 72,
          },
          {
            title: 'SPU单位',
            key: 'spUnit',
            align: 'center',
            width: 60,
          },

          {
            title: 'SPU采购成本',
            key: 'spBprice',
            align: 'center',
            width: 86,
          },
          {
            title: '仓内货架号',
            key: 'posCode',
            align: 'center',
            width: 96,
          },*/
          // {
          //   title: '操作',
          //   key: 'action',
          //   width: 114,
          //   align: 'center',
          //   render: (h, params) => {
          //     return h('div', [
          //       h('Button', {
          //         props: {
          //           type: 'primary',
          //           size: 'small'
          //         },
          //         style: {
          //           marginRight: '4px'
          //         },
          //         on: {
          //           click: () => {
          //             this.edit(params.row);
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
          //             _this.remove(params.row, params.index)
          //           }
          //         }
          //       }, '删除')
          //     ]);
          //   }
          // }
        ],
        goodsList: [],
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        getParams: {},
        addMsg: {},
        ruleInline: {
          skCount: [
            {required: true, message: '请输入采购数量', trigger: 'blur'}
          ],
          spBprice: [
            {required: true, message: '请输入单价', trigger: 'blur'}
          ],
        },
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
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_GET_SKU,
          data: {
            sku: value
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_GET_SKU, params).then(res => {
          this.$Message.destroy();
          if (value && res.data && res.data.list) {
            _this.skuList = res.data.list;
            if (_this.skuList.length == 1 && _this.skuList[0].skCode != null) {
              _this.keywords = value
              _this.skuList[0].spBprice = _this.skuMsg.spBprice
              _this.skuList[0].skCount = 1
              _this.skuList[0].goodsCode = _this.skuMsg.goodsCode
              _this.skuMsg = _this.skuList[0]
            }
          }

          _this.skuList.forEach(function (item) {
            let showMsg = item.skName
            if (item.skNorm && item.mark) {
              showMsg = item.skName + "【" + "规格：" + item.skNorm + "、备注：" + item.mark + "、编码：" + item.skCode + "】"
            } else if (item.skNorm && !item.mark) {
              showMsg = item.skName + "【" + "规格：" + item.skNorm + "、编码：" + item.skCode + "】"
            } else if (!item.skNorm && item.mark) {
              showMsg = item.skName + "【" + "备注：" + item.mark + "、编码：" + item.skCode + "】"
            }
            _this.showList.push(showMsg)
          })
        })

      },
      handleSelect(value) {
        const _this = this
        if (value) {
          var valList = value.split("【");
          var valCode = value.substr(value.length - 10, 9);
          _this.skuList.forEach(function (item) {
            if ((item.skName == valList[0] && item.skCode == valCode) || item.skCode.indexOf(valList[0]) > -1) {
              // item.spBprice = _this.skuMsg.spBprice
              // item.skCount = _this.skuMsg.skCount
              // item.count = _this.skuMsg.count
              // item.posCode = _this.skuMsg.posCode
              _this.skuMsg = item
            }
          })
        }
      },
      addgoods() {
        const _this = this
        let params = {
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_CREATE,
          data: {
            recCode: _this.recCode,
            list: [
              {
                skCode: _this.addMsg.skCode,
                skCount: _this.addMsg.skCount,
                spCode: _this.addMsg.spCode,
                spBprice: Number(_this.spuPrice),
                count: _this.addMsg.spCount,
                spCunit: _this.skuMsg.spCunit
              },
            ]
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_CREATE, params).then(res => {
          if (res.success) {
            _this.getDetail();
            _this.$Message.info('添加成功');
            _this.skuMsg = {}
            _this.keywords = ' '
            _this.spuPrice = 0
          }
        })
      },
      confirmAdd() {
        const _this = this
        _this.skuMsg.count = _this.skuMsg.skCount * _this.skuMsg.spCount;
        // _this.skuMsg.skCountPrice=_this.accMul(_this.skuMsg.count,_this.skuMsg.spBprice)
        _this.addMsg = JSON.parse(JSON.stringify(_this.skuMsg))
        _this.addMsg.spCount = _this.skuMsg.count


        if (_this.skuMsg.skName != null || _this.skuMsg.skCode != null) {
          _this.$refs.formInline.validate((valid) => {
            if (valid && _this.addMsg.spBprice > 0 && _this.addMsg.skCount > 0) {
              if (_this.skuMsg.spCunit == 1) {
                if (_this.isInteger(_this.skuMsg.skCount) == true) {
                  _this.addgoods()
                } else {
                  this.$Modal.warning({
                    title: '提示',
                    content: '数量格式不正确',
                  });
                }
              } else {
                _this.addgoods();
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
      edit(params) {
        const _this = this
        // _this.editGoods = params
        _this.modal = true;
        _this.goodsMsg = JSON.parse(JSON.stringify(params))
        this.espuPrice = _this.goodsMsg.spBprice
        let spBprice = (this.goodsMsg.spCount / this.goodsMsg.skCount) * this.goodsMsg.spBprice
        spBprice = _this.ForDight(spBprice, 2)
        _this.goodsMsg.spBprice = spBprice.toFixed(2)
        _this.getParams.params = params
      },
      confirmEdit() {
        const _this = this
        //editForm
        _this.$refs.editForm.validate((valid) => {
          if (valid && _this.goodsMsg.spBprice > 0 && _this.goodsMsg.skCount > 0) {
            if (_this.getParams.params.spCunit == 1) {
              if (_this.isInteger(_this.goodsMsg.skCount) == true) {
                this.editGood()
              } else {
                this.$Modal.warning({
                  title: '提示',
                  content: '数量格式不正确',
                });
              }
            } else {
              this.editGood()
            }
          } else {
            this.$Modal.warning({
              title: '提示',
              content: '请补全货品信息',
            });
          }
        })
      },

      ForDight(Dight, How) {
        Dight = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How);
        return Dight;
      },
      editGood() {
        let _this = this;
        let count = _this.getParams.params.spCount / _this.getParams.params.skCount * _this.goodsMsg.skCount;
        let params = {
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_COUNT_UPDATE,
          data: {
            recCode: _this.recCode,
            gbCode: _this.goodsMsg.gbCode,
            skCount: _this.goodsMsg.skCount,
            spBprice: Number(_this.espuPrice),
            spCode: _this.goodsMsg.spCode,
            count: count,
            spCunit: _this.getParams.params.spCunit
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_COUNT_UPDATE, params).then(res => {
          if (res.success) {
            _this.getDetail();
            _this.$Message.info('修改成功');
            _this.espuPrice = 0
          }
        })
      },
      espBpriceChange() {
        if (this.goodsMsg.spBprice && this.goodsMsg.spCount) {
          let espuPrice = this.goodsMsg.spBprice / (this.goodsMsg.spCount / this.goodsMsg.skCount);
          espuPrice = this.ForDight(espuPrice, 2)
          this.espuPrice = espuPrice.toFixed(2);
        } else {
          this.espuPrice = 0
        }
      },
      spBpriceChange() {
        if (this.skuMsg.spBprice && this.skuMsg.spCount) {
          let spuPrice = this.skuMsg.spBprice / this.skuMsg.spCount;
          spuPrice = this.ForDight(spuPrice, 2);
          this.spuPrice = spuPrice.toFixed(2);
        } else {
          this.spuPrice = 0
        }
      },
      getDetail(page) {
        const _this = this;
        page = page ? page : 0
        _this.goodsList = [];
        _this.recCode = _this.$route.query.recCode
        let params = {
          service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_DETAIL,
          data: {
            recCode: _this.recCode,
            pageCurrent: page
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_DETAIL, params).then(res => {
          if (res.data && res.data.list) {
            _this.pageSize = Number(res.data.pageSize);
            _this.totalCount = Number(res.data.totalCount);
            _this.pageCurrent = Number(res.data.pageCurrent);
            _this.goodsList = res.data.list;
          }
        })
      },
      remove(gbCode, index) {
        const _this = this;
        this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            let params = {
              service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_DELETE,
              data: {
                recCode: _this.recCode,
                gbCode: gbCode.gbCode
              }
            }
            _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_GOODS_DELETE, params).then(res => {
              if (res.success) {
                _this.getDetail();
                _this.$Message.info('此货品删除成功');
              }
            })
          }
        })
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.getDetail(pageCurrent)
      },
      // accMul(arg1,arg2){
      //   var m=0,s1=arg1.toString(),s2=arg2.toString();
      //   try{m+=s1.split(".")[1].length}catch(e){}
      //   try{m+=s2.split(".")[1].length}catch(e){}
      //   return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
      // },
    },
    mounted() {
      this.getDetail();
      if (this.$route.query.recStatus != 1) {
        this.columns.pop();
      }
      this.contentHeight = Number(window.innerHeight - 170);
      if (this.goodsList.length > 0) {
        this.tableHeight = Number(window.innerHeight - 278)
      }
    },
    watch: {
      goodsList: function () {
        if (this.goodsList.length > 0) {
          this.tableHeight = Number(window.innerHeight - 278)
        }
      }
    }
  }
</script>
