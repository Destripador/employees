<template>
	<div class="top">
		<div class="file-container">
			<!-- Botón para subir archivos -->
			<input ref="fileInput"
				type="file"
				class="file-input"
				@change="uploadFile">

			<div style="display: flex; justify-content: space-between; gap: 12px;">
				<NcButton v-if="navigationStack.length > 0" @click="goBack">
					<template #icon>
						<ArrowLeft :size="20" />
					</template>
				</NcButton>
				<NcButton style="margin-left: auto;" @click="OpenFolder()">
					<template #icon>
						<FolderMoveOutline :size="20" />
					</template>
					Abrir
				</NcButton>
				<NcButton @click="CreateFolder()">
					<template #icon>
						<FolderPlusOutline :size="20" />
					</template>
					Crear
				</NcButton>
				<NcButton @click="$refs.fileInput.click()">
					<template #icon>
						<CloudUpload :size="20" />
					</template>
					Subir
				</NcButton>
				<NcButton @click="reloadFiles()">
					<template #icon>
						<Reload :size="20" />
					</template>
				</NcButton>
			</div>
			<table v-if="files.length > 0" class="file-table">
				<thead>
					<tr>
						<th>📄 Archivo</th>

						<!-- th>🔗 Acción</th -->
					</tr>
				</thead>
				<tbody>
					<tr v-for="file in files" :key="file.id" @dblclick="exploreFolder(file)">
						<td>
							<div class="file-item">
								<FolderOutline v-if="file.isFolder" :size="20" />
								<FileOutline v-else :size="20" />
								<span class="file-name">
									{{ truncateText(file.name) }}
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<p v-if="files.length === 0" class="empty-msg" style="margin-top: 20px;">
				No hay archivos disponibles.
			</p>
		</div>
		<NcDialog :open.sync="showDialogFolder"
			is-form
			:buttons="buttons"
			name="Crear Carpeta"
			@submit="FolderAction()">
			<NcTextField v-model="Folder" label="Nuevo nombre" required />
		</NcDialog>
	</div>
</template>

<script>
import FolderPlusOutline from 'vue-material-design-icons/FolderPlusOutline.vue'
import FolderMoveOutline from 'vue-material-design-icons/FolderMoveOutline.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import CloudUpload from 'vue-material-design-icons/CloudUpload.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Reload from 'vue-material-design-icons/Reload.vue'

import { getClient, defaultRootPath } from '@nextcloud/files/dav'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { /* NcActions, NcActionButton, */ NcButton, NcDialog, NcTextField } from '@nextcloud/vue'

