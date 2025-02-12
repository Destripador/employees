<!-- eslint-disable vue/require-v-for-key -->
<template>
	<div>
		<!-- Mostrar icono de carga mientras se cargan los datos -->
		<div v-if="loading">
			<div class="center-screen" style="background-color: #fff;">
				<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
			</div>
		</div>
		<!-- Mostrar contenido principal cuando los datos están cargados -->
		<div v-else id="admin">
			<div>
				<h2 class="board-title">
					<AccountGroup :size="20" decorative class="icon" />
					<span>Empleados</span>
				</h2>
			</div>

			<!-- Pestañas para diferentes categorías de empleados -->
			<VueTabs>
				<!-- Pestaña de empleados activos -->
				<VTab title="Empleados activos">
					<div v-if="Empleados.length > 0" class="container">
						<table class="grid">
							<tr>
								<th class="header__cell header__cell--avatar">
									&nbsp;
								</th>
								<th>{{ t('empleados', 'Nombre') }}</th>
								<th>{{ t('empleados', 'Opciones') }}</th>
							</tr>
							<!-- Listar empleados activos -->
							<tr v-for="(item, index) in Empleados" v-bind="$attrs">
								<td class="row__cell row__cell--avatar">
									<NcAvatar :user="item.uid"
										:display-name="item.displayname"
										:show-user-status-compact="false"
										:show-user-status="false" />
								</td>
								<td v-if="item.displayname">
									{{ item.displayname }}
								</td>
								<td v-else>
									{{ item.uid }}
								</td>
								<td>
									<NcActions>
										<NcActionButton @click="DeactiveUserDialog(index, item.displayname)">
											<template #icon>
												<AccountOff :size="20" />
											</template>
											Deshabilitar cuenta
										</NcActionButton>
									</NcActions>
								</td>
							</tr>
						</table>
					</div>
					<!-- Mostrar mensaje si no hay empleados activos -->
					<div v-else class="container">
						<br>
						<NcEmptyContent name="Aun no existen usuarios">
							<template #icon>
								<AccountOff :size="20" />
							</template>
						</NcEmptyContent>
					</div>
				</VTab>

				<!-- Pestaña de empleados desactivados -->
				<VTab title="Empleados desactivados">
					<div v-if="Desactivados.length > 0" class="container">
						<table class="grid">
							<tr>
								<th class="header__cell header__cell--avatar">
								&nbsp;
								</th>
								<th>{{ t('empleados', 'Nombre') }}</th>
								<th>{{ t('empleados', 'Opciones') }}</th>
							</tr>
							<!-- Listar empleados desactivados -->
							<tr v-for="(item, index) in Desactivados" v-bind="$attrs">
								<td class="row__cell row__cell--avatar">
									<NcAvatar :user="item.uid"
										:display-name="item.displayname"
										:show-user-status-compact="false"
										:show-user-status="false" />
								</td>
								<td v-if="item.displayname">
									{{ item.displayname }}
								</td>
								<td v-else>
									{{ item.uid }}
								</td>
								<td>
									<NcActions>
										<NcActionButton close-after-click @click="ActivarUsuario(index)">
											<template #icon>
												<AccountPlus :size="20" />
											</template>
											Activar
										</NcActionButton>
										<NcActionButton close-after-click @click="EliminarUserDialog(index)">
											<template #icon>
												<Delete :size="20" />
											</template>
											Eliminar
										</NcActionButton>
									</NcActions>
								</td>
							</tr>
						</table>
					</div>
					<!-- Mostrar mensaje si no hay empleados desactivados -->
					<div v-else class="container">
						<br>
						<NcEmptyContent name="Aun no existen usuarios">
							<template #icon>
								<AccountOff :size="20" />
							</template>
						</NcEmptyContent>
					</div>
				</VTab>

				<!-- Pestaña de empleados sin cuenta -->
				<VTab title="Empleados sin cuenta">
					<div v-if="Usuarios.length > 0" class="container">
						<table class="grid">
							<tr>
								<th class="header__cell header__cell--avatar">
&nbsp;
								</th>
								<th>{{ t('empleados', 'Nombre') }}</th>
								<th>{{ t('empleados', 'Opciones') }}</th>
							</tr>
							<!-- Listar empleados sin cuenta -->
							<tr v-for="(item, index) in Usuarios" v-bind="$attrs">
								<td class="row__cell row__cell--avatar">
									<NcAvatar :user="item.uid"
										:display-name="item.displayname"
										:show-user-status-compact="false"
										:show-user-status="false" />
								</td>
								<td>{{ JSON.parse(item.data).displayname.value }}</td>
								<td>
									<NcActions>
										<NcActionButton @click="ActivarUser(index)">
											<template #icon>
												<Plus :size="20" />
											</template>
											Activar
										</NcActionButton>
									</NcActions>
								</td>
							</tr>
						</table>
					</div>
				</VTab>
			</VueTabs>
		</div>
		<!-- Diálogo de confirmación para desactivar usuario -->
		<NcDialog :open.sync="showDeactiveUserDialog"
			name="Confirmacion"
			:message="'Esta seguro que desea deshabilitar la cuenta de \n' + selected.name + '?'"
			:buttons="buttons" />
		<!-- Diálogo de confirmación para eliminar usuario -->
		<NcDialog :open.sync="showEliminarUserDialog"
			name="¿Esta seguro que desea eliminar?"
			message="Esta accion eliminara toda la informacion del empleado"
			:buttons="ButtonsEliminarUser" />
	</div>
