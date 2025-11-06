<?php
/**
 * Modelo: AI
 * Gestiona la interacción con diferentes proveedores de IA (Gemini u Ollama)
 * Compatible con PHP 8.0+
 */

namespace App\Models;

class AIModel
{
    private $provider;
    private $geminiModel;
    private $ollamaModel;
    private $useFallback;

    public function __construct()
    {
        // Determinar qué proveedor usar (por defecto: gemini)
        $this->provider = strtolower($_ENV['AI_PROVIDER'] ?? 'gemini');
        
        // Activar fallback automático si está configurado (por defecto: true si Gemini es el principal)
        $this->useFallback = filter_var($_ENV['AI_USE_FALLBACK'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        
        // Inicializar Gemini si está configurado
        $geminiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        if (!empty($geminiKey)) {
            try {
                $this->geminiModel = new GeminiModel();
                error_log('✅ Gemini inicializado correctamente');
            } catch (\Exception $e) {
                error_log('⚠️ No se pudo inicializar Gemini: ' . $e->getMessage());
            }
        }
        
        // Inicializar Ollama si está disponible (no crítico si falla)
        if ($this->useFallback || $this->provider === 'ollama') {
            try {
                // Si es solo respaldo, omitir verificación inicial (se verificará cuando se use)
                $skipCheck = ($this->provider !== 'ollama');
                $this->ollamaModel = new OllamaModel($skipCheck);
                
                // Verificar disponibilidad sin lanzar excepción
                if ($this->ollamaModel->isAvailable()) {
                    error_log('✅ Ollama inicializado correctamente como respaldo');
                } else {
                    error_log('⚠️ Ollama no está disponible actualmente, pero se intentará usar si es necesario');
                }
            } catch (\Exception $e) {
                error_log('⚠️ Ollama no está disponible: ' . $e->getMessage());
                $this->ollamaModel = null;
                // No lanzar excepción, solo registrar el error
            }
        }
        
        // Validar que al menos un proveedor esté disponible
        if ($this->provider === 'gemini' && $this->geminiModel === null) {
            if ($this->ollamaModel === null) {
                throw new \RuntimeException('No hay proveedores de IA disponibles. Configura GEMINI_API_KEY o instala Ollama.');
            }
            error_log('⚠️ Gemini no disponible, usando Ollama como principal');
            $this->provider = 'ollama';
        } elseif ($this->provider === 'ollama' && $this->ollamaModel === null) {
            if ($this->geminiModel === null) {
                throw new \RuntimeException('Ollama no está disponible. Instala Ollama o configura GEMINI_API_KEY.');
            }
            error_log('⚠️ Ollama no disponible, usando Gemini como principal');
            $this->provider = 'gemini';
        }
        
        error_log("Proveedor principal: {$this->provider}, Fallback activado: " . ($this->useFallback ? 'sí' : 'no'));
    }

    /**
     * Genera embeddings para un texto (con fallback si es necesario)
     */
    public function getEmbedding(string $text): array
    {
        // Preferir Gemini para embeddings (mejor calidad)
        if ($this->geminiModel !== null) {
            try {
                return $this->geminiModel->getEmbedding($text);
            } catch (\RuntimeException $e) {
                // Si Gemini falla y tenemos Ollama, usar Ollama
                if ($this->useFallback && $this->ollamaModel !== null) {
                    error_log("⚠️ Gemini embeddings falló, usando Ollama como respaldo");
                    return $this->ollamaModel->getEmbedding($text);
                }
                throw $e;
            }
        }
        
        // Si no hay Gemini, usar Ollama
        if ($this->ollamaModel !== null) {
            return $this->ollamaModel->getEmbedding($text);
        }
        
        throw new \RuntimeException('No hay proveedores de IA disponibles para embeddings');
    }

    /**
     * Genera embeddings para múltiples textos (con fallback si es necesario)
     */
    public function getEmbeddings(array $texts): array
    {
        // Preferir Gemini para embeddings (mejor calidad)
        if ($this->geminiModel !== null) {
            try {
                return $this->geminiModel->getEmbeddings($texts);
            } catch (\RuntimeException $e) {
                // Si Gemini falla y tenemos Ollama, usar Ollama
                if ($this->useFallback && $this->ollamaModel !== null) {
                    error_log("⚠️ Gemini embeddings falló, usando Ollama como respaldo");
                    return $this->ollamaModel->getEmbeddings($texts);
                }
                throw $e;
            }
        }
        
        // Si no hay Gemini, usar Ollama
        if ($this->ollamaModel !== null) {
            return $this->ollamaModel->getEmbeddings($texts);
        }
        
        throw new \RuntimeException('No hay proveedores de IA disponibles para embeddings');
    }

    /**
     * Genera una respuesta usando contexto con fallback automático
     */
    public function generateAnswer(string $question, array $context, array $conversationHistory = []): string
    {
        // Si el proveedor principal es Gemini, intentar primero con Gemini
        if ($this->provider === 'gemini' && $this->geminiModel !== null) {
            try {
                return $this->geminiModel->generateAnswer($question, $context, $conversationHistory);
            } catch (\RuntimeException $e) {
                // Si Gemini falla y tenemos Ollama como respaldo, usarlo
                if ($this->useFallback && $this->ollamaModel !== null) {
                    // Verificar que Ollama esté disponible antes de usarlo
                    if ($this->ollamaModel->isAvailable()) {
                        $errorMsg = $e->getMessage();
                        error_log("⚠️ Gemini falló ({$errorMsg}), usando Ollama como respaldo");
                        try {
                            return $this->ollamaModel->generateAnswer($question, $context, $conversationHistory);
                        } catch (\RuntimeException $ollamaError) {
                            error_log("❌ Ollama también falló: " . $ollamaError->getMessage());
                            throw $e; // Lanzar el error original de Gemini
                        }
                    } else {
                        error_log("⚠️ Gemini falló pero Ollama no está disponible");
                    }
                }
                // Si no hay fallback, lanzar el error
                throw $e;
            }
        }
        
        // Si el proveedor principal es Ollama o Gemini no está disponible
        if ($this->ollamaModel !== null) {
            try {
                return $this->ollamaModel->generateAnswer($question, $context, $conversationHistory);
            } catch (\RuntimeException $e) {
                // Si Ollama falla y tenemos Gemini como respaldo, intentar con Gemini
                if ($this->useFallback && $this->geminiModel !== null) {
                    $errorMsg = $e->getMessage();
                    error_log("⚠️ Ollama falló ({$errorMsg}), usando Gemini como respaldo");
                    return $this->geminiModel->generateAnswer($question, $context, $conversationHistory);
                }
                throw $e;
            }
        }
        
        throw new \RuntimeException('No hay proveedores de IA disponibles');
    }

    /**
     * Genera una respuesta usando solo conocimiento general (sin documentos) con fallback automático
     */
    public function generateGeneralAnswer(string $question, array $conversationHistory = []): string
    {
        // Si el proveedor principal es Gemini, intentar primero con Gemini
        if ($this->provider === 'gemini' && $this->geminiModel !== null) {
            try {
                return $this->geminiModel->generateGeneralAnswer($question, $conversationHistory);
            } catch (\RuntimeException $e) {
                // Si Gemini falla y tenemos Ollama como respaldo, usarlo
                if ($this->useFallback && $this->ollamaModel !== null) {
                    // Verificar que Ollama esté disponible antes de usarlo
                    if ($this->ollamaModel->isAvailable()) {
                        $errorMsg = $e->getMessage();
                        error_log("⚠️ Gemini falló ({$errorMsg}), usando Ollama como respaldo");
                        try {
                            return $this->ollamaModel->generateGeneralAnswer($question, $conversationHistory);
                        } catch (\RuntimeException $ollamaError) {
                            error_log("❌ Ollama también falló: " . $ollamaError->getMessage());
                            throw $e; // Lanzar el error original de Gemini
                        }
                    } else {
                        error_log("⚠️ Gemini falló pero Ollama no está disponible");
                    }
                }
                // Si no hay fallback, lanzar el error
                throw $e;
            }
        }
        
        // Si el proveedor principal es Ollama o Gemini no está disponible
        if ($this->ollamaModel !== null) {
            try {
                return $this->ollamaModel->generateGeneralAnswer($question, $conversationHistory);
            } catch (\RuntimeException $e) {
                // Si Ollama falla y tenemos Gemini como respaldo, intentar con Gemini
                if ($this->useFallback && $this->geminiModel !== null) {
                    $errorMsg = $e->getMessage();
                    error_log("⚠️ Ollama falló ({$errorMsg}), usando Gemini como respaldo");
                    return $this->geminiModel->generateGeneralAnswer($question, $conversationHistory);
                }
                throw $e;
            }
        }
        
        throw new \RuntimeException('No hay proveedores de IA disponibles');
    }

    /**
     * Obtiene el proveedor actual
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
}

