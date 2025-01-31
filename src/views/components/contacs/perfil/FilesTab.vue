<template>
	<div class="top">
		<div>
			<h2>📂 Lista de Archivos</h2>
			<p v-if="files.length === 0">No hay archivos disponibles.</p>
			<ul v-else>
				<li v-for="file in files" :key="file.id">
					{{ file.name }}
					<a :href="file.downloadUrl" download>{{file}}</a>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
import { getClient, defaultRootPath } from '@nextcloud/files/dav'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import 'vue-nav-tabs/themes/vue-tabs.css'
import axios from '@nextcloud/axios'

// ICONOS

import {
} from '@nextcloud/vue'

export default {
	name: 'FilesTab',

	components: {
		/*
		Badgeaccountoutline,
		MapMarkerOutline,
		CakeVariantOutline,
		EmailOutline,
		NcButton,
		NcSelect,
        */
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
		config: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			files: [],
			nextcloudVersion: '',
			filesPath: '',
			Direccion: '',
			Estado_civil: '',
			Telefono_contacto: '',
			Rfc: '',
			Imss: '',
			Contacto_emergencia: '',
			Numero_emergencia: '',
			Curp: '',
			Fecha_nacimiento: '',
			Correo_contacto: '',
			Genero: '',
			GeneroOptions: ['Masculino', 'Femenino'],
			EstadoCiviloptions: ['soltero', 'casado', 'divorciado', 'viudo', 'union libre'],
		}
	},

	watch: {
		data(news, old) {
			if (news) {
				this.setAttr(
					news.Direccion,
					news.Estado_civil,
					news.Telefono_contacto,
					news.Rfc,
					news.Imss,
					news.Contacto_emergencia,
					news.Numero_emergencia,
					news.Curp,
					news.Fecha_nacimiento,
					news.Correo_contacto,
					news.Genero,
				)
			}
		},
	},

	mounted() {
		this.fetchFiles()
		this.nextcloudVersion = this.OC.config.versionstring || 'Desconocida'
		this.filesPath = this.OCA.Files || 'No disponible'
		this.setAttr(
			this.data.Direccion,
			this.data.Estado_civil,
			this.data.Telefono_contacto,
			this.data.Rfc,
			this.data.Imss,
			this.data.Contacto_emergencia,
			this.data.Numero_emergencia,
			this.data.Curp,
			this.data.Fecha_nacimiento,
			this.data.Correo_contacto,
			this.data.Genero,
		)
	},

	methods: {
		setAttr(Direccion, EstadoCivil, TelefonoContacto, Rfc, Imss, ContactoEmergencia, NumeroEmergencia, Curp, FechaNacimiento, CorreoContacto, Genero) {
			this.Direccion = this.checknull(Direccion)
			this.Estado_civil = this.checknull(EstadoCivil)
			this.Telefono_contacto = this.checknull(TelefonoContacto)
			this.Rfc = this.checknull(Rfc)
			this.Imss = this.checknull(Imss)
			this.Contacto_emergencia = this.checknull(ContactoEmergencia)
			this.Numero_emergencia = this.checknull(NumeroEmergencia)
			this.Curp = this.checknull(Curp)
			this.Fecha_nacimiento = this.checknull(FechaNacimiento)
			this.Correo_contacto = this.checknull(CorreoContacto)
			this.Genero = this.checknull(Genero)
		},

		async fetchFiles() {
			try {
				const client = getClient()
				const path = '/EMPLEADOS/' // Cambia esto según tu directorio real
				const fullPath = `${defaultRootPath}${path}`

				// eslint-disable-next-line no-console
				console.log('📂 Consultando ruta:', fullPath)

				const response = await client.getDirectoryContents(fullPath, { details: true })

				// eslint-disable-next-line no-console
				console.log('🔍 Respuesta de getDirectoryContents:', response)

				if (response.data && Array.isArray(response.data)) {
					this.files = response.data.map((file) => ({
						id: file.id || file.etag,
						name: file.basename || file.name,
						size: file.size || 0,
						downloadUrl: file.href,
					}))
				} else {
					console.error('⚠️ La respuesta NO contiene un array en `data`:', response)
					this.files = []
				}
			} catch (error) {
				console.error('❌ Error al obtener los archivos:', error)
			}
		},

		checknull(satanizar) {
			if (!satanizar && satanizar === null) {
				return ''
			}
			return satanizar
		},

		async CambiosPersonal() {
			try {
				await axios.post(generateUrl('/apps/empleados/CambiosPersonal'),
					{
						Id_empleados: this.data.Id_empleados,
						Direccion: this.checknull(this.Direccion),
						Estado_civil: this.checknull(this.Estado_civil),
						Telefono_contacto: this.checknull(this.Telefono_contacto),
						Rfc: this.checknull(this.Rfc),
						Imss: this.checknull(this.Imss),
						Contacto_emergencia: this.checknull(this.Contacto_emergencia),
						Numero_emergencia: this.checknull(this.Numero_emergencia),
						Curp: this.checknull(this.Curp),
						Fecha_nacimiento: this.checknull(this.Fecha_nacimiento),
						Correo_contacto: this.checknull(this.Correo_contacto),
						Genero: this.checknull(this.Genero),
					})
					.then(
						(response) => {
							this.$root.$emit('getall')
							this.$root.$emit('show', false)
							showSuccess('Datos actualizados')
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
.wrapper {
	display: flex;
	gap: 4px;
	align-items: flex-end;
	flex-wrap: wrap;
}
.external-label {
  display: flex; /* Activa Flexbox */
  align-items: center; /* Alinea verticalmente */
  gap: 10px; /* Espacio entre el label y el input */
  margin-top: 10px;;
}

.labeltype {
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 5px; /* Espacio entre el icono y el texto */
  min-width: 150px; /* Define un ancho mínimo para la etiqueta */
}

.inputtype {
  flex: 1; /* Hace que el input ocupe el espacio restante */
  height: 40px;
  padding: 8px 12px;
  font-size: 14px;
  border-radius: 5px;
  border: 1px solid #ccc;
  width: 100%;
}
.emergency-contact {
	border: 1px solid rgba(0,0,0,0.17);
}
</style>
