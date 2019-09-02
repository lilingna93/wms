<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>货品数据管理</BreadcrumbItem>
	        <BreadcrumbItem>SKU管理</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
	        	<Form :label-width="80" ref="formInline" inline>
			    	<FormItem label="一级分类">
			            <Select style="width:160px;" v-model="searchData.spType" @on-change="changeType(searchData.spType)">
			            	<Option  v-for = "(item,key) in type" :value="key" :key="key">{{item}}</Option >
			            </Select >
			        </FormItem>
			        <FormItem label="二级分类">
			        	 <Select style="width:160px;" v-model="searchData.spSubtype">
			            	<Option  v-for = "(item,key) in subType" :value="key" :key="key">{{item}}</Option >
			            </Select >
			        </FormItem>
			        <FormItem label="状态">
			            <Select style="width:160px;" v-model="searchData.skStatus">
			            	<Option  value="0">全部</Option >
			            	<Option  value="1">已上线</Option >
			            	<Option  value="2">已下线</Option >
			            </Select >
			        </FormItem>
    				<FormItem label="产品名称">
			            <Input type="text" placeholder="" v-model="searchData.spName"></Input>
			        </FormItem>
				    <Button type="primary" @click="queryTable()">查询</Button>
				    <Button type="success" style="text-align:right;margin-left: 20px;"  @click="linkToExternal()">外部客户SKU设置</Button>
			    </Form>


			    <!-- <Button type="warning" style="margin-bottom:10px;" @click="pushData">推送最新SKU</Button> -->
			    <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data" :loading="loading"></Table>
			    <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total  :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
	        </div>
	    </Card>
	</Content>
</template>
<script>
	export default{
		data(){
			return {
				contentHeight: 0,
        		tableHeight: 0,
				loading:true,
				type:type,
				triggerData:subType,
				subType:{"0":"全部"},
				searchData:{
					spName:'',
					spType:'0',
					spSubtype:'0',
					skStatus:'0',
				},
				pageData:{
					columns:[
						{
							title:'SKU编码',
							key:'skCode'
						},
						{
							title:'所属SPU编码',
							key:'spCode'
						},
						{
							title:'产品名称',
							key:'spName'
						},
						{
							title:'规格',
							key:'skNorm'
						},
						{
							title:'规格数量',
							key:'spCount'
						},
						{
							title:'采购单位',
							key:'skUnit'
						},
						{
	                        title: '状态',
	                        key: 'skStatus',
	                        width: 150,
	                        align: 'center',
	                        render: (h, params) => {
							  return h('i-switch', {
							    props: {
							   	  'size':'large',
							      'true-value': '1',
							      'false-value': '2',
							      'value': params.row.skStatus
							    },
							    on:{
							    	'on-change':() => {
							    		this.loading=true;
							    		this.switchStatus(params.row.skCode,params.row.skStatus);
							    	},
							    }
							  }, [
							    h('span', {
								  	slot: 'open',
								  	domProps:{
								        innerHTML: '上线'
								    }
								}),
								h('span', {
								  	slot:  'close',
								  	domProps:{
								        innerHTML: '下线'
								    }
								})
							  ])
							}

					}],
					data:[],
					totalCount:0,
					pageSize:0,
					pageCurrent:0
				}
			}

		},
	    mounted () {
            this.queryTable();
            this.tableHeight = Number(window.innerHeight - 278);
      		this.contentHeight = Number(window.innerHeight - 170);
        },
	    methods:{
	    	pushData(){
	    		var params={
	    			"service":this.Api.VUENS_WMS_SKU_SKU_PUBLISH,
	    			"data":{
		    			"push":'1',
	    			}
	    		}
	    		this.$http.post(this.Api.VUENS_WMS_SKU_SKU_PUBLISH,params).then(res =>{
	    			if(res.success){
	    				this.$Modal.success({
		    				title:'提示',
		    				content:res.message
	    				})
	    			}

	    		})

	    	},
	    	queryTable(curPage){
	    		curPage=curPage?curPage:0;
	    		var params={
	    			"service":this.Api.VENUS_WMS_SKU_SKU_SEARCH,
	    			"data":{
	    				"spName":this.searchData.spName,
	    				"spType":this.searchData.spType,
	    				"spSubtype":this.searchData.spSubtype,
	    				"skStatus":this.searchData.skStatus,
	    				"pageCurrent":curPage
	    			}
	    		}
	    		this.$http.post(this.Api.VENUS_WMS_SKU_SKU_SEARCH,params).then(res =>{
	    			this.loading=false;
	    			this.pageData.data=res.data.list;
	    			this.pageData.totalCount=parseInt(res.data.totalCount);
		    		this.pageData.pageSize=parseInt(res.data.pageSize);
		    		this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
	    		})
	    	},
	    	changeType(typeCode){
            	this.searchData.spSubtype="0";
            	if(typeCode=="0"){
            		this.subType={"0":"全部"};
            	}else{
            		for(let item in this.triggerData){
	            		if(item==typeCode){
	            			this.subType=this.triggerData[typeCode];

	            		}
	            	}
            	}
            },
	    	handleChange(count){
	    		count=count-1;
	    		this.queryTable(count);
	    	},
	    	switchStatus(skCode,skStatus){
	    		if(skStatus=='1'){
	    			var params={
	    				"service":this.Api.VENUS_WMS_SKU_STATUS_OFFLINE,
	    				"data":{
	    					"skCode":skCode,
	    					"skStatus":'2'
	    				}
	    			}
	    			this.$http.post(this.Api.VENUS_WMS_SKU_STATUS_OFFLINE,params).then(res =>{
		    			if(res.success){
		    				this.loading=false;
		    				this.queryTable(this.pageData.pageCurrent)
		    				this.$Modal.success({
		    					title:'提示',
		    					content:res.message
		    				})
		    			}
	    			})
	    		}else{
	    			var params={
	    				"service":this.Api.VENUS_WMS_SKU_STATUS_ONLINE,
	    				"data":{
	    					"skCode":skCode,
	    					"skStatus":'1'
	    				}

	    			}
	    			this.$http.post(this.Api.VENUS_WMS_SKU_STATUS_ONLINE,params).then(res =>{
		    			if(res.success){
		    				this.loading=false;
		    				this.queryTable(this.pageData.pageCurrent)
		    				this.$Modal.success({
		    					title:'提示',
		    					content:res.message
		    				})
		    			}
	    			})
	    		}
	    	},
	    	linkToExternal(){
	    		this.$router.push({
		          path: '/externalSku'
		        });
	    	}
	    },
	}
</script>
