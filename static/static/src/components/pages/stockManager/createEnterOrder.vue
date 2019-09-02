<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>创建入仓单</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Modal
          v-model="modal"
          title="添加货品"
          ok-text="确定添加"
          cancel-text="取消"
          @on-ok="addGoods"
          @on-visible-change="handleReset('formInline')">
          <Form ref="formInline" :label-width="94" :rules="ruleInline" inline :model="goodsList" style="width: 100%">
            <FormItem label="名称/编码:">
              <AutoComplete
                v-model="keywords"
                :data="showList"
                @on-search="handleSearch"
                @on-select="handleSelect"
                placeholder="请输入商品名称或编码"
                style="width:390px"></AutoComplete>
            </FormItem>
            <span style="display:block;height: 38px;line-height: 38px;width: 260px;margin-bottom: 16px">
            <FormItem label="商品规格:">
                <Input v-model="goodsList.skNorm" :disabled="true" style="width: 160px"></Input>
            </FormItem>
          </span>
            <span style="display: block;height: 38px;line-height: 38px;font-size: 12px;margin-bottom: 20px">
              <FormItem label="采购数量:" prop="skCount" style="margin-right: 0">
                <Input placeholder="请输入数量" v-model="goodsList.skCount" style="width:160px"></Input>
              </FormItem>
             {{goodsList.skUnit}}
            </span>
            <span style="display: block;height: 38px;line-height: 38px;font-size: 12px">
              <FormItem label="采购价格:" prop="spBprice" style="margin-right: 0" >
                <Input placeholder="请输入价格" v-model="goodsList.spBprice" @on-change="spBpriceChange"
                       style="width:160px"></Input>
              </FormItem>
              <span v-if="goodsList.skUnit">
                 元 / {{goodsList.skUnit}}
              </span>
              <span style="display: inline-block;" v-if="spuPrice>0">（<span style="color: red">¥{{spuPrice}} / {{goodsList.spUnit}}</span>）</span>
            </span>
            <FormItem label="仓内货架号:" style="margin-right: 0" :style="{marginTop:'14px'}" :label-width="94">
              <Input placeholder="请输入仓内货架号" v-model="goodsList.shelfNum" :disabled="isHouseType"></Input>
            </FormItem>
          </Form>
        </Modal>
        <Form :label-width="90" inline>
          <FormItem label="入仓单类型：" v-if="receipt_type.length>0">
            <Select :style="{width:'160px'}" v-model="selectType" :disabled="receipt_type.length==1">
              <Option v-for="(item,index) in  receipt_type" :value="item.code" :key="index">{{item.label}}</Option>
            </Select>
          </FormItem>
          <FormItem label="入仓备注：">
            <Input placeholder="入仓备注" v-model="mark" style="width: 180px"></Input>
          </FormItem>
          <Button type="primary"   @click="openModal" style="float: right;">添加货品</Button>
        </Form>
        <Table :height="tableHeight" border @on-select="handleRowChange" ref="selection" :columns="columns"
               :data="enterOrder"></Table>
        <div
          style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
          <div style="float: right">
            <Checkbox v-model="isFast" :disabled="true">免仓内操作</Checkbox>
            <Button type="success" size="large" vertical="false" :disabled="isDisable" @click="createEnterOrder">创建入仓预报单</Button>
          </div>
          <!--<Page :total="orderList.length" :current="pageCurrent" :page-size="pageSize" @on-change="pageChange"
                show-total show-elevator></Page>-->
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "create-enter-order",
    data() {
      return {
        selectType:'',
        receipt_type:receipt_type,
        isDisable: false,
        isHouseType: false,
        contentHeight: 0,
        spuPrice: null,
        tableHeight: 0,
        modal: false,
        keywords: null,
        mark: null,
        skuList: [],
        showList: [],
        goodsList: {
          skCount: null,
          goodsCode: null,
          shelfNum: ''
        },
        addList: {},
        pageSize: 1000,
        pageCurrent: 1,
        checked: false,
        isFast: true,
        houseType: Boolean(houseType),
        orderList: [],      //全部数据
        enterOrder: [],    //当前页显示的数据
        ruleInline: {
          skCount: [
            {required: true, message: '请输入采购数量', trigger: 'blur'}
          ],
          spBprice: [
            {required: true, message: '请输入价格', trigger: 'blur'}
          ],
        },
        list: [],
        columns: [
          {
            align: 'center',
            title: '名称',
            key: 'skName'
          },
         /* {
            align: 'center',
            title: 'SKU编码',
            key: 'skCode',
          },*/
          {
            align: 'center',
            title: '规格',
            key: 'skNorm'
          },

          {
            align: 'center',
            title: '数量',
            key: 'skCount',
            width: 60,
          },
          {
            align: 'center',
            title: '单位',
            key: 'skUnit',
            width: 66,
          },
          {
            align: 'center',
            title: '单价',
            key: 'spPrice',
            width: 66,
          },
          /*
          {
            align: 'center',
            title: 'SPU总数量',
            key: 'spCount',
            width: 78,
          },
          {
            align: 'center',
            title: 'SPU单位',
            key: 'spUnit',
            width: 64,
          },*/
          {
            align: 'center',
            title: '总价',
            key: 'skCountPrice'
          },
          {
            align: 'center',
            title: '仓内货架号',
            key: 'shelfNum'
          },
          {
            title: '操作',
            key: 'action',
            width: 68,
            align: 'center',
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: 'error',
                    size: 'small'
                  },
                  on: {
                    click: () => {
                      this.remove(params.index)
                    }
                  }
                }, '删除')
              ]);
            }
          }
        ],
      }
    },
    methods: {
      handleReset(name){
        this.$refs[name].resetFields();
      },
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
              _this.skuList[0].skCount = 1
              _this.skuList[0].goodsCode = _this.goodsList.goodsCode
              _this.goodsList = _this.skuList[0]
            }
          }
          _this.skuList.forEach(function (item) {
            if (item.skCode && item.skName) {
              let showMsg = item.skName
              if (item.skNorm && item.mark) {
                showMsg = item.skName + "【"+ "规格：" + item.skNorm + "、备注：" + item.mark+"、编码："+item.skCode+"】"
              } else if (item.skNorm && !item.mark) {
                showMsg = item.skName + "【" + "规格：" + item.skNorm  +"、编码："+item.skCode+"】"
              } else if (!item.skNorm && item.mark) {
                showMsg = item.skName + "【"+ "备注：" + item.mark  +"、编码："+item.skCode +"】"
              }
              _this.showList.push(showMsg)
            }
          })
        })
      },
      handleSelect(value) {
        const _this = this
        if (value) {
          var valList = value.split("【");
          let val = value.split("：")
          let vals = val[val.length-1]
          var valCode = val[val.length-1].substr(0,vals.length-1)
          _this.skuList.forEach(function (item) {
            if ((item.skName==valList[0] && item.skCode == valCode)  || item.skCode.indexOf(valList[0]) > -1) {
              // item.spBprice = _this.goodsList.spBprice
              // item.skCount =1
              // item.goodsCode = _this.goodsList.goodsCode

              // if (_this.houseType) {
              //   let shelfNum = _this.goodsList.shelfNum
              //   _this.goodsList = item
              //   _this.goodsList.shelfNum = shelfNum
              // }
              _this.goodsList = item
            }
          })
        }
      },
      handleRowChange(selection, row) {
      },
      ForDight(Dight, How) {
        Dight = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How);
        return Dight;
      },
      spBpriceChange() {
        if (this.goodsList.spBprice && this.goodsList.spCount) {
          let spuPrice = this.goodsList.spBprice / this.goodsList.spCount
          spuPrice = this.ForDight(spuPrice, 2)
          this.spuPrice = spuPrice.toFixed(2)
        } else {
          this.spuPrice = 0
        }
      },
      addGoods() {
        const _this = this
        _this.modal = false
        _this.goodsList.skCountPrice = this.ForDight(_this.spuPrice *  _this.goodsList.spCount * _this.goodsList.skCount, 2).toFixed(2)
        _this.goodsList.spBprice = _this.spuPrice
        _this.goodsList.spPrice = this.ForDight(_this.spuPrice*_this.goodsList.spCount,2).toFixed(2)
        let goodsList = JSON.parse(JSON.stringify(_this.goodsList));
        _this.addList = goodsList
        _this.addList.spCount = _this.goodsList.spCount * _this.goodsList.skCount
        let shelfNum = this.goodsList.shelfNum
        if (_this.goodsList.skName != null || _this.goodsList.skCode != null) {
          _this.$refs.formInline.validate((valid) => {
              if (valid && _this.addList.spBprice > 0 && _this.addList.skCount > 0) {
                if(_this.goodsList.spCunit==1) {
                  if (_this.isInteger(_this.goodsList.skCount) == true) {
                    _this.add()
                  } else {
                    this.$Modal.warning({
                      title: '提示',
                      content: '数量格式不正确',
                    });
                  }
                }else{
                  _this.add()
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
      add() {
        let _this = this;
        let params = {
          skCode: _this.addList.skCode,
          skCount: _this.addList.skCount,
          spCode: _this.addList.spCode,
          count: _this.addList.spCount,
          spBprice: _this.spuPrice,
          spCunit:_this.addList.spCunit,
          supCode: _this.addList.supCode
        }
        if (_this.list.length>0){
          for (let i = 0;i < _this.list.length; i++){
            if (_this.list[i].skCode == params.skCode&&_this.list[i].spBprice==params.spBprice) {
              _this.orderList[i].skCount = parseFloat(_this.list[i].skCount) + parseFloat(params.skCount);
              _this.list[i].skCount = parseFloat(_this.list[i].skCount) + parseFloat(params.skCount);
              _this.orderList[i].count = parseFloat(_this.list[i].count) +(parseFloat(params.skCount)*parseFloat(_this.goodsList.spCount));
              _this.list[i].count = parseFloat(_this.list[i].count) +(parseFloat(params.skCount)*parseFloat(_this.goodsList.spCount));
              _this.orderList[i].skCountPrice = parseFloat(_this.orderList[i].skCountPrice)+parseFloat(_this.ForDight((_this.list[i].count) *_this.goodsList.spBprice, 2).toFixed(2))
              _this.goodsList = {}
              _this.spuPrice = 0
              _this.keywords = ' '
              return;
            }
          }
          _this.list.push(params);
          _this.orderList.push(_this.addList);
        }else {
          _this.list.push(params);
          _this.orderList.push(_this.addList);
        }

        // if (_this.houseType) {
        //   _this.goodsList = {}
        //   _this.goodsList.shelfNum = shelfNum
        // } else {
          _this.goodsList = {}
        // }
        _this.spuPrice = 0
        _this.keywords = ' '
      },
      createEnterOrder() {
        let _this = this
        if(!_this.selectType){
          _this.$Message.error('请选择入仓单类型');
        } else {
          _this.isDisable = true
          setTimeout(() => {
            this.isDisable = false
          }, 1000);
          let params = {
            service: _this.Api.VENUS_WMS_RECEIPT_RECEIPT_CREATE,
            data: {
              list: _this.list,
              isFast: Number(_this.isFast),
              mark: _this.mark,
              type:_this.selectType
            }
          }
          if (params.data.list.length > 0) {
            _this.$http.post(_this.Api.VENUS_WMS_RECEIPT_RECEIPT_CREATE, params).then(res => {
              if (res.success) {
                _this.$Message.info('创建入仓预报单成功');
                _this.orderList = [];
                _this.list = [];
                _this.selectType= ''
              }
            })
          } else {
            this.$Modal.warning({
              title: '提示',
              content: '请添加货品后再创建',
            });
          }
        }
      },
      openModal() {
        this.modal = true;
      },
      remove(index) {
        let _this = this;
        _this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            _this.orderList.splice(index, 1);
            _this.list.splice(index, 1);
          }
        })
      },
      pageChange(page) {
        this.pageCurrent = page
        this.updateDataShow()
      },
      updateDataShow() {
        let startPage = (this.pageCurrent - 1) * this.pageSize
        let endPage = startPage + this.pageSize
        this.enterOrder = this.orderList.slice(startPage, endPage)
      },
    },
    mounted() {
      if(this.receipt_type.length== 1  ){
        this.selectType = this.receipt_type[0].code
      }
      // if (this.houseType) {
      //   this.checked = true
      //   this.goodsList.shelfNum = '#1'
      //   this.isHouseType = true
      // }
      if (this.orderList.length > 0) {
        this.tableHeight = Number(window.innerHeight - 274)
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      orderList: function () {
        this.enterOrder = this.orderList.slice(0, this.pageSize)
        if (this.orderList.length > 0) {
          this.tableHeight = Number(window.innerHeight - 274)
        }
      }
    }
  }
</script>
<style>
  .ivu-table-cell {
    padding: 0 4px !important;
  }

  .ivu-table th {
    height: 30px !important;
  }

  .ivu-table td {
    height: 36px !important;
  }
  .ivu-select-dropdown-list {
    max-height: 300px;
  }
</style>
