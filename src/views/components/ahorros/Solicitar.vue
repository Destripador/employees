<template id="content">
	<NcAppContent v-if="loading == true" name="Loading">
		<NcLoadingIcon />
	</NcAppContent>
	<NcAppContent v-else name="Loading">
		<div class="main-content">
			<div v-if="userdata.state == 1" class="pack_card">
				<div class="pack_name">
					Ya puedes solicitar tu ahorro!
				</div>

				<p class="description">
					Solicita hasta el <strong>90% del saldo acumulado</strong> de tu Fondo de Ahorro, considerando tanto la aportación del empleado como la del empleador.
				</p>

				<div class="bottom">
					<div class="price_container">
						<span class="devise">$</span>
						<span class="price">{{ employee[0].Fondo_ahorro }}</span>
						<span class="date"> <small><strong>mi ahorro</strong></small></span>
					</div>
					<NcButton aria-label="center (default)"
						type="primary"
						wide
						@click="showSolicitud()">
						<template #icon>
							<Check :size="20" />
						</template>
						Generar solicitud
					</NcButton>
				</div>
			</div>
			<div v-if="userdata.state == 2">
				<NcNoteCard type="success" text="Tu solicitud ha sido enviada, espera la respuesta." />
			</div>
			<div v-if="userdata.state == 0 || !userdata.state">
				<NcNoteCard type="info" text="Tu perfil esta en modo solo lectura." />
			</div>
		</div>

		<NcModal
			v-if="modal && userdata.state == 1"
			ref="modalRef"
			size="large"
			name="Solicitud"
			@close="showSolicitud()">
			<div class="modal__content">
				<div class="semi-container">
					<div class="content-box">
						<div style="padding: 25px; display: flex;">
							<div>
								<ul class="list-style">
									<li>
										<p class="mb-0">
											El día 30 de junio se realizará el depósito correspondiente al Préstamo del Fondo de Ahorro, el cual no genera intereses.
										</p>
									</li>
									<li>
										<p class="mb-0">
											El monto disponible podrá ser hasta el 90% del total acumulado a la fecha, considerando tanto la aportación del trabajador como la del empleador.
										</p>
									</li>
									<li>
										<p class="mb-0">
											En este formulario, la cantidad debe registrarse en pesos. Si deseas solicitar un porcentaje específico, puedes indicarlo en el campo de notas.
										</p>
									</li>
									<li>
										<p class="mb-0">
											<strong>Importante:</strong> Verifica cuidadosamente la información antes de enviar tu solicitud, ya que no podrá ser cancelada ni modificada.
										</p>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="mymoney">
						El 90% de tu ahorro es: {{ aproxFormateado }}
					</div>
					<div class="form-box">
						<NcTextField
							:model-value="cantidadFormateada"
							label="Cantidad a solicitar"
							trailing-button-icon="close"
							:show-trailing-button="cantidad !== ''"
							@trailing-button-click="limpiarCantidad"
							@input="actualizarCantidad"
							@keypress="soloNumerosYPunto">
							<template #icon>
								<CurrencyUsd :size="20" />
							</template>
						</NcTextField>
						<br>
						<NcTextArea v-model="notas"
							label="Notas"
							placeholder="Notas" />
						<br>
						<div>
							<div class="center">
								<NcCheckboxRadioSwitch
									:checked.sync="acept_terms"
									value="true"
									name="acept_terms">
									<strong style="font-size: 12px; margin-left: 10px;">
										Autorizo a Gossler, S.C. a descontar de mi ahorro el monto correspondiente según las condiciones del préstamo.
									</strong>
								</NcCheckboxRadioSwitch>
							</div>
							<div class="center">
								<NcButton
									class="center"
									style="margin-top: 20px;"
									text="Enviar Solicitud"
									type="primary"
									@click="EnviarSolicitud()">
									Enviar solicitud
								</NcButton>
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

// iconos
// import ClockAlertOutline from 'vue-material-design-icons/ClockAlertOutline.vue'
import CurrencyUsd from 'vue-material-design-icons/CurrencyUsd.vue'
import Check from 'vue-material-design-icons/Check.vue'

