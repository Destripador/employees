<!-- eslint-disable vue/require-v-for-key -->
<template>
	<div v-if="loading">
		<!-- Sección de carga -->
		<div class="center-screen">
			<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
		</div>
	</div>

	<div v-else id="admin">
		<!-- Título principal -->
		<div>
			<h2 class="board-title">
				<AccountGroup :size="20" decorative class="icon" />
				<span>Configuraciones globales</span>
			</h2>
		</div>

		<div class="container">
			<!-- Bloque: Switch para “guardado automático de notas” -->
			<div class="grid">
				<NcCheckboxRadioSwitch
					:checked="guardado_notas"
					type="switch"
					@update:checked="onChangeGuardadoNotas">
					Guardado automático de notas
				</NcCheckboxRadioSwitch>
			</div>

			<br>

			<!-- Bloque: Switch para “Acumular vacaciones” -->
			<div class="grid">
				<NcCheckboxRadioSwitch
					:checked="acumular_vacaciones"
					type="switch"
					@update:checked="onChangeacumular_vacaciones">
					Permitir a todos los usuarios acumular vacaciones
				</NcCheckboxRadioSwitch>
			</div>

			<br>
			<!-- Bloque: activar o desactivar modulo -->
			<div class="grid">
				<NcNoteCard type="info" heading="Modulo de ahorro">
					<p>
						Si el módulo de ahorro está habilitado, los usuarios podrán ver la opción de ahorro en su menú.
					</p>
					<br>
					<p>
						Al habilitar el modulo, todos los estados de los usuarios se reinician a 0.
					</p>
					<br>
					<NcCheckboxRadioSwitch
						:checked="modulo_ahorro"
						type="switch"
						@update:checked="onChangemodulo_ahorro">
						Activar módulo de ahorro
					</NcCheckboxRadioSwitch>
				</NcNoteCard>
			</div>

			<br>

			<!-- Bloque: activar o desactivar modulo -->
			<div class="grid">
				<NcNoteCard type="info" heading="Modulo de ausencias">
					<p>
						Si el módulo de ausencias está habilitado, los usuarios podrán ver la opción de ausencias en su menú.
					</p>
					<br>
					<NcCheckboxRadioSwitch
						:checked="modulo_ausencias"
						type="switch"
						@update:checked="onChangemodulo_ausencias">
						Activar módulo de ausencias
					</NcCheckboxRadioSwitch>
				</NcNoteCard>
			</div>

			<br>

			<!-- Bloque: Selector único para el Gestor de datos -->
			<div class="grid">
				<NcNoteCard v-if="selected_user" type="warning" heading="ATENCION">
					<p>
						Si decide cambiar el usuario gestor de archivos cuando ya haya sido asignado,
						puede generarse pérdida de archivos.
						Considere generar un respaldo antes de realizar este proceso.
					</p>
				</NcNoteCard>

				<NcSelect
					v-model="selected_user"
					input-label="Usuario gestor de datos"
					:options="optionsGestor"
					:user-select="true" />

				<NcButton
					aria-label="Aplicar cambios"
					type="primary"
					@click="saveGestor">
					Aplicar cambios
				</NcButton>
			</div>

			<br>

			<!-- Bloque: Selector múltiple para Capital Humano -->
			<div class="grid">
				<NcSelect
					v-bind="propsCapitalHumano"
					v-model="selectedUsers" />

				<NcButton
					aria-label="Aplicar cambios"
					type="primary"
					@click="saveCapitalHumano">
					Aplicar cambios
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script>
// Iconos
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'

