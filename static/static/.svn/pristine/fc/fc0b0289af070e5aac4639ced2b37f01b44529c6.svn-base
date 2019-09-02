<template>
  <div>
    <div class="logo">{{appname}}</div>
    <Menu width="auto" theme="dark" :accordion="true" @on-select="selectFn" :active-name="$route.path">
      <div v-for="(item,index) in menu " :key="index">
        <Submenu :name="index" v-if="item.children && item.children.length>0 && (item.permissions&auth)>0">
          <template slot="title">
            {{item.name}}
          </template>
          <Menu-item :name="sub.path" v-for="(sub,i) in item.children" :key="i" v-if="(sub.permissions&auth)>0">
            {{sub.name}}
          </Menu-item>
        </Submenu>
        <div>
          <Menu-item :name="item.path" :key="index"
                     v-if="item.children.length==0">
            {{item.name}}
          </Menu-item>
        </div>
      </div>
    </Menu>
  </div>
</template>

<script>
  import menu from '@/router/menu'
  export default {
    name: "nav-bar",
    data() {
      return {
        menu: menu, // 导航菜单
        auth:auth,
        appname:config.appname
      }
    },
    methods: {
      selectFn (path) {
        this.$router.push({
          path: path,
        })
      }
    },
    created() {
    },
    mounted() {
    }
  }
</script>

<style scoped>
  .logo{
    width: 120px;
    height: 30px;
    background: #5b6270;
    border-radius: 3px;
    margin: 18px 0 18px 36px;
    color: #fff;
    line-height: 30px;
    text-align: center;
  }
</style>
