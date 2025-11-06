<?php
/**
 * Controlador: Autenticación
 * Maneja la autenticación de administradores
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ConfigModel;

class AuthController
{
    /**
     * Verifica la contraseña de administrador
     */
    public function verifyPassword(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $password = $data['password'] ?? '';

            if (empty($password)) {
                return $this->jsonResponse($response, [
                    'authenticated' => false,
                    'message' => 'Contraseña no proporcionada'
                ], 400);
            }

            $configModel = new ConfigModel();
            $isValid = $configModel->verifyPassword($password);

            if ($isValid) {
                return $this->jsonResponse($response, [
                    'authenticated' => true,
                    'message' => 'Autenticación exitosa'
                ]);
            } else {
                return $this->jsonResponse($response, [
                    'authenticated' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'authenticated' => false,
                'message' => 'Error al verificar contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia la contraseña de administrador (requiere autenticación previa)
     */
    public function changePassword(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword)) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ], 400);
            }

            // Verificar contraseña actual
            $configModel = new ConfigModel();
            if (!$configModel->verifyPassword($currentPassword)) {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Contraseña actual incorrecta'
                ], 401);
            }

            // Cambiar contraseña
            if ($configModel->changePassword($newPassword)) {
                return $this->jsonResponse($response, [
                    'success' => true,
                    'message' => 'Contraseña cambiada exitosamente'
                ]);
            } else {
                return $this->jsonResponse($response, [
                    'success' => false,
                    'message' => 'Error al cambiar la contraseña'
                ], 500);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
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

