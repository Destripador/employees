<!-- eslint-disable object-curly-newline -->
<template>
	<div class="contacts-list__item-wrapper">
		<!-- Display empty content if no data is present -->
		<div v-if="Object.keys(data).length === 0">
			<div class="emptycontent">
				<img src="../../../../img/crowesito-think.png" width="170px">
				<h2>Selecciona un empleado para empezar</h2>
			</div>
		</div>
		<!-- Display contact details if data is present -->
		<div v-else class="container">
			<div>
				<div class="container-search-profile">
					<div class="button-container-profile">
						<NcActions>
							<template #icon>
								<AccountCog :size="20" />
							</template>
							<NcActionButton :close-after-click="true" @click="showEdit">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								{{ hability }} edicion
							</NcActionButton>
							<NcActionSeparator />
							<NcActionButton :close-after-click="true" :disabled="true" @click="showEdit">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								Exportar
							</NcActionButton>
							<NcActionButton :close-after-click="true" :disabled="true" @click="showEdit">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								Enviar correo
							</NcActionButton>
							<NcActionButton @click="DeactiveUserDialog(data.Id_empleados)">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								Deshabilitar a {{ data.Id_empleados }}
							</NcActionButton>
						</NcActions>
					</div>
				</div>
			</div>
			<div class="center">
				<NcAvatar :user="data.uid" :display-name="data.uid" :size="120" />
				<div v-if="data.displayname">
					<h2>{{ data.displayname }}</h2>
				</div>
				<div v-else>
					<h2>{{ data.uid }}</h2>
				</div>
				<div>
					<VueTabs active-tab-color="#fdb913c"
						active-text-color="white"
						type="pills"
						centered>
						<VTab title="Empleado">
							<EmpleadoTab :data="data"
								:show="show"
								:empleados="Empleados"
								:automaticsave="automatic_save_note" />
						</VTab>
						<VTab title="Personal">
							<PersonalTab :data="data"
								:show="show"
								:empleados="Empleados" />
						</VTab>
						<VTab title="Archivos">
							<FilesTab :data="data"
								:show="show"
								:empleados="Empleados" />
						</VTab>
					</VueTabs>
				</div>
			</div>
		</div>
		<!-- Diálogo de confirmación para desactivar usuario -->
		<NcDialog :open.sync="showDeactiveUserDialog"
			name="Confirmacion"
			message="Esta seguro que desea deshabilitar la cuenta?"
			:buttons="buttons" />
	</div>
</template>

<script>
import EmpleadoTab from './Tabs/EmpleadoTab.vue'
import PersonalTab from './Tabs/PersonalTab.vue'
import FilesTab from './Tabs/FilesTab.vue'
import { VueTabs, VTab } from 'vue-nav-tabs/dist/vue-tabs.js'
import 'vue-nav-tabs/themes/vue-tabs.css'

// ICONS
import AccountEdit from 'vue-material-design-icons/AccountEdit.vue'
import AccountCog from 'vue-material-design-icons/AccountCog.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'

import {
	NcAvatar,
	NcActions,
	NcActionButton,
	NcActionSeparator,
	NcDialog,
} from '@nextcloud/vue'

export default {
	name: 'EmployeeDetails',

	components: {
		EmpleadoTab,
		PersonalTab,
		NcAvatar,
		VueTabs,
		VTab,
		NcActionSeparator,
		NcActions,
		AccountCog,
		AccountEdit,
		NcActionButton,
		FilesTab,
		NcDialog,
	},

	inject: ['configuraciones'],

	props: {
		data: {
			type: Object,
			required: true,
		},

	},

	data() {
		return {
			automatic_save_note: this.configuraciones.automatic_save_note,
			show: false,
			Empleados: [],
			hability: 'Habilitar',
			showDeactiveUserDialog: false,
			SelectedEmpleado: null,
			buttons: [
				{
					label: 'Ok',
					type: 'primary',
					callback: () => { this.DeactiveUser() },
				},
			],
		}
	},

	mounted() {
		this.$root.$on('show', (data) => {
			this.show = data
		})
	},

	methods: {
		showEdit() {
			this.show = !this.show
			this.hability = this.show ? 'Deshabilitar' : 'Habilitar'
			if (this.show) {
				this.$bus.emit('getall')
			}
		},

		// Mostrar diálogo para desactivar usuario
		DeactiveUserDialog(IdEmpleado) {
			this.showDeactiveUserDialog = true
			this.SelectedEmpleado = IdEmpleado
		},

		// Desactivar usuario activo
		async DeactiveUser() {
			try {
				await axios.post(generateUrl('/apps/empleados/DesactivarEmpleado'),
					{
						id_empleados: this.SelectedEmpleado,
					})
					.then(
						(response) => {
							this.$bus.emit('getall')
							this.$bus.emit('send-data', {})
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
.envelope {
	.app-content-list-item-icon {
		height: 40px; // To prevent some unexpected spacing below the avatar
	}

	&__subtitle {
		display: flex;
		gap: 4px;

		&__subject {
			color: var(--color-main-text);
			line-height: 130%;
			overflow: hidden;
			text-overflow: ellipsis;
		}
	}
}

.list-item-style {
	list-style: none;
}

.contacts-list__item-wrapper {
	&[draggable='true'] .avatardiv * {
		cursor: move !important;
	}

	&[draggable='false'] .avatardiv * {
		cursor: not-allowed !important;
	}
}

#emptycontent, .emptycontent {
	margin-top: 2vh;
}

.container {
	padding: 20px 15px 0px 6px;
	align-items: center;
}

.container-progress {
	margin-right: 30%;
	margin-left: 30%;
	margin-top: 20px;
	align-items: center;
}

.wrapper {
	display: flex;
	gap: 4px;
	align-items: flex-end;
	flex-wrap: wrap;
	margin-right: 5%;
	margin-left: 5%;
}

.contacts-list {
	max-height: calc(100vh - var(--header-height) - 48px);
	overflow: auto;
}

.contacts-list__header {
	min-height: 48px;
}

.margin-left-icon {
	margin-right: 20px;
}

.button-container-profile {
	float: right;
	margin-top: -10px;
}
</style>
