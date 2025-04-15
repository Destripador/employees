<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<div class="text-center section">
				<section class="layout">
					<div class="grow2">
						<DatePicker v-model="date"
							is-expanded
							is-range
							@input="onRangeSelected" />
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
											Tengo dudas
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
									How to make this material card ?
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
			@close="closeModal" />
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
							<div class="info-vacaciones">
								<h2>🏖️ Tabla de Vacaciones – Información para Empleados</h2>
								<p>
									Esta tabla te muestra <strong>cuántos días de vacaciones te corresponden</strong> según los años que lleves trabajando en la empresa. Es una guía basada en la <strong>Ley Federal del Trabajo</strong>, reformada en 2023.
								</p>

								<h3>🤔 Preguntas frecuentes</h3>

								<h4>¿Desde cuándo tengo derecho a vacaciones?</h4>
								<p>
									Desde tu primer año trabajado completo ya puedes disfrutar de vacaciones. El mínimo son <strong>12 días</strong>, y va aumentando cada año.
								</p>

								<h4>¿Cómo se cuentan los días?</h4>
								<p>
									Los días que aparecen en la tabla son <strong>días hábiles</strong>. No cuentan sábados, domingos ni feriados.
								</p>

								<h4>¿Puedo dividir mis vacaciones?</h4>
								<p>
									Sí. Por ley, al menos la mitad debe tomarse de corrido, pero puedes hablar con Recursos Humanos para distribuir los días según tus necesidades y las de tu equipo.
								</p>
								<h4>¿Qué pasa si no tomo mis vacaciones?</h4>
								<p v-if="configuraciones.acumular_vacaciones === 'true'">
									Las vacaciones no tomadas <strong>no se pierden</strong>, pero es importante usarlas. Descansar es un derecho y también ayuda a tu salud y desempeño.
								</p>
								<p v-else>
									Si no tomas tus vacaciones, <strong>se pierden</strong>. Es importante que las uses para cuidar tu salud y bienestar.
								</p>

								<h4>¿Puedo pedir más días de vacaciones?</h4>
								<p>
									Sí, aunque lo establecido por ley es el mínimo, la empresa puede ofrecer más días como <strong>prestación adicional</strong>. Revisa tu contrato o habla con RH.
								</p>

								<h3>✅ Recomendación</h3>
								<p>
									Consulta esta tabla cada vez que cumplas un aniversario laboral. Así podrás <strong>planear tus descansos con anticipación</strong> y disfrutar al máximo tus días libres.
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</NcModal>
	</NcAppContent>
</template>

<script>
// Importing necessary components

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
			date: new Date(),
			modal: false,
			ModalAniversario: false,
			Ausencias: [],
			Aniversarios: [],
		}
	},

	mounted() {
		this.GetAusencias()
	},

	methods: {
		onRangeSelected(range) {
			// eslint-disable-next-line no-console
			console.log('Rango seleccionado:', range)
			this.modal = true
		},
		showAniversarioModal() {
			this.getAniversarios()
		},
		closeModal() {
			this.modal = false
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
