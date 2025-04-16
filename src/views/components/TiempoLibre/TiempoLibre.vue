<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<div class="text-center section">
				<section class="layout">
					<div class="grow2">
						{{ date.start }}
						<DatePicker :key="calendarKey"
							v-model="date"
							is-expanded
							is-range
							:min-date="FechaInitial"
							:max-date="FechaMaxima"
							@dayclick="calcularFechaMaxima()"
							@input="onRangeSelected"
							@drag="dragValue = $event" />
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
		<NcModal
			v-if="modal"
			name="Name inside modal"
			@close="closeModal">
			<div class="table_component" role="region" tabindex="0">
				<div class="modal__content">
					<form class="bg-white shadow-md rounded px-8 pt-6 pb-8" @submit.prevent>
						<div class="mb-4">
							<span class="block text-gray-600 text-sm text-left font-bold mb-2">Select Range</span>
							<DatePicker
								v-model="date"
								mode="date"
								is-range>
								<template #default="{ inputValue, inputEvents, isDragging }">
									<div class="flex flex-col sm:flex-row justify-start items-center">
										<div class="relative flex-grow">
											<svg
												class="text-gray-600 w-4 h-full mx-2 absolute pointer-events-none"
												fill="none"
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												viewBox="0 0 24 24"
												stroke="currentColor">
												<path
													d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
											</svg>
											<input
												class="flex-grow pl-8 pr-2 py-1 bg-gray-100 border rounded w-full"
												:class="isDragging ? 'text-gray-600' : 'text-gray-900'"
												:value="inputValue.start"
												v-on="inputEvents.start">
										</div>
										<span class="flex-shrink-0 m-2">
											<svg
												class="w-4 h-4 stroke-current text-gray-600"
												viewBox="0 0 24 24">
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M14 5l7 7m0 0l-7 7m7-7H3" />
											</svg>
										</span>
										<div class="relative flex-grow">
											<svg
												class="text-gray-600 w-4 h-full mx-2 absolute pointer-events-none"
												fill="none"
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												viewBox="0 0 24 24"
												stroke="currentColor">
												<path
													d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
											</svg>
											<input
												class="flex-grow pl-8 pr-2 py-1 bg-gray-100 border rounded w-full"
												:class="isDragging ? 'text-gray-600' : 'text-gray-900'"
												:value="inputValue.end"
												v-on="inputEvents.end">
										</div>
									</div>
								</template>
							</DatePicker>
						</div>
					</form>
				</div>
			</div>
		</NcModal>

		<!-- INICIO MODAL ANIVERSARIOS SOLO INFO -->
		<NcModal
			v-if="ModalAniversario"
			ref="modalRef"
			size="large"
			name="Tabla de aniversarios"
			@close="closeModal">
			<div class="table_component" role="region" tabindex="0">
				<div class="modal__content">
					<div class="layout">
						<div class="grow3">
							<table>
								<caption>
									<span class="caption-title">Tabla de aniversarios</span>
								</caption>
								<thead>
									<tr>
										<th>Numero Aniversario</th>
										<th>Dias libres</th>
										<!--th>opciones</th-->
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item) in Aniversarios" :key="item.id_aniversario">
										<td>
											{{ item.numero_aniversario }}
										</td>
										<td>
											{{ item.dias }}
										</td>
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
			FechaInitial: new Date(),
			FechaMaxima: null,
			modal: false,
			ModalAniversario: false,
			Ausencias: [],
			Aniversarios: [],
			dragValue: null,
			calendarKey: 0,
		}
	},

	mounted() {
		this.GetAusencias()
	},
	methods: {
		onRangeSelected(range) {
			this.modal = true
		},
		showAniversarioModal() {
			this.getAniversarios()
		},
		closeModal() {
			this.modal = false
			this.ModalAniversario = false
			this.dragValue = null

			this.calendarKey++

			this.date = {
				start: null,
				end: null,
			}
			this.FechaInitial = new Date()
			this.FechaMaxima = null
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
			this.FechaInitial = this.dragValue.start

			const fechaInicio = this.FechaInitial
			const diasTotales = Math.floor(this.Ausencias.dias_disponibles)
			const horasExtra = (this.Ausencias.dias_disponibles % 1) * 24

			let diasContados = 0
			const fechaMax = new Date(fechaInicio)

			while (diasContados < diasTotales) {
				fechaMax.setDate(fechaMax.getDate() + 1)
				const diaSemana = fechaMax.getDay()
				// 0 = domingo, 6 = sábado
				if (diaSemana !== 0 && diaSemana !== 6) {
					diasContados++
				}
			}

			// Suma las horas extra, si aplica
			fechaMax.setHours(fechaMax.getHours() + horasExtra)

			this.FechaMaxima = fechaMax
		},
	},
}
</script>

<style>
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
  box-shadow: 0 10px 15px -3px rgba(33,150,243,.4),0 4px 6px -4px rgba(33,150,243,.4);
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
  letter-spacing: 0;
  line-height: 1.375;
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
  display: block;
}

.actions {
  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;
  border: none;
  outline: none;
  box-shadow: 0 4px 6px -1px rgba(33,150,243,.4),0 2px 4px -2px rgba(33,150,243,.4);
  color: rgb(255 255 255);
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
    height: 100%;
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    border-spacing: 1px;
    text-align: left;
}

.table_component th {
    border: 1px solid #dededf;
    background-color: #eceff1;
    color: #000000;
    padding: 5px;
}

.table_component td {
    border: 1px solid #dededf;
    background-color: #ffffff;
    color: #000000;
    padding: 5px;
}

.caption-title {
    font-weight: bold;
}

.caption-buttons {
    float: right;
    padding-bottom: 6px;
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
