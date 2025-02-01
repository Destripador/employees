<template>
	<div class="top">
		<div class="file-container">
			<!-- Botón para subir archivos -->
			<input ref="fileInput"
				type="file"
				class="file-input"
				@change="uploadFile">
			<button class="upload-btn" @click="$refs.fileInput.click()">
				Subir Archivo
			</button>

			<!-- Botón para regresar a la carpeta anterior -->
			<button v-if="currentPath !== '/EMPLEADOS/'" class="back-btn" @click="goBack">
				⬅ Volver
			</button>

			<p v-if="files.length === 0" class="empty-msg">
				No hay archivos disponibles.
			</p>

			<table v-if="files.length > 0" class="file-table">
				<thead>
					<tr>
						<th>📄 Archivo</th>
						<th>📏 Tamaño</th>
						<th>🔗 Acción</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="file in files" :key="file.id" @dblclick="exploreFolder(file)">
						<td>
							<span :class="getFileIcon(file.name)" class="file-icon" />
							{{ file.name }}
						</td>
						<td>{{ formatSize(file.size) }}</td>
						<td>
							<NcActions>
								<NcActionButton icon="eye" @click="viewFile(file)">Ver</NcActionButton>
								<NcActionButton icon="download" @click="downloadFile(file)">Descargar</NcActionButton>
								<NcActionButton icon="external-link" @click="openFile(file)">Abrir</NcActionButton>
								<NcActionButton icon="trash" @click="deleteFile(file)">Eliminar</NcActionButton>
								<NcActionButton icon="edit" @click="renameFile(file)">Renombrar</NcActionButton>
							</NcActions>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import { getClient, defaultRootPath } from '@nextcloud/files/dav'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcActions, NcActionButton } from '@nextcloud/vue'

export default {
	name: 'FilesTab',
	components: { NcActions, NcActionButton },

	props: {
		data: { type: Object, required: true },
		show: { type: Boolean, required: true },
		empleados: { type: Array, required: true },
		config: { type: String, required: true },
	},

	data() {
		return {
			files: [],
			currentPath: `${defaultRootPath}/EMPLEADOS/${this.data.uid} - ${this.data.displayname.toUpperCase()}/`,
		}
	},

	watch: {
		data: {
			handler(newVal) {
				if (newVal) this.fetchFiles()
			},
			deep: true,
		},
	},

	mounted() {
		this.fetchFiles()
	},

	methods: {
		// 📂 Obtener archivos de Nextcloud
		async fetchFiles() {
			try {
				const client = getClient()
				const fullPath = `${this.currentPath}`

				// eslint-disable-next-line no-console
				console.log('📂 Consultando ruta:', fullPath)

				const response = await client.getDirectoryContents(fullPath, { details: true })

				if (Array.isArray(response.data)) {
					this.files = response.data.map(file => ({
						id: file.id || file.etag,
						name: file.basename || file.name,
						size: file.size || 0,
						isFolder: file.type === 'directory',
						location: this.currentPath,
						downloadUrl: file.href,
					}))
				} else {
					// eslint-disable-next-line no-console
					console.error('⚠️ La respuesta NO contiene un array en `data`:', response)
					showError('⚠️ La respuesta NO contiene un array en `data`')
					this.files = []
				}
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al obtener los archivos:', error)
				showError('❌ Error al obtener los archivos: ' + error.message)
				this.files = []
			}
		},

		// 📂 Explorar carpetas con doble clic
		exploreFolder(file) {
			if (file.isFolder) {
				this.currentPath = `${this.currentPath}${file.name}/`
				this.fetchFiles()
			}
		},

		// 🔙 Volver a la carpeta anterior
		goBack() {
			if (this.currentPath !== `/EMPLEADOS/${this.data.uid} - ${this.data.displayname.toUpperCase()}/`) {
				const pathSegments = this.currentPath.split('/').filter(Boolean)
				pathSegments.pop()
				this.currentPath = `/${pathSegments.join('/')}/`
				this.fetchFiles()
			}
		},

		// 📄 Ver archivo
		viewFile(file) {
			// eslint-disable-next-line no-console
			console.log('👀 Ver archivo:', file)
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
				// eslint-disable-next-line no-console
				console.error(JSON.stringify(file))
				await client.deleteFile(file.location)
				showSuccess('✅ Archivo eliminado correctamente')
				this.fetchFiles()
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al eliminar archivo:', error)
				showError('❌ Error al eliminar archivo: ' + error.message)
			}
		},

		// ✏ Renombrar archivo
		async renameFile(file) {
			const newName = prompt('📝 Nuevo nombre del archivo:', file.name)
			if (!newName || newName === file.name) return

			try {
				const client = getClient()
				const newPath = `${this.currentPath}${newName}`
				await client.move(file.downloadUrl, newPath)

				showSuccess('✅ Archivo renombrado correctamente')
				this.fetchFiles()
			} catch (error) {
				// eslint-disable-next-line no-console
				console.error('❌ Error al renombrar archivo:', error)
				showError('❌ Error al renombrar archivo: ' + error.message)
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

		// 🖼️ Obtener icono de archivo
		getFileIcon(fileName) {
			if (fileName.endsWith('.pdf')) return 'icon-pdf'
			if (fileName.endsWith('.jpg') || fileName.endsWith('.png')) return 'icon-image'
			if (fileName.endsWith('.docx')) return 'icon-doc'
			return 'icon-default'
		},
	},
}
</script>

<style scoped>
/* 📂 Estilos inspirados en Nextcloud */
.file-container {
  max-width: 900px;
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

/* Botón de subida */
.upload-btn, .back-btn {
  background-color: #0078d4;
  color: white;
  border: none;
  padding: 8px 15px;
  margin: 10px;
  cursor: pointer;
  border-radius: 4px;
}
.upload-btn:hover, .back-btn:hover {
  background-color: #005ea2;
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

.icon-pdf { background-image: url('https://cdn-icons-png.flaticon.com/512/337/337946.png'); }
.icon-image { background-image: url('https://cdn-icons-png.flaticon.com/512/2659/2659360.png'); }
.icon-doc { background-image: url('https://cdn-icons-png.flaticon.com/512/281/281760.png'); }
.icon-default { background-image: url('https://cdn-icons-png.flaticon.com/512/149/149071.png'); }
</style>
