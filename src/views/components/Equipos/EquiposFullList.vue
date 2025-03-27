<template>
	<AppContentList class="content-list">
		<div class="contacts-list__header">
			<div class="search-contacts-field">
				<div class="container-search">
					<div class="input-container">
						<input v-model="query" type="text" :placeholder="t('empleados', 'Buscar empleados...')">
					</div>
					<div class="button-container">
						<NcActions
							:open="button"
							@click="toggle">
							<template #icon>
								<Cog :size="20" />
							</template>
							<NcActionButton @click="AgregarNuevo()">
								<template #icon>
									<AccountMultiplePlusOutline :size="20" />
								</template>
								Agregar area nueva
							</NcActionButton>
							<NcActionButton @click="Exportar()">
								<template #icon>
									<DatabaseExport :size="20" />
								</template>
								Exportar listado
							</NcActionButton>
							<NcActionSeparator />
							<!--NcActionButton @click="showMessage('Delete')">
								<template #icon>
									<Download :size="20" />
								</template>
								Exportar plantilla vacia
							</NcActionButton-->
							<NcActionButton @click="$refs.file.click()">
								<template #icon>
									<Upload :size="20" />
								</template>
								Importar datos desde plantilla
							</NcActionButton>
						</NcActions>
					</div>
				</div>
			</div>
		</div>
		<VirtualList ref="scroller"
			class="contacts-list"
			data-key="Id_equipo"
			:data-sources="filteredList"
			:data-component="EquiposListItem"
			:estimate-size="60"
			:extra-props="{reloadBus}" />
		<input
			ref="file"
			type="file"
			style="display: none"
			accept=".xlsx"
			@change="importar()">
		<NcModal
			v-if="modal"
			ref="modalRef"
			name="Agregar nuevo equipo"
			@close="closeModal">
			<div class="modal__content">
				<div class="form-group center">
					<NcTextField :value.sync="nombre_area"
						label="Nombre del area" />
					<br>
					<NcSelect
						v-model="selected_user"
						input-label="jefe de grupo"
						:options="optionsGestor"
						:user-select="true" />
					<br>
					<NcButton
						class="center"
						aria-label="Guardar cambios"
						type="primary"
						@click="crearEquipo()">
						Guardar cambios
					</NcButton>
				</div>
			</div>
		</NcModal>
	</AppContentList>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

// Iconos
import DatabaseExport from 'vue-material-design-icons/DatabaseExport.vue'
import AccountMultiplePlusOutline from 'vue-material-design-icons/AccountMultiplePlusOutline.vue'
// import Download from 'vue-material-design-icons/Download.vue'
import Upload from 'vue-material-design-icons/Upload.vue'
import Cog from 'vue-material-design-icons/Cog.vue'

import {
	NcAppContentList as AppContentList,
	NcActions,
	NcActionButton,
	NcModal,
	NcTextField,
	NcButton,
	NcSelect,
} from '@nextcloud/vue'
import EquiposListItem from './EquiposListItem.vue'
import VirtualList from 'vue-virtual-scroll-list'

export default {
	name: 'EquiposFullList',

	components: {
		AppContentList,
		VirtualList,
		NcActions,
		NcActionButton,
		Cog,
		Upload,
		DatabaseExport,
		AccountMultiplePlusOutline,
		NcModal,
		NcTextField,
		NcButton,
		NcSelect,
	},

	props: {
		list: {
			type: Array,
			required: true,
		},
		contacts: {
			type: Array,
			required: true,
		},
		searchQuery: {
			type: String,
			default: '',
		},
		reloadBus: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			EquiposListItem,
			query: '',
			modal: false,
			button: false,
			options: [],
			nombre_area: '',
			optionsGestor: [], // Se llena con response.data.Users
			selected_user: null, // Gestor de datos seleccionado
		}
	},

	computed: {
		filteredList() {
			return this.contacts
				.filter(item => this.matchSearch(item.Nombre))
		},
	},

	watch: {
		modal(news, olds) {
			if (olds !== news) {
				this.getallsEquipos()
			}
		},
	},

	mounted() {
		this.query = this.searchQuery
	},

	methods: {
		matchSearch(Equipos) {
			if (this.query.trim() !== '') {
				return Equipos.toString().toLowerCase().search(this.query.trim().toLowerCase()) !== -1
			}
			return true
		},

		async getallsEquipos() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetEquiposFix'))
					.then(
						(response) => {
							this.options = response.data
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		Exportar() {
			this.toggle()
			axios.get(
				generateUrl('/apps/empleados/ExportListEquipos'),
				{
					responseType: 'blob',
				},
			).then(
				(response) => {
					const url = URL.createObjectURL(new Blob([response.data], {
						type: 'application/vnd.ms-excel',
					}))

					const link = document.createElement('a')
					link.href = url
					link.setAttribute('download', 'historial.xlsx')
					document.body.appendChild(link)
					link.click()
				},
				(err) => {
					showError(t('ahorrosgossler', 'Se ha producido un error ' + err + ', reporte al administrador'))
					this.exportardata = false
				},
			)
		},
		async importar() {
			this.toggle()
			const formData = new FormData()
			formData.append('equipofileXLSX', this.$refs.file.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/ImportListEquipos'), formData,
					{
						headers: {
							'Content-Type': 'multipart/form-data',
						},
					})
					.then(
						(response) => {
							this.$root.$emit('getall')
							this.$root.$emit('reload')
							this.$root.$emit('send-data-equipos', {})
							showSuccess(t('empleados', 'Se actualizo la base de datos exitosamente'))
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		async AgregarNuevo() {
			this.toggle()

			try {
				const response = await axios.get(generateUrl('/apps/empleados/GetConfigurations'))
				// Lista de usuarios disponibles
				this.optionsGestor = response.data.Users

			} catch (err) {
				this.loading = false
				showError('Se ha producido una excepción [GetConfigurations]: ' + err)
				console.error(err)
			}

			this.modal = true
		},
		closeModal() {
			this.modal = false
		},
		toggle() {
			this.button = !this.button
		},
		async crearEquipo() {
			try {
				await axios.post(generateUrl('/apps/empleados/crearEquipo'),
					{
						nombre: this.nombre_area,
						jefe: this.selected_user.id,
					})
					.then(
						(response) => {
							showSuccess('Area creada exitosamente')
							this.$root.$emit('reload')
							this.nombre_area = ''
							this.modal = false
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
	},
}
</script>

<style lang="scss" scoped>
// Make virtual scroller scrollable
.contacts-list {
	max-height: calc(100vh - var(--header-height) - 48px);
	overflow: auto;
}

// Add empty header to contacts-list that solves overlapping of contacts with app-navigation-toogle
.contacts-list__header {
	min-height: 48px;
}

// Search field
.search-contacts-field {
	padding: 5px 10px 5px 50px;
	margin-top: 4px;

	> input {
		width: 100%;
	}
}

.content-list {
	overflow-y: auto;
	padding: 0 4px;
}

.container-search {
    display: flex;
}
.input-container {
    flex: 1;
    margin-right: 5px;
}
.input-container input {
    width: 100%;
}
.button-container button {
    width: 100%;
}

.modal__content {
	margin: 50px;
}

.modal__content h2 {
	text-align: center;
}

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
