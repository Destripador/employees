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
						<div>
							<div>Vacaciones</div>
							<div>dias</div>
							<div>Dias asignados</div>
						</div>
					</div>
				</section>
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

<style>
.layout {
  width: 100%;

  display: flex;
  gap: 16px;
}

.grow1 { flex-grow: 1; }
.grow2 { flex-grow: 2; }
</style>
