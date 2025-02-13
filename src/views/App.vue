<template id="content">
	<NcContent app-name="empleados">
		<navigator />
		<EmployeeList />
	</NcContent>
</template>

<script>
// Importing necessary components
import navigator from './navigator/Sidenavigation.vue'
import EmployeeList from './components/ListaEmpleados/EmployeeList.vue'
import { NcContent } from '@nextcloud/vue'

export default {
	name: 'App',
	components: {
		navigator,
		EmployeeList,
		NcContent,
	},
	provide() {
		return {
			configuraciones: this.configuraciones,
		}
	},

	props: {
		// Configuration parameters
		parameters: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			configuraciones: this.parameters,
		}
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
