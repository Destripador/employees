<template>
	<div class="top">
		<div class="file-container" @drop.prevent="handleDrop" @dragover.prevent>
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
				<div v-if="showLoading" style="display: flex; align-items: center; gap: 8px;">
					<NcLoadingIcon :size="25" message="Procesando archivos..." />
					<p>Subiendo archivo | espere</p>
				</div>
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
								<ImageIcon v-else-if="file.name.match(/\\.(jpg|png|jpeg|gif)$/i)" :size="20" />
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
			Folder: '',
			buttons: [{ label: 'Crear', type: 'primary', nativeType: 'submit' }],
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
			}
		},
		exploreFolder(file) {
			if (file.isFolder) {
				this.navigationStack.push(this.currentPath)
				this.currentPath += `${file.name}/`
				this.fetchFiles()
			}
		},
		goBack() {
			if (this.navigationStack.length) {
				this.currentPath = this.navigationStack.pop()
				this.fetchFiles()
			}
		},
		CreateFolder() {
			this.showDialogFolder = true
		},
		async FolderAction() {
			try {
				const safeFolder = this.Folder.trim().replace(/[<>:"/\\|?*]/g, '_')
				await getClient().createDirectory(`${this.currentPath}${safeFolder}/`)
				this.Folder = ''
				showSuccess('✅ Carpeta creada.')
				this.fetchFiles()
			} catch (e) {
				showError('❌ ' + e.message)
			}
		},
		async subirArchivo(file, destino) {
			const start = performance.now()
			await Upload(destino, file)
			const duracion = (performance.now() - start) / 1000
			const velocidad = file.size / duracion
			let delay = Math.min((file.size / velocidad) * 1.5 * 1000, 5000)
			delay = Math.max(delay, 500)
			for (let i = 0; i < 5; i++) {
				try {
					const remoto = await this.client.stat(this.currentPath + file.name)
					if (remoto?.size === file.size) {
						showSuccess(`✅ Archivo ${file.name} subido correctamente.`)
						return
					}
				} catch {}
				await new Promise(resolve => setTimeout(resolve, delay))
			}
			console.warn(`⚠️ Archivo ${file.name} no confirmado tras reintentos.`)
		},
		async uploadFile(event) {
			const files = Array.from(event.target.files)
			if (!files.length) return
			this.showLoading = true
			const destinoBase = this.currentPath.replace(/^\/files\/[^/]+/, '')
			for (const file of files) {
				await this.subirArchivo(file, destinoBase + file.name)
			}
			await this.fetchFiles()
			this.showLoading = false
		},
		async handleDrop(event) {
			const files = Array.from(event.dataTransfer.files)
			if (!files.length) return
			this.showLoading = true
			const destinoBase = this.currentPath.replace(/^\/files\/[^/]+/, '')
			for (const file of files) {
				await this.subirArchivo(file, destinoBase + file.name)
			}
			await this.fetchFiles()
			this.showLoading = false
		},
		truncateText(text, length = 75) {
			return text?.length > length ? text.substring(0, length) + '...' : text
		},
		formatSize(bytes) {
			if (!bytes) return '-'
			if (bytes < 1024) return `${bytes} B`
			const kb = bytes / 1024
			if (kb < 1024) return `${kb.toFixed(2)} KB`
			return `${(kb / 1024).toFixed(2)} MB`
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			return new Date(dateString).toLocaleString('es-MX')
		},
		OpenFolder() {
			const url = `${window.location.origin}/apps/files?dir=${encodeURIComponent(this.getCleanPath(this.currentPath))}`
			window.open(url, '_blank')
		},
		getCleanPath(path) {
			const seg = path.split('/').filter(Boolean)
			return seg.length > 2 ? '/' + seg.slice(2).join('/') + '/' : path
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
	border: 2px #ccc;
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
.file-table th { background-color: #f4f4f4; font-weight: bold; }
.file-table tr:hover { background-color: #f9f9f9; }
.file-input { display: none; }
.empty-msg { color: #888; text-align: center; }
.file-item {
	display: flex;
	align-items: center;
	gap: 8px;
}
.file-name { white-space: nowrap; }
</style>
