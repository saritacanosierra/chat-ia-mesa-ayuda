/**
 * Modelo: Preguntas
 * Gestiona las preguntas y respuestas del chat
 */
class QuestionModel {
    /**
     * Envía una pregunta al servidor
     * @param {string} question - Pregunta del usuario
     * @param {Array} conversationHistory - Historial de conversación (opcional)
     * @returns {Promise<Object>} - Respuesta del servidor
     */
    async askQuestion(question, conversationHistory = []) {
        const url = `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.ASK}`;
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    question: question,
                    conversation_history: conversationHistory
                })
            });

            // Verificar si la respuesta es JSON válido
            const contentType = response.headers.get('content-type');
            
            if (!contentType || !contentType.includes('application/json')) {
                // El servidor está respondiendo con HTML (probablemente un error de PHP)
                throw new Error('SERVER_CONFIG_ERROR');
            }

            // Si la respuesta no es OK, intentar leer el mensaje de error
            if (!response.ok) {
                let errorMessage = `Error HTTP ${response.status}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.detail || errorData.message || errorMessage;
                } catch (e) {
                    // Si no se puede parsear JSON, usar el status
                    if (response.status === 400) {
                        errorMessage = 'No hay archivos entrenados. Por favor, sube un archivo primero desde la vista de Archivos BD.';
                    } else if (response.status === 500) {
                        errorMessage = 'Error en el servidor. Por favor, verifica la configuración del backend.';
                    }
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            // Detectar errores de conexión específicamente
            if (error.message.includes('Failed to fetch') || 
                error.message.includes('NetworkError') ||
                error.message === 'Network request failed') {
                throw new Error('CONNECTION_ERROR');
            }
            // Si ya es un Error personalizado, mantenerlo
            throw error;
        }
    }
}

