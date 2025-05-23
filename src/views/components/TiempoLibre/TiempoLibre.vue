<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<div class="text-center section">
				<section class="layout">
					<div class="grow2">
						<div class="text-center sectionPicker">
							<DatePicker
								ref="calendarRef"
								:key="calendarKey"
								v-model="date"
								class="custom-calendar"
								is-expanded
								is-range
								:min-date="FechaInitial"
								:max-date="FechaMaxima"
								@dayclick="calcularFechaMaxima()"
								@input="onRangeSelected"
								@drag="dragValue = $event" />
						</div>
					</div>
					<div class="grow1">
						<div class="cards">
							<div class="headers">
								<div class="btn-top-right">
									<NcActions>
										<NcActionButton @click="showAniversarioModal">
											<template #icon>
												<CalendarQuestionOutline :size="20" />
											</template>
											Mi informacion
										</NcActionButton>
									</NcActions>
								</div>
								<div>
									<h2 class="h2-white">
										Vacaciones
									</h2>
								</div>
								<div class="vacations">
									<div class="gl">
										<Airplane />
									</div>
									<div class="gl">
										{{ formatearDias(Ausencias.dias_disponibles) }}
									</div>
								</div>
							</div>
							<div class="infos">
								<p class="titles">
									ejemplo mensaje lorem
								</p>
								<p>
									Lorem ipsum dolor sit amet consectetur adipisicing elit. Maxime mollitia,
									molestiae quas vel sint commodi.
								</p>
							</div>
							<div class="footers">
								<p class="tags">
									#HTML
								</p>
								<button type="button" class="action">
									Get started
								</button>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
		<!-- INICIO MODAL SOLICITUD DE VACACIONES -->
		<NcModal
			v-if="modal"
			size="large"
			name="Formato de ausencias"
			@close="closeModal">
			<NuevaSolicitud
				v-if="modal"
				ref="modalRef"
				:date="date"
				:dias-solicitados="diasSolicitados"
				:dias-disponibles="Ausencias.dias_disponibles"
				:prima="Ausencias.prima_vacacional"
				@close="closeModal" />
		</NcModal>
		<!-- FINAL MODAL SOLICITUD DE VACACIONES -->

		<!-- INICIO MODAL ANIVERSARIOS SOLO INFO -->
		<NcModal
			v-if="ModalAniversario"
			ref="modalRef"
			size="large"
			name="Tabla de aniversarios"
			@close="closeModalAniversario">
			<div class="table_component" role="region" tabindex="0">
				<div class="modal__content">
					<div class="layout">
						<div class="grow3">
							<TrofeosAniversarios :info="Ausencias" :acumular="configuraciones.acumular_vacaciones" />
							<br>
							<table>
								<caption>
									<span class="caption-title">Tabla de aniversarios</span>
								</caption>
								<thead>
									<tr>
										<th>Aniversario(s)</th>
										<th>Días libres</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(grupo, index) in AniversariosAgrupados" :key="index">
										<td>
											<span v-if="grupo.desde === grupo.hasta">
												{{ grupo.desde }}
											</span>
											<span v-else>
												{{ grupo.desde }} al {{ grupo.hasta }}
											</span>
										</td>
										<td>{{ grupo.dias }}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="grow4">
							<MensajeAniversarios :info="Ausencias" :acumular="configuraciones.acumular_vacaciones" />
						</div>
					</div>
				</div>
			</div>
		</NcModal>
		<!-- FINAL MODAL ANIVERSARIOS SOLO INFO -->
	</NcAppContent>
</template>

<script>
// Importing necessary components
import MensajeAniversarios from './MensajeAniversarios.vue'
import TrofeosAniversarios from './TrofeosAniversarios.vue'
import NuevaSolicitud from './Modal/NuevaSolicitud.vue'

import { ref } from 'vue'

