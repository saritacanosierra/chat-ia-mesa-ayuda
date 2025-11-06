<?php
/**
 * Controlador: Preguntas Frecuentes
 * Maneja el listado y gestión de preguntas frecuentes
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\FrequentQuestionsModel;

class FrequentQuestionsController
{
    /**
     * Lista las preguntas frecuentes
     */
    public function listQuestions(Request $request, Response $response): Response
    {
        try {
            $frequentQuestionsModel = new FrequentQuestionsModel();
            $limit = (int)($request->getQueryParams()['limit'] ?? 20);
            
            $questions = $frequentQuestionsModel->getMostFrequent($limit);
            $stats = $frequentQuestionsModel->getStats();

            return $this->jsonResponse($response, [
                'questions' => $questions,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al obtener preguntas frecuentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca preguntas similares
     */
    public function searchQuestions(Request $request, Response $response): Response
    {
        try {
            $query = $request->getQueryParams()['q'] ?? '';
            
            if (empty($query)) {
                return $this->jsonResponse($response, [
                    'detail' => 'Parámetro de búsqueda requerido'
                ], 400);
            }

            $frequentQuestionsModel = new FrequentQuestionsModel();
            $questions = $frequentQuestionsModel->searchSimilar($query, 5);

            return $this->jsonResponse($response, [
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al buscar preguntas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una pregunta frecuente
     */
    public function deleteQuestion(Request $request, Response $response, array $args): Response
    {
        try {
            $questionId = (int)($args['id'] ?? 0);
            
            if ($questionId <= 0) {
                return $this->jsonResponse($response, [
                    'detail' => 'ID de pregunta inválido'
                ], 400);
            }

            $frequentQuestionsModel = new FrequentQuestionsModel();
            $deleted = $frequentQuestionsModel->deleteQuestion($questionId);

            if (!$deleted) {
                return $this->jsonResponse($response, [
                    'detail' => 'Pregunta no encontrada'
                ], 404);
            }

            return $this->jsonResponse($response, [
                'message' => 'Pregunta eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al eliminar pregunta: ' . $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}

