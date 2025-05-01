<template>
	<!--NcContent app-name="ahorrosgossler">
	    <navigator/>
		<NcAppContent-->
	<div v-if="loading">
		<div class="center">
			<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
		</div>
	</div>
	<div v-else>
		<div class="container">
			<h2 class="board-title">
				<Archive :size="20" decorative class="icon" />
				<span>Historial</span>
			</h2>
		</div>
		<div v-if="historial.length > 0">
			<ul style="padding: 10px;">
				<li v-for="item in historial" :key="item.id">
					<NcListItem
						:name="'This is an active element with highlighted counter'"
						:bold="true"
						:active="true"
						:details="item.fecha_solicitud"
						counter-type="highlighted"
						@click.prevent>
						<template #name>
							Cantidad solicitada: {{ Intl.NumberFormat("es-MX", {style: "currency", currency: "MXN"}).format(item.cantidad_solicitada) }}
						</template>
						<template #subname>
							<p v-if="item.nota">
								{{ item.nota }}
							</p>
						</template>
						<template #indicator>
							<!-- Color dot -->
							<div v-if="item.estado == 0">
								<CheckboxBlankCircle :size="16" fill-color="#cc0000" />
							</div>
							<div v-else>
								<CheckboxBlankCircle :size="16" fill-color="#8fce00" />
							</div>
						</template>
					</NcListItem>
				</li>
			</ul>
		</div>
		<div v-else id="emptycontent">
			<h2>
				{{ t('ahorrosgossler', 'Aun no has realizado ningun movimiento') }}
			</h2>
		</div>
	</div>
	<!--/NcAppContent>
	</NcContent-->
</template>

<script>

import { showError /* showSuccess */ } from '@nextcloud/dialogs'

// Iconos
import Archive from 'vue-material-design-icons/Archive.vue'
import CheckboxBlankCircle from 'vue-material-design-icons/CheckboxBlankCircle.vue'

// AXIOS
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// Imports
import {
	NcLoadingIcon,
	NcListItem,
} from '@nextcloud/vue'

export default {
	name: 'Historial',
	components: {
		NcLoadingIcon,
		NcListItem,
		Archive,
		CheckboxBlankCircle,
	},

	props: {
		id: {
			type: Number,
			required: true,
		},
	},

	data() {
		return {
			loading: true,
			historial: [],
		}
	},

	async mounted() {
		this.gethistorial()
	},

	methods: {
		async gethistorial() {
			try {
				const response = await axios.get(generateUrl('apps/empleados/getHistorial/' + this.id))

				// Extraer el nombre de usuario del objeto de respuesta
				this.historial = response.data
			} catch (e) {
				console.error(e)
				showError(t('ahorrosgossler', 'Could not fetch your information'))
			}
			this.loading = false
		},
	},
}
</script>
<style scoped>
	#emptycontent, .emptycontent {
		margin-top: 1vh;
	}
	.center-screen {
		display: flex;
		justify-content: center;
		align-items: center;
		text-align: center;
		min-height: 100vh;
	}
	.center {
		margin: auto;
		width: 50%;
		padding: 10px;
	}
	.container{
		padding-left: 20px;
	}
	.board-title {
		margin-right: 10px;
		font-size: 25px;
		display: flex;
		align-items: center;
		font-weight: bold;
		.icon {
			margin-right: 8px;
		}
	}
</style>
