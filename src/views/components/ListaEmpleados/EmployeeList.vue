<template id="EmployeeList">
	<NcAppContent v-if="loadingProp" name="Loading">
		<NcEmptyContent class="empty-content">
			<template #icon>
				<NcLoadingIcon :size="20" />
			</template>
		</NcEmptyContent>
	</NcAppContent>

	<NcAppContent v-else name="Loading">
		<!-- contacts list -->
		<template #list>
			<ContentList
				:employees="empleadosProp"
				:search-query="searchQuery" />
		</template>

		<!-- main contacts details -->
		<EmployeeDetails :data="data_empleado" :empleados-prop="empleadosProp" />
	</NcAppContent>
</template>

<script>
// agregados
import ContentList from './ContentList.vue'
import EmployeeDetails from './EmployeeDetails.vue'

import {
	NcEmptyContent,
	NcAppContent,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'EmployeeList',
	components: {
		NcEmptyContent,
		NcAppContent,
		NcLoadingIcon,
		ContentList,
		EmployeeDetails,
	},

	props: {
		empleadosProp: {
			type: Array,
			required: true,
		},
		loadingProp: {
			type: Boolean,
			required: true,
		},
	},

	data() {
		return {
			searchQuery: '',
			data_empleado: {},
		}
	},

	mounted() {
		this.$bus.on('send-data', (data) => {
			this.data_empleado = data
		})
	},
}
</script>
