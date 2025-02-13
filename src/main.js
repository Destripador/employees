import Vue from 'vue'
import App from './views/App.vue'

import router from './router/index.js'
import Router from 'vue-router'

Vue.use(Router)
Vue.mixin({ methods: { t, n } })

// Agregar OC y OCA a Vue para acceso global
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

// Obtener configuraciones del módulo desde el DOM
const dataElement = document.getElementById('data')
const configuraciones = dataElement ? JSON.parse(dataElement.getAttribute('data-parameters') || '{}') : {}

// Crear la vista
const View = Vue.extend(App)

new View({
	router,
	propsData: {
		parameters: configuraciones,
	},
}).$mount('#content')
