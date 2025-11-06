/**
 * Modelo: Archivos
 * Gestiona la carga y validación de archivos
 */
class FileModel {
    /**
     * Valida el tipo de archivo
     * @param {File} file - Archivo a validar
     * @throws {Error} - Si el archivo no es válido
     */
    validateFileType(file) {
        const allowedTypes = ['.pdf', '.txt', '.xlsx', '.md'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(fileExtension)) {
            throw new Error('Solo se permiten archivos PDF, TXT, XLSX o MD');
        }
    }

    /**
     * Valida el tamaño del archivo
     * @param {File} file - Archivo a validar
     * @throws {Error} - Si el archivo es demasiado grande
     */
    validateFileSize(file) {
        if (file.size > CONFIG.MAX_FILE_SIZE) {
            throw new Error(
                `El archivo es demasiado grande. Máximo: ${CONFIG.MAX_FILE_SIZE / 1024 / 1024}MB`
            );
        }
    }

    /**
     * Formatea el tamaño del archivo
     * @param {number} bytes - Tamaño en bytes
     * @returns {string} - Tamaño formateado
     */
    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    /**
     * Sube un archivo al servidor
     * @param {File} file - Archivo a subir
     * @returns {Promise<Object>} - Respuesta del servidor
     */
    async uploadFile(file) {
        this.validateFileType(file);
        this.validateFileSize(file);

        const formData = new FormData();
        formData.append('file', file);

        try {
            console.log('📤 Subiendo archivo:', file.name, '(' + this.formatFileSize(file.size) + ')');
            
            // Crear un AbortController para timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 300000); // 5 minutos timeout
            
            const response = await fetch(
                `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.UPLOAD}`,
                {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                }
            );

            clearTimeout(timeoutId);

            if (!response.ok) {
                let errorMessage = 'Error al subir el archivo';
                try {
                    const data = await response.json();
                    errorMessage = data.detail || errorMessage;
                } catch (e) {
                    errorMessage = `Error HTTP ${response.status}: ${response.statusText}`;
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();
            console.log('✅ Archivo procesado exitosamente:', data);
            
            return data;
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('El procesamiento del archivo está tardando demasiado. Por favor, intenta con un archivo más pequeño o intenta de nuevo más tarde.');
            }
            
            console.error('❌ Error al subir archivo:', error);
            console.error('❌ Tipo de error:', error.name);
            console.error('❌ Mensaje:', error.message);
            
            // Verificar si es un error de red
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError') || error.message === 'TypeError: Failed to fetch') {
                throw new Error('No se pudo conectar con el servidor. Verifica que el servidor esté corriendo en ' + CONFIG.API_BASE_URL);
            }
            
            throw new Error(`Error de conexión: ${error.message}`);
        }
    }

    /**
     * Obtiene el estado del sistema
     * @returns {Promise<Object>} - Estado del sistema
     */
    async getSystemStatus() {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.ROOT}`
            );
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            throw new Error(`Error al obtener el estado: ${error.message}`);
        }
    }
}

