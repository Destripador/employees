<template id="EmployeeList">
	<NcAppContent v-if="loading" name="Loading">
		<NcEmptyContent class="empty-content" name="Cargando">
			<template #icon>
				<NcLoadingIcon :size="20" />
			</template>
		</NcEmptyContent>
	</NcAppContent>
	<NcAppContent v-else name="Loading">
		<!-- contacts list -->
		<template #list>
			<EquiposFullList
				:list="EquiposList"
				:contacts="Equipos"
				:search-query="searchQuery"
				:reload-bus="reloadBus" />
		</template>

		<!-- main contacts details -->
		<EquiposDetails :data="data_Equipos" :people-area="peopleArea" />
	</NcAppContent>
</template>

<script>
// agregados
import EquiposFullList from './EquiposFullList.vue'
import EquiposDetails from './perfil/EquiposDetails.vue'

import { showError /* showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import mitt from 'mitt'

import {
	NcEmptyContent,
	NcAppContent,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'EquiposList',
	components: {
		EquiposFullList,
		NcEmptyContent,
		NcAppContent,
		NcLoadingIcon,
		EquiposDetails,
		// ContactsList,
	},

	data() {
		return {
			loading: true,
			Equipos: [],
			searchQuery: '',
			reloadBus: mitt(),
			EquiposList: [],
			data_Equipos: {},
			peopleArea: {},
		}
	},

	async mounted() {
		this.getall()
		this.$root.$on('send-data-equipos', (data) => {
			this.data_Equipos = data
			this.getallequipo(data.Id_equipo)
		})
		this.$root.$on('delete-Equipos', (data) => {
			this.getall()
		})
		this.$root.$on('reload', () => {
			this.getall()
		})
	},

	methods: {
		async getallequipo(equipo) {
			try {
				await axios.get(generateUrl('/apps/empleados/GetEmpleadosEquipo/' + equipo))
					.then(
						(response) => {
							this.peopleArea = response.data
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		async getall() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetEquiposList'))
					.then(
						(response) => {
							this.Equipos = response.data
							this.loading = false
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