import { showError /* showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import 'v-calendar/src/styles/base.css'
import { DatePicker } from 'v-calendar'

// icons
import Airplane from 'vue-material-design-icons/Airplane.vue'
import CalendarQuestionOutline from 'vue-material-design-icons/CalendarQuestionOutline.vue'

import {
	NcAppContent,
	NcModal,
	NcActions,
	NcActionButton,
} from '@nextcloud/vue'
export default {
	name: 'TiempoLibre',

	components: {
		MensajeAniversarios,
		TrofeosAniversarios,
		NuevaSolicitud,
		NcAppContent,
		DatePicker,
		NcModal,
		NcActions,
		NcActionButton,
		Airplane,
		CalendarQuestionOutline,
	},

	inject: ['employee', 'configuraciones'],

	data() {
		return {
			modalRef: ref(null),
			date: ref({
				start: new Date(),
				end: null,
			}),
			FechaInitial: null,
			FechaMaxima: null,
			modal: false,
			ModalAniversario: false,
			Ausencias: [],
			Aniversarios: [],
			dragValue: null,
			calendarKey: 0,
			diasSolicitados: 0,
		}
	},

	computed: {
		AniversariosAgrupados() {
			const agrupados = []
			let inicio = null
			let fin = null
			let diasActual = null

			this.Aniversarios.forEach((item, index) => {
				const diasNumero = Number(item.dias) // Por si viene como string
				if (diasActual === null) {
					inicio = item.numero_aniversario
					fin = item.numero_aniversario
					diasActual = diasNumero
				} else if (diasNumero === diasActual) {
					fin = item.numero_aniversario
				} else {
					agrupados.push({
						desde: inicio,
						hasta: fin,
						dias: diasActual,
					})
					inicio = item.numero_aniversario
					fin = item.numero_aniversario
					diasActual = diasNumero
				}

				if (index === this.Aniversarios.length - 1) {
					agrupados.push({
						desde: inicio,
						hasta: fin,
						dias: diasActual,
					})
				}
			})

			return agrupados
		},
	},

	mounted() {
		this.$bus.on('close-solicitud', () => {
			this.GetAusencias()
			this.closeModal()
		})
		this.GetAusencias()
		document.addEventListener('keydown', this.detectarEscape)
		document.addEventListener('click', this.handleClickOutside)
	},
	beforeUnmount() {
		document.removeEventListener('keydown', this.detectarEscape)
		document.removeEventListener('click', this.handleClickOutside)
	},
	methods: {
		detectarEscape(e) {
			if (e.key === 'Escape') {
				this.restartCalendar() // o cualquier acción que quieras
			}
		},
		handleClickOutside(event) {
			const calendar = this.$refs.calendarRef?.$el
			const modal = this.$refs.modalRef?.$el

			if (
				calendar
				&& !calendar.contains(event.target)
				&& (!modal || !modal.contains(event.target))
			) {
				this.restartCalendar()
			}
		},
		onRangeSelected(range) {
			if (!range || !range.start || !range.end) return

			const startDate = new Date(range.start)
			const endDate = new Date(range.end)

			// Paso 1: Validar que no termine en fin de semana
			const dia = endDate.getDay() // 0 = domingo, 6 = sábado
			if (dia === 0 || dia === 6) {
				console.warn('⚠️ El rango termina en fin de semana (sábado o domingo).')
				showError('No puedes finalizar tu ausencia en fin de semana')
				this.restartCalendar()
				return
			}

			// Paso 2: Contar los días hábiles
			let fecha = new Date(startDate)
			let diasHabiles = 0

			while (fecha <= endDate) {
				const diaSemana = fecha.getDay()
				if (diaSemana !== 0 && diaSemana !== 6) {
					diasHabiles++
				}
				fecha = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate() + 1) // ✅ AVANZAMOS la fecha
			}

			// eslint-disable-next-line no-console
			console.log(`📅 Días hábiles seleccionados: ${diasHabiles}`)

			// Aquí puedes guardar o usar los días hábiles como desees
			this.diasSolicitados = diasHabiles
			this.modal = true
			// eslint-disable-next-line no-console
			console.log('Selected range:', range)
		},

		showAniversarioModal() {
			this.getAniversarios()
		},
		closeModal() {
			this.modal = false
			this.restartCalendar()
		},
		closeModalAniversario() {
			this.ModalAniversario = false
		},
		async GetAusencias() {
			try {
				try {
					const response = await axios.post(generateUrl('/apps/empleados/GetAusenciasByUser'), {
						id: this.employee[0].Id_empleados,
					})

					// eslint-disable-next-line no-console
					console.log(response.data)
					this.Ausencias = response.data[0]
				} catch (err) {
					showError(err)
				}
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		async getAniversarios() {
			try {
				await axios.get(generateUrl('/apps/empleados/Getaniversarios'))
					.then(
						(response) => {
							this.Aniversarios = response.data
							// eslint-disable-next-line no-console
							console.log(this.Aniversarios)
							// this.loading = false

							this.ModalAniversario = true
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},
		formatearDias(dias) {
			const entero = Math.floor(dias)
			const decimal = dias % 1

			if (decimal === 0) {
				return `${entero} ${entero === 1 ? 'día' : 'días'}`
			} else if (decimal === 0.5) {
				return `${entero} ${entero === 1 ? 'día' : 'días'} y medio`
			} else {
				return `${dias} días` // puedes redondear o manejar más decimales si quieres
			}
		},
		calcularFechaMaxima() {
			const nDate = new Date()
			const start = this.dragValue?.start
			const end = this.dragValue?.end
			// eslint-disable-next-line no-console
			console.log('start', start.getDate(), nDate.getDate())
			// eslint-disable-next-line no-console
			console.log('start', start.getMonth(), nDate.getMonth())
			// eslint-disable-next-line no-console
			console.log('start', start.getFullYear(), nDate.getFullYear())
			// eslint-disable-next-line no-console
			console.log('TOP', start, nDate)

			// Validar si hay un rango seleccionado
			if (!start || !end) return
			if (
				new Date(start.getFullYear(), start.getMonth(), start.getDate())
				< new Date(nDate.getFullYear(), nDate.getMonth(), nDate.getDate())
			) {
				showError('No puedes solicitar ausencias en fechas pasadas')
				this.restartCalendar()
			}

			// Paso 1: Validar si solo se seleccionaron sábado y/o domingo
			let fecha = new Date(start) // 🟢 CLONAMOS correctamente
			let soloFinesDeSemana = true

			while (fecha <= end) {
				const dia = fecha.getDay()
				if (dia !== 0 && dia !== 6) {
					soloFinesDeSemana = false
					break
				}
				fecha = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate() + 1) // ✅ AVANZAMOS la fecha
			}

			if (soloFinesDeSemana) {
				this.restartCalendar()
				return
			}

			// Paso 2: Cálculo normal de FechaMaxima
			this.FechaInitial = start

			// Esta parte del código está comentada porque no se utiliza en el momento actual
			// y se ha dejado para referencia futura.
			// limita la fecha maxima segun las vacaciones disponibles
			/*
			const diasTotales = Math.floor(this.Ausencias.dias_disponibles)
			const horasExtra = (this.Ausencias.dias_disponibles % 1) * 24

			let diasContados = 1
			const fechaMax = new Date(start)

			while (diasContados < diasTotales) {
				fechaMax.setDate(fechaMax.getDate() + 1)
				const diaSemana = fechaMax.getDay()
				if (diaSemana !== 0 && diaSemana !== 6) {
					diasContados++
				}
			}

			fechaMax.setHours(fechaMax.getHours() + horasExtra)
			this.FechaMaxima = fechaMax
			*/
		},

		restartCalendar() {
			this.calendarKey++

			this.date = {
				start: new Date(),
				end: null,
			}
			this.FechaInitial = null
			this.FechaMaxima = null
			this.dragValue = null
		},
	},
}
</script>
<style scoped>
/* 🎯 Estilos locales del componente */
.layout {
  width: 100%;
  display: flex;
  gap: 16px;
}

