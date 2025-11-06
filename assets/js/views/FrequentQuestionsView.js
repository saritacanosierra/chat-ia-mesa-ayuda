/**
 * Vista: Preguntas Frecuentes
 * Maneja la visualización de preguntas frecuentes
 */
class FrequentQuestionsView {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
    }

    /**
     * Muestra las preguntas frecuentes
     * @param {Array} questions - Lista de preguntas
     * @param {Object} stats - Estadísticas
     * @param {Function} onDelete - Callback para eliminar pregunta
     */
    displayQuestions(questions, stats, onDelete) {
        if (!questions || questions.length === 0) {
            this.container.innerHTML = `
                <div class="empty-files">
                    📭 No hay preguntas frecuentes registradas aún.
                </div>
            `;
            return;
        }

        let html = `
            <div class="files-stats">
                <span>📊 Total: ${stats.total_questions || 0} pregunta(s)</span>
                <span>🔢 Total de veces preguntadas: ${stats.total_asked || 0}</span>
            </div>
            <div class="questions-list">
        `;

        questions.forEach((question, index) => {
            const formattedDate = this.formatDate(question.last_asked_at);
            const source = question.source ? ` (${Utils.sanitize(question.source)})` : '';
            
            html += `
                <div class="question-item">
                    <div class="question-header">
                        <span class="question-number">#${index + 1}</span>
                        <span class="question-count">🔄 ${question.times_asked} vez${question.times_asked > 1 ? 'es' : ''}</span>
                        <button class="delete-btn-small" data-question-id="${question.id}" title="Eliminar pregunta">
                            🗑️
                        </button>
                    </div>
                    <div class="question-text">
                        <strong>❓ Pregunta:</strong> ${Utils.sanitize(question.question)}
                    </div>
                    <div class="answer-text">
                        <strong>💬 Respuesta:</strong> ${Utils.sanitize(question.answer)}
                        ${source ? `<span class="question-source">${source}</span>` : ''}
                    </div>
                    <div class="question-meta">
                        Última vez: ${formattedDate}
                    </div>
                </div>
            `;
        });

        html += '</div>';
        this.container.innerHTML = html;

        // Agregar event listeners a los botones de eliminar
        this.container.querySelectorAll('.delete-btn-small').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const questionId = e.target.closest('.delete-btn-small').getAttribute('data-question-id');
                if (questionId && onDelete) {
                    onDelete(parseInt(questionId));
                }
            });
        });
    }

    /**
     * Muestra un estado de carga
     */
    displayLoading() {
        this.container.innerHTML = '<div class="loading">Cargando preguntas frecuentes...</div>';
    }

    /**
     * Muestra un error
     */
    displayError(message) {
        this.container.innerHTML = `
            <div class="empty-files" style="color: var(--color-error);">
                ❌ ${Utils.sanitize(message)}
            </div>
        `;
    }

    /**
     * Formatea la fecha
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }
}

