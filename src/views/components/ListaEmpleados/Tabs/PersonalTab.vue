<template>
	<div class="top">
		<!-- Direccion -->
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
		<!-- Rfc -->
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
		<!-- Imss -->
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
		<!-- Curp -->
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
		<!-- Fecha de Nacimiento -->
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
		<!-- Correo -->
		<div class="external-label">
			<label for="Correo_contacto" class="labeltype">
				<EmailOutline :size="20" />
				Correo
			</label>
			<input id="Correo_contacto"
				v-model="Correo_contacto"
				type="email"
				:disabled="!show"
				class="inputtype">
		</div>
		<br>
		<!-- Estado Civil -->
		<div class="label-input-trabajo">
			<NcSelect v-model="Estado_civil"
				class="select"
				:disabled="!show"
				input-label="Estado Civil"
				:options="EstadoCiviloptions" />
		</div>
		<!-- Genero -->
		<div class="label-input-trabajo">
			<NcSelect v-model="Genero"
				class="select"
				:disabled="!show"
				input-label="Genero"
				:options="GeneroOptions" />
		</div>
		<br>
		<!-- Contacto de Emergencia -->
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
		<!-- Boton para aplicar cambios -->
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
		data(news) {
			if (news) {
				this.setAttr(news)
			}
		},
	},

	mounted() {
		this.setAttr(this.data)
	},

	methods: {
		setAttr(data) {
			this.Direccion = this.checknull(data.Direccion)
			this.Estado_civil = this.checknull(data.Estado_civil)
			this.Telefono_contacto = this.checknull(data.Telefono_contacto)
			this.Rfc = this.checknull(data.Rfc)
			this.Imss = this.checknull(data.Imss)
			this.Contacto_emergencia = this.checknull(data.Contacto_emergencia)
			this.Numero_emergencia = this.checknull(data.Numero_emergencia)
			this.Curp = this.checknull(data.Curp)
			this.Fecha_nacimiento = this.checknull(data.Fecha_nacimiento)
			this.Correo_contacto = this.checknull(data.Correo_contacto)
			this.Genero = this.checknull(data.Genero)
		},

		checknull(value) {
			return value === null ? '' : value
		},

		async CambiosPersonal() {
			try {
				await axios.post(generateUrl('/apps/empleados/CambiosPersonal'), {
					Id_empleados: this.data.Id_empleados,
					Direccion: this.checknull(this.Direccion),
					Estado_civil: this.checknull(this.Estado_civil),
					Telefono_contacto: this.checknull(this.Teléfono_contacto),
					Rfc: this.checknull(this.Rfc),
					Imss: this.checknull(this.Imss),
					Contacto_emergencia: this.checknull(this.Contacto_emergencia),
					Numero_emergencia: this.checknull(this.Numero_emergencia),
					Curp: this.checknull(this.Curp),
					Fecha_nacimiento: this.checknull(this.Fecha_nacimiento),
					Correo_contacto: this.checknull(this.Correo_contacto),
					Genero: this.checknull(this.Genero),
				})
				this.$root.$emit('getall')
				this.$root.$emit('show', false)
				showSuccess('Datos actualizados')
			} catch (err) {
				showError(`Se ha producido una excepcion [03] [${err}]`)
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
	display: flex;
	align-items: center;
	gap: 10px;
	margin-top: 3px;
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
.emergency-contact {
	border: 1px solid rgba(0,0,0,0.17);
}
.top {
	margin-top: 20px;
}
</style>
