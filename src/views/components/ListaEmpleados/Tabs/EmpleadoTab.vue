<template>
	<div>
		<div class="top">
			<div class="main">
				<div class="box1">
					<!-- Socio and Gerente Select Inputs -->
					<div v-if="show" class="main">
						<div class="label-input-trabajo">
							<NcSelect v-model="socio"
								class="select"
								input-label="Socio"
								:disabled="!show"
								:options="empleados"
								:user-select="true" />
						</div>

						<div class="label-input-trabajo">
							<NcSelect v-model="gerente"
								class="select"
								:disabled="!show"
								input-label="Gerente"
								:options="empleados"
								:user-select="true" />
						</div>
					</div>

					<!-- Departamento and Puesto Select Inputs -->
					<div class="main">
						<div class="label-input-trabajo">
							<NcSelect id="Id_departamento"
								v-model="area"
								class="container__select"
								:disabled="!show"
								:options="optionsarea"
								input-label="Departamento" />
						</div>

						<div class="label-input-trabajo">
							<NcSelect id="Id_puesto"
								v-model="puesto"
								class="container__select"
								:disabled="!show"
								:options="optionspuesto"
								input-label="Puesto" />
						</div>
					</div>

					<!-- Various Input Fields -->
					<div class="external-label">
						<label for="Numero_empleado" class="labeltype">
							<Badgeaccountoutline :size="20" />
							Num. Empleado
						</label>
						<input id="Numero_empleado"
							v-model="Numero_empleado"
							type="text"
							:disabled="!show"
							class="inputtype">
					</div>

					<div class="external-label">
						<label for="Ingreso" class="labeltype">
							<Calendarrange :size="20" />
							Fecha de Ingreso
						</label>
						<input id="Ingreso"
							v-model="Ingreso"
							type="date"
							:disabled="!show"
							class="inputtype">
					</div>

					<div class="external-label">
						<label for="Fondo_clave" class="labeltype">
							<Piggybankoutline :size="20" />
							Fondo Clave
						</label>
						<input id="Fondo_clave"
							v-model="Fondo_clave"
							type="text"
							:disabled="!show"
							class="inputtype">
					</div>

					<div class="external-label">
						<label for="Numero_cuenta" class="labeltype">
							<Bank :size="20" />
							Cuenta Bancaria
						</label>
						<input id="Numero_cuenta"
							v-model="Numero_cuenta"
							type="text"
							:disabled="!show"
							class="inputtype">
					</div>

					<div class="external-label">
						<label for="Equipo_asignado" class="labeltype">
							<Laptopaccount :size="20" />
							Equipo Asignado
						</label>
						<input id="Equipo_asignado"
							v-model="Equipo_asignado"
							type="text"
							:disabled="!show"
							class="inputtype">
					</div>

					<div class="external-label">
						<label for="Sueldo" class="labeltype">
							<Cash :size="20" />
							Sueldo
						</label>
						<input id="Sueldo"
							v-model="Sueldo"
							type="text"
							:disabled="!show"
							class="inputtype">
					</div>
				</div>

				<!-- Organization Chart -->
				<div class="box2" :style="show ? { display: 'none' } : {}">
					<div class="box-chart">
						<OrganizationChart :datasource="generateChar(data.uid, gerente, socio)">
							<template slot-scope="{ nodeData }">
								<div class="title">
									{{ nodeData.title }}
								</div>
								<div class="content">
									<div class="center">
										<div class="avatar-chart mini-top">
											<NcAvatar
												:user="nodeData.name"
												:display-name="nodeData.name"
												:size="40"
												:show-user-status="false" />
										</div>
										<div class="name-chart">
											{{ nodeData.name }}
										</div>
									</div>
								</div>
							</template>
						</OrganizationChart>
					</div>
				</div>
			</div>
			<br>
			<!-- Employee Notes -->
			<NcTextArea class="top"
				label="NOTAS EMPLEADO"
				resize="vertical"
				:disabled="show"
				:value.sync="inputValue" />
			<NcButton
				v-if="automaticsave === 'false'"
				aria-label="Guardar nota"
				type="primary"
				@click="guardarNota()">
				Guardar nota
			</NcButton>
			<br>
			<div class="div-center">
				<NcButton
					v-if="show"
					aria-label="Guardar"
					type="primary"
					@click="CambiosEmpleado()">
					Aplicar cambios
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import OrganizationChart from 'vue-organization-chart'
import { generateUrl } from '@nextcloud/router'
import 'vue-nav-tabs/themes/vue-tabs.css'
import axios from '@nextcloud/axios'
import debounce from 'debounce'

