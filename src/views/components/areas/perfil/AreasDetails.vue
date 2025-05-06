<!-- eslint-disable object-curly-newline -->
<template>
	<div class="contacts-list__item-wrapper">
		<div v-if="Object.keys( data ).length == 0">
			<div class="emptycontent">
				<img src="../../../../../img/crowesito-think.png" width="170px">
				<h2>Selecciona un area para mas detalles</h2>
			</div>
		</div>
		<div v-else>
			<div>
				<div class="container-search-profile">
					<div class="button-container-profile">
						<NcActions>
							<template #icon>
								<AccountCog :size="20" />
							</template>
							<NcActionButton
								:close-after-click="true"
								@click="showEdit()">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								Habilitar edicion
							</NcActionButton>
							<NcActionButton
								:close-after-click="true"
								@click="ChangeView()">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								Cambiar tipo de vista
							</NcActionButton>
							<NcActionSeparator />
							<NcActionButton
								:close-after-click="true"
								@click="showDialog = true">
								<template #icon>
									<DeleteAlert :size="20" />
								</template>
								Eliminar departamento
							</NcActionButton>
							<NcDialog :open.sync="showDialog"
								name="Confirmar"
								:message="'Desea eliminar ' + data.Nombre + '?'"
								:buttons="buttons" />
						</NcActions>
					</div>
				</div>
			</div>
			<div class="center">
				<div v-if="!show">
					<div>
						<h2>{{ data.Nombre }}</h2>
					</div>
					<div v-if="data.Id_padre">
						<h1>{{ data.Id_padre }}</h1>
					</div>
				</div>
				<div v-else>
					<div class="wrapper">
						<NcTextField
							:value.sync="area"
							:v-model="area"
							label="Nombre del area/departamento" />
					</div>
					<div>
						<NcSelect v-model="padre"
							input-label="Area Padre"
							:options="options" />
					</div>
					<br>
					<div>
						<NcButton
							class="center"
							aria-label="Guardar cambios"
							type="primary"
							@click="guardarcambioarea()">
							Guardar cambios
						</NcButton>
					</div>
				</div>
				<div class="rsg-title">
					<h3>Empleados en departamento</h3>
				</div>
				<div>
					<div v-if="preferencias_areas"
						class="rsg">
						<ul class="container flex">
							<li v-for="(item) in peopleArea.area"
								:key="item.Id_empleados"
								class="flex-item">
								<div class="card">
									<div>
										<NcAvatar :user="item.Id_user" :display-name="item.Id_user" size="60" />
									</div>
									<div class="right">
										<div class="card-1">
											{{ item.displayname ? item.displayname : item.Id_user }}
										</div>
										<div class="card-2">
											{{ item.Id_user }}
										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div v-else
						class="rsgd">
						<ul class="">
							<NcListItem v-for="(item) in peopleArea.area"
								:key="item.Id_empleados"
								bold
								:name="item.displayname ? item.displayname : item.Id_user">
								<template #icon>
									<NcAvatar
										:size="44"
										:user="item.Id_user"
										:display-name="item.displayname ? item.displayname : item.Id_user" />
								</template>
								<template v-if="!item.displayname" #subname>
									{{ item.Id_user }}
								</template>
							</NcListItem>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// ICONOS
import DeleteAlert from 'vue-material-design-icons/DeleteAlert.vue'
import AccountEdit from 'vue-material-design-icons/AccountEdit.vue'
import AccountCog from 'vue-material-design-icons/AccountCog.vue'
// import Cancel from 'vue-material-design-icons/Cancel.vue'
// import Check from 'vue-material-design-icons/Check.vue'
// import Cog from 'vue-material-design-icons/Cog.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'

import {
	NcAvatar,
	NcActions,
	NcActionButton,
	NcActionSeparator,
	NcDialog,
	NcTextField,
	NcSelect,
	NcButton,
	NcListItem,
	// NcProgressBar,
} from '@nextcloud/vue'

