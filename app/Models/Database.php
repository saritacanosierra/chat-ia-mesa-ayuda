<?php
/**
 * Modelo: Database
 * Gestiona la conexión a la base de datos MySQL
 */

namespace App\Models;

class Database
{
    private static $instance = null;
    private $db;

    private function __construct()
    {
        // Obtener configuración de la base de datos desde .env
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'chat_mesa_ayuda';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        try {
            $this->db = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // Crear tablas si no existen
            $this->initializeTables();
        } catch (\PDOException $e) {
            // Si la base de datos no existe, intentar crearla
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $this->createDatabase($host, $username, $password, $dbname, $charset);
                // Reintentar conexión
                $this->db = new \PDO($dsn, $username, $password, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]);
                $this->initializeTables();
            } else {
                throw new \RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }
    }

    /**
     * Obtiene la instancia singleton de la base de datos
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO
     */
    public function getConnection(): \PDO
    {
        return $this->db;
    }

    /**
     * Crea la base de datos si no existe
     */
    private function createDatabase(string $host, string $username, string $password, string $dbname, string $charset): void
    {
        try {
            $pdo = new \PDO("mysql:host={$host};charset={$charset}", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\PDOException $e) {
            throw new \RuntimeException('No se pudo crear la base de datos. Por favor, créala manualmente en phpMyAdmin: ' . $e->getMessage());
        }
    }

    /**
     * Inicializa las tablas de la base de datos
     */
    private function initializeTables(): void
    {
        // Tabla de archivos
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS files (
                id VARCHAR(50) PRIMARY KEY,
                filename VARCHAR(255) NOT NULL,
                type VARCHAR(10) NOT NULL,
                chunks INT NOT NULL,
                size INT DEFAULT 0,
                uploaded_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Tabla de preguntas frecuentes
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS frequent_questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                question TEXT NOT NULL,
                answer TEXT NOT NULL,
                source TEXT,
                times_asked INT DEFAULT 1,
                last_asked_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_question (question(255)),
                INDEX idx_times_asked (times_asked DESC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Tabla de configuración de la aplicación
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS app_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                config_key VARCHAR(100) UNIQUE NOT NULL,
                config_value TEXT NOT NULL,
                description TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_config_key (config_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Inicializar contraseña por defecto si no existe
        $this->initializeDefaultConfig();
    }

    /**
     * Inicializa la configuración por defecto
     */
    private function initializeDefaultConfig(): void
    {
        // Verificar si ya existe la contraseña
        $stmt = $this->db->prepare("SELECT config_value FROM app_config WHERE config_key = 'admin_password'");
        $stmt->execute();
        $existing = $stmt->fetch();

        // Si no existe, crearla con la contraseña por defecto
        if (!$existing) {
            $defaultPassword = 'quokka123456'; // Contraseña por defecto
            $stmt = $this->db->prepare("
                INSERT INTO app_config (config_key, config_value, description)
                VALUES ('admin_password', ?, 'Contraseña de administrador para acceder a la configuración')
            ");
            $stmt->execute([$defaultPassword]);
        }
    }
}

