<template id="content">
	<NcContent app-name="empleados">
		<navigator />
		<router-view />
	</NcContent>
</template>

<script>
// Importing necessary components
import navigator from './navigator/Sidenavigation.vue'
import { NcContent } from '@nextcloud/vue'

export default {
	name: 'App',
	components: {
		navigator,
		NcContent,
	},

	provide() {
		return {
			configuraciones: this.configuraciones,
			groupuser: this.groupsuser,
		}
	},

	props: {
		// Configuration parameters
		parameters: {
			type: Object,
			required: true,
		},
		groupsUser: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			configuraciones: this.parameters,
			groupsuser: this.groupsUser,
		}
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
