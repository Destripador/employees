<template>
	<div class="top">
		<div class="file-container" @drop.prevent="handleDrop" @dragover.prevent>
			<NcLoadingIcon v-if="showLoading" :size="50" message="Procesando archivos..." />

			<!-- Botón para subir archivos -->
			<input ref="fileInput"
				type="file"
				class="file-input"
				multiple
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
						<th>Tamaño</th>
						<th>Última modificación</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="file in files" :key="file.id" @click="exploreFolder(file)">
						<td>
							<div class="file-item">
								<FolderOutline v-if="file.isFolder" :size="20" />
								<FilePdfBox v-else-if="file.name.endsWith('.pdf')" :size="20" />
								<ImageIcon v-else-if="file.name.match(/\.(jpg|png|jpeg|gif)$/i)" :size="20" />
								<FileOutline v-else :size="20" />
								<span class="file-name">{{ truncateText(file.name) }}</span>
							</div>
						</td>
						<td>{{ file.isFolder ? '-' : formatSize(file.size) }}</td>
						<td>{{ formatDate(file.modified) }}</td>
					</tr>
				</tbody>
			</table>

			<p v-if="files.length === 0 && !showLoading" class="empty-msg">
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
import FilePdfBox from 'vue-material-design-icons/FilePdfBox.vue'
import ImageIcon from 'vue-material-design-icons/Image.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Reload from 'vue-material-design-icons/Reload.vue'

import { getClient, defaultRootPath } from '@nextcloud/files/dav'
import { upload as Upload } from '@nextcloud/upload'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton, NcDialog, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