export default {
	name: 'FilesTab',
	components: {
		// NcActions,
		// NcActionButton,
		FolderPlusOutline,
		FolderOutline,
		NcButton,
		FileOutline,
		CloudUpload,
		ArrowLeft,
		FolderMoveOutline,
		NcDialog,
		NcTextField,
		Reload,
	},

	props: {
		data: { type: Object, required: true },
		show: { type: Boolean, required: true },
		empleados: { type: Array, required: true },
		config: { type: String, required: true },
	},

	data() {
		return {
			files: [],
			currentPath: null,
			navigationStack: [],
			client: null,
			response: null,
			showDialogFolder: false,
			buttons: [
				{
					label: 'Renombrar',
					type: 'primary',
					nativeType: 'submit',
				},
			],
			Folder: '',
			FolderLocation: '',
		}
	},

	watch: {
		data(news, old) {
			if (news) {
				this.currentPath = `${defaultRootPath}/EMPLEADOS/${news.uid} - ${news.displayname.toUpperCase()}/`
				this.navigationStack = []
				this.fetchFiles()
			}
		},
	},

	mounted() {
		this.currentPath = `${defaultRootPath}/EMPLEADOS/${this.data.uid} - ${this.data.displayname.toUpperCase()}/`
		this.fetchFiles()
		// 🔄 Auto-actualizar la lista de archivos cada 30 segundos
		this.autoRefreshInterval = setInterval(() => {
			this.fetchFiles()
		}, 10000) // 30 segundos
	},

	beforeDestroy() {
		// 🛑 Detener la auto-actualización cuando el componente se destruya
		if (this.autoRefreshInterval) {
			clearInterval(this.autoRefreshInterval)
		}
	},

	methods: {
		// 📂 Obtener archivos de la carpeta actual
		async fetchFiles() {
			try {
				this.client = getClient()
				this.response = await this.client.getDirectoryContents(this.currentPath, { details: true })

				if (Array.isArray(this.response.data)) {
					// eslint-disable-next-line no-console
					console.log('👀 Todos los archivos:', this.response)
					this.files = this.response.data.map(file => ({
						id: file.id || file.etag,
						name: file.basename || file.name,
						size: file.size || 0,
						isFolder: file.type === 'directory', // Identificar si es carpeta
						location: this.currentPath,
					}))
				} else {
					showError('⚠️ La respuesta NO contiene un array en `data`')
					this.files = []
				}
			} catch (error) {
				showError('❌ Error al obtener los archivos: ' + error.message)
				this.files = []
			}
		},

		// 📂 Explorar carpetas con doble clic
		exploreFolder(file) {
			if (file.isFolder) {
				// Guardar la ruta actual en la pila antes de cambiar
				this.navigationStack.push(this.currentPath)
				this.currentPath = `${this.currentPath}${file.name}/`
				this.fetchFiles()
			}
		},

		// 🔙 Volver a la carpeta anterior
		goBack() {
			if (this.navigationStack.length > 0) {
				this.currentPath = this.navigationStack.pop() // Regresar a la última ruta guardada
				this.fetchFiles()
			}
		},

		// ⬇ Descargar archivo
		downloadFile(file) {
			window.location.href = file.downloadUrl
		},

		// 🔗 Abrir archivo
		openFile(file) {
			window.open(file.downloadUrl, '_blank')
		},

		// 🗑 Eliminar archivo
		async deleteFile(file) {
			try {
				const client = getClient()
				await client.deleteFile(file.location + '/' + file.name)
				showSuccess('✅ Archivo eliminado correctamente')
				this.fetchFiles()
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al eliminar archivo:', error)
				showError('❌ Error al eliminar archivo: ' + error.message)
			}
		},

		// ✏ Renombrar archivo
		CreateFolder() {
			this.showDialogFolder = true
		},

		async FolderAction() {
			try {
				const client = getClient()
				await client.createDirectory(this.currentPath + '/' + this.Folder)
				this.Folder = ''
				showSuccess('✅ Archivo eliminado correctamente')
				this.fetchFiles()
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al eliminar archivo:', error)
				showError('❌ Error al eliminar archivo: ' + error.message)
			}
		},

		// ⬆️ Subir un archivo (CORREGIDO)
		async uploadFile(event) {
			const file = event.target.files[0]
			if (!file) return

			try {
				const client = getClient()
				const filePath = `${this.currentPath}${file.name}`

				// eslint-disable-next-line no-console
				console.log('⬆️ Intentando subir archivo a:', filePath)

				// Verificar si la carpeta actual existe antes de subir
				const folderExists = await client.exists(this.currentPath)

				if (!folderExists) {
					// eslint-disable-next-line no-console
					console.log('📂 Carpeta no existe, creando:', this.currentPath)
					await client.createDirectory(this.currentPath)
				}

				await client.putFileContents(filePath, file, { contentLength: file.size })

				// eslint-disable-next-line no-console
				console.log('✅ Archivo subido correctamente.')
				this.fetchFiles() // Recargar lista después de subir
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al subir archivo:', error)
				showError('❌ Error al subir archivo: ' + error.message)
			}
		},
		// 📏 Convertir bytes en KB / MB
		formatSize(bytes) {
			if (bytes < 1024) return `${bytes} B`
			const kb = bytes / 1024
			if (kb < 1024) return `${kb.toFixed(2)} KB`
			return `${(kb / 1024).toFixed(2)} MB`
		},

		truncateText(text, length = 75) {
			if (!text) return '' // Si no hay texto, retorna vacío
			return text.length > length ? text.substring(0, length) + '...' : text
		},

		OpenFolder() {
			if (!this.currentPath) {
				// eslint-disable-next-line no-console
				console.error('⚠️ No se puede abrir la carpeta, `currentPath` no está definido.')
				return
			}

			// Construir la URL para abrir en la app de archivos de Nextcloud
			const nextcloudFilesUrl = `${window.location.origin}/apps/files?dir=${encodeURIComponent(this.getCleanPath(this.currentPath))}`

			// eslint-disable-next-line no-console
			console.log('🔗 Abriendo carpeta en Nextcloud Files:', nextcloudFilesUrl)

			// Abrir en una nueva pestaña
			window.open(nextcloudFilesUrl, '_blank')
		},

		getCleanPath(path) {
			if (!path) return ''

			// Dividir la ruta en segmentos
			const pathSegments = path.split('/').filter(segment => segment !== '')

			// Verificar que la ruta tenga al menos 3 segmentos para poder eliminar los primeros 2
			if (pathSegments.length > 2) {
				return '/' + pathSegments.slice(2).join('/') + '/'
			}

			// Si la ruta no tiene suficientes niveles, devolverla tal cual
			return path
		},

		reloadFiles() {
			this.fetchFiles()
		},
	},
}
</script>

<style scoped>
/* 📂 Estilos inspirados en Nextcloud */
.file-container {
  margin: 0 auto;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  text-align: center;
}

/* Tabla de archivos */
.file-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
.file-table th, .file-table td {
  border-bottom: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}
.file-table th {
  background-color: #f4f4f4;
  font-weight: bold;
}
.file-table tr:hover {
  background-color: #f9f9f9;
}

/* Input oculto para seleccionar archivos */
.file-input {
  display: none;
}

/* Mensaje de archivos vacíos */
.empty-msg {
  color: #888;
  text-align: center;
}

/* 🔹 Iconos de archivos */
.file-icon {
  display: inline-block;
  width: 16px;
  height: 16px;
  margin-right: 8px;
  background-size: cover;
}
.file-item {
    display: flex;
    align-items: center; /* Centra verticalmente */
    gap: 8px; /* Espacio entre icono y texto */
}

.file-name {
    white-space: nowrap; /* Evita que el texto se divida en varias líneas */
}
</style>
