<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        /**
         * Endpoint para guardar kas revisiones
         * - name (string) - Nombre de la revision
         * - link (string) - Link de la revision
         * - general_comments (string) - Comentarios generales
         * - start_date (string) - Fecha de inicio
         * - proposed_date (string) - Fecha propuesta
         * - actual_end_date (string) - Fecha de finalizacion
         * - solicitations_id (int) - Id de la solicitud
         */
        $app->post('/revisions', \App\Controllers\api\RevisionsApiController::class . ':store');
        /**
         * Endpoint para listar los solicitudes sin revisiones
         *
         */
        $app->get('/revisions/getSolicitationsNotRevisions', \App\Controllers\api\RevisionsApiController::class . ':getSolicitationsNotRevisions');
        /**
         * Endpoint para listar los solicitudes sin revisiones
         *
         */
        $app->get('/revisions/getSolicitationsNotRevisionsByProgram/{program_id}', \App\Controllers\api\RevisionsApiController::class . ':getSolicitationsNotRevisionsByProgram');
        /**
         * Endpoint para listar los solicitudes sin revisiones
         *
         */
        $app->get('/revisions/getSolicitationsRevisionsByProgram/{program_id}', \App\Controllers\api\RevisionsApiController::class . ':getSolicitationsRevisionsByProgram');

        /**
         * Endpoint para listar los solicitudes con revisiones
         */
        $app->get('/revisions', \App\Controllers\api\RevisionsApiController::class . ':getRevisionsByStatus');
        
    });

};