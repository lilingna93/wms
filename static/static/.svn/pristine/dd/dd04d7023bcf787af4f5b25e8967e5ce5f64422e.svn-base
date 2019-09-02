<style>
	.operate{
		margin-bottom: 10px;
		overflow: hidden;
	}
</style>
<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>货品数据管理</BreadcrumbItem>
	        <BreadcrumbItem>供货商管理</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
	        	<Form :label-width="100" ref="formInline" inline>
    				<FormItem label="编码 / 名称：">
			            <Input type="text" placeholder="" v-model="searchData.suName"></Input>
			        </FormItem>
				    <Button type="primary" @click="queryTable()">查询</Button>
				    <Button type="warning" style="float:right" @click="addSup = true">添加供应商</Button>
			    </Form>
			    <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data" :loading="loading"></Table>
			    <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total  :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
			    <Modal
			        v-model="addSup"
			        title="添加供应商"
			        @on-ok="confirmAdd"
			        @on-visible-change="handleReset('addSupData')">
			        <Form :label-width="100" ref="addSupData" :model="addSupData">
				        <FormItem label="供应商名称：" prop="suName">
				            <Input type="text" style="width:300px;" v-model="addSupData.suName"></Input>
				        </FormItem>
				        <FormItem label="联系人姓名：" prop="suManager">
				            <Input type="text" style="width:300px;" v-model="addSupData.suManager"></Input>
				        </FormItem>
				        <FormItem label="联系电话："  prop="suPhone">
				            <Input type="text" style="width:300px;" v-model="addSupData.suPhone"></Input>
				        </FormItem>
				        <FormItem label="供应商类型：" prop="isSupplier">
			                <Checkbox v-model="addSupData.isSupplier">自有供应商</Checkbox>
			        	</FormItem>
			        </Form>
			    </Modal>
			    <Modal
			        v-model="editSup"
			        title="修改供应商"
			        @on-ok="confirmEdit">
			        <Form :label-width="100" >
				        <FormItem label="供应商名称：">
				            <Input type="text" style="width:300px;" v-model="editSupData.suName"></Input>
				        </FormItem>
				        <FormItem label="联系人姓名：">
				            <Input type="text" style="width:300px;" v-model="editSupData.suManager"></Input>
				        </FormItem>
				        <FormItem label="联系电话：">
				            <Input type="text" style="width:300px;" v-model="editSupData.suPhone"></Input>
				        </FormItem>
				        <FormItem label="供应商类型：">
			                <Checkbox v-model="editSupData.isSupplier">自有供应商</Checkbox>
			        	</FormItem>

			        </Form>
			    </Modal>
	        </div>
	    </Card>
	</Content>
