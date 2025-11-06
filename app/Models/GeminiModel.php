<?php
/**
 * Modelo: Gemini
 * Gestiona la interacción con la API de Google Gemini usando llamadas HTTP directas
 * Compatible con PHP 8.0+
 */

namespace App\Models;

class GeminiModel
{
    private $apiKey;
    private $model;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY no configurada');
        }

        $this->model = 'gemini-2.0-flash'; // Modelo rápido y estable
    }

    /**
     * Realiza una petición HTTP a la API de Gemini
     */
    private function makeRequest(string $endpoint, array $data, string $method = 'POST'): array
    {
        $url = $this->baseUrl . $endpoint . '?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 60 // Gemini puede tardar más
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('Error de conexión con Gemini: ' . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "Error HTTP $httpCode";
            
            // Detectar errores específicos de rate limiting
            if ($httpCode === 429 || strpos($errorMessage, 'Resource exhausted') !== false) {
                throw new \RuntimeException('RATE_LIMIT_EXCEEDED: ' . $errorMessage);
            }
            
            throw new \RuntimeException('Error de Gemini API: ' . $errorMessage);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Error al decodificar respuesta de Gemini');
        }

        return $decoded;
    }

    /**
     * Genera embeddings para un texto
     * Nota: Gemini usa text-embedding-004 para embeddings
     */
    public function getEmbedding(string $text): array
    {
        $response = $this->makeRequest('/models/text-embedding-004:embedContent', [
            'model' => 'models/text-embedding-004',
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ]);

        return $response['embedding']['values'];
    }

    /**
     * Genera embeddings para múltiples textos
     * Procesa en paralelo usando batch requests cuando es posible
     */
    public function getEmbeddings(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $embeddings = [];
        
        // Procesar embeddings uno por uno (Gemini batch API no está disponible para embeddings)
        // Optimizado con logging para mostrar progreso
        $total = count($texts);
        foreach ($texts as $index => $text) {
            try {
                $embeddings[] = $this->getEmbedding($text);
                
                // Log de progreso cada 5 embeddings
                if (($index + 1) % 5 == 0 || ($index + 1) == $total) {
                    error_log("✅ Embeddings generados: " . ($index + 1) . " / $total");
                }
                
                // Pequeña pausa cada 10 embeddings para evitar rate limiting
                if (($index + 1) % 10 == 0) {
                    usleep(50000); // 0.05 segundos
                }
            } catch (\Exception $e) {
                error_log("❌ Error al generar embedding para texto #" . ($index + 1) . " de $total: " . $e->getMessage());
                throw $e;
            }
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

        $response = $this->makeRequest('/models/gemini-2.0-flash:generateContent', [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7, // Más natural y conversacional
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048
            ]
        ]);

        // Extraer el texto de la respuesta
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \RuntimeException('No se pudo obtener la respuesta de Gemini');
    }

    /**
     * Genera una respuesta usando solo conocimiento general (sin documentos)
     * Útil para saludos, conversación general, etc.
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

        $response = $this->makeRequest('/models/gemini-2.0-flash:generateContent', [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.8, // Más creativo y natural
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024
            ]
        ]);

        // Extraer el texto de la respuesta
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return $response['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \RuntimeException('No se pudo obtener la respuesta de Gemini');
    }
}

