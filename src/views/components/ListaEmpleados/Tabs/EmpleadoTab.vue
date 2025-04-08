<template>
	<div class="well">
		<div class="top">
			<div class="main">
				<div class="box1">
					<div>
						<div class="divider">
							<span>Informacion Laboral</span>
						</div>
						<div class="flexible">
							<!-- numero de empleado -->
							<div class="box1Inside">
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

							<!-- Sueldo -->
							<div class="box1Inside">
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

							<!-- Numero de cuenta bancaria -->
							<div class="box1Inside">
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
						</div>
						<div class="flexible top">
							<!-- Fecha de ingreso -->
							<div class="box1Inside">
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

							<!-- Aniversario -->
							<div class="box1Inside">
								<label for="Aniversario" class="labeltype">
									<PartyPopper :size="20" />
									Aniversario
								</label>
								<input id="Aniversario"
									v-model="Aniversario"
									type="text"
									:disabled="!show"
									class="inputtype">
							</div>

							<!-- vacaciones disponibles -->
							<div class="box1Inside">
								<label for="Vacaciones" class="labeltype">
									<BagSuitcase :size="20" />
									Vacaciones
								</label>
								<input id="Vacaciones"
									v-model="Vacaciones"
									type="text"
									:disabled="!show"
									class="inputtype">
							</div>

							<!-- calcular vacaciones -->
							<div v-if="Ingreso && (!Aniversario) && (!Vacaciones)" class="topRefresh MarginRight">
								<NcButton
									type="primary"
									:disabled="!show"
									@click="CalcularVacaciones()">
									<template #icon>
										<Refresh :size="20" />
									</template>
								</NcButton>
							</div>
						</div>
					</div>
					<div>
						<div class="divider">
							<span>Fondo de ahorro</span>
						</div>
						<div class="flexible">
							<div class="box1Inside">
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

							<div class="box1Inside">
								<label for="Fondo_ahorro" class="labeltype">
									<Piggybankoutline :size="20" />
									Fondo ahorro
								</label>
								<input id="Fondo_ahorro"
									v-model="Fondo_ahorro"
									type="text"
									:disabled="!show"
									class="inputtype">
							</div>
						</div>
					</div>
					<div>
						<div class="divider">
							<span>Sistemas</span>
						</div>
						<div class="flexible">
							<div class="box1Inside">
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
						</div>
					</div>
				</div>

				<div class="box2">
					<div class="divider">
						<span>Extructura Laboral</span>
					</div>
					<div>
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
									class="container__select_puesto"
									:disabled="!show"
									:options="optionspuesto"
									input-label="Puesto" />
							</div>
						</div>

						<!-- Socio and Gerente Select Inputs -->
						<div v-if="show" class="main">
							<div class="label-input-trabajo">
								<NcSelect v-model="socio"
									class="select"
									input-label="Socio"
									:disabled="!show"
									:options="EmpleadosList"
									:user-select="true" />
							</div>

							<div class="label-input-trabajo">
								<NcSelect v-model="gerente"
									class="select"
									:disabled="!show"
									input-label="Gerente"
									:options="EmpleadosList"
									:user-select="true" />
							</div>
						</div>
						<!-- equipo edit -->
						<div v-if="show" class="main">
							<div class="label-input-puesto">
								<NcSelect v-model="Equipo"
									class="select"
									:disabled="!show"
									input-label="Equipo"
									:options="optionsequipos" />
							</div>
						</div>
						<div v-else class="">
							<div class="rst-title">
								<div class="title_flex">
									<div class="subtitle_flex">
										<NcAvatar :user="Equipo.jefe" :display-name="Equipo.jefe" :size="20" />
									</div>
									<div>
										<h1> {{ Equipo.label }} </h1>
									</div>
								</div>
							</div>
							<div class="rst">
								<!--ul class="container flex">
									<li v-for="(item) in peopleEquipo.equipo"
										:key="item.Id_empleados"
										class="item flex-item">
										<NcAvatar :user="item.Id_user" :display-name="item.Id_user" style="margin-top: inherit;" />
										<h3>{{ item.Id_user }}</h3>
										<NcAvatar :user="item.Id_user" :display-name="item.Id_user" />
										<h3>{{ item.Id_user }}</h3>
									</li>
								</ul-->
								<ul>
									<NcListItem
										v-for="(item) in peopleEquipo.equipo"
										:key="item.Id_empleados"
										:name="item.displayname ? item.displayname : item.Id_user">
										<template #icon>
											<NcAvatar disable-menu
												:size="44"
												:user="item.Id_user"
												:display-name="item.Id_user" />
										</template>
									</NcListItem>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
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
// import debounce from 'debounce'