import {
	NcLoadingIcon,
	NcAppContent,
	NcNoteCard,
	NcButton,
	NcModal,
	NcTextField,
	NcTextArea,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'
export default {
	name: 'Solicitar',

	components: {
		NcLoadingIcon,
		NcAppContent,
		NcNoteCard,
		Check,
		CurrencyUsd,
		NcButton,
		NcModal,
		NcTextField,
		NcTextArea,
		NcCheckboxRadioSwitch,
		// ClockAlertOutline,
	},

	inject: ['employee'],

	data() {
		return {
			loading: true,
			userdata: [],
			options: [],
			modal: false,
			modalRef: ref(null),
			cantidad: '',
			notas: '',
			aproxValor: 0, // número real para validaciones
			aproxFormateado: '',
			acept_terms: [],
		}
	},
	computed: {
		cantidadFormateada() {
			if (this.cantidad === '') return ''
			const partes = this.cantidad.toString().split('.')
			partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')
			return partes.join('.')
		},
	},

	mounted() {
		this.aproxValor = Number(this.employee[0].Fondo_ahorro.replace(',', '')) * 0.9
		this.aproxFormateado = Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(this.aproxValor)
		this.getAll()
	},
	methods: {
		async getAll() {
			this.loading = true
			try {
				await axios.post(generateUrl('/apps/empleados/GetInfoAhorro'),
					{
						Id_user: this.employee[0].Id_empleados,
					})
					.then(
						(response) => {
							this.userdata = response.data[0]
							this.loading = false
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		showSolicitud() {
			this.modal = !this.modal
		},
		actualizarCantidad(event) {
			let valor = event.target.value.replace(/,/g, '')

			if (!/^\d*\.?\d*$/.test(valor)) {
				valor = this.cantidad
			}

			if (Number(valor) > this.aproxValor) {
				valor = this.cantidad
			}

			this.cantidad = valor
		},
		soloNumerosYPunto(event) {
			const char = String.fromCharCode(event.which)

			if (!/[0-9.]/.test(char)) {
				event.preventDefault()
			}

			if (char === '.' && this.cantidad.includes('.')) {
				event.preventDefault()
			}

			const posibleValor = (this.cantidad + char).replace(/,/g, '')
			if (Number(posibleValor) > this.aproxValor) {
				event.preventDefault()
			}
		},
		limpiarCantidad() {
			this.cantidad = ''
		},
		EnviarSolicitud() {
			// eslint-disable-next-line no-console
			console.log(this.acept_terms)
			if (this.acept_terms[0] === 'true' && this.cantidad !== '') {
				axios.post(
					generateUrl('/apps/empleados/EnviarSolicitud'),
					{
						id_ahorro: this.userdata.id_ahorro,
						cantidad_solicitada: this.cantidad,
						nota: this.notas,
					},
				).then(
					(response) => {
						this.getAll()
						this.modal = false
						this.cantidad = ''
						this.notas = ''
						this.aproxValor = 0
						this.aproxFormateado = ''
						this.acept_terms = []
					},
					(err) => {
						showError(Promise.reject(err))
					},
				)
				/* .catch((err) => {
                            //console.log(Promise.reject(err))
                        }) */
			} else {
				showError(t('ahorrosgossler', 'X - Verifique el formulario'))
			}
		},
	},
}
</script>

<style>
.pack_card {
  position: relative;
  display: flex;
  flex-direction: column;
  border-radius: 0.5rem;
  border: 2px solid rgb(223, 223, 223);
  padding: 1.5rem 1rem 1rem 1rem;
  margin-top: 1rem;
  background-color: #fff;
  max-width: 100%;
}

.banner {
  position: absolute;
  left: 0px;
  right: 0px;
  top: -2rem;
  display: flex;
  justify-content: center;
}

.banner_tag {
  display: flex;
  height: 1.5rem;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  background-color: rgb(99 102 241);
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  line-height: 1rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #fff;
}

.pack_name {
  margin-bottom: 0.5rem;
  text-align: center;
  font-size: 1.5rem;
  line-height: 2rem;
  font-weight: 700;
  color: rgb(31 41 55 );
}

.description {
  margin: 0 auto 2rem auto;
  text-align: center;
  color: rgb(107 114 128 );
}

.bottom {
  margin-top: auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.price_container {
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 0.25rem;
}

.devise {
  align-self: flex-start;
  color: rgb( 75 85 99 );
}

.price {
  font-size: 2.25rem;
  line-height: 2.5rem;
  font-weight: 700;
  color: rgb(31 41 55 );
}

.date {
  color: rgb(107 114 128 );
}

.main-content {
	margin-top: 50px;
	margin-left: 20px;
	margin-right: 20px;
}

.container{
        padding-left: 50px;
        padding-right: 24px;
    }
    .semi-container{
        padding-left: 24px;
        padding-right: 24px;
    }
    .board-title {
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
    .content-box{
		margin-top: 15px;
		margin-bottom: 10px;
        -moz-border-radius:50px;
        -webkit-border-radius:50px;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    .list-style {
        list-style-type: square;
    }
	.mymoney {
	padding: 10px;
	margin: 10px 10px 10px 10px;
	text-align: center;
	font-size: 1.5rem;
	font-weight: 700;
	}
	.center {
		display: flex;
		justify-content: center;
		align-items: center;
	}
	.form-box {
		margin: 10px 10px 10px 10px;
		padding: 10px;
	}
</style>
