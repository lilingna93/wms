// import Vue from 'vue'
import App from './App'
import router from './router'
// import iView from 'iview'
// import 'iview/dist/styles/iview.css'
import util from './util/util'
import http from './util/request'
import {Api} from './util/request'
// import axios from 'axios'
Vue.config.productionTip = false;
// Vue.use(iView);
Vue.prototype.$util = util;
Vue.prototype.$http = http;
Vue.prototype.Api = Api;
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