</template>
<script>
	export default{
		methods:{
			queryTable(curPage){
				curPage=curPage?curPage:0;
	    		var params={
	    			"service":this.Api.VENUS_WMS_SUPPLIER_SUP_SEARCH,
	    			"data":{
	    				"suName":this.searchData.suName,
	    				"pageCurrent":curPage
	    			}
	    		}
	    		this.$http.post(this.Api.VENUS_WMS_SUPPLIER_SUP_SEARCH,params).then(res =>{
	    			this.loading=false;
	    			this.pageData.data=res.data.list;
	    			this.pageData.totalCount=parseInt(res.data.totalCount);
	    			this.pageData.pageSize=parseInt(res.data.pageSize);
	    			this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
	    		})
	    	},
	    	remove (index) {
		        this.$Modal.confirm({
		          	title: '提示',
		          	content: `确定要删除吗？`,
		          	cancelText:`取消`,
		          	onOk: () =>{
		            	this.pageData.data.splice(index, 1);
		          	}
		        })
	      	},
	      	confirmAdd(){
	      		if(this.addSupData.isSupplier){
	      			this.addSupData.suType=1;
	      		}else{
	      			this.addSupData.suType=2;
	      		}
	      		var params={
	      			"service":this.Api.VENUS_WMS_SUPPLIER_SUP_ADD,
	      			"data":this.addSupData
	      		}
	      		this.$http.post(this.Api.VENUS_WMS_SUPPLIER_SUP_ADD,params).then(res =>{
	    			if(res.success){
	    				this.$Modal.success({
	    					title:'提示',
	    					content:res.message
	    				})
	    				this.queryTable();
	    			}
	    		})
	      	},
	      	confirmEdit(){
	      		if(this.editSupData.isSupplier){
	      			this.editSupData.suType=1;
	      		}else{
	      			this.editSupData.suType=2;
	      		}
	      		var params={
	      			"service":this.Api.VENUS_WMS_SUPPLIER_SUP_UPDATE,
	      			"data":this.editSupData
	      		}
	      		this.$http.post(this.Api.VENUS_WMS_SUPPLIER_SUP_UPDATE,params).then(res =>{
	    			if(res.success){
	    				this.$Modal.success({
	    					title:'提示',
	    					content:res.message
	    				})
	    				this.queryTable();
	    			}
	    		})
	      	},
	      	handleChange(count){
	    		count=count-1;
	    		this.queryTable(count);
	    	},
	      	handleReset(name){
	      		this.$refs[name].resetFields();
	      	},
	      	deleteSupplier(suCode){
	      		var params={
					"service":this.Api.VENUS_WMS_SUPPLIER_SUP_DELETE,
					"data":{
	    				"suCode":suCode
	    			}
	    		}
	      		this.$http.post(this.Api.VENUS_WMS_SUPPLIER_SUP_DELETE,params).then(res =>{
	    			if(res.success){
	    				this.queryTable();
	    				this.$Message.info(res.message);
	    			}
	    		})
	      	}
		},
		mounted(){
			this.queryTable();
			this.tableHeight = Number(window.innerHeight - 278);
      		this.contentHeight = Number(window.innerHeight - 170);
		},
		data(){
			return {
				contentHeight: 0,
        		tableHeight: 0,
				loading:true,
				addSup:false,
				editSup:false,
				addSupData:{
					suName:'',
					suManager:'',
					suPhone:'',
					isSupplier:false,
					suType:2
				},
				editSupData:{
					suCode:'',
					suName:'',
					suManager:'',
					suPhone:'',
					isSupplier:false,
					suType:2
				},
				searchData:{
					suName:'',
				},
				pageData:{
					columns:[

						{
							title:'供应商编码',
							key:'suCode'
						},
						{
							title:'供货商名称',
							key:'suName'
						},
						{
							title:'联系人姓名',
							key:'suManager'
						},
						{
							title:'联系电话',
							key:'suPhone'
						},
						{
				            title: '操作',
				            key: 'action',
				            width: 150,
				            align: 'center',
				            render: (h, params) => {
				              return h('div', [
				                h('Button', {
				                  props: {
				                    type: 'primary',
				                    size: 'small'
				                  },
				                  style: {
				                    marginRight: '5px'
				                  },
				                  on: {
				                    click: () => {
				                    	this.editSupData.suCode = params.row.suCode;
				                      	this.editSupData.suName = params.row.suName;
				                      	this.editSupData.suManager = params.row.suManager;
				                      	this.editSupData.suPhone = params.row.suPhone;
				                      	if(params.row.suType==2){
				                      		this.editSupData.isSupplier=false;
				                      	}else if(params.row.suType==1){
				                      		this.editSupData.isSupplier=true;
				                      	}
				                      	this.editSup=true;
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
					                      	title:'提示',
					                      	content:'确定要删除吗？',
						                      	onOk: () => {
						                      		this.deleteSupplier(params.row.suCode);
						                      	}
				                      	})
				                    }
				                  }
				                }, '删除'),
				              ]);
				            }
				          }

					],
					data:[],
					totalCount:0,
					pageSize:0,
					pageCurrent:0
				}

			}

		}
	}
</script>
