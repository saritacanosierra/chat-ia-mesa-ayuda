<?php
/**
 * Controlador: AIStatus
 * Muestra el estado actual del proveedor de IA (Gemini u Ollama)
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\AIModel;
use App\Models\OllamaModel;

class AIStatusController
{
    /**
     * Obtiene el estado actual del proveedor de IA
     */
    public function getAIStatus(Request $request, Response $response): Response
    {
        try {
            $aiModel = new AIModel();
            $provider = $aiModel->getProvider();
            
            $status = [
                'provider' => $provider,
                'provider_name' => ucfirst($provider),
                'fallback_enabled' => filter_var($_ENV['AI_USE_FALLBACK'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'config' => [
                    'ai_provider' => $_ENV['AI_PROVIDER'] ?? 'gemini',
                    'gemini_configured' => !empty($_ENV['GEMINI_API_KEY'] ?? ''),
                    'ollama_configured' => !empty($_ENV['OLLAMA_BASE_URL'] ?? 'http://localhost:11434'),
                    'ollama_model' => $_ENV['OLLAMA_MODEL'] ?? 'llama2',
                    'ollama_url' => $_ENV['OLLAMA_BASE_URL'] ?? 'http://localhost:11434'
                ],
                'availability' => []
            ];
            
            // Verificar disponibilidad de Gemini
            $status['availability']['gemini'] = [
                'available' => !empty($_ENV['GEMINI_API_KEY'] ?? ''),
                'message' => !empty($_ENV['GEMINI_API_KEY'] ?? '') 
                    ? 'Gemini está configurado' 
                    : 'Gemini no está configurado (falta GEMINI_API_KEY)'
            ];
            
            // Verificar disponibilidad de Ollama
            try {
                $ollamaModel = new OllamaModel(true);
                $ollamaAvailable = $ollamaModel->isAvailable();
                $status['availability']['ollama'] = [
                    'available' => $ollamaAvailable,
                    'message' => $ollamaAvailable 
                        ? 'Ollama está disponible y corriendo' 
                        : 'Ollama no está disponible (no está corriendo o no está instalado)',
                    'model' => $ollamaModel->getModel(),
                    'installed_models' => $ollamaAvailable ? $ollamaModel->listInstalledModels() : []
                ];
            } catch (\Exception $e) {
                $status['availability']['ollama'] = [
                    'available' => false,
                    'message' => 'Ollama no está disponible: ' . $e->getMessage()
                ];
            }
            
            // Determinar qué proveedor se está usando actualmente
            $status['current_usage'] = [
                'active_provider' => $provider,
                'will_use_fallback' => $status['fallback_enabled'],
                'message' => $this->getUsageMessage($status)
            ];
            
            return $this->jsonResponse($response, [
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            error_log('Error al obtener estado de AI: ' . $e->getMessage());
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Error al obtener estado: ' . $e->getMessage(),
                'status' => null
            ], 500);
        }
    }
    
    /**
     * Genera un mensaje descriptivo del uso actual
     */
    private function getUsageMessage(array $status): string
    {
        $provider = $status['provider'];
        $fallback = $status['fallback_enabled'];
        $geminiAvail = $status['availability']['gemini']['available'];
        $ollamaAvail = $status['availability']['ollama']['available'];
        
        if ($provider === 'gemini' && $geminiAvail) {
            if ($fallback && $ollamaAvail) {
                return "Usando Gemini como principal. Ollama disponible como respaldo automático.";
            } elseif ($fallback && !$ollamaAvail) {
                return "Usando Gemini como principal. Fallback a Ollama configurado pero Ollama no está disponible.";
            } else {
                return "Usando solo Gemini (fallback desactivado).";
            }
        } elseif ($provider === 'ollama' && $ollamaAvail) {
            if ($fallback && $geminiAvail) {
                return "Usando Ollama como principal. Gemini disponible como respaldo automático.";
            } elseif ($fallback && !$geminiAvail) {
                return "Usando Ollama como principal. Fallback a Gemini configurado pero Gemini no está disponible.";
            } else {
                return "Usando solo Ollama (fallback desactivado).";
            }
        } else {
            return "Estado desconocido. Verifica la configuración.";
        }
    }
    
    /**
     * Helper para respuestas JSON
     */
    private function jsonResponse(Response $response, array $data, int $statusCode = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}