// ICONOS
import Badgeaccountoutline from 'vue-material-design-icons/BadgeAccountOutline.vue'
import Piggybankoutline from 'vue-material-design-icons/PiggyBankOutline.vue'
import Calendarrange from 'vue-material-design-icons/CalendarRange.vue'
import Laptopaccount from 'vue-material-design-icons/LaptopAccount.vue'
import Bank from 'vue-material-design-icons/Bank.vue'
import Cash from 'vue-material-design-icons/Cash.vue'

import {
	NcAvatar,
	NcTextArea,
	NcButton,
	NcSelect,
} from '@nextcloud/vue'

export default {
	name: 'EmpleadoTab',

	components: {
		NcAvatar,
		Badgeaccountoutline,
		Calendarrange,
		Bank,
		Piggybankoutline,
		Laptopaccount,
		Cash,
		OrganizationChart,
		NcTextArea,
		NcButton,
		NcSelect,
	},

	props: {
		data: {
			type: Object,
			required: true,
		},
		show: {
			type: Boolean,
			required: true,
		},
		empleados: {
			type: Array,
			required: true,
		},
		automaticsave: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			area: '',
			puesto: '',
			gerente: null,
			socio: null,
			notas: this.data.Notas ?? '',
			optionsarea: [],
			optionspuesto: [],
			Numero_empleado: '',
			Ingreso: '',
			Fondo_clave: '',
			Numero_cuenta: '',
			Equipo_asignado: '',
			Sueldo: '',
			areaSend: '',
			puestoSend: '',
		}
	},

	computed: {
		inputValue: {
			get() {
				return this.notas
			},
			set(value) {
				this.debouncePropertyChange(value.trim())
			},
		},
		debouncePropertyChange() {
			return debounce(function(value) {
				this.notas = value
				if (this.automaticsave === 'true') {
					this.guardarNota()
				}
			}, 700)
		},
	},

	watch: {
		data(news) {
			this.notas = news.Notas
			if (news) {
				this.setAttr(
					news.Numero_empleado,
					news.Ingreso,
					news.Id_departamento,
					news.Id_puesto,
					news.Id_gerente,
					news.Id_socio,
					news.Fondo_clave,
					news.Numero_cuenta,
					news.Equipo_asignado,
					news.Sueldo)
			}
		},
	},

	mounted() {
		this.notas = this.data.Notas
		this.setAttr(
			this.data.Numero_empleado,
			this.data.Ingreso,
			this.data.Id_departamento,
			this.data.Id_puesto,
			this.data.Id_gerente,
			this.data.Id_socio,
			this.data.Fondo_clave,
			this.data.Numero_cuenta,
			this.data.Equipo_asignado,
			this.data.Sueldo)
	},

	methods: {
		setAttr(NumeroEmpleado, Ingreso, Area, Puesto, Gerente, Socio, FondoClave, NumeroCuenta, EquipoAsignado, Sueldo) {
			this.Numero_empleado = this.checknull(NumeroEmpleado)
			this.Ingreso = this.checknull(Ingreso)
			this.area = this.checknull(Area)
			this.puesto = this.checknull(Puesto)
			this.gerente = this.checknull(Gerente)
			this.socio = this.checknull(Socio)
			this.Fondo_clave = this.checknull(FondoClave)
			this.Numero_cuenta = this.checknull(NumeroCuenta)
			this.Equipo_asignado = this.checknull(EquipoAsignado)
			this.Sueldo = this.checknull(Sueldo)

			this.getAreas(this.area)
			this.getPuestos(this.puesto)
		},

		async getAreas(Area) {
			try {
				const response = await axios.get(generateUrl('/apps/empleados/GetAreasFix'))
				this.optionsarea = response.data
				if (Area && Area.length !== 0) {
					this.area = this.optionsarea.find(areas => areas.value === parseInt(Area)).label
				} else {
					this.area = ''
				}
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		async getPuestos(Puesto) {
			try {
				const response = await axios.get(generateUrl('/apps/empleados/GetPuestosFix'))
				this.optionspuesto = response.data
				if (Puesto && Puesto.length !== 0) {
					this.puesto = this.optionspuesto.find(role => role.value === parseInt(Puesto)).label
				} else {
					this.puesto = ''
				}
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		generateChar(user, gerente, socio) {
			if (!gerente) gerente = 'Sin Asignar'
			if (!socio) socio = 'Sin asignar'
			return {
				id: '1',
				name: socio,
				title: 'Socio',
				children: [
					{
						id: '2',
						name: gerente,
						title: 'Gerente',
						children: [
							{ id: '3', name: user, title: 'Empleado' },
						],
					},
				],
			}
		},

		checknull(value) {
			return value ?? ''
		},

		async guardarNota() {
			try {
				await axios.post(generateUrl('/apps/empleados/GuardarNota'), {
					id_empleados: this.data.Id_empleados,
					nota: this.notas,
				})
				showSuccess('Nota ha sido actualizada')
				this.$root.$emit('getall')
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},

		async CambiosEmpleado() {
			try {
				this.areaSend = this.optionsarea.find(role => role.label === this.area)?.value || ''
				this.puestoSend = this.optionspuesto.find(role => role.label === this.puesto)?.value || ''
				this.socio = this.socio?.id || this.socio
				this.gerente = this.gerente?.id || this.gerente

				await axios.post(generateUrl('/apps/empleados/CambiosEmpleado'), {
					id_empleados: this.data.Id_empleados,
					numeroempleado: this.checknull(this.Numero_empleado),
					ingreso: this.checknull(this.Ingreso),
					area: this.areaSend,
					puesto: this.puestoSend,
					socio: this.socio,
					gerente: this.checknull(this.gerente),
					fondoclave: this.checknull(this.Fondo_clave),
					numerocuenta: this.checknull(this.Numero_cuenta),
					equipoasignado: this.checknull(this.Equipo_asignado),
					sueldo: this.checknull(this.Sueldo),
				})
				this.$root.$emit('getall')
				this.$root.$emit('show', false)
				showSuccess('Datos actualizados')
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
	},
}
</script>

<style>
.box-chart {
	margin-top: 20px;
}
.box1 {
	flex: 4;
}
.box2 {
	flex: 1;
}
.main {
	margin-top: 10px;
	display: flex;
	gap: 5px;
}
.label-input-trabajo {
	display: grid;
	align-items: center;
	width: 100%;
}
.wrapper {
	display: flex;
	gap: 4px;
	align-items: flex-end;
	flex-wrap: wrap;
}
.external-label {
	display: flex;
	align-items: center;
	gap: 10px;
	margin-top: 2px;
}
.labeltype {
	font-weight: bold;
	display: flex;
	align-items: center;
	gap: 5px;
	min-width: 150px;
}
.inputtype {
	flex: 1;
	height: 40px;
	padding: 8px 12px;
	font-size: 14px;
	border-radius: 5px;
	border: 1px solid #ccc;
	width: 100%;
}
</style>
