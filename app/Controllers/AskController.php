<?php
/**
 * Controlador: Ask
 * Maneja las preguntas del usuario
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\AIModel;
use App\Models\VectorStoreModel;
use App\Models\FrequentQuestionsModel;

class AskController
{
    public function askQuestion(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $question = $data['question'] ?? '';
            $conversationHistory = $data['conversation_history'] ?? [];

            if (empty($question)) {
                return $this->jsonResponse($response, ['detail' => 'No se proporcionó ninguna pregunta'], 400);
            }

            $vectorStoreModel = new VectorStoreModel();
            $aiModel = new AIModel();
            $currentProvider = $aiModel->getProvider();

            // Detectar si es un saludo o pregunta general
            $isGeneralQuestion = $this->isGeneralQuestion($question);

            // Si no hay documentos y es pregunta general, usar conocimiento general
            if (!$vectorStoreModel->hasData() && $isGeneralQuestion) {
                $answer = $aiModel->generateGeneralAnswer($question, $conversationHistory);
                return $this->jsonResponse($response, [
                    'answer' => $answer,
                    'source' => 'Conocimiento general',
                    'provider' => $currentProvider,
                    'provider_name' => ucfirst($currentProvider)
                ]);
            }

            // Si no hay documentos y no es pregunta general, sugerir subir archivos
            if (!$vectorStoreModel->hasData()) {
                $answer = $aiModel->generateGeneralAnswer(
                    "Responde brevemente que no hay documentos cargados y que el usuario puede subir archivos. " .
                    "Sé amigable y natural.",
                    $conversationHistory
                );
                return $this->jsonResponse($response, [
                    'answer' => $answer,
                    'source' => null,
                    'provider' => $currentProvider,
                    'provider_name' => ucfirst($currentProvider)
                ]);
            }

            // Generar embedding de la pregunta
            $questionEmbedding = $aiModel->getEmbedding($question);

            // Buscar chunks similares
            $similarChunks = $vectorStoreModel->searchSimilar($questionEmbedding, 3);

            // Si no hay chunks similares o es pregunta general, combinar con conocimiento general
            if (empty($similarChunks) || $isGeneralQuestion) {
                if ($isGeneralQuestion) {
                    // Para preguntas generales, usar conocimiento general pero mencionar documentos si hay
                    $answer = $aiModel->generateGeneralAnswer($question, $conversationHistory);
                } else {
                    // Si no hay chunks pero hay documentos, intentar respuesta con conocimiento general
                    $answer = $aiModel->generateGeneralAnswer(
                        "El usuario pregunta: {$question}. " .
                        "No encontré información específica en los documentos cargados. " .
                        "Responde de manera útil usando tu conocimiento general.",
                        $conversationHistory
                    );
                }
                
                return $this->jsonResponse($response, [
                    'answer' => $answer,
                    'source' => !empty($similarChunks) ? 'Documentos + Conocimiento general' : 'Conocimiento general',
                    'provider' => $currentProvider,
                    'provider_name' => ucfirst($currentProvider)
                ]);
            }

            // Preparar contexto
            $context = array_map(function($item) {
                return $item['chunk'];
            }, $similarChunks);

            // Generar respuesta con contexto de documentos y historial de conversación
            $answer = $aiModel->generateAnswer($question, $context, $conversationHistory);

            // Preparar fuentes
            $sources = [];
            foreach (array_slice($similarChunks, 0, 2) as $item) {
                $chunk = $item['chunk'];
                $sourceName = $chunk['source'] ?? 'documento';
                $sources[] = "Fragmento " . ($chunk['chunk_number'] + 1) . " de {$sourceName}";
            }

            $sourceText = !empty($sources) ? implode(', ', $sources) : null;

            // Registrar pregunta frecuente (solo si no es pregunta general)
            if (!$isGeneralQuestion) {
                try {
                    $frequentQuestionsModel = new FrequentQuestionsModel();
                    $frequentQuestionsModel->recordQuestion($question, $answer, $sourceText);
                } catch (\Exception $e) {
                    // No fallar si no se puede registrar la pregunta
                    error_log('Error al registrar pregunta frecuente: ' . $e->getMessage());
                }
            }

            return $this->jsonResponse($response, [
                'answer' => $answer,
                'source' => $sourceText,
                'provider' => $currentProvider,
                'provider_name' => ucfirst($currentProvider)
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al procesar la pregunta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detecta si es una pregunta general (saludo, conversación, etc.)
     */
    private function isGeneralQuestion(string $question): bool
    {
        $questionLower = mb_strtolower(trim($question));
        
        // Patrones de preguntas generales
        $generalPatterns = [
            'hola', 'buenos días', 'buenas tardes', 'buenas noches',
            'cómo estás', 'qué tal', 'saludos', 'hi', 'hello',
            'gracias', 'thanks', 'de nada', 'adiós', 'hasta luego',
            'qué puedes hacer', 'qué haces', 'ayúdame', 'help',
            'quién eres', 'qué eres', 'presentación'
        ];

        foreach ($generalPatterns as $pattern) {
            if (strpos($questionLower, $pattern) !== false) {
                return true;
            }
        }

        // Si la pregunta es muy corta (probablemente saludo)
        if (strlen($questionLower) < 15) {
            return true;
        }

        return false;
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}

