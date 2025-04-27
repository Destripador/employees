<template>
	<div class="well">
		<div class="editbutton">
			<NcActions>
				<NcActionButton :close-after-click="true" @click="showEdit">
					<template #icon>
						<LanguageMarkdown :size="20" />
					</template>
					{{ hability }} diseño
				</NcActionButton>
			</NcActions>
		</div>
		<div class="top">
			<div>
				<NcRichText v-if="showMarkdown"
					:class="{'plain-text': !useMarkdown }"
					:text="inputValue"
					:autolink="true"
					:use-markdown="useMarkdown" />
				<!-- Employee Notes -->
				<NcTextArea v-if="!showMarkdown"
					input-class="model"
					class="top"
					label="NOTAS EMPLEADO"
					resize="vertical"
					:disabled="show"
					:value.sync="inputValue" />
			</div>
			<NcButton
				v-if="automaticsave === 'false'"
				aria-label="Guardar nota"
				type="primary"
				@click="guardarNota()">
				Guardar nota
			</NcButton>
			<br>
		</div>
	</div>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import 'vue-nav-tabs/themes/vue-tabs.css'
import axios from '@nextcloud/axios'
import debounce from 'debounce'

// ICONOS
import LanguageMarkdown from 'vue-material-design-icons/LanguageMarkdown.vue'
// import Piggybankoutline from 'vue-material-design-icons/PiggyBankOutline.vue'
// import Calendarrange from 'vue-material-design-icons/CalendarRange.vue'
// import Laptopaccount from 'vue-material-design-icons/LaptopAccount.vue'
// import Bank from 'vue-material-design-icons/Bank.vue'
// import Cash from 'vue-material-design-icons/Cash.vue'

import {
	NcTextArea,
	NcButton,
	NcRichText,
	NcActions,
	NcActionButton,
} from '@nextcloud/vue'

export default {
	name: 'NotasTab',

	components: {
		NcTextArea,
		NcButton,
		NcRichText,
		NcActions,
		NcActionButton,
		LanguageMarkdown,
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
			notas: this.data.Notas ?? '',
			showMarkdown: false,
			useMarkdown: true,
			hability: 'Habilitar',
		}
	},

	computed: {
		inputValue: {
			get() {
				return this.notas
			},
			set(value) {
				this.debouncePropertyChange(value.trim())
			},
		},
		debouncePropertyChange() {
			return debounce(function(value) {
				this.notas = value
				if (this.automaticsave === 'true') {
					this.guardarNota()
				}
			}, 900)
		},
	},

	watch: {
		data(news) {
			this.notas = news.Notas
		},
	},

	mounted() {
		this.notas = this.data.Notas
	},

	methods: {

		showEdit() {
			this.showMarkdown = !this.showMarkdown
			this.hability = this.showMarkdown ? 'Deshabilitar' : 'Habilitar'
		},
		async guardarNota() {
			try {
				await axios.post(generateUrl('/apps/empleados/GuardarNota'), {
					id_empleados: this.data.Id_empleados,
					nota: this.notas,
				})
				showSuccess('Nota ha sido actualizada')
				this.$bus.emit('getall')
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [' + err + ']'))
			}
		},
	},
}
</script>

<style>

.editbutton{
	float: right;
	position: absolute;
	right: 30px;
	z-index: 9999;
}

.plain-text {
	white-space: pre-line;
	height: 300px;
}

textarea.model {
	height: 300px !important;
}
</style>
