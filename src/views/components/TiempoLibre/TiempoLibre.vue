<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<VCalendar expanded />
		</div>
	</NcAppContent>
</template>

<script>
// Importing necessary components

// import { showError /* showSuccess */ } from '@nextcloud/dialogs'
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'

import { Calendar } from 'v-calendar'

import {
	NcAppContent,
} from '@nextcloud/vue'
export default {
	name: 'TiempoLibre',

	components: {
		NcAppContent,
		VCalendar: Calendar,
	},

	data() {
		return {
			events: [
				{ key: 'event-1', start: new Date(), title: 'Evento de prueba' },
			],
		}
	},

	mounted() {
	},

	methods: {
	},
}
</script>

<style scoped lang="scss">
	.container {
		padding-left: 10px;
		padding-top:30px;
	}
</style>
