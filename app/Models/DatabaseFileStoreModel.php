<?php
/**
 * Modelo: Database File Store
 * Gestiona el almacenamiento de archivos usando MySQL
 */

namespace App\Models;

class DatabaseFileStoreModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Agrega un archivo a la base de datos
     */
    public function addFile(string $filename, int $chunks, string $fileType, int $size = 0): array
    {
        $fileId = uniqid('file_');
        $uploadedAt = date('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO files (id, filename, type, chunks, size, uploaded_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$fileId, $filename, $fileType, $chunks, $size, $uploadedAt]);

        return [
            'id' => $fileId,
            'filename' => $filename,
            'type' => $fileType,
            'chunks' => $chunks,
            'size' => $size,
            'uploaded_at' => $uploadedAt
        ];
    }

    /**
     * Elimina un archivo de la base de datos
     */
    public function removeFile(string $fileId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtiene todos los archivos
     */
    public function getAllFiles(): array
    {
        $stmt = $this->db->query("
            SELECT id, filename, type, chunks, size, uploaded_at, created_at 
            FROM files 
            ORDER BY uploaded_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un archivo por ID
     */
    public function getFile(string $fileId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtiene el número total de archivos
     */
    public function getTotalFiles(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM files");
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtiene el número total de chunks
     */
    public function getTotalChunks(): int
    {
        $stmt = $this->db->query("SELECT SUM(chunks) as total FROM files");
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }
}

