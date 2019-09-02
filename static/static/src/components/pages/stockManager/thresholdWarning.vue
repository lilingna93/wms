<template>
  <Content :style="{padding: '0 16px 16px'}">
    <Breadcrumb :style="{margin: '16px 0'}">
      <BreadcrumbItem>库存货品管理</BreadcrumbItem>
      <BreadcrumbItem to="/stockManage">库存管理</BreadcrumbItem>
      <BreadcrumbItem>阈值报警</BreadcrumbItem>
    </Breadcrumb>
    <Card>
      <div :style="{minHeight:contentHeight+'px'}">
        <Form inline>
          <FormItem>
              <Button @click="downTemplate()" type="primary">下载模板</Button>
          </FormItem>
          <FormItem>
            <Upload
              :show-upload-list="false"
              :format="['xlsx']"
              :on-format-error="handleFormatError"
              :on-success="handleSuccess"
              :action="this.$http.baseUrl.host1"
              :data="{'service': this.Api.VENUS_WMS_STATUS_GWARNING_IMPORT}"
              style="float: left; margin-left: 10px;">
              <Button type="success">导入阈值</Button>
            </Upload>
          </FormItem>
        </Form>
        <Form inline>
          <FormItem>
            <div>当前收件人邮箱：<span v-for="(item,key) in email" :key="key">{{item}}，</span></div>
            <div>如需更改请联系管理员。</div>
          </FormItem>
        </Form>
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
  export default {
    name: "threshold-warning",
    data() {
      return {
        contentHeight: null,
        email:[],
        formData: {
          fname: '',
          tname: '001',
          sname: ''
        }
      }
    },
    methods: {
      downTemplate(){
        var _this=this;
        this.formData.sname='库存预警信息.xlsx';
        var params = {
          "service": this.Api.VENUS_WMS_STATUS_GWARNING_EXPORT,
          "data": {}
        }
        this.$http.post(this.Api.VENUS_WMS_STATUS_GWARNING_EXPORT, params).then(res => {
          if (res.success==true) {
            this.formData.fname=res.data;
            setTimeout(function(){
              _this.$refs.submitForm.submit();
            },200)
          }
        })
      },
      handleFormatError() {
        this.$Modal.warning({
          title: '提示',
          content: '文件格式不符，请重新选择.xlsx的文件上传！'
        });
      },
      handleSuccess(res, file) {
        if (res.error == 0) {
          if (res.success) {
            this.$Modal.success({
              title: '提示',
              content: res.message
            })
          } else {
            this.$Modal.warning({
              title: '提示',
              content: res.message
            })
          }
        } else {
          this.$Modal.warning({
            title: '提示',
            content: res.msg
          })
        }

      },
      /*submitEmail(){
        var reg=new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
        if(!reg.test(this.email)){
          Vue.prototype.$Message.warning({
            content: '邮箱地址格式不正确，请重新输入！',
            duration: 3,
            closable: true
          });
        }else{
          
        }
      }*/
    },
    mounted() {
      this.contentHeight = Number(window.innerHeight - 164);
      var params = {
          "service": this.Api.VENUS_WMS_STATUS_GWARNING_EMAIL,
          "data": {}
        }
        this.$http.post(this.Api.VENUS_WMS_STATUS_GWARNING_EMAIL, params).then(res => {
          if (res.success==true) {
           this.email=res.data.email;
          }
        })
    },

  }
</script>

<style>
</style>
