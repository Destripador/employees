<template>
	<div class="contacts-list__item-wrapper">
		<ListItem
			:key="source.Id_Equipos"
			:compact="true"
			class="list-item-style envelope"
			:name="source.Nombre"
			:counter-number="source.cantidad_empleados"
			@click.prevent="showDetails(source)">
			<template #icon>
				<div class="app-content-list-item-icon">
					<NcAvatar
						:display-name="source.Id_jefe_equipo"
						:user="source.Id_jefe_equipo"
						:show-user-status="false"
						:size="40" />
				</div>
			</template>
			<template #name>
				{{ source.Nombre }}
			</template>
			<template #subname>
				<small>jefe: {{ source.Id_jefe_equipo }}</small>
			</template>
		</ListItem>
	</div>
</template>

<script>

import {
	NcListItem as ListItem,
	NcAvatar,
} from '@nextcloud/vue'

export default {
	name: 'EquiposListItem',

	components: {
		ListItem,
		NcAvatar,
	},

	props: {
		index: {
			type: Number,
			required: true,
		},
		source: {
			type: Object,
			required: true,
		},
		reloadBus: {
			type: Object,
			required: true,
		},
	},

	methods: {
		showDetails(data) {
			this.$root.$emit('send-data-equipos', data)
			this.$root.$emit('show', false)
		},
	},
}
</script>
<style lang="scss" scoped>

.envelope {
	.app-content-list-item-icon {
		height: 40px; // To prevent some unexpected spacing below the avatar
	}

	&__subtitle {
		display: flex;
		gap: 4px;

		&__subject {
			color: var(--color-main-text);
			line-height: 130%;
			overflow: hidden;
			text-overflow: ellipsis;
		}
	}
}

.list-item-style {
	list-style: none;
}

</style>
<style lang="scss">
.contacts-list__item-wrapper {
	&[draggable='true'] .avatardiv * {
		cursor: move !important;
	}

	&[draggable='false'] .avatardiv * {
		cursor: not-allowed !important;
	}
}
</style>
