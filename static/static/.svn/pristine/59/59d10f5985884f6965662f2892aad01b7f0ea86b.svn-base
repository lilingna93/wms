<template>
  <div style="float: right">
    <Dropdown class="userBox" slot='right' @on-click="logonOut">
      <Avatar icon="person"/>
      <a href="javascript:void(0)">
        {{username}}|{{realname}}|{{warname}}
        <Icon type="arrow-down-b"></Icon>
      </a>
      <Dropdown-menu slot="list" >
        <Dropdown-item>退出登录</Dropdown-item>
      </Dropdown-menu>
    </Dropdown>
  </div>
</template>

<script>
  export default {
    name: "t-header",
    methods: {
      logonOut () {
        let params = {
          service: this.Api.VENUS_WMS_AUTH_LOGOUT,
          data: {}
        }
        this.$http.post(this.Api.VENUS_WMS_AUTH_LOGOUT, params).then(res => {
          if(res.success){
          window.location.href="//"+config.host+"/manage/login";
        }
      })
      }
    },
    data(){
      return {
        username:config.user.name,
        realname:config.user.rname,
        warname:config.user.warname
      }
    }
  }
</script>

<style scoped>

</style>
