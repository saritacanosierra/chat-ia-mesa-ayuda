/**
 * Configuración global de la aplicación
 */
// Detectar automáticamente la base del API según el entorno
// - Si la app corre en localhost pero NO en el puerto 8000 (caso típico XAMPP/Apache),
//   usar la ruta hacia la carpeta `public` servida por Apache.
// - Si corre explícitamente en 8000, mantener ese origen.
const DEFAULT_API_BASE = 'http://localhost:8000';
const currentOrigin = window.location.origin;
const currentPath = window.location.pathname.endsWith('/')
    ? window.location.pathname
    : window.location.pathname + '/';
const apachePublicBase = `${currentOrigin}${currentPath}public`;

let resolvedApiBase = DEFAULT_API_BASE;

// Caso 1: Sirviendo por Apache (localhost con puerto distinto a 8000)
if (window.location.hostname === 'localhost' && window.location.port !== '8000') {
    resolvedApiBase = apachePublicBase;
}

// Caso 2: Archivo abierto con doble clic (file://)
// Preferimos el servidor embebido en 8000 (muy simple de arrancar).
// Si usas XAMPP/Apache, abre la página vía http://localhost/... y esta rama no se ejecuta.
if (window.location.protocol === 'file:') {
    resolvedApiBase = 'http://localhost:8000';
}

const CONFIG = {
    API_BASE_URL: resolvedApiBase,
    ENDPOINTS: {
        UPLOAD: '/upload',
        ASK: '/ask',
        NETWORK_INFO: '/network-info',
        ROOT: '/',
        AUTH_VERIFY: '/auth/verify',
        AUTH_CHANGE_PASSWORD: '/auth/change-password'
    },
    STATUS_MESSAGE_TIMEOUT: 5000,
    MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB
    SYSTEM_STATUS_UPDATE_INTERVAL: 30000 // 30 segundos
};

// Log de configuración al cargar
console.log('🔧 CONFIGURACIÓN DE LA APLICACIÓN:', {
    API_BASE_URL: CONFIG.API_BASE_URL,
    ENDPOINTS: CONFIG.ENDPOINTS,
    URL_COMPLETA_NETWORK_INFO: `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.NETWORK_INFO}`,
    URL_COMPLETA_ASK: `${CONFIG.API_BASE_URL}${CONFIG.ENDPOINTS.ASK}`
});

