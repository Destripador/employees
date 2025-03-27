<template>
	<NcAppNavigation>
		<NcAppNavigationCaption heading-id="General"
			is-heading
			name="General" />
		<NcAppNavigationList aria-labelledby="General">
			<NcAppNavigationItem name="Inicio"
				:to="{ name: 'Home' }"
				exact />
		</NcAppNavigationList>

		<div v-if="isAdmin()">
			<NcAppNavigationCaption heading-id="CapitalHumano"
				is-heading
				name="Capital Humano" />
			<NcAppNavigationList aria-labelledby="CapitalHumano">
				<NcAppNavigationItem name="Empleados"
					:to="{ name: 'Empleados' }" />

				<NcAppNavigationItem name="Areas"
					:to="{ name: 'Areas' }" />

				<NcAppNavigationItem name="Puestos"
					:to="{ name: 'Puestos' }" />

				<NcAppNavigationItem name="Equipos"
					:to="{ name: 'Equipos' }" />
			</NcAppNavigationList>
		</div>

		<div>
			<NcAppNavigationCaption heading-id="Tiempo Laboral"
				is-heading
				name="Tiempo Laboral" />
			<NcAppNavigationList aria-labelledby="Tiempo Laboral">
				<NcAppNavigationItem name="Calendario"
					:to="{ name: 'Calendario' }" />
			</NcAppNavigationList>
		</div>
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

	inject: ['groupuser'],

	methods: {
		navigateTo(route) {
			this.$router.push({ name: route })
		},
		isAdmin() {
			return 'admin' in this.groupuser || 'recursos_humanos' in this.groupuser
		},
	},
}
</script>

<!--style scoped lang="scss">
</style-->
