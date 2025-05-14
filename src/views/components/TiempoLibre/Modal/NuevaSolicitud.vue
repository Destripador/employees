<template id="content">
	<div
		class="table_component"
		role="region">
		<div class="modal__content">
			<form class="bg-white shadow-md rounded px-8 pt-6 pb-8">
				<div class="table_component" role="region" tabindex="0">
					<div>
						<div class="table_component" role="region" tabindex="0">
							<table>
								<thead>
									<tr>
										<th>
											Selecciona el tipo de ausencia
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<NcSelect id="id"
												v-model="AusenciaSeleccionada"
												:no-wrap="true"
												class="hide-label"
												:options="TipoAusencias"
												:keep-open="false"
												input-label="Tipo de ausencia" />
										</td>
									</tr>
								</tbody>
							</table>
							<table v-if="AusenciaSeleccionada && AusenciaSeleccionada.descripcion">
								<thead>
									<tr>
										<th>
											Detalles
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td v-if="AusenciaSeleccionada && AusenciaSeleccionada.descripcion">
											{{ AusenciaSeleccionada.descripcion }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div v-if="AusenciaSeleccionada && AusenciaSeleccionada.descripcion">
						<div v-if="AusenciaSeleccionada &&
							AusenciaSeleccionada.solicitar_prima_vacacional == 1 &&
							diasSolicitados > TotalDias">
							<NcNoteCard type="info">
								<p>
									No puedes solicitar más días de los disponibles.
								</p>
							</NcNoteCard>
						</div>
						<div v-else>
							<table class="top">
								<caption>DATOS DEL PERIODO</caption>
								<tbody>
									<tr v-if="AusenciaSeleccionada && AusenciaSeleccionada.solicitar_prima_vacacional == 1">
										<td>Dias Disponibles</td>
										<td>
											<span v-if="TotalDias">
												{{ TotalDias }}
											</span>
										</td>
									</tr>
									<tr>
										<td>Dias a tomar</td>
										<td>
											<span class="block text-gray-600 text-sm text-left font-bold mb-2">
												{{ diasSolicitados }}
											</span>
										</td>
									</tr>
									<tr v-if="AusenciaSeleccionada && AusenciaSeleccionada.solicitar_prima_vacacional == 1">
										<td>Dias restantes</td>
										<td>
											<span v-if="RestanteDias">
												{{ RestanteDias }}
											</span>
										</td>
									</tr>
								</tbody>
							</table>
							<table class="top">
								<thead>
									<tr>
										<th>
											Periodo de ausencia
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<span v-if="date">
												Desde: {{ date.start.toLocaleDateString() }}
												-
												Hasta: {{ date.end ? date.end.toLocaleDateString() : 'Indefinido' }}
											</span>
										</td>
									</tr>
								</tbody>
							</table>
							<div v-if="AusenciaSeleccionada && AusenciaSeleccionada.solicitar_archivo">
								<div class="top">
									<NcNoteCard type="info" text="Es necesario subir un archivo que justifique tu ausencia" />
								</div>
								<input ref="fileInput"
									type="file"
									class="file-input"
									multiple
									@change="uploadFile">
								<div
									class="drop-area top"
									@dragover.prevent
									@dragenter.prevent
									@drop.prevent="handleDrop"
									@click="$refs.fileInput.click()">
									Suelta los archivos aquí o haz clic para seleccionar
								</div>
								<div v-if="selectedFiles.length > 0" class="top">
									<div class="table_component" role="region" tabindex="0">
										<table>
											<caption>Archivos seleccionados:</caption>
											<thead>
												<tr>
													<th>Nombre archivo</th>
													<th>Peso</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="(file, index) in selectedFiles" :key="index">
													<td>
														{{ file.name }}
													</td>
													<td>
														{{ file.size < 1024 * 1024 ? (file.size / 1024).toFixed(2) + ' KB' : (file.size / (1024 * 1024)).toFixed(2) + ' MB' }}
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div class="top">
								<NcCheckboxRadioSwitch
									v-if="AusenciaSeleccionada &&
										AusenciaSeleccionada.solicitar_prima_vacacional == 1 &&
										prima == 1"
									v-model="SolicitarPrima">
									Solicitar prima vacacional
								</NcCheckboxRadioSwitch>
							</div>
							<div class="top">
								<NcTextArea v-model="comentarios"
									resize="vertical"
									label="Comentarios"
									placeholder="Comenta tu solicitud (OPCIONAL)"
									helper-text="Comenta tu solicitud (OPCIONAL)" />
							</div>
						</div>
						<div class="top">
							<NcButton variant="secondary" wide>
								<template #icon>
									<Airplane :size="20" />
								</template>
								Enviar
							</NcButton>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script>
import { showError /* showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
// import 'v-calendar/src/styles/base.css'
// import { DatePicker } from 'v-calendar'

// icons
import Airplane from 'vue-material-design-icons/Airplane.vue'
// import CalendarQuestionOutline from 'vue-material-design-icons/CalendarQuestionOutline.vue'

import {
// NcActions,
// NcActionButton,
	NcButton,
	NcSelect,
	NcTextArea,
	NcCheckboxRadioSwitch,
	NcNoteCard,
} from '@nextcloud/vue'
export default {
	name: 'NuevaSolicitud',

	components: {
		NcButton,
		NcSelect,
		NcTextArea,
		NcCheckboxRadioSwitch,
		NcNoteCard,
		// DatePicker,
		// NcActions,
		// NcActionButton,
		Airplane,
		// CalendarQuestionOutline,
	},

	inject: ['employee'],

	props: {
		diasSolicitados: {
			type: Number,
			required: true,
		},
		diasDisponibles: {
			type: String,
			required: true,
		},
		date: {
			type: Object,
			required: true,
			validator: (value) => {
				return value
		&& value.start instanceof Date
		&& (value.end instanceof Date || value.end === null)
			},
		},
		prima: {
			type: Number,
			default: 0,
		},
	},

	data() {
		return {
			TipoAusencias: [],
			AusenciaSeleccionada: null,
			TotalDias: 0,
			RestanteDias: 0,
			comentarios: '',
			SolicitarPrima: false,
			selectedFiles: [],
		}
	},

	mounted() {
		this.TotalDias = parseInt(this.diasDisponibles, 10)
		this.RestanteDias = this.TotalDias - this.diasSolicitados
		this.GetTipoAusencias()
	},

	methods: {
		async GetTipoAusencias() {
			try {
				await axios.get(generateUrl('/apps/empleados/getTipo'))
					.then(
						(response) => {
							this.TipoAusencias = response.data
								.filter(item => !(item.solicitar_prima_vacacional === 1 && this.diasSolicitados > this.TotalDias))
								.map(item => ({
									id: item.id_tipo_ausencia,
									label: item.nombre,
									descripcion: item.descripcion,
									solicitar_archivo: item.solicitar_archivo,
									solicitar_prima_vacacional: item.solicitar_prima_vacacional,
								}))
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},
		handleDrop(event) {
			const files = event.dataTransfer.files
			this.uploadFile({ target: { files } }) // Reusa tu método existente
		},
		uploadFile(event) {
			const files = event.target.files || event.dataTransfer.files
			this.selectedFiles = Array.from(files) // convierte FileList a Array
		},
	},
}
</script>
<style>
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

.table_component caption {
	caption-side: top;
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
.hide-label label {
  display: none !important;
}
.file-input { display: none; }
.drop-area {
	border: 2px dashed #999;
	border-radius: 8px;
	padding: 20px;
	text-align: center;
	color: #666;
	cursor: pointer;
	transition: background-color 0.3s;
}
.drop-area:hover {
	background-color: #f0f0f0;
}

</style>