// Componentes de @nextcloud/vue
import {
	NcButton,
	NcLoadingIcon,
	NcSelect,
	NcNoteCard,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'

// Utilidades de Nextcloud
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'ListSettings',
	components: {
		AccountGroup,
		NcSelect,
		NcButton,
		NcNoteCard,
		NcLoadingIcon,
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			loading: true,

			// Configuraciones generales
			configuraciones: [],

			/**
			 * LISTA COMPLETA de usuarios (desde GetConfigurations)
			 * para el "usuario gestor" y para el selector múltiple.
			 */
			optionsGestor: [], // Se llena con response.data.Users

			selected_user: null, // Gestor de datos seleccionado
			guardado_notas: false,
			acumular_vacaciones: false,
			modulo_ahorro: false,
			modulo_ausencias: false,

			/**
			 * SELECTOR MÚLTIPLE - Capital Humano
			 */
			propsCapitalHumano: {
				inputLabel: 'Seleccionar usuarios de Capital Humano',
				userSelect: true,
				multiple: true,
				closeOnSelect: false,
				options: [], // Se llena con todos los usuarios (optionsGestor)
			},
			/**
			 * Arreglo con los IDs (uid) de los usuarios que se van a preseleccionar
			 * al cargar Capital Humano.
			 */
			selectedUsers: [],

			/**
			 * Arreglo con información proveniente de GetCapitalHumano
			 * (usuarios que realmente pertenecen a capital humano).
			 */
			capitalHumano: [],
		}
	},

	async mounted() {
		// Cargar configuraciones generales y la lista de Capital Humano en paralelo
		await Promise.all([
			this.getall(),
			this.fetchCapitalHumano(),
		])

		// Ajustar el selector múltiple para usar “optionsGestor” y preseleccionar "capitalHumano"
		this.setupCapitalHumanoSelector()

		// Desactivar loader
		this.loading = false
	},

	methods: {
		/**
		 * Carga la configuración global, incluido "Users" para el Gestor de datos.
		 */
		async getall() {
			try {
				this.loading = true
				const response = await axios.get(generateUrl('/apps/empleados/GetConfigurations'))
				// Lista de usuarios disponibles
				this.optionsGestor = response.data.Users

				// Gestor de datos actual
				this.selected_user = response.data.Gestor_actual

				// Guardado automático de notas
				this.guardado_notas = (response.data.Guardado_notas === 'true')

				// Acumular vacaciones
				this.acumular_vacaciones = (response.data.Acumular_vacaciones === 'true')

				// modulo ahorro
				this.modulo_ahorro = (response.data.modulo_ahorro === 'true')

				// modulo ausencias
				this.modulo_ausencias = (response.data.modulo_ausencias === 'true')

				this.loading = false
			} catch (err) {
				this.loading = false
				showError('Se ha producido una excepción [GetConfigurations]: ' + err)
				console.error(err)
			}
		},

		/**
		 * Actualiza el gestor de datos seleccionado
		 */
		async saveGestor() {
			if (!this.selected_user || !this.selected_user.id) {
				showError('No has seleccionado ningún Gestor de datos')
				return
			}
			try {
				await axios.post(generateUrl('/apps/empleados/ActualizarGestor'), {
					id_gestor: this.selected_user.id,
				})
				showSuccess('Gestor actualizado')
			} catch (err) {
				showError('Error al actualizar el Gestor: ' + err)
				console.error(err)
			}
		},

		/**
		 * Actualiza el gestor de datos seleccionado
		 */
		 async saveCapitalHumano() {
			// eslint-disable-next-line no-console
			console.log('ejemplo:   ', this.selectedUsers)

			try {
				await axios.post(generateUrl('/apps/empleados/UpdateCapitalHumano'), {
					capitalhumano: this.selectedUsers,
				})
				showSuccess('Capital humano actualizado')
			} catch (err) {
				showError('Error al actualizar capital humano: ' + err)
				console.error(err)
			}

		},

		/**
		 * Cambia la configuración de guardado de notas
		 */
		async onChangeGuardadoNotas() {
			this.guardado_notas = !this.guardado_notas
			try {
				await axios.post(generateUrl('/apps/empleados/ActualizarConfiguracion'), {
					id_configuracion: 'automatic_save_note',
					data: this.guardado_notas.toString(),
				})
				showSuccess('Configuración actualizada')
			} catch (err) {
				showError('Excepción [ActualizarConfiguracion]: ' + err)
				console.error(err)
			}
		},

		/**
		 * Cambia la configuración de acumulo de vacaciones
		 */
		 async onChangeacumular_vacaciones() {
			this.acumular_vacaciones = !this.acumular_vacaciones
			try {
				await axios.post(generateUrl('/apps/empleados/ActualizarConfiguracion'), {
					id_configuracion: 'acumular_vacaciones',
					data: this.acumular_vacaciones.toString(),
				})
				showSuccess('Configuración actualizada')
			} catch (err) {
				showError('Excepción [ActualizarConfiguracion]: ' + err)
				console.error(err)
			}
		},

		/**
		 * Activar o desactivar modulo de ahorro
		 */
		 async onChangemodulo_ahorro() {
			this.modulo_ahorro = !this.modulo_ahorro
			try {
				await axios.post(generateUrl('/apps/empleados/ActualizarConfiguracion'), {
					id_configuracion: 'modulo_ahorro',
					data: this.modulo_ahorro.toString(),
				})
				showSuccess('Configuración actualizada')
			} catch (err) {
				showError('Excepción [ActualizarConfiguracion]: ' + err)
				console.error(err)
			}
		},

		/**
		 * Activar o desactivar modulo de ahorro
		 */
		 async onChangemodulo_ausencias() {
			this.modulo_ausencias = !this.modulo_ausencias
			try {
				await axios.post(generateUrl('/apps/empleados/ActualizarConfiguracion'), {
					id_configuracion: 'modulo_ausencias',
					data: this.modulo_ausencias.toString(),
				})
				showSuccess('Configuración actualizada')
			} catch (err) {
				showError('Excepción [ActualizarConfiguracion]: ' + err)
				console.error(err)
			}
		},

		/**
		 * Obtiene la lista de usuarios que SON Capital Humano
		 */
		async fetchCapitalHumano() {
			try {
				const response = await fetch('/apps/empleados/GetCapitalHumano')
				if (!response.ok) throw new Error('Error al obtener datos de Capital Humano')
				this.capitalHumano = await response.json()
			} catch (error) {
				showError('Error al obtener usuarios de Capital Humano')
				console.error(error)
			}
		},

		/**
		 * Configura el selector múltiple de Capital Humano:
		 * - Usa TODOS los usuarios de "optionsGestor" como opciones
		 * - Preselecciona solo aquellos que están en "capitalHumano"
		 */
		setupCapitalHumanoSelector() {
			// 1. Convertir la lista de "optionsGestor" a formato de NcSelect
			this.propsCapitalHumano.options = this.optionsGestor.map(user => ({
				id: user.id,
				displayName: user.displayName || user.uid,
				isNoUser: false,
				icon: '',
				user: user.uid,
				preloadedUserStatus: {
					icon: '',
					status: user.isEnabled ? 'online' : 'offline',
					message: user.isEnabled ? 'Activo' : 'Inactivo',
				},
			}))

			// 2. Obtener los 'uid' de los usuarios de Capital Humano
			const capitalHumanoUids = this.capitalHumano.map(u => u.id)

			// 3. selectedUsers = IDs que coincidan con capitalHumano
			this.selectedUsers = this.propsCapitalHumano.options
				.filter(opt => capitalHumanoUids.includes(opt.id))
				.map(opt => opt.id)
		},
	},
}
</script>
