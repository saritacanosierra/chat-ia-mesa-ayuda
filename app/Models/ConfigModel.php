<?php
/**
 * Modelo: Configuración
 * Gestiona la configuración de la aplicación almacenada en la base de datos
 */

namespace App\Models;

class ConfigModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene un valor de configuración
     */
    public function getConfig(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare("SELECT config_value FROM app_config WHERE config_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['config_value'] : $default;
    }

    /**
     * Establece un valor de configuración
     */
    public function setConfig(string $key, string $value, ?string $description = null): bool
    {
        try {
            // Verificar si existe
            $stmt = $this->db->prepare("SELECT id FROM app_config WHERE config_key = ?");
            $stmt->execute([$key]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Actualizar
                $stmt = $this->db->prepare("
                    UPDATE app_config 
                    SET config_value = ?, 
                        description = COALESCE(?, description),
                        updated_at = NOW()
                    WHERE config_key = ?
                ");
                $stmt->execute([$value, $description, $key]);
            } else {
                // Insertar
                $stmt = $this->db->prepare("
                    INSERT INTO app_config (config_key, config_value, description)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$key, $value, $description]);
            }
            return true;
        } catch (\Exception $e) {
            error_log('Error al guardar configuración: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si una contraseña es correcta
     */
    public function verifyPassword(string $password): bool
    {
        $storedPassword = $this->getConfig('admin_password');
        return $storedPassword !== null && $password === $storedPassword;
    }

    /**
     * Cambia la contraseña de administrador
     */
    public function changePassword(string $newPassword): bool
    {
        return $this->setConfig('admin_password', $newPassword, 'Contraseña de administrador para acceder a la configuración');
    }
}

