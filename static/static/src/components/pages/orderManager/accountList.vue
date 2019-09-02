<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>订单管理</BreadcrumbItem>
	        <BreadcrumbItem>下单账户管理</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
	        	<Button type="warning" style="margin-bottom: 10px;" @click="modalData.accountAdd=true">添加账户</Button>
	        	<Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data" :loading="loading"></Table>
			    <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total  :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
			    <Modal
			    v-model="modalData.accountAdd"
			    title="添加账户"
			    @on-ok="addAccount"
			    @on-visible-change="handleReset('addAccountData')">
			        <Form :label-width="130" ref="addAccountData" :model="addAccountData">
				    	<FormItem label="账户名称：" prop="uName">
				            <Input type="text" style="width:280px;" v-model="addAccountData.uName"></Input>
				        </FormItem>
				        <FormItem label="账户手机号：" prop="uPhone">
				            <Input type="text" style="width:280px;" v-model="addAccountData.uPhone"></Input>
				        </FormItem>
				        <FormItem label="第三方账户Token:" prop="uToken">
				            <Input type="text" style="width:280px;" v-model="addAccountData.uToken"></Input>
				        </FormItem>
			        </Form>
			    </Modal>
			     <Modal v-model="modalData.accountEdit" title="修改账户" @on-ok="editAccount">
			        <Form :label-width="130">
				    	<FormItem label="账户名称：">
				            <Input type="text" style="width:280px;" v-model="editAccountData.uName"></Input>
				        </FormItem>
				        <FormItem label="账户手机号：">
				            <Input type="text" style="width:280px;" v-model="editAccountData.uPhone"></Input>
				        </FormItem>
				        <FormItem label="第三方账户Token:">
				            <Input type="text" style="width:280px;" v-model="editAccountData.uToken"></Input>
				        </FormItem>
			        </Form>
			    </Modal>
	        </div>
	    </Card>
	</Content>
</template>
<script>
export default {
	methods:{
		queryTable(curPage){
			curPage=curPage?curPage:0;
			var params={
				"service":this.Api.VENUS_WMS_ORDER_USER_LIST,
				"data":{
					"pageCurrent":curPage
				}
			}
			this.$http.post(this.Api.VENUS_WMS_ORDER_USER_LIST,params).then(res =>{
				this.loading=false;
				this.pageData.data=res.data.list;
				this.pageData.totalCount=parseInt(res.data.totalCount);
	    		this.pageData.pageSize=parseInt(res.data.pageSize);
	    		this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
			})
		},
		addAccount(){
			var params={
    			'service':this.Api.VENUS_WMS_ORDER_USER_ADD,
    			'data':this.addAccountData
    		}
			this.$http.post(this.Api.VENUS_WMS_ORDER_USER_ADD,params).then(res =>{
    				if(res.success){
    					this.$Modal.success({
    						title:'提示',
    						content:res.message
    					})
    					this.queryTable();
    				}
    		})
		},
		editAccount(){
			var params={
    			'service':this.Api.VENUS_WMS_ORDER_USER_UPDATE,
    			'data':this.editAccountData
    		}
			this.$http.post(this.Api.VENUS_WMS_ORDER_USER_UPDATE,params).then(res =>{
    				if(res.success){
    					this.$Modal.success({
    						title:'提示',
    						content:res.message
    					})
    					this.queryTable();
    				}
    		})
		},
		deleteAccount(uCode){
			var params={
				"service":this.Api.VENUS_WMS_ORDER_USER_DELETE,
				"data":{
    				"uCode":uCode
    			}
    		}
      		this.$http.post(this.Api.VENUS_WMS_ORDER_USER_DELETE,params).then(res =>{
    			if(res.success){
    				this.$Message.info(res.message);
    				this.queryTable();
    			}
    		})
		},
		handleReset(name){
      		this.$refs[name].resetFields();
      	},
      	handleChange(count){
    		count=count-1;
    		this.queryTable(count);
    	}
	},
	mounted(){
		this.queryTable();
		this.tableHeight = Number(window.innerHeight - 278);
      	this.contentHeight = Number(window.innerHeight - 170);
	},
  	data () {
	    return {
	    	contentHeight: 0,
        	tableHeight: 0,
	    	loading:false,
	    	pageData:{
	    		columns:[
					{
						title:'人员编码',
						key:'uCode'
					},
					{
						title:'账户名称',
						key:'uName'
					},
					{
						title:'手机号',
						key:'uPhone'
					},
					{
			            title: '操作',
			            key: 'action',
			            width: 250,
			            align: 'center',
			            render: (h, params) => {
			            	return h('div', [
			            		h('Button', {
				                  	props: {
					                    type: 'warning',
					                    size: 'small'
				                  	},
					                style: {
					                	marginRight: '10px'
					                },
				                  	on: {
				                    click: () => {
				                    	this.editAccountData.uCode=params.row.uCode;
				                    	this.editAccountData.uName=params.row.uName;
				                    	this.editAccountData.uPhone=params.row.uPhone;
				                    	this.editAccountData.uToken=params.row.uToken;
				                    	this.modalData.accountEdit=true;
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
						                      		this.deleteAccount(params.row.uCode);
						                      	}
					                      	})
				                    	}
				                  	}
				                }, '删除')
			            	]);
			            }

					}
    			],
	    		data:[],
	    		totalCount:0,
	    		pageSize:0,
	    		pageCurrent:0
	    	},
	    	modalData:{
	    		accountAdd:false,
	    		accountEdit:false,
	    	},
	    	addAccountData:{
	    		uName:'',
	    		uPhone:'',
	    		uToken:''
	    	},
	    	editAccountData:{
	    		uCode:'',
	    		uName:'',
	    		uPhone:'',
	    		uToken:''
	    	}
	    }
	}
}
</script>
