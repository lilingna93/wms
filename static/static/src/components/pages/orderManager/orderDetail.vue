<style type="text/css" media="screen">
	i{
		font-style: normal;
	}
	.ml_20{
		margin-left: 40px;
	}
</style>
<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>订单管理</BreadcrumbItem>
	        <BreadcrumbItem to="orderList">订单管理</BreadcrumbItem>
	        <BreadcrumbItem>订单详情</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
	        	<div style="background:#eee;padding:8px;margin-bottom: 8px;">
		        	<Card>
			            <p slot="title">订单信息</p>
			            <p>
			            	<span>订单编号：<i>{{oCode}}</i></span>
							<span class="ml_20">订单状态：<i>{{oStatusComm}}</i></span>
							<span class="ml_20">
								订单信息：<i>{{orderInfo.warName}}，{{orderInfo.uName}}，({{orderInfo.uPhone}})</i>
							</span>
			            </p>
			        </Card>
		        </div>
		        <div style="margin-bottom: 10px;">
		        	<Button type="warning" @click="modalData.goodsAdd = true" :disabled="disabled">添加货品</Button>
		        	<Button type="primary" @click="modalData.markEdit=true" style="margin-left: 20px" :disabled="disabled">修改订单备注</Button>
		        	<Button type="success" style="margin-left: 20px" @click="exportGoodsList">导出货品清单</Button>
		        </div>
		        <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data" :loading="loading"></Table>
		        <Modal v-model="modalData.goodsAdd"
			        title="添加货品"
			        @on-ok="addGoods"
			        @on-visible-change="handleReset('goodsAddData')">
			       	<Form :label-width="80" ref="goodsAddData" :model="goodsAddData">
			        	<FormItem label="SKU编号：" prop="skCode">
				            <Input type="text" style="width:280px;" v-model="goodsAddData.skCode"></Input>
				            <Button style="margin-left: 10px;" @click="goodsSearch">查询</Button>
				        </FormItem>
				        <FormItem label="货品名称：" prop="spName">
				            <Input type="text" style="width:280px;" disabled v-model="goodsAddData.spName"></Input>
				        </FormItem>
				        <FormItem label="货品数量：" prop="skCount">
				            <Input type="text" style="width:280px;" v-model="goodsAddData.skCount"></Input>
				        </FormItem>
			        </Form>
		        </Modal>
		        <Modal v-model="modalData.markEdit"
			        title="修改订单备注"
			        @on-ok="eidtMark"
			        @on-cancel="cancelMark">
			       	<Form :label-width="80">
			       		<FormItem label="备注：">
			        		<Input type="textarea" :autosize="{minRows: 2,maxRows: 5}" v-model="oMark"></Input>
			        	</FormItem>
			        </Form>
		        </Modal>
		        <Modal v-model="modalData.goodsEdit"
			        title="修改数量"
			        @on-ok="editGoods">
			       	<Form :label-width="80" >
			       		<FormItem label="货品数量：">
			        		<Input type="text" v-model="goodsEditData.skCount"></Input>
			        	</FormItem>
			        </Form>
		        </Modal>
		        <form ref="submitForm" :action="this.$http.baseUrl.host2" enctype="multipart/form-data" method="POST">
					<input type="hidden" name="fname" v-model="formData.fname">
					<input type="hidden" name="tname" v-model="formData.tname">
					<input type="hidden" name="sname" v-model="formData.sname">
			   	</form>
	        </div>
	    </Card>
	</Content>
