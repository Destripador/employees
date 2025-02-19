<template id="content">
	<EmployeeList
		:empleados-prop="Empleados"
		:loading-prop="loading" />
</template>

<script>
// Importing necessary components
import EmployeeList from './EmployeeList.vue'

import { showError /* showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'Employees',
	components: {
		EmployeeList,
	},

	data() {
		return {
			loading: true,
			Empleados: [],
		}
	},

	mounted() {
		this.getall()
		this.$bus.on('send-data', (data) => {
			this.data_empleado = data
		})
		this.$bus.on('getall', () => {
			this.getall()
		})
	},

	methods: {
		async getall() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetEmpleadosList'))
					.then(
						(response) => {
							this.Empleados = response.data.Empleados
							this.loading = false

							// eslint-disable-next-line no-console
							console.log('Empleados:', this.Empleados)
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},
	},
}
</script>

<style scoped lang="scss">
	.container {
		padding-left: 60px;
	}
	.board-title {
		padding-left: 60px;
		margin-right: 10px;
		margin-top: 14px;
		font-size: 25px;
		display: flex;
		align-items: center;
		font-weight: bold;
		.icon {
			margin-right: 8px;
		}
	}
</style>
