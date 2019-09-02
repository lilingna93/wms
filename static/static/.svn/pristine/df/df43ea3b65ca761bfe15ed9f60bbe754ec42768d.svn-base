<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>创建出仓单</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <!-- <Form :label-width="80" ref="formInline" :rules="ruleInline" :model="msg" inline>
           <FormItem label="客户单位">
              <Select @on-change="selectOption" :style="{width:'180px'}">
              <Option v-for="item in  warehouse" :value="item.war_name" :key="item.war_code">{{item.war_name}}</Option>
            </Select>
           </FormItem>
           <FormItem label="客户名称" >
             <Input v-model="msg.receiver"></Input>
           </FormItem>
           <FormItem label="客户电话" prop="phone">
             <Input v-model="msg.phone"></Input>
           </FormItem>
           <FormItem label="电子单号">
             <Input v-model="msg.eCode"></Input>
           </FormItem>
           <FormItem label="客户地址" prop="address">
             <Input v-model="msg.address"></Input>
           </FormItem>
           <FormItem label="邮编号码" prop="postal">
             <Input v-model="msg.postal" :style="{width:'160px'}"></Input>
           </FormItem>
           <FormItem label="送达时间">
             <DatePicker type="date" @on-change='onselectSend' placeholder="送达日期" style="width: 160px"></DatePicker>
           </FormItem>
           <FormItem label="备注信息">
           </FormItem>
         </Form>-->
        <div style="display: flex; justify-content: space-between; padding: 8px;">
          <Form :label-width="80" ref="formInline" inline :model="msg" :rules="ruleInline">
            <FormItem label="出仓单类型" v-if="invoice_type.length>0">
              <Select :style="{width:'180px'}" v-model="selectType" :disabled="invoice_type.length==1">
                <Option v-for="(item,index) in  invoice_type" :value="item.code" :key="index">{{item.label}}</Option>
              </Select>
            </FormItem>
            <FormItem label="收件人" prop="receiver">
                <AutoComplete
                  v-model="msg.receiver"
                  :data="showReceiver"
                  @on-search="searchReceiver"
                  @on-select="selectReceiver"
                  placeholder="请输入收件人（关键字）"
                  style="width:220px"></AutoComplete>
            </FormItem>
            <FormItem label="备注信息">
              <span><Input v-model="msg.mark"></Input></span>
            </FormItem>
            <FormItem>
              <Button type="primary" @click="modal = true">添加货品</Button>
            </FormItem>
          </Form>
        </div>
        <Modal
          v-model="modal"
          title="添加货品"
          ok-text="确认添加"
          cancel-text="取消添加"
          @on-ok="confirmAdd">
          <Form :label-width="94" inline ref="formInline2" :rules="rules" :model="skuMsg">
            <FormItem label="名称/编码:">
              <AutoComplete
                v-model="keywords"
                :data="showList"
                @on-search="handleSearch"
                @on-select="handleSelect"
                placeholder="input here"
                style="width:390px"></AutoComplete>
            </FormItem>
            <br/>
            <FormItem label="商品规格:">
              <Input v-model="skuMsg.skNorm" :disabled="true" style="width: 180px"></Input>
              <span v-if="goodsSpcount>0" style="color: red"><!--v-if="!houseType&&goodsSpcount>0"-->
                (当前库存剩余{{goodsSpcount}}{{skuMsg.skUnit}})
              </span>
              <!--<span v-else-if="houseType&&goodsSpcount>0" style="color: red">
                 (当前库存剩余{{goods}}{{skuMsg.spUnit}})
              </span>-->
            </FormItem>
            <br/>
            <span style="display: block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px"><!--v-if="!houseType"-->
              <FormItem label="采购数量:" style="margin-right: 0">
                 <AutoComplete
                   v-model="skuMsg.skCount"
                   @on-change="handleSet" style="width: 180px"></AutoComplete>
              </FormItem>
              {{skuMsg.skUnit}}
            </span>
            <span style="display: block;height: 33px;line-height: 33px;font-size: 12px;margin-bottom: 24px">
              <FormItem label="总数量:" prop="spCount">
                <AutoComplete
                  v-model="skuMsg.spCount"
                  @on-change="setCount"
                  style="width: 180px" :disabled="true"></AutoComplete><!--:disabled="!houseType"-->
              </FormItem>
              {{skuMsg.spUnit}}
            </span>
          </Form>
        </Modal>
        <Table :height="tableHeight" border ref="selection" :columns="orderTitle" :data="orderList"></Table>
        <div
          style="width: 100%;position:absolute;bottom:10px;margin-top:20px;;box-sizing: border-box;padding: 0 30px 0 0;text-align: right">
              <span>
                <Checkbox v-model="isFast" :disabled="true">免仓内操作</Checkbox>
                <Button type="success" size="large" vertical="false" @click="createOutOrder()">创建出仓预报单</Button>
              </span>
          <!--  <Page :total="list.length" :current="currentPage" :page-size="pageSize" @on-change="pageChange" show-total  show-elevator></Page>-->
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
        invoice_type: invoice_type,
        selectType: '',
        contentHeight: null,
        tableHeight: 0,
        warehouse: warehouse,
        goodsSpcount: 0,
        goods: 0,
        ruleInline: {
          receiver: [
            {required: true, message: '请输入客户名称', trigger: 'blur'}
          ],
          /*phone: [
            {required: true, message: '请输入客户电话', trigger: 'blur'},
          ],
          address: [
            {required: true, message: '请输入客户地址', trigger: 'blur'},
          ],
          postal: [
            {required: true, message: '请输入邮编号码', trigger: 'blur'},
          ]*/
        },
        showReceiver:[],
        checked: false,
        isFast: true,
        houseType: Boolean(houseType),
        msg: {},
        showList: [],
        keywords: null,
        pageSize: 100,
        currentPage: 1,
        skuList: [],
        skuMsg: {
          count: null,
        },
        list: [],
        keyword: null,
        modal: false,
        rules: {
          spCount: [
            {required: true, message: '请输入SPU数量', trigger: 'blur'}
          ]
        },
        orderTitle: [
          {
            align: 'center',
            title: '编号',
            key: 'skCode'
          },
          /*{
            align: 'center',
            title: 'SPU编码',
            key: 'spCode'
          },*/
          {
            align: 'center',
            title: '名称',
            key: 'skName'
          },
          {
            align: 'center',
            title: '规格',
            key: 'skNorm'
          },
          {
            align: 'center',
            title: '数量',
            key: 'skCount',
          },
          {
            align: 'center',
            title: '单位',
            key: 'skUnit',
          },
          /*{
            align: 'center',
            title: '对应SPU总数量',
            key: 'count',
          },
          {
            align: 'center',
            title: '单位',
            key: 'spUnit',
          },*/
          {
            title: '操作',
            key: 'action',
            width: 122,
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
                }, '删除'),
              ]);
            }
          }
        ],
        orderList: [],
        addMsg: [],
        setSpcount: 0
      }
    },
    methods: {
      searchReceiver(value) {
        const _this = this;
        _this.showReceiver = [];
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_SEARCHE_LIKE,
          data: {
            receiver: value
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_SEARCHE_LIKE, params).then(res => {
          if (res.success) {
            _this.showReceiver = res.data.list;
          }
        })
      },
      selectReceiver(val) {
        this.msg.receiver = val;
      },
      isInteger(obj) {
        return Math.round(obj) == obj   //是整数，则返回true，否则返回false
      },
      /*      selectOption(value) {
              const _this = this
              _this.msg = {}
              _this.warehouse.forEach(function (item) {
                if (item.war_name.indexOf(value) > -1) {
                  _this.msg.name = item.war_name;
                  _this.msg.receiver = item.war_info
                  _this.msg.address = item.war_address;
                  _this.msg.phone = item.war_phone;
                  _this.msg.eCode = item.war_eCode;
                  _this.msg.postal = item.war_postal;
                  _this.msg.pDate = item.war_pDate;
                  _this.msg.mark = item.war_mark;
                }
              })
            },*/
      handleSearch(value) {
        this.showList = []
        const _this = this;
        let params = {
          service: _this.Api.VENUS_WMS_INVOICE_INVOICE_GET_SKU,
          data: {
            sku: value
          }
        };
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
                showMsg = item.skName + "【" + "规格：" + item.skNorm + "、备注：" + item.mark + "、编码：" + item.skCode + "】"
              } else if (item.skNorm && !item.mark) {
                showMsg = item.skName + "【" + "规格：" + item.skNorm + "、编码：" + item.skCode + "】"
              } else if (!item.skNorm && item.mark) {
                showMsg = item.skName + "【" + "备注：" + item.mark + "、编码：" + item.skCode + "】"
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
          let val = value.split("：")
          let vals = val[val.length - 1]
          var valCode = val[val.length - 1].substr(0, vals.length - 1)
          _this.skuList.forEach(function (item) {
            if ((item.skName == valList[0] && item.skCode == valCode) || item.skCode.indexOf(valList[1]) > -1) {
              // item.mark = _this.skuMsg.mark
              _this.skuMsg = item
              _this.skuMsg.skCount = 1
              _this.setSpcount = item.spCount;
              _this.goodsSpcount = item.goods / item.spCount
              _this.goods = item.goods
              if (_this.list.length > 0) {
                for (let i = 0; i < _this.list.length; i++) {
                  if (_this.list[i].skCode == _this.skuMsg.skCode) {
                    _this.goodsSpcount = _this.goodsSpcount - _this.list[i].skCount;
                  }
                }
              }
            }
          })
        }
      },
      outAdd() {
        const _this = this
        if (Number(_this.skuMsg.spCount) > Number(_this.goods)) {
          this.$Modal.warning({
            title: '提示',
            content: '当前货品库存不足',
          });
        } else {
          if (this.list.length > 0) {
            for (let i = 0; i < this.list.length; i++) {
              if (_this.skuMsg.skCode == _this.list[i].skCode) {
                this.list[i].count = parseFloat(this.list[i].count) + parseFloat(this.skuMsg.spCount);
                this.list[i].skCount = parseFloat(this.list[i].skCount) + parseFloat(this.skuMsg.skCount);
                _this.skuMsg = {}
                _this.keywords = '  '
                _this.goodsSpcount = 0
                return;
              }
            }
            _this.list.push(_this.addMsg);
          } else {
            _this.list.push(_this.addMsg)
          }
          _this.skuMsg = {}
          _this.keywords = '  '
          _this.goodsSpcount = 0
        }
      },
      confirmAdd() {
        const _this = this
        let skuMsg = JSON.parse(JSON.stringify(_this.skuMsg));
        skuMsg.count = _this.skuMsg.spCount
        _this.addMsg = skuMsg
        if (_this.skuMsg.skName != null || _this.skuMsg.skCode != null) {
          _this.$refs.formInline2.validate((valid) => {
            if (valid && _this.skuMsg.spCount > 0) {
              if (_this.skuMsg.spCunit == 1) {
                if (_this.isInteger(_this.skuMsg.skCount) == true) {
                  _this.outAdd();
                } else {
                  this.$Modal.warning({
                    title: '提示',
                    content: '数量格式不正确',
                  });
                }
              } else {
                _this.outAdd();
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
      createOutOrder() {
        let _this = this
        if (!_this.selectType) {
          _this.$Message.error('请选择出仓单类型');
        } else {
          let params = {
            service: _this.Api.VENUS_WMS_INVOICE_INVOICE_CREATE,
            data: {
              list: _this.list,
              isFast: Number(_this.isFast),
              type: _this.selectType,
              receiver: _this.msg.receiver,
              mark: _this.msg.mark,
              // danw: _this.msg.danw,
              // phone: _this.msg.phone,
              // eCode: _this.msg.eCode,
              // address: _this.msg.address,
              // postal: _this.msg.postal,
              // pDate: _this.msg.pDate,
            }
          }
          _this.$refs.formInline.validate((valid) => {
            if (valid) {
              if (params.data.list.length > 0) {
                _this.$http.post(_this.Api.VENUS_WMS_INVOICE_INVOICE_CREATE, params).then(res => {
                  if (res.success) {
                    _this.$refs.formInline.resetFields();
                    _this.$Message.info('创建出仓预报单成功');
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
            } else {
              this.$Modal.warning({
                title: '提示',
                content: '请补全信息',
              });
            }
          })
        }
      },
      handleSet() {
        const _this = this;
        if (_this.skuMsg.skCount) {
          _this.skuMsg.spCount = _this.setSpcount;
          _this.skuMsg.spCount = _this.skuMsg.skCount * _this.skuMsg.spCount

        } else {
          _this.skuMsg.spCount = null
        }
      },
      setCount() {
        this.skuMsg.skCount = ''
      },
      remove(index) {
        const _this = this;
        this.$Modal.confirm({
          title: '提示',
          content: `确定要删除吗？`,
          cancelText: `取消`,
          onOk() {
            _this.list.splice(index, 1);
          }
        })
      },
      onselectSend(val) {
        if (val) {
          this.msg.pDate = ''
          this.msg.pDate = val
        }
      },
      pageChange: function (page) {
        this.currentPage = page
        this.updateDataShow()
      },
      updateDataShow: function () {
        let startPage = (this.currentPage - 1) * this.pageSize
        let endPage = startPage + this.pageSize
        this.orderList = this.list.slice(startPage, endPage)
      },
    },
    mounted() {
      if (this.invoice_type.length == 1) {
        this.selectType = this.invoice_type[0].code
      }
      // if (this.houseType) {
      //   this.checked = true
      // } else {
      this.rules = {}
      // }
      if (this.list.length > 0) {
        this.tableHeight = Number(window.innerHeight - 334)
      }
      this.contentHeight = Number(window.innerHeight - 170);
    },
    watch: {
      list: function () {
        this.orderList = this.list.slice(0, this.pageSize)
        if (this.list.length > 0) {
          this.tableHeight = Number(window.innerHeight - 334)
        }
      },
    },
  }
</script>