</template>
<script>
	export default{
		methods:{
			queryTable(){
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_DETAILS_LIST,
	    			'data':{
	    				'oCode':this.oCode,
	    			}
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_DETAILS_LIST,params).then(res =>{
	    			this.loading=false;
	    			this.orderInfo=res.data.info;
	    			if(this.orderInfo.oStatus==1){
	    				this.oStatusComm='已创建';
	    			}else if(this.orderInfo.oStatus==2){
	    				this.oStatusComm='已完成';
	    				this.disabled=true;
						this.pageData.columns.pop();
	    			}else if(this.orderInfo.oStatus==3){
	    				this.oStatusComm='已取消';
	    				this.disabled=true;
						this.pageData.columns.pop();
	    			}else if(this.orderInfo.oStatus==4){
	    				this.oStatusComm='检货中';
	    				this.disabled=true;
						this.pageData.columns.pop();
	    			}
	    			this.goodsAddData.uCode=this.orderInfo.uCode;
	    			this.pageData.data=res.data.list;
	    			this.oMark=this.orderInfo.oMark;
	    		})
			},
			addGoods(){
	    		this.goodsAddData.oCode=this.oCode;
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_GOODS_ADD,
	    			'data':this.goodsAddData
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_GOODS_ADD,params).then(res =>{
	    				if(res.success){
	    					this.$Modal.success({
	    						title:'提示',
	    						content:res.message
	    					})
	    					this.queryTable();
	    				}
	    		})
			},
			handleReset(name){
	      		this.$refs[name].resetFields();
	      	},
			eidtMark(){
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_MARK_UPDATE,
	    			'data':{
	    				'oCode':this.oCode,
	    				'oMark':this.oMark
	    			}
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_MARK_UPDATE,params).then(res =>{
	    				if(res.success){
	    					this.orderInfo.oMark=this.oMark;
	    					this.$Modal.success({
	    						title:'提示',
	    						content:res.message
	    					})
	    				}else{
	    					this.oMark=this.orderInfo.oMark;
	    				}
	    		})
			},
			cancelMark(){
				this.oMark=this.orderInfo.oMark;
			},
			exportGoodsList(){
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_DETAILEDLIST_EXPORT,
	    			'data':{
	    				'oCode':this.oCode,
	    			}
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_DETAILEDLIST_EXPORT,params).then(res =>{
	    				if(res.success){
	    					this.formData.fname=res.data;
		    				var I=this;
		    				setTimeout(function(){
		    					I.$refs.submitForm.submit();
		    				},200)
	    				}
	    		})
			},
			editGoods(){
				this.goodsEditData.oCode=this.oCode;
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_SKUCOUNT_UPDATE,
	    			'data':this.goodsEditData
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_SKUCOUNT_UPDATE,params).then(res =>{
	    				if(res.success){
	    					this.$Modal.success({
	    						title:'提示',
	    						content:res.message
	    					})
	    					this.queryTable();
	    				}
	    		})
			},
			goodsSearch(){
				var params={
	    			'service':this.Api.VENUS_WMS_ORDER_SKU_SEARCH,
	    			'data':{
	    				'skCode':this.goodsAddData.skCode,
	    			}
	    		}
				this.$http.post(this.Api.VENUS_WMS_ORDER_SKU_SEARCH,params).then(res =>{
    				if(res.success){
    					this.goodsAddData.skCode=res.data.skCode;
    					this.goodsAddData.spName=res.data.spName;
    					this.goodsAddData.spCount=res.data.spCount;
    					this.goodsAddData.spCode=res.data.spCode;
    					this.goodsAddData.spSprice=res.data.spSprice;
    					this.goodsAddData.spBprice=res.data.spBprice;
    					this.goodsAddData.suCode=res.data.suCode;
    					this.goodsAddData.pPercent=res.data.pPercent;
              this.goodsAddData.proPrice = res.data.proPrice;
    				}
	    		})
			},
			deleteOrder(goodsCode){
		        var params={
					"service":this.Api.VENUS_WMS_ORDER_GOODS_DELETE,
					"data":{
						"oCode":this.oCode,
						"goodsCode":goodsCode
					}
				}
		  		this.$http.post(this.Api.VENUS_WMS_ORDER_GOODS_DELETE,params).then(res =>{
					if(res.success){
						this.$Message.info(res.message);
						this.queryTable();
					}
				})
			}
		},
		mounted(){
			//console.log(this.$route.query.oCode);
			this.queryTable();
			this.tableHeight = Number(window.innerHeight - 340);
      		this.contentHeight = Number(window.innerHeight - 172);
		},
		data(){
			return {
				contentHeight: 0,
        		tableHeight: 0,
				loading:false,
				oCode:this.$route.query.oCode,
				disabled:false,
				oStatusComm:'',
				oMark:'',
				modalData:{
					goodsAdd:false,
					markEdit:false,
					goodsEdit:false,
				},
				goodsAddData:{
					oCode:'',
					skCode:'',
					spName:'',
					skCount:'',
					spCount:'',
					spCode:'',
					spSprice:'',
					spBprice:'',
					suCode:'',
					uCode:'',
					pPercent:'',
          proPrice:''
				},
				goodsEditData:{
					oCode:'',
					goodsCode:'',
					skCount:'',
					spCount:''
				},
				orderInfo:{},
				formData:{
					fname:'',
					tname:'003',
					sname:'货品清单.xlsx'
				},
				pageData:{
					columns:[
						{
							title:'货品编号',
							key:'goodsCode',
              align: 'center'
						},
						{
							title:'货品名称',
							key:'spName',
              align: 'center'
						},
						{
							title:'规格',
							key:'skNorm',
              align: 'center'
						},
						{
							title:'需求数量',
							key:'skCount',
              align: 'center'
						},
						{
							title:'单位',
							key:'skUnit',
              align: 'center'
						},
						{
							title:'备注',
							key:'skMark',
              align: 'center'
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
					                    	//console.log(params.row.goodsCode);
					                    	this.goodsEditData.skCount=params.row.skCount;
					                    	this.goodsEditData.goodsCode=params.row.goodsCode;
					                    	this.goodsEditData.spCount=params.row.spCount;
					                    	this.modalData.goodsEdit=true;
					                    }
					                  }
					                }, '修改数量'),
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
							                      		this.deleteOrder(params.row.goodsCode);
							                      	}
						                      	})
					                    	}
					                  	}
					                }, '删除')
				            	]);
				            }

						}
					],
					data:[]
				}
			}
		}
	}
</script>
