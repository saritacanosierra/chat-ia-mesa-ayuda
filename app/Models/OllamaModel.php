<?php
/**
 * Modelo: Ollama
 * Gestiona la interacción con Ollama (LLM local gratuito)
 * Compatible con PHP 8.0+
 * 
 * Requisitos:
 * - Ollama instalado y corriendo en localhost:11434
 * - Modelo descargado (ej: llama2, mistral, codellama)
 * 
 * Instalación de Ollama:
 * - Windows: https://ollama.ai/download
 * - Linux: curl -fsSL https://ollama.ai/install.sh | sh
 * - Mac: brew install ollama
 * 
 * Descargar modelo:
 * - ollama pull llama2
 * - ollama pull mistral
 * - ollama pull codellama
 */

namespace App\Models;

class OllamaModel
{
    private $baseUrl;
    private $model;
    private $timeout;

    public function __construct($skipConnectionCheck = false)
    {
        // URL base de Ollama (por defecto localhost:11434)
        $this->baseUrl = $_ENV['OLLAMA_BASE_URL'] ?? 'http://localhost:11434';
        
        // Modelo a usar (por defecto llama2, pero puedes usar mistral, codellama, etc.)
        $this->model = $_ENV['OLLAMA_MODEL'] ?? 'llama2';
        
        // Timeout más largo para Ollama local
        $this->timeout = 120; // 2 minutos
        
        // Verificar que Ollama esté disponible (solo si no se omite la verificación)
        if (!$skipConnectionCheck) {
            $this->checkOllamaConnection();
        }
    }

