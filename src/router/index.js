import Vue from 'vue'
import Router from 'vue-router'
import { generateUrl } from '@nextcloud/router'

import Employees from '../views/components/ListaEmpleados/Employees.vue'
import Calendario from '../views/components/TiempoLibre/TiempoLibre.vue'
import Equipos from '../views/components/Equipos/Equipos.vue'
import Puestos from '../views/components/puestos/Puestos.vue'
import Areas from '../views/components/areas/Areas.vue'
import Dashboard from '../views/components/Dashboard/Dashboard.vue'
Vue.use(Router)

export default new Router({
	mode: 'hash',
	linkActiveClass: 'active',
	// if index.php is in the url AND we got this far, then it's working:
	// let's keep using index.php in the url
	base: generateUrl('/apps/empleados', ''),
	routes: [
		{
			path: '/',
			component: Dashboard,
			name: 'Home',
		},
		{
			path: '/Empleados',
			component: Employees,
			name: 'Empleados',
		},
		{
			path: '/Puestos',
			component: Puestos,
			name: 'Puestos',
		},
		{
			path: '/Areas',
			component: Areas,
			name: 'Areas',
		},
		{
			path: '/Equipos',
			component: Equipos,
			name: 'Equipos',
		},
		{
			path: '/Calendario',
			component: Calendario,
			name: 'Calendario',
		},
	],
})
