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
        console.log('🌐 NetworkModel.fetchNetworkInfo() - URL:', url);
        
        try {
            console.log('📤 Enviando petición GET a:', url);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            console.log('📥 Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                headers: Object.fromEntries(response.headers.entries())
            });
            
            if (!response.ok) {
                // Si el servidor responde pero con error, intentar leer el mensaje
                let errorMessage = `Error HTTP ${response.status}`;
                try {
                    const errorData = await response.json();
                    console.error('❌ Error data del servidor:', errorData);
                    errorMessage = errorData.detail || errorData.message || errorMessage;
                } catch (e) {
                    console.error('❌ No se pudo parsear el error como JSON:', e);
                    // Si no se puede parsear JSON, usar el status
                }
                throw new Error(errorMessage);
            }
            
            const data = await response.json();
            console.log('✅ Datos parseados correctamente:', data);
            return data;
        } catch (error) {
            console.error('❌ Error en fetchNetworkInfo:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            // Si es un error de red (Failed to fetch), mantener el mensaje original
            if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                console.error('🔴 Error de conexión detectado - El servidor no está respondiendo');
                throw new Error('Failed to fetch');
            }
            throw new Error(`Error al cargar información de la red: ${error.message}`);
        }
    }
}

