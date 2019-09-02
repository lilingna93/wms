<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem>库存管理</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form :label-width="66" inline :model="params">
          <FormItem label="一级分类">
            <Select style="width: 100px" v-model="params.tCode" @on-change="changeType(params.tCode)">
              <Option v-for="(item,key) in type" :value="key" :key="key">{{item}}</Option>
            </Select>
          </FormItem>
          <FormItem label="二级分类">
            <Select style="width: 100px" v-model="params.cgCode">
              <Option v-for="(item,key) in subType" :value="key" :key="key">{{item}}</Option>
            </Select>
          </FormItem>
          <FormItem label="库存数量">
            <Select style="width: 100px" v-model="params.all">
              <Option value="1">全部</Option>
              <Option value="2">库存剩余</Option>
              <Option value="3">暂无库存</Option>
            </Select>
          </FormItem>
          <FormItem label="名称" :label-width="40">
            <Input v-model="params.spName"></Input>
          </FormItem>
          <FormItem label="货品编码" :label-width="60">
            <Input v-model="params.code"></Input>
          </FormItem>
          <FormItem  :label-width="20">
            <Button @click="search()" type="primary">查询</Button>
          </FormItem>
          <FormItem  :label-width="20">
            <Button type="warning" @click="linkToWarning()">阈值报警</Button>
          </FormItem>
          <FormItem  :label-width="20">
            <Button @click="down()" type="primary">下载库存</Button>
          </FormItem>
        </Form>
        <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
          <input type="hidden" name="fname" v-model="formData.fname">
          <input type="hidden" name="tname" v-model="formData.tname">
          <input type="hidden" name="sname" v-model="formData.sname">
        </form>
        <Table :height="tableHeight" border ref="selection" :columns="orderTitle" :data="list"></Table>
        <div class="page"
             style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
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
    name: "stock-manage",
    data() {
      return {
        contentHeight: null,
        tableHeight: 0,
        type: type,
        triggerData: subType,
        subType: {"0": "全部"},
        pageSize: 0,
        pageCurrent: 0,
        totalCount: 0,
        params: {
          tCode: null,
          cgCode: null,
          spName: null,
          code:null,
          all:1,
        },
        formData:{
          fname:'',
          tname:'',
          sname:''
        },
        list: [],
        keyword: null,
        modal: false,
        orderTitle: [
          {
            title: '编码',
            key: 'code',
            align: 'center',
          },
          {
            title: '名称',
            key: 'name',
            align: 'center'
          },
          {
            title: '规格',
            key: 'norm',
            align: 'center',
          },
          {
            title: '数量',
            key: 'count',
            align: 'center',
            width: 130,
          },
          {
            title: '单位',
            key: 'unit',
            align: 'center',
            width: 130,
          },
          {
            title: '操作',
            key: 'action',
            width: 130,
            align: 'center',
            render: (h, params) => {
              return h('div', [
                h('Button', {
                  props: {
                    type: 'success',
                    size: 'small'
                  },
                  on: {
                    click: () => {
                      const spCode = params.row.code;
                      this.$router.push({
                        name: 'stockDetail',
                        query: {
                          spCode: spCode
                        }
                      });
                    }
                  }
                }, '查看批次'),
              ]);
            }
          }],
      }
    },
    methods: {
      down(){
        const _this = this;
        let params={
          "service":_this.Api.VENUS_WMS_GOODS_GOODS_EXPORT,
          data: {
            all:_this.params.all,
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_EXPORT,params).then(res =>{
          if(res.success){
            this.formData.fname=res.data.fname;
            this.formData.sname=res.data.sname;
            this.formData.tname=res.data.tname;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      changeType(tCode) {
        this.params.cgCode = "0";
        if (tCode == "0") {
          this.subType = {"0": "全部"};
        } else {
          for (let item in this.triggerData) {
            if (item == tCode) {
              this.subType = this.triggerData[tCode];
            }
          }
        }
      },
      search(page) {
        let _this = this;
        _this.list = [];
        _this.totalCount = 0;
        _this.pageCurrent = 0;
        _this.pageSize = 0;
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_GOODS_GOODS_SEARCH,
          data: {
            tCode: _this.params.tCode,
            cgCode: _this.params.cgCode,
            spName: _this.params.spName,
            code: _this.params.code,
            all:_this.params.all,
            pageCurrent: page,
          }
        }

        _this.$http.post(_this.Api.VENUS_WMS_GOODS_GOODS_SEARCH, params).then(res => {
          if (res.data && res.data.list) {
            _this.list = res.data.list
            _this.totalCount = parseInt(res.data.totalCount);
            _this.pageSize = parseInt(res.data.pageSize);
            _this.pageCurrent = parseInt(res.data.pageCurrent);
          }
        })
      },
      pageChange(page) {
        let pageCurrent = page - 1;
        this.search(pageCurrent);
      },
      linkToWarning(){
        this.$router.push({
          path: '/thresholdWarning'
        });
      }
    },
    mounted() {
      this.search();
      if (this.list.length) {
        this.tableHeight = Number(window.innerHeight - 276)
      }
      this.contentHeight = Number(window.innerHeight - 164);
    },
    watch: {
      list: function () {
        if (this.list.length) {
          this.tableHeight = Number(window.innerHeight - 264)
        }
      }
    }
  }
</script>

<style>
</style>
