import Vue from 'vue'
import App from './views/components/contacs/ContactList.vue'
Vue.mixin({ methods: { t, n } })
// Agregar OC y OCA a Vue para acceso global
Vue.prototype.OC = window.OC || {}
Vue.prototype.OCA = window.OCA || {}

const View = Vue.extend(App)
new View().$mount('#contactlist')
