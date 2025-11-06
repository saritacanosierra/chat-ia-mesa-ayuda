/**
 * Vista: Mensajes del chat
 * Maneja la visualización de mensajes
 */
class MessageView {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
    }

    /**
     * Agrega un mensaje al chat
     * @param {string} role - Rol del mensaje ('user' o 'assistant')
     * @param {string} text - Texto del mensaje
     * @param {boolean} isTemporary - Si es temporal (para loading)
     * @param {string|null} source - Fuente del mensaje (opcional)
     * @returns {HTMLElement|null} - Elemento del mensaje si es temporal
     */
    addMessage(role, text, isTemporary = false, source = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${role}`;
        
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        
        // Para mensajes del asistente, permitir HTML y convertir listas
        if (role === 'assistant') {
            // Primero convertir enumeraciones a listas HTML
            let processedText = Utils.convertToLists(text);
            // Luego sanitizar HTML permitiendo etiquetas seguras
            bubble.innerHTML = Utils.sanitizeHTML(processedText);
        } else {
            // Para mensajes del usuario, solo texto plano
            bubble.textContent = text;
        }
        
        messageDiv.appendChild(bubble);

        if (source) {
            const sourceDiv = document.createElement('div');
            sourceDiv.className = 'message-source';
            sourceDiv.textContent = `Fuente: ${source}`;
            messageDiv.appendChild(sourceDiv);
        }

        this.container.appendChild(messageDiv);
        this.scrollToBottom();

        return isTemporary ? messageDiv : null;
    }

    /**
     * Hace scroll al final del contenedor
     */
    scrollToBottom() {
        this.container.scrollTop = this.container.scrollHeight;
    }

    /**
     * Obtiene el historial de conversación
     * @returns {Array} - Array de objetos con role y text
     */
    getConversationHistory() {
        const messages = this.container.querySelectorAll('.message');
        const history = [];
        let isFirstMessage = true;
        
        messages.forEach(message => {
            const role = message.classList.contains('user') ? 'user' : 'assistant';
            const bubble = message.querySelector('.message-bubble');
            
            if (bubble) {
                // Obtener texto sin HTML para el historial
                let text = '';
                if (bubble.textContent) {
                    text = bubble.textContent.trim();
                } else if (bubble.innerText) {
                    text = bubble.innerText.trim();
                } else {
                    text = bubble.innerHTML.replace(/<[^>]*>/g, '').trim();
                }
                
                // Excluir mensaje inicial de bienvenida y mensajes temporales
                const isWelcomeMessage = text.includes('👋 ¡Hola! Soy tu asistente') || 
                                        text.includes('¿En qué puedo ayudarte hoy');
                const isProcessingMessage = text.includes('🤔 Procesando');
                
                if (text && !isProcessingMessage && !isWelcomeMessage) {
                    history.push({ role, text: text });
                }
                
                isFirstMessage = false;
            }
        });
        
        return history;
    }
}