export default {
	name: 'FilesTab',
	components: {
		FolderPlusOutline,
		FolderOutline,
		NcButton,
		FileOutline,
		FilePdfBox,
		ImageIcon,
		CloudUpload,
		ArrowLeft,
		FolderMoveOutline,
		NcDialog,
		NcTextField,
		NcLoadingIcon,
		Reload,
	},

	props: {
		data: { type: Object, required: true },
		show: { type: Boolean, required: true },
		empleados: { type: Array, required: true },
	},

	data() {
		return {
			files: [],
			currentPath: null,
			navigationStack: [],
			client: null,
			showDialogFolder: false,
			showLoading: false,
			buttons: [{ label: 'Crear', type: 'primary', nativeType: 'submit' }],
			Folder: '',
		}
	},

	watch: {
		data(newData) {
			if (newData) {
				const nombre = newData.displayname?.trim() || newData.uid
				this.currentPath = `${defaultRootPath}/EMPLEADOS/${newData.uid} - ${nombre.toUpperCase()}/`
				this.navigationStack = []
				this.fetchFiles()
			}
		},
	},

	mounted() {
		const nombre = this.data.displayname?.trim() || this.data.uid
		this.currentPath = `${defaultRootPath}/EMPLEADOS/${this.data.uid} - ${nombre.toUpperCase()}/`
		this.fetchFiles()
	},

	methods: {

		async fetchFiles() {
			try {
				this.client = getClient()
				const response = await this.client.getDirectoryContents(this.currentPath, { details: true })

				this.files = Array.isArray(response.data)
					? response.data.map(file => ({
						id: file.id || file.etag,
						name: file.basename || file.name,
						size: file.size || 0,
						isFolder: file.type === 'directory',
						location: this.currentPath,
						modified: file.lastmod || null,
					}))
					: []

			} catch (error) {
				showError('❌ Error al obtener archivos: ' + error.message)
				this.files = []
			}
		},

		exploreFolder(file) {
			if (file.isFolder) {
				this.navigationStack.push(this.currentPath)
				this.currentPath = `${this.currentPath}${file.name}/`
				this.fetchFiles()
			}
		},

		goBack() {
			if (this.navigationStack.length > 0) {
				this.currentPath = this.navigationStack.pop()
				this.fetchFiles()
			}
		},

		CreateFolder() {
			this.showDialogFolder = true
		},

		async FolderAction() {
			try {
				const client = getClient()
				const safeFolder = this.Folder.trim().replace(/[<>:"/\\|?*]/g, '_')
				await client.createDirectory(`${this.currentPath}${safeFolder}/`)
				this.Folder = ''
				showSuccess('✅ Carpeta creada correctamente.')
				this.fetchFiles()
			} catch (error) {
				showError('❌ Error al crear carpeta: ' + error.message)
			}
		},

		async uploadFile(event) {
			const files = Array.from(event.target.files)
			if (!files.length) {
				showError('⚠️ No hay archivos seleccionados.')
				return
			}

			this.showLoading = true

			const destinationFolder = this.currentPath.replace(/^\/files\/[^/]+/, '')

			try {
				for (const file of files) {
					const fileDestination = destinationFolder + file.name
					// eslint-disable-next-line no-console
					console.log('Subiendo a:', fileDestination)
					await Upload(fileDestination, file)
				}

				// eslint-disable-next-line no-console
				console.log('Esperando 2 segundos antes de actualizar lista de archivos...')
				await new Promise(resolve => setTimeout(resolve, 2000))

				showSuccess('✅ Archivo subido correctamente.')
				this.fetchFiles()

			} catch (error) {
				console.error('Uploader error:', error)
				showError(`❌ Error al subir archivos: ${error.message}`)
			} finally {
				this.showLoading = false
			}
		},

		async handleDrop(event) {
			const files = Array.from(event.dataTransfer.files)
			if (!files.length) return

			this.showLoading = true

			const destinationFolder = this.currentPath.replace(/^\/files\/[^/]+/, '')

			try {
				for (const file of files) {
					const fileDestination = destinationFolder + file.name
					// eslint-disable-next-line no-console
					console.log('Subiendo (drag & drop) a:', fileDestination)
					await Upload(fileDestination, file)
				}

				// eslint-disable-next-line no-console
				console.log('Esperando 2 segundos antes de actualizar lista de archivos...')
				await new Promise(resolve => setTimeout(resolve, 2000))

				showSuccess('✅ Archivo subido correctamente.')
				this.fetchFiles()

			} catch (error) {
				showError(`❌ Error al subir archivos arrastrados: ${error.message}`)
			} finally {
				this.showLoading = false
			}
		},

		truncateText(text, length = 75) {
			return text?.length > length ? text.substring(0, length) + '...' : text
		},

		formatSize(bytes) {
			if (!bytes) return '-'
			if (bytes < 1024) return `${bytes} B`
			const kb = bytes / 1024
			if (kb < 1024) return `${kb.toFixed(2)} KB`
			const mb = kb / 1024
			return `${mb.toFixed(2)} MB`
		},

		formatDate(dateString) {
			if (!dateString) return '-'
			const date = new Date(dateString)
			return date.toLocaleString('es-MX')
		},

		OpenFolder() {
			const nextcloudFilesUrl = `${window.location.origin}/apps/files?dir=${encodeURIComponent(this.getCleanPath(this.currentPath))}`
			window.open(nextcloudFilesUrl, '_blank')
		},

		getCleanPath(path) {
			const segments = path.split('/').filter(Boolean)
			return segments.length > 2 ? '/' + segments.slice(2).join('/') + '/' : path
		},

		reloadFiles() {
			this.fetchFiles()
		},

	},
}
</script>

<style scoped>
.file-container {
	margin: 0 auto;
	padding: 20px;
	background: white;
	border-radius: 8px;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	text-align: center;
	min-height: 200px;
	border: 2px dashed #ccc;
}

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

.file-input {
	display: none;
}

.empty-msg {
	color: #888;
	text-align: center;
}

.file-item {
	display: flex;
	align-items: center;
	gap: 8px;
}

.file-name {
	white-space: nowrap;
}
</style>
