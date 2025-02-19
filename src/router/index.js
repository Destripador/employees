import Vue from 'vue'
import Router from 'vue-router'
import { generateUrl } from '@nextcloud/router'

import Employees from '../views/components/ListaEmpleados/Employees.vue'

Vue.use(Router)

export default new Router({
	mode: 'history',
	// if index.php is in the url AND we got this far, then it's working:
	// let's keep using index.php in the url
	base: generateUrl('/apps/empleados', ''),
	routes: [
		{
			path: '/',
			component: Employees,
			name: 'Empleados',
		},
		/*
		{
			path: '/puestos',
			name: 'puestos',
		},
		{
			path: '/areas',
			name: 'areas',
		},
		*/
	],
})
