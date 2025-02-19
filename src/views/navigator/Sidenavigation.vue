<template>
	<NcAppNavigation>
		<template #list>
			<!-- NcAppNavigationItem name="Inicio" href="/index.php/apps/empleados/">
				<template #icon>
					<Home :size="20" />
				</template>
			</NcAppNavigationItem -->

			<NcAppNavigationItem name="Empleados"
				:to="{ name: 'Empleados' }"
				exact>
				<template #icon>
					<!--FileDocumentPlusOutline :size="20" /-->
				</template>
			</NcAppNavigationItem>

			<NcAppNavigationItem name="Areas"
				:to="{ name: 'Areas' }" />

			<!--NcAppNavigationItem name="Puestos"
				:to="{ name: 'Puestos' }">
				<template #icon>
					<FileDocumentPlusOutline :size="20" />
				</template>
			</NcAppNavigationItem-->
		</template>
	</NcAppNavigation>
</template>

<script>
import {
	NcAppNavigation,
	NcAppNavigationItem,
} from '@nextcloud/vue'

export default {
	name: 'Sidenavigation',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
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
