/**
 * Controlador: Archivos BD
 * Coordina la lógica entre modelos y vistas para la gestión de archivos
 */
class ArchivosBDController {
    constructor() {
        // Modelos
        this.appStateModel = new AppStateModel();
        this.fileModel = new FileModel();
        this.filesModel = new FilesModel();
        this.frequentQuestionsModel = new FrequentQuestionsModel();

        // Vistas
        this.fileUploadView = new FileUploadView(
            'fileInput',
            'uploadBtn',
            'fileInfo',
            'statusMessages',
            'systemStatus'
        );
        this.filesTableView = new FilesTableView('filesTableContainer');
        this.frequentQuestionsView = new FrequentQuestionsView('frequentQuestionsContainer');

        // Inicializar
        this.init();
    }

    /**
     * Inicializa el controlador
     */
    init() {
        this.setupEventListeners();
        // No cargar automáticamente, solo cuando se abran los modales
        this.checkSystemStatus();
        this.setupStatusInterval();
        console.log('✅ Archivos BD inicializado correctamente');
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Selección de archivo
        this.fileUploadView.fileInput.addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files[0]);
        });

        // Subida de archivo
        this.fileUploadView.uploadBtn.addEventListener('click', () => {
            this.handleUpload();
        });

        // Botón de salir (cerrar sesión)
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                this.handleLogout();
            });
        }

        // Botones para abrir modales
        const viewFilesBtn = document.getElementById('viewFilesBtn');
        const viewQuestionsBtn = document.getElementById('viewQuestionsBtn');
        
        if (viewFilesBtn) {
            viewFilesBtn.addEventListener('click', () => {
                this.openFilesModal();
            });
        }

        if (viewQuestionsBtn) {
            viewQuestionsBtn.addEventListener('click', () => {
                this.openQuestionsModal();
            });
        }

        // Botones para cerrar modales
        const closeFilesModal = document.getElementById('closeFilesModal');
        const closeQuestionsModal = document.getElementById('closeQuestionsModal');
        const filesModalOverlay = document.getElementById('filesModalOverlay');
        const questionsModalOverlay = document.getElementById('questionsModalOverlay');

        if (closeFilesModal) {
            closeFilesModal.addEventListener('click', () => {
                this.closeFilesModal();
            });
        }

        if (closeQuestionsModal) {
            closeQuestionsModal.addEventListener('click', () => {
                this.closeQuestionsModal();
            });
        }

        // Cerrar al hacer clic en el overlay
        if (filesModalOverlay) {
            filesModalOverlay.addEventListener('click', () => {
                this.closeFilesModal();
            });
        }

        if (questionsModalOverlay) {
            questionsModalOverlay.addEventListener('click', () => {
                this.closeQuestionsModal();
            });
        }

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeFilesModal();
                this.closeQuestionsModal();
            }
        });
    }

    /**
     * Abre el modal de archivos
     */
    openFilesModal() {
        const modal = document.getElementById('filesModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevenir scroll del body
            this.loadFiles(); // Cargar archivos cuando se abre el modal
        }
    }

    /**
     * Cierra el modal de archivos
     */
    closeFilesModal() {
        const modal = document.getElementById('filesModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Restaurar scroll
        }
    }

    /**
     * Abre el modal de preguntas frecuentes
     */
    openQuestionsModal() {
        const modal = document.getElementById('questionsModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevenir scroll del body
            this.loadFrequentQuestions(); // Cargar preguntas cuando se abre el modal
        }
    }

    /**
     * Cierra el modal de preguntas frecuentes
     */
    closeQuestionsModal() {
        const modal = document.getElementById('questionsModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Restaurar scroll
        }
    }

    /**
     * Maneja la selección de un archivo
     * @param {File} file - Archivo seleccionado
     */
    handleFileSelect(file) {
        console.log('📁 Archivo seleccionado:', file);
        
        if (!file) {
            this.appStateModel.setSelectedFile(null);
            this.fileUploadView.clearFileInfo();
            return;
        }

        // Verificar que no sea una carpeta (los archivos tienen size > 0 o name con extensión)
        if (file.size === 0 && !file.name.includes('.')) {
            this.fileUploadView.showStatusMessage('❌ Por favor selecciona un archivo, no una carpeta', 'error');
            this.fileUploadView.clearFileInput();
            this.appStateModel.setSelectedFile(null);
            return;
        }

        try {
            this.fileModel.validateFileType(file);
            this.fileModel.validateFileSize(file);
            
            this.appStateModel.setSelectedFile(file);
            const formattedSize = this.fileModel.formatFileSize(file.size);
            this.fileUploadView.displayFileInfo(file, formattedSize);
            console.log('✅ Archivo validado correctamente:', file.name);
        } catch (error) {
            console.error('❌ Error al validar archivo:', error);
            this.fileUploadView.showStatusMessage(`❌ ${error.message}`, 'error');
            this.fileUploadView.clearFileInput();
            this.appStateModel.setSelectedFile(null);
        }
    }

    /**
     * Maneja la subida del archivo
     */
    async handleUpload() {
        if (!this.appStateModel.canUpload()) {
            return;
        }

        const file = this.appStateModel.selectedFile;
        this.appStateModel.setUploading(true);
        
        // Mostrar mensaje inicial y barra de progreso
        this.fileUploadView.setUploadButtonState(true, 'Subir y Procesar', 'Subiendo archivo...');
        this.fileUploadView.showStatusMessage('📤 Subiendo archivo al servidor...', 'info');
        this.fileUploadView.showProgressBar();
        
        // Estimar duración basada en el tamaño del archivo
        // Archivos más grandes = más tiempo (estimación: ~1 segundo por 10KB + tiempo base)
        const estimatedDuration = Math.max(60, Math.min(600, (file.size / 10240) * 1.5 + 120));
        
        // Iniciar barra de progreso estimada
        this.fileUploadView.startEstimatedProgress(estimatedDuration);

        try {
            console.log('🚀 Iniciando carga de archivo:', file.name);
            console.log('⏱️ Duración estimada:', Math.round(estimatedDuration), 'segundos');
            
            const response = await this.fileModel.uploadFile(file);
            
            // Completar la barra de progreso
            this.fileUploadView.stopEstimatedProgress();
            this.fileUploadView.updateProgress(100, '✅ Procesamiento completado');
            
            console.log('✅ Archivo procesado exitosamente:', response);
            
            this.fileUploadView.showStatusMessage(
                `✅ ${response.message} (${response.chunks} fragmentos procesados)`,
                'success'
            );
            
            // Esperar un momento antes de ocultar la barra para que el usuario vea el 100%
            setTimeout(() => {
                this.fileUploadView.hideProgressBar();
            }, 1000);
            
            this.fileUploadView.clearFileInput();
            this.fileUploadView.clearFileInfo();
            this.appStateModel.setSelectedFile(null);
            this.appStateModel.setFileLoaded(true);
            // Recargar solo si los modales están abiertos
            if (document.getElementById('filesModal').style.display === 'flex') {
                this.loadFiles();
            }
            if (document.getElementById('questionsModal').style.display === 'flex') {
                this.loadFrequentQuestions();
            }
            this.checkSystemStatus();
        } catch (error) {
            console.error('❌ Error al subir archivo:', error);
            this.fileUploadView.stopEstimatedProgress();
            this.fileUploadView.hideProgressBar();
            this.fileUploadView.showStatusMessage(`❌ ${error.message}`, 'error');
            
            // Mostrar más detalles del error en consola
            if (error.message.includes('Error de conexión')) {
                console.error('💡 Sugerencia: Verifica que el servidor esté corriendo en', CONFIG.API_BASE_URL);
            }
        } finally {
            this.appStateModel.setUploading(false);
            this.fileUploadView.setUploadButtonState(false, 'Subir y Procesar');
        }
    }

    /**
     * Verifica el estado del sistema
     */
    async checkSystemStatus() {
        try {
            const data = await this.fileModel.getSystemStatus();
            this.fileUploadView.displaySystemStatus(data);
        } catch (error) {
            this.fileUploadView.displaySystemError(error.message);
        }
    }

    /**
     * Configura el intervalo para actualizar el estado del sistema
     */
    setupStatusInterval() {
        setInterval(() => {
            this.checkSystemStatus();
        }, CONFIG.SYSTEM_STATUS_UPDATE_INTERVAL);
    }

    /**
     * Carga la lista de archivos almacenados
     */
    async loadFiles() {
        this.filesTableView.displayLoading();
        
        try {
            const data = await this.filesModel.getFiles();
            this.filesTableView.displayFiles(data.files, data.stats, (fileId) => {
                this.handleDeleteFile(fileId);
            });
        } catch (error) {
            console.error('Error al cargar archivos:', error);
            this.filesTableView.displayError(error.message);
        }
    }

    /**
     * Maneja la eliminación de un archivo
     */
    async handleDeleteFile(fileId) {
        if (!confirm('¿Estás seguro de que deseas eliminar este archivo? Esto también eliminará todos sus fragmentos del conocimiento base.')) {
            return;
        }

        try {
            await this.filesModel.deleteFile(fileId);
            this.fileUploadView.showStatusMessage('✅ Archivo eliminado exitosamente', 'success');
            // Recargar solo si el modal está abierto
            if (document.getElementById('filesModal').style.display === 'flex') {
                this.loadFiles();
            }
            this.checkSystemStatus();
        } catch (error) {
            console.error('Error al eliminar archivo:', error);
            this.fileUploadView.showStatusMessage(`❌ ${error.message}`, 'error');
        }
    }

    /**
     * Carga las preguntas frecuentes
     */
    async loadFrequentQuestions() {
        this.frequentQuestionsView.displayLoading();
        
        try {
            const data = await this.frequentQuestionsModel.getQuestions(20);
            this.frequentQuestionsView.displayQuestions(
                data.questions, 
                data.stats,
                (questionId) => {
                    this.handleDeleteQuestion(questionId);
                }
            );
        } catch (error) {
            console.error('Error al cargar preguntas frecuentes:', error);
            this.frequentQuestionsView.displayError(error.message);
        }
    }

    /**
     * Maneja el cierre de sesión (logout)
     */
    handleLogout() {
        if (confirm('¿Estás seguro de que deseas salir? Se cerrará tu sesión y perderás acceso a la configuración.')) {
            // Limpiar sesión
            sessionStorage.removeItem('admin_authenticated');
            
            // Redirigir a la página principal
            window.location.href = 'index.html';
        }
    }

    /**
     * Maneja la eliminación de una pregunta frecuente
     */
    async handleDeleteQuestion(questionId) {
        if (!confirm('¿Estás seguro de que deseas eliminar esta pregunta frecuente?')) {
            return;
        }

        try {
            await this.frequentQuestionsModel.deleteQuestion(questionId);
            this.fileUploadView.showStatusMessage('✅ Pregunta eliminada exitosamente', 'success');
            // Recargar solo si el modal está abierto
            if (document.getElementById('questionsModal').style.display === 'flex') {
                this.loadFrequentQuestions();
            }
        } catch (error) {
            console.error('Error al eliminar pregunta:', error);
            this.fileUploadView.showStatusMessage(`❌ ${error.message}`, 'error');
        }
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ArchivosBDController();
    });
} else {
    new ArchivosBDController();
}