export default {
	name: 'AreasDetails',

	components: {
		NcAvatar,
		NcActionSeparator,
		NcActions,
		AccountCog,
		AccountEdit,
		NcActionButton,
		DeleteAlert,
		NcDialog,
		NcTextField,
		NcSelect,
		NcButton,
		NcListItem,
	},

	props: {
		data: {
			type: Object,
			required: true,
		},
		peopleArea: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			show: false,
			options: [],
			Empleados: [],
			showDialog: false,
			buttons: [
				{
					label: 'Cancelar',
					callback: () => { this.lastResponse = 'Pressed "Cancel"' },
				},
				{
					label: 'Eliminar',
					type: 'primary',
					callback: () => { this.eliminarDepartamento(this.data.Id_departamento) },
				},
			],
			area: '',
			padre: '',
			preferencias_areas: null,
		}
	},
	mounted() {
		this.$root.$on('show', (data) => {
			this.show = data
		})
		this.preferencias_areas = localStorage.getItem('nextcloud_empleados_preferencias_areas')
		if (this.preferencias_areas === null) {
			// No existía, la inicializamos
			localStorage.setItem('nextcloud_empleados_preferencias_areas', 'false')
			this.preferencias_areas = false
		} else {
			// Convertimos el string a boolean
			this.preferencias_areas = this.preferencias_areas === 'true'
		}
	},

	methods: {
		showEdit() {
			this.show = !this.show
			if (this.show === true) {
				// eslint-disable-next-line no-console
				this.getall()
				this.padre = this.data.Id_padre
				this.area = this.data.Nombre
			}
		},
		async eliminarDepartamento(departamento) {
			this.showDialog = false
			try {
				await axios.post(generateUrl('/apps/empleados/EliminarArea'),
					{
						id_departamento: departamento,
					})
					.then(
						(response) => {
							showSuccess('Area eliminada exitosamente')
							this.$root.$emit('reload')
							this.$root.$emit('send-data-areas', {})
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},

		ChangeView() {
			this.preferencias_areas = !this.preferencias_areas
			localStorage.setItem('nextcloud_empleados_preferencias_areas', this.preferencias_areas)
		},

		checknull(satanizar) {
			if (satanizar == null) {
				return ''
			}

			return satanizar
		},

		async getall() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetAreasFix'))
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

		async guardarcambioarea() {
			if (this.padre == null) {
				this.padre = ''
			} else if (this.padre.label) {
				this.padre = this.padre.label
			}

			try {
				await axios.post(generateUrl('/apps/empleados/GuardarCambioArea'),
					{
						id_departamento: this.data.Id_departamento,
						padre: this.padre,
						nombre: this.area,
					})
					.then(
						(response) => {
							showSuccess('Area eliminada exitosamente')
							this.$root.$emit('reload')
							this.$root.$emit('send-data-areas', {})
							this.showEdit()
						},
						(err) => {
							this.showEdit()
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
<style>
.button-container-profile {
  float: right;
  margin-top: -10px;
}
.container {
  margin-right: 10px;
  margin-left: 10px;
  margin-top: 20px;
  align-items: center;
}
.rsgd {
	padding-top: 16px;
	padding-bottom: 16px;
	border: 1px solid rgb(232, 232, 232);
	margin-left: 20px;
	margin-right: 20px;
}
.rsg-title {
	background-color: rgba(240, 240, 240, 0.37);
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
	margin-left: 20px;
	margin-right: 20px;
	width: auto;
	margin-top: 20px;
}

.item {
  box-shadow: rgba(0, 41, 0, 0.15) 0px 0px 11px 1px;
  width: 100px;
  margin: 10px;
  border-radius: 15px;
}

/*float layout*/
.float {
  max-width: 1200px;
  margin: 0 auto;
}
.float:after {
  content: ".";
  display: block;
  height: 0;
  clear: both;
  visibility: hidden;
}
.float-item {
  float: left;
}

/*inline-block*/
.inline-b {
  max-width:1200px;
  margin:0 auto;
}
.inline-b-item {
  display: inline-block;
}

/*Flexbox*/
.flex {
  padding: 0;
  margin: 0;
  list-style: none;

  display: -webkit-box;
  display: -moz-box;
  display: -ms-flexbox;
  display: -webkit-flex;
  display: flex;

  -webkit-flex-flow: row wrap;
  flex-flow: row wrap;
  justify-content: space-around;
}

h2 {
  font-size: 28px;
  margin-bottom: auto;
}

.wrapper {
	display: flex;
	gap: 4px;
	align-items: flex-end;
	flex-wrap: wrap;
}

.external-label {
	display: flex;
	width: 100%;
	margin-top: 1rem;
}

.external-label label {
	padding-top: 7px;
	padding-right: 14px;
	white-space: nowrap;
}
.grid {
	display: grid;
	grid-template-columns: repeat(1, 500px);
	gap: 10px;
}

.card {
  --gray: rgba(229, 231, 235, 1);
  width: 350px;
  display: flex;
  grid-gap: 1.25rem;
  gap: 1.25rem;
  border-radius: 1rem;
  background-color: rgba(255, 255, 255, 1);
  padding: 1.5rem;
  box-shadow: rgba(0, 41, 0, 0.15) 0px 0px 11px 1px;
}

.card-1 {
  font-size: 14px;
  font-weight: bold;
}

.right {
  display: flex;
  flex: 1 1 0%;
  flex-direction: column;
  grid-gap: 1.25rem;
}

.card-2 {
font-size: 14px;
}

@keyframes pulse {
  to {
    opacity: .2;
  }
}
</style>
