<!-- eslint-disable vue/require-v-for-key -->
<template>
	<div>
		<div class="container">
			<div class="flex">
				<div class="mitad">
					<div>
						<div class="table_component" role="region" tabindex="0">
							<table>
								<caption>
									<span class="caption-title">Tabla de aniversarios</span>
									<span class="caption-buttons">
										<NcActions>
											<NcActionButton :close-after-click="true" @click="showAddAniversario">
												<template #icon>
													<Plus :size="20" />
												</template>
												Crear nuevo aniversario
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="$refs.file.click()">
												<template #icon>
													<Import :size="20" />
												</template>
												Importar listado
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="Exportar()">
												<template #icon>
													<Export :size="20" />
												</template>
												Exportar listado / plantilla
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="vaciar()">
												<template #icon>
													<Delete :size="20" />
												</template>
												Vaciar tabla aniversarios
											</NcActionButton>
										</NcActions>
									</span>
								</caption>
								<thead>
									<tr>
										<th>Numero Aniversario</th>
										<th>Dias libres</th>
										<!--th>opciones</th-->
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item) in Aniversarios" v-bind="$attrs">
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
					</div>
				</div>
				<div class="mitad">
					<h1 class="text-2xl font-bold">
						Tipos de ausencias
					</h1>
				</div>
			</div>
		</div>
		<NcModal
			v-if="modalAddAniversario"
			ref="modalRef"
			name="agregar"
			@close="closeModalAniversario">
			<div class="modal__content">
				<h2>Agregue informacion del nuevo aniversario</h2>
				<div class="form-group">
					<NcTextField label="numero de aniversario" :value.sync="NumeroAniversario" />
				</div>
				<div class="form-group">
					<NcTextField label="Dias" :value.sync="DiasAniversario" />
				</div>

				<NcButton
					:disabled="!NumeroAniversario || !DiasAniversario"
					@click="AgregarNuevoAniversario">
					Submit
				</NcButton>
			</div>
		</NcModal>
		<input
			ref="file"
			type="file"
			style="display: none"
			accept=".xlsx"
			@change="importar()">
	</div>
</template>

<script>
// global registration
// import { VueTabs, VTab } from 'vue-nav-tabs/dist/vue-tabs.js'
// import 'vue-nav-tabs/themes/vue-tabs.css'

// iconos
// import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import Delete from 'vue-material-design-icons/Delete.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Import from 'vue-material-design-icons/Import.vue'
import Export from 'vue-material-design-icons/Export.vue'
// import WalletPlus from 'vue-material-design-icons/WalletPlus'
// import Cog from 'vue-material-design-icons/Cog.vue'
// import Check from 'vue-material-design-icons/Check'

// imports
import {
	NcActions,
	NcActionButton,
	NcModal,
	NcTextField,
	NcButton,
} from '@nextcloud/vue'
import { ref } from 'vue'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
	name: 'TiempoLaboralSettings',
	components: {
		NcActions,
		NcActionButton,
		NcModal,
		NcTextField,
		NcButton,
		Plus,
		Import,
		Export,
		Delete,
	},

	data() {
		return {
			modalAddAniversario: false,
			modalRef: ref(null),

			Aniversarios: [],
			NumeroAniversario: null,
			DiasAniversario: null,
		}
	},

	mounted() {
		this.getAniversarios()
	},

	methods: {

		showAddAniversario() {
			this.modalAddAniversario = true
		},
		closeModalAniversario() {
			this.modalAddAniversario = false
		},
		async getAniversarios() {
			// this.loading = true
			this.closeModalAniversario()
			this.DiasAniversario = null
			this.NumeroAniversario = null
			try {
				await axios.get(generateUrl('/apps/empleados/Getaniversarios'))
					.then(
						(response) => {
							this.Aniversarios = response.data
							// eslint-disable-next-line no-console
							console.log(this.Aniversarios)
							// this.loading = false
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},
		async AgregarNuevoAniversario() {
			try {
				await axios.post(generateUrl('/apps/empleados/AgregarNuevoAniversario'), {
					numero_aniversario: this.NumeroAniversario,
					fecha_de: '',
					fecha_hasta: '',
					dias: this.DiasAniversario,
				})
				showSuccess('Nota ha sido actualizada')
				this.getAniversarios()
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
		async vaciar() {
			// this.loading = true
			this.closeModalAniversario()
			try {
				await axios.get(generateUrl('/apps/empleados/VaciarAniversarios'))
					.then(
						(response) => {
							this.getAniversarios()
							showSuccess('Tabla de aniversarios vaciada')
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [' + err + ']'))
			}
		},
		Exportar() {
			axios.get(
				generateUrl('/apps/empleados/ExportListAniversarios'),
				{
					responseType: 'blob',
				},
			).then(
				(response) => {
					const url = URL.createObjectURL(new Blob([response.data], {
						type: 'application/vnd.ms-excel',
					}))

					const link = document.createElement('a')
					link.href = url
					link.setAttribute('download', 'aniversarios.xlsx')
					document.body.appendChild(link)
					link.click()
				},
				(err) => {
					showError(t('ahorrosgossler', 'Se ha producido un error ' + err + ', reporte al administrador'))
					this.exportardata = false
				},
			)
		},
		async importar() {
			// eslint-disable-next-line no-console
			console.log(this.$refs.file.files[0])
			const formData = new FormData()
			formData.append('fileXLSX', this.$refs.file.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/ImportListAniversarios'), formData,
					{
						headers: {
							'Content-Type': 'multipart/form-data',
						},
					})
					.then(
						(response) => {
							this.getAniversarios()
							showSuccess(t('empleados', 'Se actualizo la base de datos exitosamente'))
						},
						(err) => {
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
.flex {
    display: flex;
    justify-content: space-between;
}
.mitad {
      flex: 1;
      padding: 20px;
      border: 1px solid #000;
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
