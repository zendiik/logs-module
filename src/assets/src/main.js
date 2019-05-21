import Vue from 'vue'
import App from './App.vue'
import store from './store'
import axios from 'axios'
import VueAxios from 'vue-axios'

// import './plugins/bootstrap-vue'
import './plugins/font-awesome'

Vue.use(VueAxios, axios)
Vue.config.productionTip = false
Vue.mixin({
	methods: {
		log(value) {
			console.log(value)
		},
	}
})

new Vue({
    store,
    render: h => h(App)
}).$mount('#app')
