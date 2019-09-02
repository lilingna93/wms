<template>
	<Content :style="{padding: '0 16px 16px'}">
	    <Breadcrumb :style="{margin: '16px 0'}">
	        <BreadcrumbItem>订单管理</BreadcrumbItem>
	        <BreadcrumbItem>订单管理</BreadcrumbItem>
	    </Breadcrumb>
	    <Card>
	        <div :style="{minHeight:contentHeight+'px'}">
	        	<Form :label-width="80" inline>
		            <FormItem label="起始日期：">
		              <DatePicker type="datetime" placeholder="开始日期" style="width: 200px" :value="searchData.startTime" @on-change="changeStartDate"></DatePicker>
		            </FormItem>
		            <FormItem label="结束日期：">
		              <DatePicker type="datetime" placeholder="结束日期" style="width: 200px" :value="searchData.endTime"  @on-change="changeEndDate"></DatePicker>
		            </FormItem>
		            <FormItem label="客户仓库：">
		             	 <Select style="width:200px" v-model="searchData.warCode">
		             	 	<Option v-for="item in warehouse" :value="item.war_code" :key="item.war_code">{{item.war_name}}</Option >
		             	 </Select>

		            </FormItem>
		        </Form>
		        <Form :label-width="80" inline>
		        	<FormItem label="订单状态">
		             	<Select style="width:200px" v-model="searchData.oStatus">
		             	 	<Option value="0">全部</Option>
		             	 	<Option value="1">已创建</Option>
		             	 	<Option value="4">验货中</Option>
		             	 	<Option value="2">已完成</Option>
		             	 	<Option value="3">已取消</Option>
		             	</Select>
		            </FormItem>
		            <FormItem label="送货日期" v-if="show">
		            	<Select style="width:200px" v-model="searchData.oPdate">
		             	 	<Option value="0">全部</Option>
		             	 	<Option v-for="item in pDate" :value="item" :key="item">{{item}}</Option>
		             	</Select>
		            </FormItem>
		            <Button type="warning" style="margin-left:20px;" @click="queryTable()">查询</Button>
		        </Form>
		        <Button type="primary" style="margin-bottom: 10px" @click="exportOrder">导出仓库自采订单</Button>
		        <Button type="warning" style="margin-bottom: 10px;margin-left: 10px" @click="exportKmOrder">导出仓库自有订单</Button>
            <Button type="info"  style="margin-bottom: 10px;margin-left: 10px" @click="exportCustomerOrder">导出客户自采订单</Button>
			    <Table :height="tableHeight" border ref="selection" :columns="pageData.columns" :data="pageData.data" :loading="loading" @on-selection-change="recordSelection"></Table>
			    <Page style="text-align: right;margin-top: 10px;" :total="pageData.totalCount" show-elevator show-total  :page-size="pageData.pageSize" :current="pageData.pageCurrent+1" @on-change="handleChange"></Page>
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
			changeStartDate(date){
				this.searchData.startTime=date;
			},
			changeEndDate(date){
				this.searchData.endTime=date;
			},
			queryTable(curPage){
	    		var params={
	    			"service":this.Api.VENUS_WMS_ORDER_ORD_SEARCH,
	    			"data":{
	    				"startTime":this.searchData.startTime,
	    				"endTime":this.searchData.endTime,
	    				"oStatus":this.searchData.oStatus,
	    				"warCode":this.searchData.warCode,
	    				"oPdate":this.searchData.oPdate,
	    				"pageCurrent":curPage
	    			}
	    		}
	    		this.$http.post(this.Api.VENUS_WMS_ORDER_ORD_SEARCH,params).then(res =>{
	    			this.loading=false;
	    			this.pageData.data=res.data.list;
	    			this.pageData.totalCount=parseInt(res.data.totalCount);
	    			this.pageData.pageSize=parseInt(res.data.pageSize);
		    		this.pageData.pageCurrent=parseInt(res.data.pageCurrent);
	    			for(var i=0;i<res.data.list.length;i++){
	    				var item=res.data.list[i];
	    				this.pDate.push(item.oPdate);
	    			}
	    			if(this.pDate.length>1){
	    				this.pDate=Array.from(new Set(this.pDate));
	    				this.show=true;
	    			}else{
	    				this.show=false;
	    			}

	    		})
	    	},
	    	updateStatus(oCode,oStatus){
	        	var params={
	      			"service":this.Api.VENUS_WMS_ORDER_STATUS_UPDATE,
	      			"data":{
	      				"oCode":oCode,
	      				"oStatus":oStatus
	      			}
	      		}
	      		this.$http.post(this.Api.VENUS_WMS_ORDER_STATUS_UPDATE,params).then(res =>{
	    			if(res.success){
	    				if(oStatus==2){
	    					this.$Message.info('订单已标记完成');
	    				}else if(oStatus==3){
	    					this.$Message.info('订单取消成功');
	    				}
	    				this.queryTable();
	    			}
				})
	    	},
	      	recordSelection(selected){
	      		this.selectedData=selected;
	      	},
	      	exportOrder(){
	      		this.formData.sname="采购单(仓库自采).xlsx";
	      		if(this.selectedData.length==0){
	      			this.$Modal.warning({
	                    title: '提示',
	                    content: '请选择订单！'
                	});
	      		}else{
	      			let oCodes=[];
	      			for(var i=0;i<this.selectedData.length;i++){
	      				oCodes.push(this.selectedData[i].oCode);
	      			}
	      			//console.log(ocodes);
	      			var params={
	      				"service":this.Api.VENUS_WMS_ORDER_ORD_EXPORT,
	      				"data":{
	      					"oCodes":oCodes
	      				}
	      			}
	      			this.$http.post(this.Api.VENUS_WMS_ORDER_ORD_EXPORT,params).then(res =>{
		    			if(res.success){
		    				this.formData.fname=res.data;
		    				var I=this;
		    				setTimeout(function(){
		    					I.$refs.submitForm.submit();
		    				},200)
		    			}
	    			})
	      		}
	      	},
	      	exportKmOrder(){
	      		this.formData.sname="采购单(仓库自有).xlsx";
	      		if(this.selectedData.length==0){
	      			this.$Modal.warning({
	                    title: '提示',
	                    content: '请选择订单！'
                	});
	      		}else{
	      			let oCodes=[];
	      			for(var i=0;i<this.selectedData.length;i++){
	      				oCodes.push(this.selectedData[i].oCode);
	      			}
	      			var params={
	      				"service":this.Api.VENUS_WMS_ORDER_ORD_INV_EXPORT,
	      				"data":{
	      					"oCodes":oCodes
	      				}
	      			}
	      			this.$http.post(this.Api.VENUS_WMS_ORDER_ORD_INV_EXPORT,params).then(res =>{
		    			if(res.success){
		    				this.formData.fname=res.data;
		    				var I=this;
		    				setTimeout(function(){
		    					I.$refs.submitForm.submit();
		    				},200)
		    			}
	    			})
	      		}
	      	},
          exportCustomerOrder() {
            this.formData.sname="采购单(客户自采).xlsx";
            if(this.selectedData.length==0){
              this.$Modal.warning({
                title: '提示',
                content: '请选择订单！'
              });
            }else{
              let oCodes=[];
              for(var i=0;i<this.selectedData.length;i++){
                oCodes.push(this.selectedData[i].oCode);
              }
              var params={
                "service":this.Api.VENUS_WMS_ORDER_NOTTHEIROWN_ORD_EXPORT,
                "data":{
                  "oCodes":oCodes
                }
              }
              this.$http.post(this.Api.VENUS_WMS_ORDER_NOTTHEIROWN_ORD_EXPORT,params).then(res =>{
                if(res.success){
                this.formData.fname=res.data;
                var I=this;
                setTimeout(function(){
                  I.$refs.submitForm.submit();
                },200)
              }
            })
            }
          },
	      	handleChange(count){
	    		count=count-1;
	    		this.queryTable(count);
	    	},
		},
		mounted(){
			this.queryTable();
			this.tableHeight = Number(window.innerHeight - 370);
      		this.contentHeight = Number(window.innerHeight - 170);
		},
		data(){
			return {
				contentHeight: 0,
        		tableHeight: 0,
				warehouse:warehouse,
				loading:true,
				show:false,
				pDate:[],
				selectedData:[],
				searchData:{
					startTime:'',
					endTime:'',
					oStatus:'0',
					warCode:'0',
					oPdate:'0'
				},
				pageData:{
					columns:[
						{
	                        type: 'selection',
	                        width: 60,
	                        align: 'center'
                    	},
						{
							title:'订单编号',
							key:'oCode'
						},
						{
							title:'创建时间',
							key:'oCtime'
						},
						{
							title:'送货日期',
							key:'oPdate',
              width: 80,
						},
						{
							title:'状态',
							key:'oStatus',
              width: 60,
							render: (h, params) => {
								let text='';
								if(params.row.oStatus==1){
									text='已创建'
								}else if(params.row.oStatus==2){
									text='已完成'
								}else if(params.row.oStatus==3){
									text="已取消"
								}else if(params.row.oStatus==4){
									text="检货中"
								}else if(params.row.oStatus==5){
                  text="处理中"
                }
								return h('div',text)
							}
						},
						{
							title:'内部销售金额',
							key:'oSprice',
              width: 100,
						},
						{
							title:'客户销售金额',
							key:'cltSprice',
              width: 100,
						},
						{
							title:'客户利润',
							key:'cltProfit',
              width: 100,
						},
						{
							title:'餐饮单位',
							key:'warName'
						},
						{
				            title: '操作',
				            key: 'action',
				            width: 250,
				            align: 'center',
				            render: (h, params) => {
				            	let arrBtn=[];
				            	if(params.row.oStatus==1){
				            		arrBtn=[
				            			h('Button', {
						                  props: {
						                    type: 'warning',
						                    size: 'small'
						                  },
						                  style: {
						                    marginRight: '5px'
						                  },
						                  on: {
						                    click: () => {
						                    	this.$router.push({
						                    		path: '/orderDetail',
							                        query: {
							                          oCode :params.row.oCode
							                        }
						                    	});

						                    }
						                  }
						                }, '修改'),
						                h('Button', {
						                  props: {
						                    type: 'success',
						                    size: 'small'
						                  },
						                  style: {
						                    marginRight: '5px'
						                  },
						                  on: {
						                    click: () => {
						                    	var oCode=params.row.oCode;
						                      	this.$Modal.confirm({
							                      	title:'提示',
							                      	content:'确定标记完成？',
							                      	onOk: () => {
	                            						this.updateStatus(oCode,'2');
							                      	}
						                      	})
						                    }
						                  }
						                }, '标记完成'),

						                 h('Button', {
						                  props: {
						                    type: 'error',
						                    size: 'small'
						                  },
                               style: {
                                 marginRight: '5px'
                               },
						                  on: {
						                    click: () => {
						                      	var oCode=params.row.oCode;
						                      	this.$Modal.confirm({
						                      		title:'提示',
						                      		content:'确定取消订单？',
						                      		onOk: () => {
														this.updateStatus(oCode,'3')
							                      	}
						                      	})
						                    }
						                  }
						                }, '取消订单')

						            ]
				            	}else if(params.row.oStatus==2 || params.row.oStatus==3 || params.row.oStatus==4 || params.row.oStatus==5){
				            		arrBtn=[
				            			h('Button', {
						                  props: {
						                    type: 'warning',
						                    size: 'small'
						                  },
						                  on: {
						                    click: () => {
						                      	this.$router.push({
						                    		path: '/orderDetail',
							                        query: {
							                          oCode :params.row.oCode
							                        }
						                    	});
						                    }
						                  }
						                }, '查看')
						            ]
				            	}
				              	return h('div', arrBtn);
				            }
				          }

					],
					data:[],
					totalCount:0,
					pageSize:0,
					pageCurrent:0
				},
				formData:{
					fname:'',
					tname:'002',
					sname:''
				}

			}

		}
	}
</script>
