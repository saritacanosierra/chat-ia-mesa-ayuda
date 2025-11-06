<?php
/**
 * Punto de entrada de la aplicación PHP
 * Backend para Chat IA con RAG - Estructura MVC
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Configurar timeouts para procesamiento de archivos grandes
set_time_limit(600); // 10 minutos
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurar error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Verificar configuración según el proveedor (más flexible con fallback)
$aiProvider = strtolower($_ENV['AI_PROVIDER'] ?? 'gemini');
$hasGemini = isset($_ENV['GEMINI_API_KEY']) && !empty($_ENV['GEMINI_API_KEY']);

// Si el proveedor es Gemini pero no hay API key, verificar si hay Ollama como respaldo
if ($aiProvider === 'gemini' && !$hasGemini) {
    // Intentar verificar si Ollama está disponible
    $ollamaUrl = $_ENV['OLLAMA_BASE_URL'] ?? 'http://localhost:11434';
    $ch = curl_init($ollamaUrl . '/api/tags');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_CONNECTTIMEOUT => 1
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new RuntimeException('GEMINI_API_KEY no encontrada en el archivo .env y Ollama no está disponible. ' .
            'Configura GEMINI_API_KEY o instala Ollama (https://ollama.ai/download)');
    }
    
    error_log('⚠️ Gemini no configurado, pero Ollama está disponible como respaldo');
}

// Crear aplicación Slim
$app = AppFactory::create();

// Configurar CORS
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, DELETE, OPTIONS');
});

// Middleware para parsear JSON
$app->addBodyParsingMiddleware();

// Rutas
$app->get('/', function (Request $request, Response $response) {
    $data = [
        'message' => 'API de Chat IA funcionando',
        'status' => 'ok',
        'endpoints' => [
            'upload' => '/upload - Sube un archivo PDF, TXT, XLSX o MD para entrenar',
            'ask' => '/ask - Haz una pregunta sobre el contenido de los archivos',
            'network_info' => '/network-info - Información básica de la red',
            'ai_status' => '/ai-status - Estado del proveedor de IA (Gemini/Ollama)',
            'files' => '/files - Lista de archivos almacenados',
            'frequent_questions' => '/frequent-questions - Preguntas más frecuentes',
            'ollama_models' => '/ollama-models - Lista de modelos instalados en Ollama'
        ]
    ];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/network-info', 'App\Controllers\NetworkInfoController:getNetworkInfo');
$app->get('/ai-status', 'App\Controllers\AIStatusController:getAIStatus');
$app->post('/upload', 'App\Controllers\UploadController:uploadFile');
$app->post('/ask', 'App\Controllers\AskController:askQuestion');
$app->get('/files', 'App\Controllers\FilesController:listFiles');
$app->delete('/files/{id}', 'App\Controllers\FilesController:deleteFile');
$app->get('/frequent-questions', 'App\Controllers\FrequentQuestionsController:listQuestions');
$app->get('/frequent-questions/search', 'App\Controllers\FrequentQuestionsController:searchQuestions');
$app->delete('/frequent-questions/{id}', 'App\Controllers\FrequentQuestionsController:deleteQuestion');
$app->post('/auth/verify', 'App\Controllers\AuthController:verifyPassword');
$app->post('/auth/change-password', 'App\Controllers\AuthController:changePassword');
$app->get('/ollama-models', 'App\Controllers\OllamaModelsController:listModels');

// Manejar OPTIONS para CORS
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->run();
