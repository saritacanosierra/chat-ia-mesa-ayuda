<?php
/**
 * Modelo: Red
 * Gestiona la información de la red
 */

namespace App\Models;

class NetworkModel
{
    /**
     * Obtiene información de la red
     */
    public function getNetworkInfo(): array
    {
        return [
            'network_name' => 'Red de Mesa de Ayuda',
            'description' => 'Sistema de asistencia basado en IA para resolver consultas de usuarios',
            'features' => [
                'Respuestas basadas en documentos cargados',
                'Soporte 24/7',
                'Información actualizada del conocimiento base'
            ],
            'status' => 'operativo'
        ];
    }
}

