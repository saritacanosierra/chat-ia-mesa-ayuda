<?php
/**
 * Modelo: Preguntas Frecuentes
 * Gestiona el almacenamiento de preguntas frecuentes
 */

namespace App\Models;

class FrequentQuestionsModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra o actualiza una pregunta frecuente
     */
    public function recordQuestion(string $question, string $answer, ?string $source = null): void
    {
        // Buscar si ya existe una pregunta similar
        $stmt = $this->db->prepare("
            SELECT id, times_asked FROM frequent_questions 
            WHERE question = ? 
            LIMIT 1
        ");
        $stmt->execute([$question]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Actualizar contador y última fecha
            $stmt = $this->db->prepare("
                UPDATE frequent_questions 
                SET times_asked = times_asked + 1,
                    last_asked_at = NOW(),
                    answer = ?,
                    source = ?
                WHERE id = ?
            ");
            $stmt->execute([$answer, $source, $existing['id']]);
        } else {
            // Insertar nueva pregunta
            $stmt = $this->db->prepare("
                INSERT INTO frequent_questions (question, answer, source, times_asked, last_asked_at)
                VALUES (?, ?, ?, 1, NOW())
            ");
            $stmt->execute([$question, $answer, $source]);
        }
    }

    /**
     * Obtiene las preguntas más frecuentes
     */
    public function getMostFrequent(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM frequent_questions 
            ORDER BY times_asked DESC, last_asked_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las preguntas frecuentes
     */
    public function getAllQuestions(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM frequent_questions 
            ORDER BY times_asked DESC, last_asked_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene estadísticas de preguntas frecuentes
     */
    public function getStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_questions,
                SUM(times_asked) as total_asked,
                AVG(times_asked) as avg_times_asked
            FROM frequent_questions
        ");
        return $stmt->fetch();
    }

    /**
     * Elimina una pregunta frecuente
     */
    public function deleteQuestion(int $questionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM frequent_questions WHERE id = ?");
        $stmt->execute([$questionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Busca preguntas similares
     */
    public function searchSimilar(string $query, int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM frequent_questions 
            WHERE question LIKE ? OR answer LIKE ?
            ORDER BY times_asked DESC
            LIMIT ?
        ");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }
}

