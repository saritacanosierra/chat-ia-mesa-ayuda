<?php
/**
 * Modelo: File Store
 * Gestiona el almacenamiento y listado de archivos procesados
 */

namespace App\Models;

class FileStoreModel
{
    private $filesDbPath;
    private $files = [];

    public function __construct()
    {
        $this->filesDbPath = __DIR__ . '/../../vector_db/files.json';
        $this->loadData();
    }

    /**
     * Agrega un archivo a la lista
     */
    public function addFile(string $filename, int $chunks, string $fileType): array
    {
        $fileId = uniqid('file_');
        $fileData = [
            'id' => $fileId,
            'filename' => $filename,
            'type' => $fileType,
            'chunks' => $chunks,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'size' => 0 // Se puede calcular si es necesario
        ];

        $this->files[$fileId] = $fileData;
        $this->persistData();

        return $fileData;
    }

    /**
     * Elimina un archivo de la lista
     */
    public function removeFile(string $fileId): bool
    {
        if (isset($this->files[$fileId])) {
            unset($this->files[$fileId]);
            $this->persistData();
            return true;
        }
        return false;
    }

    /**
     * Obtiene todos los archivos
     */
    public function getAllFiles(): array
    {
        return array_values($this->files);
    }

    /**
     * Obtiene un archivo por ID
     */
    public function getFile(string $fileId): ?array
    {
        return $this->files[$fileId] ?? null;
    }

    /**
     * Obtiene el número total de archivos
     */
    public function getTotalFiles(): int
    {
        return count($this->files);
    }

    /**
     * Obtiene el número total de chunks
     */
    public function getTotalChunks(): int
    {
        $total = 0;
        foreach ($this->files as $file) {
            $total += $file['chunks'] ?? 0;
        }
        return $total;
    }

    /**
     * Carga datos desde archivo
     */
    private function loadData(): void
    {
        if (file_exists($this->filesDbPath)) {
            $data = json_decode(file_get_contents($this->filesDbPath), true);
            if ($data && isset($data['files'])) {
                $this->files = $data['files'];
            }
        }
    }

    /**
     * Persiste datos en archivo
     */
    private function persistData(): void
    {
        $data = [
            'files' => $this->files,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $dir = dirname($this->filesDbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->filesDbPath, json_encode($data, JSON_PRETTY_PRINT));
    }
}

