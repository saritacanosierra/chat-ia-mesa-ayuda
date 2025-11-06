<?php
/**
 * Controlador: Network Info
 * Maneja la información de la red
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\NetworkModel;

class NetworkInfoController
{
    public function getNetworkInfo(Request $request, Response $response): Response
    {
        $networkModel = new NetworkModel();
        $data = $networkModel->getNetworkInfo();

        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}

