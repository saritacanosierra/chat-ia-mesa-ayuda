/**
 * Controlador: Vista Usuario
 * Coordina la lógica entre modelos y vistas para la vista de usuario
 */
class UsuarioController {
    constructor() {
        // Modelos
        this.appStateModel = new AppStateModel();
        this.networkModel = new NetworkModel();
        this.questionModel = new QuestionModel();

        // Vistas
        this.messageView = new MessageView('messages');
        this.networkInfoView = new NetworkInfoView('networkInfoCard');
        this.robot3DView = new Robot3DView('robot3DViewer', this);

        // Elementos del DOM
        this.questionInput = document.getElementById('questionInput');
        this.sendBtn = document.getElementById('sendBtn');
        this.configBtn = document.getElementById('configBtn');

        // Inicializar
        this.init();
    }

    /**
     * Inicializa el controlador
     */
    init() {
        console.log('🚀 Inicializando UsuarioController...');
        console.log('📋 Elementos del DOM encontrados:', {
            questionInput: !!this.questionInput,
            sendBtn: !!this.sendBtn,
            configBtn: !!this.configBtn
        });
        this.setupEventListeners();
        console.log('📡 Cargando información de la red...');
        this.loadNetworkInfo();
        this.appStateModel.setFileLoaded(true); // Usuario siempre puede hacer preguntas
        
        // Inicializar robot 3D
        this.initRobot3D();
        
        console.log('✅ Chat IA (Vista Usuario) inicializado correctamente');
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Envío de pregunta
        this.sendBtn.addEventListener('click', () => this.handleSendQuestion());

        // Envío con Enter
        this.questionInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleSendQuestion();
            }
        });

        // Verificar estado de autenticación al cargar
        this.checkAuthStatus();

        // Botón de configuración - Muestra/oculta menú desplegable
        if (this.configBtn) {
            const configMenu = document.getElementById('configMenu');
            const contactAdvisorBtn = document.getElementById('contactAdvisorBtn');
            const loginConfigBtn = document.getElementById('loginConfigBtn');
            const configMenuItem = document.getElementById('configMenuItem');
            
            this.configBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isVisible = configMenu.style.display === 'block';
                configMenu.style.display = isVisible ? 'none' : 'block';
            });

            // Cerrar menú al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!this.configBtn.contains(e.target) && configMenu && !configMenu.contains(e.target)) {
                    configMenu.style.display = 'none';
                }
            });

            // Botón para ingresar a configuración
            if (loginConfigBtn) {
                loginConfigBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.showAuthModal();
                    if (configMenu) {
                        configMenu.style.display = 'none';
                    }
                });
            }

            // Botón para contactar asesor
            if (contactAdvisorBtn) {
                contactAdvisorBtn.addEventListener('click', () => {
                    this.handleContactAdvisor();
                    if (configMenu) {
                        configMenu.style.display = 'none';
                    }
                });
            }
        }

        // Configurar modal de autenticación
        this.setupAuthModal();

    }

    /**
     * Verifica el estado de autenticación y actualiza la UI
     */
    checkAuthStatus() {
        const isAuthenticated = sessionStorage.getItem('admin_authenticated') === 'true';
        const configMenuItem = document.getElementById('configMenuItem');
        const loginConfigBtn = document.getElementById('loginConfigBtn');
        
        if (isAuthenticated) {
            if (configMenuItem) configMenuItem.style.display = 'flex';
            if (loginConfigBtn) loginConfigBtn.style.display = 'none';
        } else {
            if (configMenuItem) configMenuItem.style.display = 'none';
            if (loginConfigBtn) loginConfigBtn.style.display = 'flex';
        }
    }

    /**
     * Configura el modal de autenticación
     */
    setupAuthModal() {
        const authModal = document.getElementById('authModal');
        const closeAuthModal = document.getElementById('closeAuthModal');
        const cancelAuthBtn = document.getElementById('cancelAuthBtn');
        const loginBtn = document.getElementById('loginBtn');
        const passwordInput = document.getElementById('passwordInput');
        const authError = document.getElementById('authError');

        // Cerrar modal
        const closeModal = () => {
            authModal.style.display = 'none';
            passwordInput.value = '';
            authError.style.display = 'none';
        };

        if (closeAuthModal) {
            closeAuthModal.addEventListener('click', closeModal);
        }

        if (cancelAuthBtn) {
            cancelAuthBtn.addEventListener('click', closeModal);
        }

        // Cerrar al hacer clic en el overlay
        const overlay = authModal.querySelector('.auth-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeModal);
        }

        // Cerrar con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && authModal.style.display === 'flex') {
                closeModal();
            }
        });

        // Botón de login
        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                this.handleLogin();
            });
        }

        // Enter en el campo de contraseña
        if (passwordInput) {
            passwordInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleLogin();
                }
            });
        }
    }

    /**
     * Muestra el modal de autenticación
     */
    showAuthModal() {
        const authModal = document.getElementById('authModal');
        const passwordInput = document.getElementById('passwordInput');
        if (authModal) {
            authModal.style.display = 'flex';
            // Enfocar el campo de contraseña
            setTimeout(() => {
                if (passwordInput) passwordInput.focus();
            }, 100);
        }
    }

    /**
     * Maneja el proceso de login
     */
    async handleLogin() {
        const passwordInput = document.getElementById('passwordInput');
        const authError = document.getElementById('authError');
        const authModal = document.getElementById('authModal');

        if (!passwordInput || !authError || !authModal) return;

        const password = passwordInput.value.trim();

        if (!password) {
            authError.textContent = 'Por favor ingresa la contraseña';
            authError.style.display = 'block';
            return;
        }

        try {
            // Verificar contraseña con el backend
            const response = await fetch(`${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.AUTH_VERIFY}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ password: password })
            });

            const data = await response.json();

            if (data.authenticated) {
                // Autenticación exitosa
                sessionStorage.setItem('admin_authenticated', 'true');
                authModal.style.display = 'none';
                passwordInput.value = '';
                authError.style.display = 'none';
                
                // Actualizar UI
                this.checkAuthStatus();
                
                // Mostrar mensaje de éxito
                this.messageView.addMessage('assistant', '✅ Autenticación exitosa. Ahora puedes acceder a la configuración.');
            } else {
                // Contraseña incorrecta
                authError.textContent = '❌ ' + (data.message || 'Contraseña incorrecta');
                authError.style.display = 'block';
                passwordInput.value = '';
                passwordInput.focus();
            }
        } catch (error) {
            console.error('Error al verificar contraseña:', error);
            authError.textContent = '❌ Error de conexión. Por favor, intenta de nuevo.';
            authError.style.display = 'block';
        }
    }

    /**
     * Maneja el contacto con un asesor
     */
    handleContactAdvisor() {
        // Mostrar información de contacto en el chat
        const contactInfo = `Para contactar con un asesor, puedes usar cualquiera de las siguientes opciones:

<ul>
<li>📧 <strong>Email:</strong> soporte@mesaayuda.com</li>
<li>📞 <strong>Teléfono:</strong> +57 1 234 5678</li>
<li>🎫 <strong>Ticket:</strong> Crea un ticket en el sistema de gestión</li>
</ul>

Horario de atención: Lunes a Viernes de 8:00 AM a 6:00 PM`;
        
        this.messageView.addMessage('assistant', contactInfo);
    }

    /**
     * Maneja el envío de una pregunta
     */
    async handleSendQuestion() {
        const question = this.questionInput.value.trim();
        
        if (!question || !this.appStateModel.canProcess()) {
            return;
        }

        // Obtener historial de conversación ANTES de agregar el nuevo mensaje
        const history = this.messageView.getConversationHistory();
        const recentHistory = history.slice(-10); // Solo últimos 10 mensajes

        // Agregar pregunta del usuario
        this.messageView.addMessage('user', question);
        this.questionInput.value = '';
        this.setInputEnabled(false);

        // Mostrar mensaje de carga
        const loadingMessage = this.messageView.addMessage(
            'assistant',
            '🤔 Procesando tu pregunta...',
            true
        );

        this.appStateModel.setProcessing(true);

        try {
            const response = await this.questionModel.askQuestion(question, recentHistory);
            
            // Remover mensaje de carga
            if (loadingMessage) {
                loadingMessage.remove();
            }

            // Mostrar respuesta
            this.messageView.addMessage(
                'assistant',
                response.answer,
                false,
                response.source
            );
        } catch (error) {
            if (loadingMessage) {
                loadingMessage.remove();
            }
            
            // Mensaje de error más amigable según el tipo de error
            let errorMessage = '';
            if (error.message === 'CONNECTION_ERROR') {
                errorMessage = '⚠️ No se pudo conectar con el servidor backend.\n\n' +
                    'Por favor, verifica que:\n' +
                    '• El servidor esté corriendo en http://localhost:8000\n' +
                    '• Las dependencias de Composer estén instaladas (ejecuta: composer install)\n' +
                    '• El archivo .env esté configurado con tu API key de Gemini';
            } else if (error.message === 'SERVER_CONFIG_ERROR') {
                errorMessage = '⚠️ Error de configuración del servidor.\n\n' +
                    'El servidor está corriendo pero no puede ejecutar el código PHP.\n\n' +
                    '🔧 SOLUCIÓN:\n' +
                    '1. Abre una terminal en la carpeta del proyecto\n' +
                    '2. Ejecuta: composer install\n' +
                    '3. Asegúrate de tener un archivo .env con tu API key de Gemini\n' +
                    '4. Reinicia el servidor PHP\n\n' +
                    'Esto NO es un problema de conexión con Gemini, sino que faltan las dependencias de Composer.';
            } else if (error.message.includes('No hay archivo entrenado')) {
                errorMessage = '📭 No hay archivos entrenados en el sistema.\n\n' +
                    'Por favor, ve a Archivos BD (icono ⚙️) y sube un documento PDF, TXT, XLSX o MD para comenzar.';
            } else if (error.message.includes('RATE_LIMIT_EXCEEDED') || 
                       error.message.includes('Resource exhausted') ||
                       error.message.includes('429')) {
                errorMessage = '⏳ Lo siento, el servicio de IA está temporalmente sobrecargado.\n\n' +
                    'Esto puede ocurrir cuando hay muchas solicitudes simultáneas. Por favor:\n\n' +
                    '• Espera unos momentos (30-60 segundos) y vuelve a intentar\n' +
                    '• Intenta con una pregunta más corta\n' +
                    '• Si el problema persiste, verifica los límites de tu cuenta de Google Gemini API\n\n' +
                    'El servicio debería estar disponible nuevamente en breve. 😊';
            } else {
                errorMessage = `❌ ${error.message}`;
            }
            
            this.messageView.addMessage('assistant', errorMessage);
        } finally {
            this.appStateModel.setProcessing(false);
            this.setInputEnabled(true);
            this.questionInput.focus();
        }
    }

    /**
     * Habilita o deshabilita el input
     * @param {boolean} enabled - Si está habilitado
     */
    setInputEnabled(enabled) {
        this.questionInput.disabled = !enabled;
        this.sendBtn.disabled = !enabled;
        this.sendBtn.textContent = enabled ? 'Enviar' : '';
    }

    /**
     * Inicializa el robot 3D
     */
    async initRobot3D() {
        try {
            // Puedes pasar una URL personalizada de Spline aquí
            // Si no se pasa, usará un ejemplo por defecto
            // Para usar tu propio robot: exporta desde Spline Design y sube el archivo .splinecode
            await this.robot3DView.init();
            console.log('🤖 Robot 3D inicializado');
        } catch (error) {
            console.warn('⚠️ No se pudo cargar el robot 3D:', error);
            // El robot mostrará un fallback automáticamente
        }
    }

    /**
     * Carga la información de la red
     */
    async loadNetworkInfo() {
        console.log('📡 loadNetworkInfo() - Iniciando...');
        this.networkInfoView.displayLoading();
        
        try {
            console.log('📡 Intentando obtener información de la red desde:', `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.NETWORK_INFO}`);
            const data = await this.networkModel.fetchNetworkInfo();
            console.log('✅ Información de la red recibida:', data);
            this.networkInfoView.displayNetworkInfo(data);
        } catch (error) {
            // Si hay error, simplemente ocultar la sección de información de red
            // No es crítica para el funcionamiento del chat
            console.error('❌ Error al cargar información de la red:', {
                message: error.message,
                stack: error.stack,
                tipo: error.name
            });
            const section = document.getElementById('networkInfoSection');
            if (section) {
                section.style.display = 'none';
            }
            console.warn('⚠️ Se ocultó la sección de información de red debido al error');
        }
    }

}

// Inicializar cuando el DOM esté listo
console.log('📄 Estado del DOM:', document.readyState);
if (document.readyState === 'loading') {
    console.log('⏳ Esperando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ DOMContentLoaded - Creando UsuarioController');
        new UsuarioController();
    });
} else {
    console.log('✅ DOM ya está listo - Creando UsuarioController inmediatamente');
    new UsuarioController();
}