    /**
     * Verifica que Ollama esté corriendo
     */
    private function checkOllamaConnection(): void
    {
        $ch = curl_init($this->baseUrl . '/api/tags');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \RuntimeException(
                "Ollama no está disponible en {$this->baseUrl}. " .
                "Asegúrate de que Ollama esté instalado y corriendo. " .
                "Instala desde: https://ollama.ai/download"
            );
        }
    }
    
    /**
     * Verifica si Ollama está disponible sin lanzar excepción
     */
    public function isAvailable(): bool
    {
        try {
            $ch = curl_init($this->baseUrl . '/api/tags');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 2
            ]);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Realiza una petición HTTP a la API de Ollama
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10
        ];

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('Error de conexión con Ollama: ' . $error . 
                '. Verifica que Ollama esté corriendo en ' . $this->baseUrl);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error'] ?? "Error HTTP $httpCode";
            throw new \RuntimeException('Error de Ollama API: ' . $errorMessage);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Error al decodificar respuesta de Ollama');
        }

        return $decoded;
    }

    /**
     * Genera embeddings para un texto
     * NOTA: Ollama no tiene embeddings nativos, usa una aproximación simple
     * Para embeddings reales, considera usar Gemini solo para embeddings
     */
    public function getEmbedding(string $text): array
    {
        // Ollama no tiene embeddings nativos
        // Usamos una aproximación simple basada en hash
        // Para producción, considera usar Gemini solo para embeddings
        $hash = hash('sha256', $text);
        $embedding = [];
        
        // Generar vector de 768 dimensiones (similar a modelos de embeddings)
        for ($i = 0; $i < 768; $i++) {
            $seed = hexdec(substr($hash, $i % 64, 1)) + $i;
            $embedding[] = (sin($seed) + 1) / 2; // Normalizar entre 0 y 1
        }
        
        return $embedding;
    }

    /**
     * Genera embeddings para múltiples textos
     */
    public function getEmbeddings(array $texts): array
    {
        $embeddings = [];
        foreach ($texts as $text) {
            $embeddings[] = $this->getEmbedding($text);
        }
        return $embeddings;
    }

    /**
     * Genera una respuesta usando contexto de documentos
     */
    public function generateAnswer(string $question, array $context, array $conversationHistory = []): string
    {
        $contextText = implode("\n\n", array_column($context, 'text'));

        // Construir historial de conversación si existe
        $historyText = '';
        if (!empty($conversationHistory)) {
            $historyParts = [];
            foreach ($conversationHistory as $msg) {
                $role = $msg['role'] === 'user' ? 'Usuario' : 'Asistente';
                $historyParts[] = "{$role}: {$msg['text']}";
            }
            $historyText = "\n\nHistorial de conversación anterior:\n" . implode("\n", $historyParts) . "\n";
        }

        $prompt = "Eres un asistente de mesa de ayuda amigable y profesional. 
Usa el siguiente contexto de documentos para responder la pregunta del usuario.
Si el contexto no contiene información suficiente, puedes complementar con tu conocimiento general para dar una respuesta completa y útil.
Sé natural, conversacional y amigable en tus respuestas.

IMPORTANTE: 
- Esta es una conversación en curso. NO incluyas saludos como '¡Hola!' o 'Buenos días' a menos que sea el primer mensaje.
- Responde directamente a la pregunta del usuario de forma natural y continua con la conversación.
- Cuando proporciones listas o pasos numerados, usa formato HTML con etiquetas <ul> y <li>. 
- Ejemplo: <ul><li>Primer punto</li><li>Segundo punto</li></ul>
- Si la respuesta es simple o no requiere lista, usa texto normal.

Contexto de documentos:
{$contextText}{$historyText}

Pregunta actual del usuario: {$question}

Respuesta (usa HTML con <ul> y <li> para listas, sé natural y conversacional, NO incluyas saludos innecesarios):";

        return $this->generateText($prompt);
    }

    /**
     * Genera una respuesta usando solo conocimiento general (sin documentos)
     */
    public function generateGeneralAnswer(string $question, array $conversationHistory = []): string
    {
        // Construir historial de conversación si existe
        $historyText = '';
        if (!empty($conversationHistory)) {
            $historyParts = [];
            foreach ($conversationHistory as $msg) {
                $role = $msg['role'] === 'user' ? 'Usuario' : 'Asistente';
                $historyParts[] = "{$role}: {$msg['text']}";
            }
            $historyText = "\n\nHistorial de conversación anterior:\n" . implode("\n", $historyParts) . "\n";
        }

        // Determinar si es el primer mensaje
        $isFirstMessage = empty($conversationHistory);

        $prompt = "Eres un asistente de mesa de ayuda amigable, profesional y conversacional.
Responde de manera natural y útil a la pregunta del usuario.
" . ($isFirstMessage ? "Si es un saludo, responde de forma cálida y ofrece ayuda." : "Esta es una conversación en curso. NO incluyas saludos como '¡Hola!' o 'Buenos días' a menos que el usuario te salude explícitamente.") . "
Si es una pregunta general, proporciona información útil y actualizada.
Sé conciso pero amigable.

IMPORTANTE: 
- " . ($isFirstMessage ? "Puedes saludar si es apropiado." : "NO incluyas saludos innecesarios. Responde directamente a la pregunta.") . "
- Cuando proporciones listas o pasos numerados, usa formato HTML con etiquetas <ul> y <li>. 
- Ejemplo: <ul><li>Primer punto</li><li>Segundo punto</li></ul>
- Si la respuesta es simple o no requiere lista, usa texto normal.

{$historyText}Pregunta actual del usuario: {$question}

Respuesta (usa HTML con <ul> y <li> para listas, sé natural y conversacional" . ($isFirstMessage ? "" : ", NO incluyas saludos innecesarios") . "):";

        return $this->generateText($prompt);
    }

    /**
     * Genera texto usando Ollama
     */
    private function generateText(string $prompt): string
    {
        $response = $this->makeRequest('/api/generate', [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
                'top_p' => 0.9,
                'top_k' => 40
            ]
        ]);

        // Ollama devuelve la respuesta en 'response'
        if (isset($response['response'])) {
            return trim($response['response']);
        }

        throw new \RuntimeException('No se pudo obtener la respuesta de Ollama');
    }

    /**
     * Obtiene el modelo actual
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Lista todos los modelos instalados en Ollama
     * 
     * @return array Array con información de los modelos instalados
     *               Formato: [['name' => 'llama2', 'size' => '3.8GB', 'modified' => '...'], ...]
     */
    public function listInstalledModels(): array
    {
        try {
            $ch = curl_init($this->baseUrl . '/api/tags');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return [];
            }
            
            $data = json_decode($response, true);
            if (!isset($data['models']) || !is_array($data['models'])) {
                return [];
            }
            
            $models = [];
            foreach ($data['models'] as $model) {
                $models[] = [
                    'name' => $model['name'] ?? '',
                    'size' => $this->formatBytes($model['size'] ?? 0),
                    'modified' => isset($model['modified_at']) ? date('Y-m-d H:i:s', strtotime($model['modified_at'])) : '',
                    'digest' => $model['digest'] ?? ''
                ];
            }
            
            return $models;
        } catch (\Exception $e) {
            error_log('Error al listar modelos de Ollama: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Formatea bytes a formato legible (KB, MB, GB)
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

