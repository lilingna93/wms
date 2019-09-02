<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>品控报表</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}" style="text-align: center">
        <div class="goodsList">
          <Table :height="tableHeight" border  :columns="listTit" :data="list"></Table>
          <div class="page"
               style="width: 100%;position:absolute;bottom:10px;margin-top:20px;box-sizing: border-box;padding: 0 30px 0 0">
            <Page :total="pageParams.totalCount" :current="pageParams.pageCurrent+1" style="float: right" :page-size="pageParams.pageSize" @on-change="pageChange" show-total
                  show-elevator></Page>
          </div>
          <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
            <input type="hidden" name="fname" v-model="formData.fname">
            <input type="hidden" name="tname" v-model="formData.tname">
            <input type="hidden" name="sname" v-model="formData.sname">
          </form>
        </div>
      </div>
    </Card>
  </Content>
</template>

<script>
  export default {
    name: "qa-report",
    data() {
      return {
        username: config.user.rname,
        formData:{
          fname:'',
          tname:'',
          sname:''
        },
        contentHeight: 0,
        listTit:[
          {
            title: '报表名称',
            align: 'center',
            key: 'fname'
          },
          {
            title: '操作',
            align: 'center',
            render: (h, params) => {
              let actionBtn = [];
              actionBtn = [
                h('Button', {
                  props: {
                    type: 'primary',
                    size: 'small',
                  },
                  on: {
                    click: () => {
                      this.down(params.row);
                    }
                  }
                }, '下载')
              ]
              return h('div', actionBtn);
            }
          }
        ],
        list :[],
        pageParams:{
          totalCount:0,
          pageCurrent:0,
          pageSize:100
        }
      }
    },
    methods: {
      down(params) {
        let _this = this;
        _this.formData.fname= params.sfname
        _this.formData.sname= params.fname+'.xlsx '
        _this.formData.tname= params.scatalogue
        let param = {
          service: _this.Api.VENUS_WMS_REPORTDOWNLOAD_DOWNLOAD_FILE,
          data: {
            id:params.id,
            username:this.username
          }
        };
        _this.$http.post(_this.Api.VENUS_WMS_REPORTDOWNLOAD_DOWNLOAD_FILE, param).then(res => {
          if (res.success) {
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      pageChange(count) {
        count = count - 1;
        this.getList(count);
      },
      getList(page) {
        let _this = this;
        page = page ? page : 0
        let params = {
          service: _this.Api.VENUS_WMS_REPORTDOWNLOAD_REPORTDOWNLOAD_LIST,
          data: {
            sdepartments:5,
            pageCurrent: page,
            pageSize:_this.pageParams.pageSize
          }
        }
        _this.$http.post(_this.Api.VENUS_WMS_REPORTDOWNLOAD_REPORTDOWNLOAD_LIST, params).then(res => {
          if (res.data && res.data.list) {
            _this.list = res.data.list;
            _this.pageParams.totalCount = parseInt(res.data.totalCount);
            _this.pageParams.pageCurrent = parseInt(res.data.pageCurrent);
          }
        })
      }
    },
    mounted() {
      this.contentHeight = Number(window.innerHeight - 170);
      this.tableHeight = Number(window.innerHeight - 200);
      this.getList();
    },
  }
</script>

<style scoped>

</style>
