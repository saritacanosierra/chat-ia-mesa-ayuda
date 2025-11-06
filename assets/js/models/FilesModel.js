/**
 * Modelo: Archivos almacenados
 * Gestiona la lista de archivos almacenados
 */
class FilesModel {
    /**
     * Obtiene la lista de archivos almacenados
     * @returns {Promise<Object>} - Lista de archivos y estadísticas
     */
    async getFiles() {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}/files`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al obtener archivos:', error);
            throw new Error(`Error al obtener archivos: ${error.message}`);
        }
    }

    /**
     * Elimina un archivo
     * @param {string} fileId - ID del archivo a eliminar
     * @returns {Promise<Object>} - Respuesta del servidor
     */
    async deleteFile(fileId) {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}/files/${fileId}`,
                {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.detail || `Error HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al eliminar archivo:', error);
            throw new Error(`Error al eliminar archivo: ${error.message}`);
        }
    }
}

