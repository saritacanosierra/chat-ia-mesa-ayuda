/**
 * Vista: Carga de archivos
 * Maneja la visualización de la carga de archivos
 */
class FileUploadView {
    constructor(fileInputId, uploadBtnId, fileInfoId, statusMessagesId, systemStatusId) {
        this.fileInput = document.getElementById(fileInputId);
        this.uploadBtn = document.getElementById(uploadBtnId);
        this.fileInfo = document.getElementById(fileInfoId);
        this.statusMessages = document.getElementById(statusMessagesId);
        this.systemStatus = document.getElementById(systemStatusId);
        
        // Elementos de la barra de progreso
        this.progressContainer = document.getElementById('progressContainer');
        this.progressFill = document.getElementById('progressFill');
        this.progressText = document.getElementById('progressText');
        this.progressStatus = document.getElementById('progressStatus');
        
        this.progressInterval = null;
    }

    /**
     * Muestra información del archivo seleccionado
     * @param {File} file - Archivo seleccionado
     * @param {string} formattedSize - Tamaño formateado
     */
    displayFileInfo(file, formattedSize) {
        this.fileInfo.textContent = `Archivo seleccionado: ${file.name} (${formattedSize})`;
        this.uploadBtn.disabled = false;
    }

    /**
     * Limpia la información del archivo
     */
    clearFileInfo() {
        this.fileInfo.textContent = '';
        this.uploadBtn.disabled = true;
    }

    /**
     * Actualiza el estado del botón de carga
     * @param {boolean} isUploading - Si está subiendo
     * @param {string} text - Texto del botón
     * @param {string} progressMessage - Mensaje de progreso opcional
     */
    setUploadButtonState(isUploading, text, progressMessage = '') {
        this.uploadBtn.disabled = isUploading;
        
        if (isUploading) {
            let buttonText = '<span class="loading"></span> Procesando...';
            if (progressMessage) {
                buttonText += `<br><small style="font-size: 0.8em; opacity: 0.8;">${progressMessage}</small>`;
            }
            this.uploadBtn.innerHTML = buttonText;
        } else {
            this.uploadBtn.innerHTML = text;
        }
    }

    /**
     * Muestra un mensaje de estado
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo de mensaje ('success', 'error', 'warning')
     */
    showStatusMessage(message, type) {
        const statusDiv = document.createElement('div');
        statusDiv.className = `status-message ${type}`;
        statusDiv.textContent = message;
        this.statusMessages.appendChild(statusDiv);

        setTimeout(() => {
            statusDiv.remove();
        }, CONFIG.STATUS_MESSAGE_TIMEOUT);
    }

    /**
     * Muestra el estado del sistema
     * @param {Object} data - Datos del sistema
     */
    displaySystemStatus(data) {
        this.systemStatus.innerHTML = `
            <h3>✅ Sistema Operativo</h3>
            <p><strong>Estado:</strong> ${data.status}</p>
            <p><strong>Mensaje:</strong> ${data.message}</p>
            <p><strong>Endpoints disponibles:</strong></p>
            <ul>
                <li>${data.endpoints.upload}</li>
                <li>${data.endpoints.ask}</li>
            </ul>
        `;
    }

    /**
     * Muestra un error en el estado del sistema
     * @param {string} message - Mensaje de error
     */
    displaySystemError(message) {
        this.systemStatus.innerHTML = `
            <p style="color: var(--color-error);">❌ Error al conectar con el servidor: ${Utils.sanitize(message)}</p>
        `;
    }

    /**
     * Limpia el input de archivo
     */
    clearFileInput() {
        this.fileInput.value = '';
    }

    /**
     * Muestra la barra de progreso
     */
    showProgressBar() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'block';
            this.updateProgress(0, 'Iniciando carga...');
        }
    }

    /**
     * Oculta la barra de progreso
     */
    hideProgressBar() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'none';
        }
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }

    /**
     * Actualiza la barra de progreso
     * @param {number} percentage - Porcentaje (0-100)
     * @param {string} status - Texto de estado
     */
    updateProgress(percentage, status = '') {
        if (!this.progressContainer || !this.progressFill) return;
        
        // Limitar el porcentaje entre 0 y 100
        percentage = Math.max(0, Math.min(100, percentage));
        
        this.progressFill.style.width = percentage + '%';
        if (this.progressText) {
            this.progressText.textContent = Math.round(percentage) + '%';
        }
        if (this.progressStatus && status) {
            this.progressStatus.textContent = status;
        }
    }

    /**
     * Inicia una animación de progreso estimado
     * @param {number} estimatedDuration - Duración estimada en segundos
     * @param {Array<Object>} stages - Etapas del proceso [{name, duration, startPercent}]
     */
    startEstimatedProgress(estimatedDuration = 300, stages = []) {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        const startTime = Date.now();
        const defaultStages = stages.length > 0 ? stages : [
            { name: 'Subiendo archivo...', duration: 0.05, startPercent: 0 },
            { name: 'Procesando documento...', duration: 0.15, startPercent: 5 },
            { name: 'Generando embeddings...', duration: 0.70, startPercent: 20 },
            { name: 'Guardando en base de datos...', duration: 0.10, startPercent: 90 }
        ];

        let currentStageIndex = 0;
        let stageStartTime = startTime;

        this.progressInterval = setInterval(() => {
            const elapsed = (Date.now() - startTime) / 1000; // segundos
            const progress = Math.min(95, (elapsed / estimatedDuration) * 100); // Máximo 95% hasta que termine

            // Determinar la etapa actual basada en el tiempo
            let accumulatedPercent = 0;
            for (let i = 0; i < defaultStages.length; i++) {
                const stage = defaultStages[i];
                const stageDuration = estimatedDuration * stage.duration;
                
                if (elapsed <= accumulatedPercent + stageDuration) {
                    currentStageIndex = i;
                    break;
                }
                accumulatedPercent += stageDuration;
            }

            // Calcular porcentaje dentro de la etapa actual
            const currentStage = defaultStages[currentStageIndex];
            const stageProgress = Math.min(100, (progress - currentStage.startPercent) / 
                (100 - currentStage.startPercent) * 100);

            this.updateProgress(progress, currentStage.name);
        }, 100); // Actualizar cada 100ms
    }

    /**
     * Detiene la animación de progreso estimado
     */
    stopEstimatedProgress() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }
}

