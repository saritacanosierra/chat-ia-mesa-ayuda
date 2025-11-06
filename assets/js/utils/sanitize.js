/**
 * Utilidades para sanitización de texto
 */

const Utils = {
    /**
     * Sanitiza texto para prevenir XSS (solo texto plano)
     * @param {string} text - Texto a sanitizar
     * @returns {string} - Texto sanitizado
     */
    sanitize(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Sanitiza HTML permitiendo solo etiquetas seguras (listas, negritas, etc.)
     * @param {string} html - HTML a sanitizar
     * @returns {string} - HTML sanitizado
     */
    sanitizeHTML(html) {
        // Crear un elemento temporal
        const temp = document.createElement('div');
        temp.innerHTML = html;
        
        // Lista de etiquetas permitidas
        const allowedTags = ['ul', 'li', 'ol', 'strong', 'em', 'b', 'i', 'p', 'br', 'span'];
        const allowedAttributes = [];
        
        // Función recursiva para limpiar elementos
        function cleanElement(element) {
            const children = Array.from(element.children);
            
            children.forEach(child => {
                const tagName = child.tagName.toLowerCase();
                
                // Si la etiqueta no está permitida, extraer su contenido
                if (!allowedTags.includes(tagName)) {
                    // Mover el contenido del hijo al padre
                    while (child.firstChild) {
                        element.insertBefore(child.firstChild, child);
                    }
                    element.removeChild(child);
                } else {
                    // Limpiar atributos no permitidos
                    Array.from(child.attributes).forEach(attr => {
                        if (!allowedAttributes.includes(attr.name)) {
                            child.removeAttribute(attr.name);
                        }
                    });
                    
                    // Limpiar recursivamente los hijos
                    cleanElement(child);
                }
            });
        }
        
        cleanElement(temp);
        
        return temp.innerHTML;
    },

    /**
     * Convierte enumeraciones numéricas en listas HTML
     * @param {string} text - Texto que puede contener enumeraciones
     * @returns {string} - Texto con listas HTML
     */
    convertToLists(text) {
        // Patrón para detectar enumeraciones: número seguido de punto y texto
        // Ejemplo: "1. Primer punto\n2. Segundo punto"
        const listPattern = /(\d+\.\s+[^\n]+(?:\n\d+\.\s+[^\n]+)+)/g;
        
        return text.replace(listPattern, (match) => {
            // Dividir por líneas que empiezan con número
            const items = match.split(/\n(?=\d+\.\s+)/);
            const listItems = items.map(item => {
                // Remover el número y punto inicial
                const cleanItem = item.replace(/^\d+\.\s+/, '').trim();
                // Remover negritas si existen (las conservaremos)
                return `<li>${cleanItem}</li>`;
            }).join('');
            
            return `<ul>${listItems}</ul>`;
        });
    }
};