.grow1 { flex: 3; }
.grow2 { flex: 7; }
.grow3 { flex: 3; }
.grow4 { flex: 3; }

.cards {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-radius: 0.75rem;
  background-color: white;
  height: 370px;
  border: 1px solid #cbd5e0;
}

.headers {
  position: relative;
  background-clip: border-box;
  margin-top: 1.5rem;
  margin-left: 1rem;
  margin-right: 1rem;
  border-radius: 0.75rem;
  background-color: rgb(33 150 243);
  box-shadow: 0 10px 15px -3px rgba(33,150,243,.4), 0 4px 6px -4px rgba(33,150,243,.4);
  height: 8rem;
  text-align: center;
}

.infos {
  border: none;
  padding: 1.5rem;
  text-align: center;
}

.titles {
  color: rgb(38 50 56);
  font-weight: 600;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.footers {
  padding: 0.75rem;
  border: 1px solid rgb(236 239 241);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: rgba(0, 140, 255, 0.082);
}

.tags {
  font-weight: 300;
  font-size: .75rem;
}

.actions {
  user-select: none;
  border: none;
  outline: none;
  box-shadow: 0 4px 6px -1px rgba(33,150,243,.4), 0 2px 4px -2px rgba(33,150,243,.4);
  color: white;
  text-transform: uppercase;
  font-weight: 700;
  font-size: .75rem;
  padding: 0.75rem 1.5rem;
  background-color: rgb(33 150 243);
  border-radius: 0.5rem;
}

.vacations {
  display: flex;
  justify-content: center;
  color: white;
}

.h2-white {
  color: white;
}

.btn-top-right {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background-color: white;
  border: none;
  border-radius: 50%;
  padding: 0.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  cursor: pointer;
  font-size: 1rem;
  transition: transform 0.2s ease;
}

.btn-top-right:hover {
  transform: scale(1.1);
}

.table_component {
  overflow: auto;
  width: 100%;
}

.table_component table {
  border: 1px solid #dededf;
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  text-align: left;
}

.table_component th,
.table_component td {
  border: 1px solid #dededf;
  padding: 5px;
}

.table_component th {
  background-color: #eceff1;
  color: black;
}

.table_component td {
  background-color: white;
  color: black;
}

.caption-title {
  font-weight: bold;
}

.modal__content {
  margin: 50px;
}

.form-group {
  margin: 2rem 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.sectionPicker {
	padding: 2px 10px 0 22px;
}

/* Ocultar scrollbar */
::-webkit-scrollbar {
  width: 0px;
}
::-webkit-scrollbar-track {
  display: none;
}

/* 🌐 Estilos globales para v-calendar */
::v-deep(.custom-calendar.vc-container) {
  --day-border: 1px solid #b8c2cc;
  --day-border-highlight: 1px solid #b8c2cc;
  --day-width: 90px;
  --day-height: 90px;
  --weekday-bg: #f8fafc;
  --weekday-border: 1px solid #eaeaea;

  border-radius: 0;
  width: 100%;
}

::v-deep(.custom-calendar.vc-container .vc-header) {
  background-color: #f1f5f8;
  padding: 10px 0;
}

::v-deep(.custom-calendar.vc-container .vc-weeks) {
  padding: 0;
}

::v-deep(.custom-calendar.vc-container .vc-weekday) {
  background-color: var(--weekday-bg);
  border-bottom: var(--weekday-border);
  border-top: var(--weekday-border);
  padding: 5px 0;
}

::v-deep(.custom-calendar.vc-container .vc-day) {
  text-align: left;
  height: var(--day-height);
  min-width: var(--day-width);
  background-color: white;
}

::v-deep(.custom-calendar.vc-container .vc-day-content) {
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 0;
  padding-left: 10px;
}

::v-deep(.custom-calendar.vc-container .vc-highlight) {
	padding-left: 30px;
}
::v-deep(.custom-calendar.vc-container .vc-day-box-right-center) {
  display: block;
}
::v-deep(.custom-calendar.vc-container .vc-highlight-base-start) {
	width: 100% !important;
}

::v-deep(.custom-calendar.vc-container .vc-day-box-left-center) {
  display: block;
}
::v-deep(.custom-calendar.vc-container .vc-highlight-base-end) {
	width: 10% !important;
  border-radius:0px 15px 19px 0px !important;
}
::v-deep(.custom-calendar.vc-container .vc-day-box-center-center) {
  justify-content: center;
  align-items: center;
  transform-origin: 50% 50%;
  display: block;
}

::v-deep(.custom-calendar.vc-container .vc-day.weekday-1),
::v-deep(.custom-calendar.vc-container .vc-day.weekday-7) {
  background-color: #eff8ff;
}

::v-deep(.custom-calendar.vc-container .vc-day:not(.on-bottom)) {
  border-bottom: var(--day-border);
}

::v-deep(.custom-calendar.vc-container .vc-day.weekday-1:not(.on-bottom)) {
  border-bottom: var(--day-border-highlight);
}

::v-deep(.custom-calendar.vc-container .vc-day:not(.on-right)) {
  border-right: var(--day-border);
}

::v-deep(.custom-calendar.vc-container .vc-day-dots) {
  margin-bottom: 5px;
}
</style>
