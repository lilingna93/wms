<style scoped>
    .layout{
        border: 1px solid #d7dde4;
        background: #f5f7f9;
        position: relative;
        border-radius: 4px;
        overflow: hidden;
    }
    .layout-header-bar{
        background: #fff;
        box-shadow: 0 1px 1px rgba(0,0,0,.1);
    }
    .layout-logo{
        width: 120px;
        height: 30px;
        background: #5b6270;
        border-radius: 3px;
        margin-top: 15px;
        margin-left: 20px;
        margin-bottom: 10px;
        color: #fff;
        line-height: 30px;
        text-align: center;
    }

</style>
<template>
    <div class="layout">
        <Sider :style="{position: 'fixed', height: '100vh', left: 0, overflow: 'auto'}">
           <navBar></navBar>
        </Sider>
        <Layout :style="{marginLeft: '200px'}">
           <Header :style="{background: '#fff', boxShadow: '0 2px 3px 2px rgba(0,0,0,.1)',textAlign:'right'}">
                <t-header></t-header>
           </Header>
          <!--<keep-alive include="purchase-order">-->
            <router-view></router-view>
          <!--</keep-alive>-->
        </Layout>
    </div>
</template>

<script>
    import tHeader from '@/components/common/tHeader'
    import navBar from '@/components/common/navBar'
    export default {
        name: 'index',
        data () {
          return {
            show:true,
            loaded:''
          }
        },
        components: {
          tHeader,
          navBar
        },
        methods: {

        }
    }

    // export default{
    //     data(){
    //         return {
    //             activeName:'1-1',
    //             openName:'1'
    //         }
    //     },
    //     methods:{
    //         routeTo(url){
    //             this.$router.push(url);
    //         },
    //         onSelect(e){
    //             this.activeName=e;
    //             this.openName=e.split('-')[0];
    //             this.$nextTick(function() {
    //                 this.$refs.leftMenu.updateOpened();
    //                 this.$refs.leftMenu.updateActiveName();
    //             })
    //         },

    //     },
    //     mounted:function(){
    //         this.$router.push('/');
    //     }
    // }

</script>
