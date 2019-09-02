<style>
    .ivu-table .table-info-cell-mon{
      background-color: #ecf5ff;
      font-weight: bold;
    }
    .ivu-table .table-info-cell-tus{
      background-color: #f6effe;
      font-weight: bold;
      }
    .ivu-table .table-info-cell-wen{
        background-color: #f0f9eb;
        font-weight: bold;
      }
      .ivu-table .table-info-cell-tur{
        background-color: #fef0f0;
        font-weight: bold;
      }
      .ivu-table .table-info-cell-fri{
        background-color: oldlace;
        font-weight: bold;
      }
</style>
<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>货品数据管理</BreadcrumbItem>
	        <BreadcrumbItem>自动上下架</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
            <Table :columns="columns" border :data="data"></Table>

	        </div>
	    </Card>
	</Content>
</template>
<script>
	export default{
		methods:{

		},
		mounted(){
			this.tableHeight = Number(window.innerHeight - 278);
      this.contentHeight = Number(window.innerHeight - 170);
		},
    data(){
	    return {
	      data:[
          {
            weekday:'周一',
            cellClassName: {
              weekday: 'table-info-cell-mon'
            }
          },
          {
            weekday:'周二',
            cellClassName: {
              weekday: 'table-info-cell-tus'
            }
          },
          {
            weekday:'周三',
            cellClassName: {
              weekday: 'table-info-cell-wen'
            }
          },
          {
            weekday:'周四',
            cellClassName: {
              weekday: 'table-info-cell-tur'
            }
          },
          {
            weekday:'周五',
            cellClassName: {
              weekday: 'table-info-cell-fri'
            }
          }
        ],
        columns:[
          {
            title:'时间',
            key:'weekday',
            align:'center'
          },
          {
            title:'导入操作',
            key:'action',
            align:'center',
            render:(h,params)=>{
              let type = parseInt(params.index) + 1;
              let _this = this;
                return h('Upload',{
                    props:{
                      action:this.$http.baseUrl.host1,
                      multiple:false,
                      showUploadList:false,
                      format:['xlsx'],
                      data:{
                        'service': this.Api.VENUS_WMS_SKUEXTERNAL_AUTO_IMPORT,
                        'type':type
                      },
                      'on-success':(res,file)=>{
                        if(res.success){
                          _this.$Message.success('商品导入成功');
                        }
                      },
                      'on-format-error':()=>{
                        _this.$Message.warning({
                          content: '文件格式不符，请选择.xlsx格式的文件上传',
                          duration: 3,
                          closable: params.index
                        });
                      }
                    }
                  },[
                    h('Button', {
                      props:{
                        type:'primary'
                      }
                    },'导入')
							    ])
              }
          },
          {
            title:'导出操作',
            key:'action',
            align:'center',
            render:(h,params)=>{
              let type = parseInt(params.index) + 1;
              return h('div',[
                  h('Button',{
                    props:{
                      type:'success',
                      size:'default'
                    },
                    on:{
                      click:()=>{
                        let params = {
                          "service": this.Api.VENUS_WMS_SKUEXTERNAL_AUTO_EXPLODE,
                          data :{
                            type:type
                          }
                        };
                  this.$http.post(this.Api.VENUS_WMS_SKUEXTERNAL_AUTO_EXPLODE,params).then(res=>{
                      if(res.success){
                        window.location.href = res.data;
                      }
                  })
                      }
                    }
                  },'导出')
              ])
            }
          },
        ],
      }
    },
	}
</script>
