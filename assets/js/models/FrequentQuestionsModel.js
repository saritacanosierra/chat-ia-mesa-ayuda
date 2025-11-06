/**
 * Modelo: Preguntas Frecuentes
 * Gestiona las preguntas frecuentes almacenadas
 */
class FrequentQuestionsModel {
    /**
     * Obtiene las preguntas frecuentes
     * @param {number} limit - Número máximo de preguntas
     * @returns {Promise<Object>} - Lista de preguntas y estadísticas
     */
    async getQuestions(limit = 20) {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}/frequent-questions?limit=${limit}`,
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
            console.error('Error al obtener preguntas frecuentes:', error);
            throw new Error(`Error al obtener preguntas frecuentes: ${error.message}`);
        }
    }

    /**
     * Busca preguntas similares
     * @param {string} query - Término de búsqueda
     * @returns {Promise<Array>} - Lista de preguntas encontradas
     */
    async searchQuestions(query) {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}/frequent-questions/search?q=${encodeURIComponent(query)}`,
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

            const data = await response.json();
            return data.questions || [];
        } catch (error) {
            console.error('Error al buscar preguntas:', error);
            throw new Error(`Error al buscar preguntas: ${error.message}`);
        }
    }

    /**
     * Elimina una pregunta frecuente
     * @param {number} questionId - ID de la pregunta
     * @returns {Promise<Object>} - Respuesta del servidor
     */
    async deleteQuestion(questionId) {
        try {
            const response = await fetch(
                `${CONFIG.API_BASE_URL}/frequent-questions/${questionId}`,
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
            console.error('Error al eliminar pregunta:', error);
            throw new Error(`Error al eliminar pregunta: ${error.message}`);
        }
    }
}

