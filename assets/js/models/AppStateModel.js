/**
 * Modelo: Estado de la aplicación
 * Gestiona el estado global de la aplicación
 */
class AppStateModel {
    constructor() {
        this.isProcessing = false;
        this.isUploading = false;
        this.isFileLoaded = false;
        this.selectedFile = null;
    }

    setProcessing(value) {
        this.isProcessing = value;
    }

    setUploading(value) {
        this.isUploading = value;
    }

    setFileLoaded(value) {
        this.isFileLoaded = value;
    }

    setSelectedFile(file) {
        this.selectedFile = file;
    }

    canProcess() {
        return !this.isProcessing && this.isFileLoaded;
    }

    canUpload() {
        return !this.isUploading && this.selectedFile !== null;
    }
}