</template>

<script>
// Importar iconos
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import AccountOff from 'vue-material-design-icons/AccountOff.vue'
import AccountPlus from 'vue-material-design-icons/AccountPlus.vue'

// Importar componentes y utilidades
import { NcActions, NcActionButton, NcLoadingIcon, NcAvatar, NcDialog, NcEmptyContent } from '@nextcloud/vue'
import { showError } from '@nextcloud/dialogs'
import { VueTabs, VTab } from 'vue-nav-tabs/dist/vue-tabs.js'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'EmpleadosSettings',
	components: {
		NcAvatar,
		NcActions,
		NcActionButton,
		NcLoadingIcon,
		AccountGroup,
		Delete,
		Plus,
		VueTabs,
		VTab,
		AccountOff,
		NcDialog,
		AccountPlus,
		NcEmptyContent,
	},

	data() {
		return {
			showDeactiveUserDialog: false,
			showEliminarUserDialog: false,
			selected: [],
			loading: true,
			Empleados: [],
			Usuarios: [],
			Desactivados: [],
			map: {},
			ButtonsEliminarUser: [
				{
					label: 'Ok',
					type: 'primary',
					callback: () => { this.EliminarUser(this.selected.index) },
				},
			],
			buttons: [
				{
					label: 'Ok',
					type: 'primary',
					callback: () => { this.DeactiveUser(this.selected.index) },
				},
			],
		}
	},

	async mounted() {
		this.getall()
	},

	methods: {
		// Obtener todas las listas de usuarios
		async getall() {
			// this.loading = true
			try {
				await axios.get(generateUrl('/apps/empleados/GetUserLists'))
					.then(
						(response) => {
							// Inicializar listas vacías
							this.Usuarios = []
							this.Empleados = response.data.Empleados
							this.Desactivados = response.data.Desactivados

							// Crear un mapa de empleados y desactivados para rápida eliminación
							this.map = {}

							// Marcar empleados activos en el mapa
							response.data.Empleados.forEach(empleado => {
								this.map[empleado.Id_user] = true
							})

							// Marcar empleados desactivados en el mapa
							response.data.Desactivados.forEach(empleado => {
								this.map[empleado.Id_user] = true
							})

							// Filtrar `Usuarios`, eliminando los que están en `Empleados` y `Desactivados`
							this.Usuarios = response.data.Users.filter(user => !this.map[user.uid])

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

		// Mostrar diálogo para desactivar usuario
		DeactiveUserDialog(index, name) {
			this.selected.index = index
			this.selected.name = name
			this.showDeactiveUserDialog = true
		},
		// Mostrar diálogo para eliminar usuario
		EliminarUserDialog(index) {
			this.selected.index = index
			this.showEliminarUserDialog = true
		},

		// Activar usuario desactivado
		async ActivarUsuario(index) {
			try {
				await axios.post(generateUrl('/apps/empleados/ActivarUsuario'),
					{
						id_empleados: this.Desactivados[index].Id_empleados,
					})
					.then(
						(response) => {
							this.getall()
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		// Eliminar usuario
		async EliminarUser(index) {
			this.showDeactiveUserDialog = false
			try {
				await axios.post(generateUrl('/apps/empleados/EliminarEmpleado'),
					{
						id_empleados: this.Desactivados[index].Id_empleados,
					})
					.then(
						(response) => {
							this.getall()
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		// Desactivar usuario activo
		async DeactiveUser(index) {
			try {
				await axios.post(generateUrl('/apps/empleados/DesactivarEmpleado'),
					{
						id_empleados: this.Empleados[index].Id_empleados,
					})
					.then(
						(response) => {
							this.getall()
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		// Activar usuario sin cuenta
		async ActivarUser(index) {
			try {
				await axios.post(generateUrl('/apps/empleados/ActivarEmpleado'),
					{
						id_user: this.Usuarios[index].uid,
					})
					.then(
						(response) => {
							this.getall()
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [02] [' + err + ']'))
			}
		},
	},
}
</script>

<style>
/* Estilos para el título del tablero */
.board-title {
	padding-left: 20px;
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

/* Estilos para centrar el icono de carga en la pantalla */
.center-screen {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  min-height: 100vh;
}

/* Estilos para los títulos */
.titles {
	margin-right: 10px;
	margin-top: 14px;
	font-size: 17px;
	display: flex;
	align-items: center;
	.icon {
		margin-right: 8px;
	}
}

/* Estilos para el contenedor */
.container {
	padding-left: 20px;
	padding-right: 20px;
}

/* Estilos para la tabla */
.rsg {
	padding-top: 16px;
	padding-bottom: 16px;
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
	display: flex;
	margin-left: 20px;
	margin-right: 20px;
	width: auto;
}
</style>
