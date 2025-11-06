<?php
/**
 * Controlador: Upload
 * Maneja la subida y procesamiento de archivos
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\DocumentModel;
use App\Models\AIModel;
use App\Models\VectorStoreModel;
use App\Models\DatabaseFileStoreModel;

class UploadController
{
    public function uploadFile(Request $request, Response $response): Response
    {
        // Asegurar que los errores se muestren
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        
        try {
            error_log('📤 Iniciando carga de archivo...');
            error_log('📤 Método: ' . $request->getMethod());
            error_log('📤 Content-Type: ' . ($request->getHeaderLine('Content-Type') ?: 'N/A'));
            
            $uploadedFiles = $request->getUploadedFiles();
            error_log('📤 Archivos recibidos: ' . count($uploadedFiles));
            
            if (empty($uploadedFiles['file'])) {
                error_log('❌ No se proporcionó ningún archivo');
                return $this->jsonResponse($response, ['detail' => 'No se proporcionó ningún archivo'], 400);
            }

            $file = $uploadedFiles['file'];
            error_log('📁 Archivo recibido: ' . $file->getClientFilename() . ' (' . $file->getSize() . ' bytes)');
            
            $documentModel = new DocumentModel();
            
            if (!$documentModel->validateFileType($file->getClientFilename())) {
                error_log('❌ Formato no soportado: ' . $file->getClientFilename());
                return $this->jsonResponse($response, [
                    'detail' => 'Formato no soportado. Solo se permiten archivos PDF, TXT, XLSX o MD'
                ], 400);
            }

            // Guardar archivo temporalmente
            $uploadDir = $documentModel->getUploadDir();
            $fileExtension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $tempPath = $uploadDir . '/' . uniqid() . '.' . $fileExtension;
            $file->moveTo($tempPath);
            error_log('💾 Archivo guardado temporalmente: ' . $tempPath);

            try {
                error_log('📄 Procesando documento...');
                // Procesar documento según su tipo
                switch ($fileExtension) {
                    case 'pdf':
                        $chunks = $documentModel->processPdf($tempPath);
                        break;
                    case 'xlsx':
                        $chunks = $documentModel->processXlsx($tempPath);
                        break;
                    case 'md':
                        $chunks = $documentModel->processMd($tempPath);
                        break;
                    case 'txt':
                    default:
                        $chunks = $documentModel->processTxt($tempPath);
                        break;
                }

                if (empty($chunks)) {
                    throw new \Exception('No se pudieron extraer chunks del archivo');
                }

                error_log('✅ Chunks extraídos: ' . count($chunks));

                // Generar embeddings
                error_log('🤖 Generando embeddings...');
                $aiModel = new AIModel();
                $texts = array_column($chunks, 'text');
                
                // Procesar embeddings en lotes para evitar timeouts
                $embeddings = [];
                $totalChunks = count($texts);
                $batchSize = 10; // Procesar 10 a la vez
                
                for ($i = 0; $i < $totalChunks; $i += $batchSize) {
                    $batch = array_slice($texts, $i, $batchSize);
                    error_log("🔄 Procesando lote " . (floor($i / $batchSize) + 1) . " de " . ceil($totalChunks / $batchSize) . " (" . count($batch) . " chunks)");
                    
                    try {
                        $batchEmbeddings = $aiModel->getEmbeddings($batch);
                        $embeddings = array_merge($embeddings, $batchEmbeddings);
                    } catch (\Exception $e) {
                        error_log('❌ Error al generar embeddings del lote: ' . $e->getMessage());
                        throw new \Exception('Error al generar embeddings: ' . $e->getMessage());
                    }
                }
                
                error_log('✅ Embeddings generados: ' . count($embeddings));

                // Guardar información del archivo
                error_log('💾 Guardando información del archivo en la base de datos...');
                try {
                    $fileStoreModel = new DatabaseFileStoreModel();
                    $fileData = $fileStoreModel->addFile(
                        $file->getClientFilename(),
                        count($chunks),
                        $fileExtension,
                        $file->getSize()
                    );
                    error_log('✅ Archivo guardado en BD con ID: ' . $fileData['id']);
                } catch (\Exception $e) {
                    error_log('❌ Error al guardar archivo en BD: ' . $e->getMessage());
                    throw new \Exception('Error al guardar archivo en la base de datos: ' . $e->getMessage());
                }

                // Guardar en vector store (agregar, no reemplazar)
                error_log('💾 Guardando chunks en vector store...');
                try {
                    $vectorStoreModel = new VectorStoreModel();
                    $vectorStoreModel->saveChunks($chunks, $embeddings, $fileData['id']);
                    error_log('✅ Chunks guardados en vector store');
                } catch (\Exception $e) {
                    error_log('❌ Error al guardar chunks en vector store: ' . $e->getMessage());
                    // Intentar eliminar el archivo de la BD si falla el vector store
                    try {
                        $fileStoreModel->removeFile($fileData['id']);
                    } catch (\Exception $e2) {
                        error_log('⚠️ No se pudo eliminar el archivo de la BD después del error: ' . $e2->getMessage());
                    }
                    throw new \Exception('Error al guardar chunks: ' . $e->getMessage());
                }

                // Eliminar archivo temporal
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                    error_log('🗑️ Archivo temporal eliminado');
                }

                error_log('✅ Archivo procesado exitosamente: ' . $file->getClientFilename());
                
                return $this->jsonResponse($response, [
                    'message' => "Archivo '{$file->getClientFilename()}' procesado exitosamente",
                    'chunks' => count($chunks),
                    'file_id' => $fileData['id'],
                    'status' => 'success'
                ]);

            } catch (\Exception $e) {
                error_log('❌ Error en procesamiento: ' . $e->getMessage());
                error_log('📍 Stack trace: ' . $e->getTraceAsString());
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            error_log('❌ Error general: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            error_log('❌ File: ' . $e->getFile() . ':' . $e->getLine());
            
            return $this->jsonResponse($response, [
                'detail' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        } catch (\Error $e) {
            // Capturar errores fatales de PHP
            error_log('❌ Error fatal: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            error_log('❌ File: ' . $e->getFile() . ':' . $e->getLine());
            
            return $this->jsonResponse($response, [
                'detail' => 'Error fatal al procesar el archivo: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
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

