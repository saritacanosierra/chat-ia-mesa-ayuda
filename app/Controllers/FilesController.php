<?php
/**
 * Controlador: Files
 * Maneja la lista y eliminación de archivos almacenados
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DatabaseFileStoreModel;
use App\Models\VectorStoreModel;

class FilesController
{
    /**
     * Lista todos los archivos almacenados
     */
    public function listFiles(Request $request, Response $response): Response
    {
        try {
            $fileStoreModel = new DatabaseFileStoreModel();
            $files = $fileStoreModel->getAllFiles();
            
            $stats = [
                'total_files' => $fileStoreModel->getTotalFiles(),
                'total_chunks' => $fileStoreModel->getTotalChunks()
            ];

            return $this->jsonResponse($response, [
                'files' => $files,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al obtener la lista de archivos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un archivo específico
     */
    public function deleteFile(Request $request, Response $response, array $args): Response
    {
        try {
            $fileId = $args['id'] ?? '';
            
            if (empty($fileId)) {
                return $this->jsonResponse($response, [
                    'detail' => 'ID de archivo no proporcionado'
                ], 400);
            }

            $fileStoreModel = new DatabaseFileStoreModel();
            $vectorStoreModel = new VectorStoreModel();

            // Verificar que el archivo existe
            $file = $fileStoreModel->getFile($fileId);
            if (!$file) {
                return $this->jsonResponse($response, [
                    'detail' => 'Archivo no encontrado'
                ], 404);
            }

            // Eliminar del vector store
            $removedChunks = $vectorStoreModel->removeChunksByFileId($fileId);
            
            // Eliminar del registro de archivos
            $fileStoreModel->removeFile($fileId);

            return $this->jsonResponse($response, [
                'message' => "Archivo '{$file['filename']}' eliminado exitosamente",
                'removed_chunks' => $removedChunks
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'detail' => 'Error al eliminar el archivo: ' . $e->getMessage()
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

