<?php
/**
 * Modelo: Vector Store
 * Gestiona el almacenamiento y búsqueda de vectores
 */

namespace App\Models;

class VectorStoreModel
{
    private $vectorDbPath;
    private $chunks = [];
    private $embeddings = [];

    public function __construct()
    {
        $this->vectorDbPath = __DIR__ . '/../../vector_db/data.json';
        $this->loadData();
    }

    /**
     * Guarda chunks y sus embeddings (agrega en lugar de reemplazar)
     */
    public function saveChunks(array $chunks, array $embeddings, string $fileId = null): void
    {
        if (count($chunks) !== count($embeddings)) {
            throw new \InvalidArgumentException('El número de chunks y embeddings debe ser igual');
        }

        // Agregar fileId a cada chunk para rastrear el origen
        $fileId = $fileId ?? uniqid('file_');
        foreach ($chunks as &$chunk) {
            $chunk['file_id'] = $fileId;
        }

        // Agregar nuevos chunks y embeddings (no reemplazar)
        $this->chunks = array_merge($this->chunks, $chunks);
        $this->embeddings = array_merge($this->embeddings, $embeddings);
        
        $this->persistData();
    }

    /**
     * Elimina chunks de un archivo específico
     */
    public function removeChunksByFileId(string $fileId): int
    {
        $removed = 0;
        $newChunks = [];
        $newEmbeddings = [];

        foreach ($this->chunks as $index => $chunk) {
            if (($chunk['file_id'] ?? '') !== $fileId) {
                $newChunks[] = $chunk;
                $newEmbeddings[] = $this->embeddings[$index];
            } else {
                $removed++;
            }
        }

        $this->chunks = $newChunks;
        $this->embeddings = $newEmbeddings;
        
        if ($removed > 0) {
            $this->persistData();
        }

        return $removed;
    }

    /**
     * Busca los chunks más similares
     */
    public function searchSimilar(array $queryEmbedding, int $k = 3): array
    {
        if (empty($this->embeddings)) {
            return [];
        }

        $scores = [];
        foreach ($this->embeddings as $index => $embedding) {
            $similarity = $this->cosineSimilarity($queryEmbedding, $embedding);
            $scores[$index] = $similarity;
        }

        arsort($scores);
        $results = [];
        $topIndices = array_slice(array_keys($scores), 0, $k, true);
        
        foreach ($topIndices as $index) {
            $results[] = [
                'chunk' => $this->chunks[$index],
                'score' => $scores[$index]
            ];
        }

        return $results;
    }

    /**
     * Calcula similitud coseno
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Carga datos desde archivo
     */
    private function loadData(): void
    {
        if (file_exists($this->vectorDbPath)) {
            $data = json_decode(file_get_contents($this->vectorDbPath), true);
            if ($data) {
                $this->chunks = $data['chunks'] ?? [];
                $this->embeddings = $data['embeddings'] ?? [];
            }
        }
    }

    /**
     * Persiste datos en archivo
     */
    private function persistData(): void
    {
        $data = [
            'chunks' => $this->chunks,
            'embeddings' => $this->embeddings,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $dir = dirname($this->vectorDbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->vectorDbPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Verifica si hay datos
     */
    public function hasData(): bool
    {
        return !empty($this->chunks);
    }

    /**
     * Limpia todos los datos
     */
    public function clear(): void
    {
        $this->chunks = [];
        $this->embeddings = [];
        if (file_exists($this->vectorDbPath)) {
            unlink($this->vectorDbPath);
        }
    }

}

