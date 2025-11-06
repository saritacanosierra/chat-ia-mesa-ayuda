<?php
/**
 * Controlador: OllamaModels
 * Gestiona la lista de modelos instalados en Ollama
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\OllamaModel;

class OllamaModelsController
{
    /**
     * Lista todos los modelos instalados en Ollama
     */
    public function listModels(Request $request, Response $response): Response
    {
        try {
            // Crear instancia de OllamaModel sin verificar conexión (para no lanzar excepción)
            $ollamaModel = new OllamaModel(true);
            
            // Verificar si Ollama está disponible
            if (!$ollamaModel->isAvailable()) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Ollama no está disponible. Asegúrate de que Ollama esté instalado y corriendo.',
                    'models' => [],
                    'current_model' => null
                ], 503);
            }
            
            // Obtener lista de modelos
            $models = $ollamaModel->listInstalledModels();
            $currentModel = $ollamaModel->getModel();
            
            return $this->jsonResponse($response, [
                'success' => true,
                'message' => 'Modelos obtenidos correctamente',
                'models' => $models,
                'current_model' => $currentModel,
                'ollama_url' => $_ENV['OLLAMA_BASE_URL'] ?? 'http://localhost:11434'
            ]);
            
        } catch (\Exception $e) {
            error_log('Error al listar modelos de Ollama: ' . $e->getMessage());
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Error al obtener modelos: ' . $e->getMessage(),
                'models' => [],
                'current_model' => null
            ], 500);
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

