<template id="content">
	<NcAppContent name="Loading">
		<div>
			<h1>holaaaaaaaaaaaaaa</h1>
		</div>
	</NcAppContent>
</template>

<script>
// Importing necessary components

// import { showError /* showSuccess */ } from '@nextcloud/dialogs'
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
import {
	NcAppContent,
} from '@nextcloud/vue'
export default {
	name: 'Solicitar',

	components: {
		NcAppContent,
	},

	data() {
		return {
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
		padding-left: 60px;
	}
	.board-title {
		padding-left: 60px;
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
</style>
