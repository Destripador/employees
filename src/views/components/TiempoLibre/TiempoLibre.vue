<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<div class="text-center section">
				<p>Fecha seleccionada: {{ date }}</p>
				<DatePicker v-model="date"
					is-expanded
					is-range
					@input="onRangeSelected" />
			</div>
		</div>
		<NcModal
			v-if="modal"
			name="Name inside modal"
			@close="closeModal" />
	</NcAppContent>
</template>

<script>
// Importing necessary components

// import { showError /* showSuccess */ } from '@nextcloud/dialogs'
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
import 'v-calendar/src/styles/base.css'
import { DatePicker } from 'v-calendar'

import {
	NcAppContent,
	NcModal,
} from '@nextcloud/vue'
export default {
	name: 'TiempoLibre',

	components: {
		NcAppContent,
		DatePicker,
		NcModal,
	},

	data() {
		return {
			date: new Date(),
			modal: false,
		}
	},

	mounted() {
	},

	methods: {
		onRangeSelected(range) {
			// eslint-disable-next-line no-console
			console.log('Rango seleccionado:', range)
			this.modal = true
		},
		closeModal() {
			this.modal = false
		},
	},
}
</script>
