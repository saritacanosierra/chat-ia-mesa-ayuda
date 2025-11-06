/**
 * Vista: Tabla de archivos
 * Maneja la visualización de la tabla de archivos almacenados
 */
class FilesTableView {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
    }

    /**
     * Muestra la tabla de archivos
     * @param {Array} files - Lista de archivos
     * @param {Object} stats - Estadísticas
     * @param {Function} onDelete - Callback para eliminar archivo
     */
    displayFiles(files, stats, onDelete) {
        if (!files || files.length === 0) {
            this.container.innerHTML = `
                <div class="empty-files">
                    📭 No hay archivos almacenados. Sube un archivo para comenzar.
                </div>
            `;
            return;
        }

        let tableHTML = `
            <div class="files-stats">
                <span>📊 Total: ${stats.total_files} archivo(s)</span>
                <span>📦 Fragmentos: ${stats.total_chunks}</span>
            </div>
            <table class="files-table">
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Tipo</th>
                        <th>Fragmentos</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;

        files.forEach(file => {
            const typeClass = this.getTypeClass(file.type);
            const formattedDate = this.formatDate(file.uploaded_at);
            
            tableHTML += `
                <tr>
                    <td><strong>${Utils.sanitize(file.filename)}</strong></td>
                    <td><span class="file-type-badge ${typeClass}">${file.type.toUpperCase()}</span></td>
                    <td>${file.chunks}</td>
                    <td>${formattedDate}</td>
                    <td>
                        <button class="delete-btn" data-file-id="${file.id}" title="Eliminar archivo">
                            🗑️ Eliminar
                        </button>
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                </tbody>
            </table>
        `;

        this.container.innerHTML = tableHTML;

        // Agregar event listeners a los botones de eliminar
        this.container.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const fileId = e.target.getAttribute('data-file-id');
                if (fileId && onDelete) {
                    onDelete(fileId);
                }
            });
        });
    }

    /**
     * Muestra un estado de carga
     */
    displayLoading() {
        this.container.innerHTML = '<div class="loading">Cargando archivos...</div>';
    }

    /**
     * Muestra un error
     */
    displayError(message) {
        this.container.innerHTML = `
            <div class="empty-files" style="color: var(--color-error);">
                ❌ ${Utils.sanitize(message)}
            </div>
        `;
    }

    /**
     * Obtiene la clase CSS para el tipo de archivo
     */
    getTypeClass(type) {
        const typeMap = {
            'pdf': 'pdf',
            'txt': 'txt',
            'xlsx': 'xlsx',
            'md': 'md'
        };
        return typeMap[type.toLowerCase()] || 'txt';
    }

    /**
     * Formatea la fecha
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }
}

