/**
 * Modelo: Información de la red
 * Gestiona los datos de la red
 */
class NetworkModel {
    /**
     * Carga la información de la red desde el servidor
     * @returns {Promise<Object>} - Datos de la red
     */
    async fetchNetworkInfo() {
        const url = `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.NETWORK_INFO}`;
        
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                // Si el servidor responde pero con error, intentar leer el mensaje
                let errorMessage = `Error HTTP ${response.status}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.detail || errorData.message || errorMessage;
                } catch (e) {
                    // Si no se puede parsear JSON, usar el status
                }
                throw new Error(errorMessage);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            // Si es un error de red (Failed to fetch), mantener el mensaje original
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                throw new Error('Failed to fetch');
            }
            throw new Error(`Error al cargar información de la red: ${error.message}`);
        }
    }
}

