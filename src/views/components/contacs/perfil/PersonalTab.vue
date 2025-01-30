<template>
	<div class="top">
		<div class="external-label">
			<label for="Direccion" class="labeltype">
				<MapMarkerOutline :size="20" />
				Direccion
			</label>
			<input id="Direccion"
				v-model="Direccion"
				type="text"
				:disabled="!show"
				class="inputtype">
		</div>
		<div class="external-label">
			<label for="Rfc" class="labeltype">
				<Badgeaccountoutline :size="20" />
				Rfc
			</label>
			<input id="Rfc"
				v-model="Rfc"
				type="text"
				:disabled="!show"
				class="inputtype">
		</div>
		<div class="external-label">
			<label for="Imss" class="labeltype">
				<Badgeaccountoutline :size="20" />
				Imss
			</label>
			<input id="Imss"
				v-model="Imss"
				type="text"
				:disabled="!show"
				class="inputtype">
		</div>
		<div class="external-label">
			<label for="Curp" class="labeltype">
				<Badgeaccountoutline :size="20" />
				Curp
			</label>
			<input id="Curp"
				v-model="Curp"
				type="text"
				:disabled="!show"
				class="inputtype">
		</div>
		<div class="external-label">
			<label for="Fecha_nacimiento" class="labeltype">
				<CakeVariantOutline :size="20" />
				Fech. Nacimiento
			</label>
			<input id="Fecha_nacimiento"
				v-model="Fecha_nacimiento"
				type="date"
				:disabled="!show"
				class="inputtype">
		</div>
		<div class="external-label">
			<label for="Correo_contacto" class="labeltype">
				<EmailOutline :size="20" />
				Correo
			</label>
			<input id="Correo_contacto"
				v-model="Correo_contacto"
				type="mail"
				:disabled="!show"
				class="inputtype">
		</div>
		<br>
		<div class="label-input-trabajo">
			<NcSelect v-model="Estado_civil"
				class="select"
				:disabled="!show"
				input-label="Estado Civil"
				:options="EstadoCiviloptions" />
		</div>
		<div class="label-input-trabajo">
			<NcSelect v-model="Genero"
				class="select"
				:disabled="!show"
				input-label="Genero"
				:options="GeneroOptions" />
		</div>
		<br>
		<div class="emergency-contact">
			<div class="external-label">
				<label for="Telefono_contacto" class="labeltype">
					<Badgeaccountoutline :size="20" />
					Num. Contacto
				</label>
				<input id="Telefono_contacto"
					v-model="Telefono_contacto"
					type="text"
					:disabled="!show"
					class="inputtype">
			</div>
			<div class="external-label">
				<label for="Contacto_emergencia" class="labeltype">
					<Badgeaccountoutline :size="20" />
					Nom. Contacto
				</label>
				<input id="Contacto_emergencia"
					v-model="Contacto_emergencia"
					type="text"
					:disabled="!show"
					class="inputtype">
			</div>
			<div class="external-label">
				<label for="Numero_emergencia" class="labeltype">
					<Badgeaccountoutline :size="20" />
					Num. Contacto
				</label>
				<input id="Numero_emergencia"
					v-model="Numero_emergencia"
					type="text"
					:disabled="!show"
					class="inputtype">
			</div>
			<br>
		</div>
		<br>
		<div class="div-center">
			<NcButton
				v-if="show"
				aria-label="Guardar"
				type="primary"
				@click="CambiosPersonal()">
				Aplicar cambios
			</NcButton>
		</div>
	</div>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import 'vue-nav-tabs/themes/vue-tabs.css'
import axios from '@nextcloud/axios'

// ICONOS
import EmailOutline from 'vue-material-design-icons/EmailOutline.vue'
import Badgeaccountoutline from 'vue-material-design-icons/BadgeAccountOutline.vue'
import MapMarkerOutline from 'vue-material-design-icons/MapMarkerOutline.vue'
import CakeVariantOutline from 'vue-material-design-icons/CakeVariantOutline.vue'

import {
	NcButton,
	NcSelect,
} from '@nextcloud/vue'

export default {
	name: 'PersonalTab',

	components: {
		Badgeaccountoutline,
		MapMarkerOutline,
		CakeVariantOutline,
		EmailOutline,
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
		config: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
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
