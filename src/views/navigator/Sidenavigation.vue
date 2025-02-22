<template>
	<NcAppNavigation>
		<NcAppNavigationCaption heading-id="Administrativo"
			is-heading
			name="Administrativo" />
		<NcAppNavigationList aria-labelledby="Administrativo">
			<NcAppNavigationItem name="Empleados"
				:to="{ name: 'Empleados' }"
				exact />

			<NcAppNavigationItem name="Areas"
				:to="{ name: 'Areas' }" />

			<NcAppNavigationItem name="Puestos"
				:to="{ name: 'Puestos' }" />
		</NcAppNavigationList>
	</NcAppNavigation>
</template>

<script>
import {
	NcAppNavigation,
	NcAppNavigationItem,
	NcAppNavigationList,
	NcAppNavigationCaption,
} from '@nextcloud/vue'

export default {
	name: 'Sidenavigation',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationList,
		NcAppNavigationCaption,
	},

	methods: {
		navigateTo(route) {
			this.$router.push({ name: route })
		},
	},
}
</script>

<!--style scoped lang="scss">
</style-->