// ICONOS
import Badgeaccountoutline from 'vue-material-design-icons/BadgeAccountOutline.vue'
import Piggybankoutline from 'vue-material-design-icons/PiggyBankOutline.vue'
import Calendarrange from 'vue-material-design-icons/CalendarRange.vue'
import Laptopaccount from 'vue-material-design-icons/LaptopAccount.vue'
import BagSuitcase from 'vue-material-design-icons/BagSuitcase.vue'
import PartyPopper from 'vue-material-design-icons/PartyPopper.vue'
import Refresh from 'vue-material-design-icons/Refresh.vue'
import Bank from 'vue-material-design-icons/Bank.vue'
import Cash from 'vue-material-design-icons/Cash.vue'

import {
	NcAvatar,
	NcButton,
	NcSelect,
	NcListItem,
} from '@nextcloud/vue'

export default {
	name: 'EmpleadoTab',

	components: {
		NcAvatar,
		Badgeaccountoutline,
		Calendarrange,
		Bank,
		PartyPopper,
		BagSuitcase,
		Refresh,
		Piggybankoutline,
		Laptopaccount,
		Cash,
		OrganizationChart,
		NcButton,
		NcSelect,
		NcListItem,
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
			optionsarea: [],
			optionspuesto: [],
			optionsequipos: [],
			Numero_empleado: '',
			Ingreso: '',
			Fondo_clave: '',
			Fondo_ahorro: '',
			Numero_cuenta: '',
			Equipo_asignado: '',
			Sueldo: '',
			Equipo: '',
			areaSend: '',
			puestoSend: '',
			EquipoSend: '',
			peopleEquipo: {},
			EmpleadosList: [],
			Aniversario: '',
			Vacaciones: '',
		}
	},

	watch: {
		data(news) {
			if (news) {
				this.setAttr(
					news.Numero_empleado,
					news.Ingreso,
					news.Id_departamento,
					news.Id_puesto,
					news.Id_gerente,
					news.Id_socio,
					news.Fondo_clave,
					news.Fondo_ahorro,
					news.Numero_cuenta,
					news.Id_equipo,
					news.Equipo_asignado,
					news.Sueldo)
			}
		},
	},

	mounted() {
		this.EmpleadosList = this.empleados.map(empleados => ({
			id: empleados.Id_user,
			displayName: empleados.displayname ? empleados.displayname : empleados.Id_user,
			isNoUser: false,
			icon: '',
			user: empleados.Id_user,
		}))
		this.setAttr(
			this.data.Numero_empleado,
			this.data.Ingreso,
			this.data.Id_departamento,
			this.data.Id_puesto,
			this.data.Id_gerente,
			this.data.Id_socio,
			this.data.Fondo_clave,
			this.data.Fondo_ahorro,
			this.data.Numero_cuenta,
			this.data.Id_equipo,
			this.data.Equipo_asignado,
			this.data.Sueldo)
	},

	methods: {
		setAttr(NumeroEmpleado, Ingreso, Area, Puesto, Gerente, Socio, FondoClave, FondoAhorro, NumeroCuenta, Equipo, EquipoAsignado, Sueldo) {

			this.Numero_empleado = this.checknull(NumeroEmpleado)
			this.Ingreso = this.checknull(Ingreso)
			this.area = Area
			this.puesto = Puesto
			this.gerente = this.checknull(Gerente)
			this.socio = this.checknull(Socio)
			this.Fondo_clave = this.checknull(FondoClave)
			this.Fondo_ahorro = this.checknull(FondoAhorro)
			this.Numero_cuenta = this.checknull(NumeroCuenta)
			this.Equipo = this.checknull(Equipo)
			this.Equipo_asignado = this.checknull(EquipoAsignado)
			this.Sueldo = this.checknull(Sueldo)

			this.getAreas(this.area)
			this.getPuestos(this.puesto)
			this.getEquipos(this.Equipo)
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

		async getEquipos(Equipo) {
			this.loading = false
			this.GetAllEquipo(Equipo)
			try {
				await axios.get(generateUrl('/apps/empleados/GetEquiposList'))
					.then(
						(response) => {
							this.optionsequipos = response.data.map(equipo => ({
								value: equipo.Id_equipo,
								label: equipo.Nombre,
								jefe: equipo.Id_jefe_equipo,
							}))
							if (Equipo && Equipo.length !== 0) {
								this.Equipo = this.optionsequipos.find(role => role.value === parseInt(Equipo))
							} else {
								this.Equipo = ''
							}
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		async GetAllEquipo(equipo) {
			try {
				await axios.get(generateUrl('/apps/empleados/GetEmpleadosEquipo/' + equipo))
					.then(
						(response) => {
							this.peopleEquipo = response.data
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},

		generateChar(user, gerente, socio) {
			if (!gerente) gerente = 'Sin Asignar'
			if (!socio) socio = 'Sin asignar'
			return {
				id: 'nodo-oculto',
				children: [
					{ id: '1', name: socio, title: 'Socio' },
					{ id: '2', name: gerente, title: 'Gerente' },
					{ id: '3', name: user, title: 'Empleado' },
				],
			}
		},

		checknull(value) {
			return value ?? ''
		},

		async CambiosEmpleado() {
			try {

				this.areaSend = this.area.value
				this.puestoSend = this.puesto.value

				if (!this.area.value) {
					this.areaSend = this.optionsarea.find(role => role.label === this.area)?.value || ''
				}

				if (!this.puesto.value) {
					this.puestoSend = this.optionspuesto.find(role => role.label === this.puesto)?.value || ''
				}

				this.socio = this.socio?.id || this.socio
				this.gerente = this.gerente?.id || this.gerente

				await axios.post(generateUrl('/apps/empleados/CambiosEmpleado'), {
					id_empleados: this.data.Id_empleados,
					numeroempleado: this.checknull(this.Numero_empleado),
					ingreso: this.checknull(this.Ingreso),
					area: this.checknull(this.areaSend),
					puesto: this.checknull(this.puestoSend),
					socio: this.socio,
					gerente: this.checknull(this.gerente),
					fondoclave: this.checknull(this.Fondo_clave),
					fondoahorro: this.checknull(this.Fondo_ahorro),
					numerocuenta: this.checknull(this.Numero_cuenta),
					equipoasignado: this.checknull(this.Equipo_asignado),
					equipo: this.Equipo.value,
					sueldo: this.checknull(this.Sueldo),
				})
				this.GetAllEquipo(this.Equipo.value)
				this.$bus.emit('getall')
				this.$bus.emit('show', false)
				showSuccess('Datos actualizados')
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},

		async CalcularVacaciones() {
			try {
				try {
					const response = await axios.post(generateUrl('/apps/empleados/GetAniversarioByDate'), {
						ingreso: this.checknull(this.Ingreso),
					})

					// eslint-disable-next-line no-console
					console.log(response.data)
					this.Aniversario = response.data[0]?.numero_aniversario
					this.Vacaciones = response.data[0]?.dias
					// this.peopleEquipo = response.data
				} catch (err) {
					showError(err)
				}
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
	},
}
</script>

<style>
.box-chart {
	margin-top: 10px;
}
.box{
	display: flex;
}
.box1 {
	flex: 3;
	padding-right: 5%;
}
.box1Inside {
	flex: 3;
}
.MarginRight {
	padding-right: 5px;
}
.box2 {
	flex: 2;
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
.labelEmpleado {
	font-weight: bold;
	display: inline-flex;
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

.divider {
  position: relative;
  text-align: center;
  margin: 2rem 0;
}

.divider::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 100%;
  height: 1px;
  background: #ccc;
  z-index: 0;
}

.divider span {
  position: relative;
  background: #fff; /* o el color del fondo */
  padding: 0 1rem;
  z-index: 1;
  font-weight: 500;
}

.label-input-puesto {
	display: grid;
	margin-top: 5px;
	align-items: center;
	width: 100%;
}

.rst {
	padding-top: 5px;
	padding-bottom: 5px;
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
}
.rst-title {
	background-color: rgba(240, 240, 240, 0.37);
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
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
.title_flex {
	display: flex;
	justify-content: center;
}
.subtitle_flex {
	padding-top: 5px;
	margin-right: 20px;
}
#nodo-oculto {
  display: none;
  height: 0;
  padding: 0;
  margin: 0;
}
.orgchart{
	min-height: 10px;
}
.flexible {
	display: flex;
	align-items: center;
	gap: 10px;
}
.topRefresh {
	margin-top: 26px;
}
</style>
