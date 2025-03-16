<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
         /**
         * Endpoint para crear una solicitud 
         *
         * Datos esperados:
         * - seeds_id : id de la semilla (requerido)
         * - link_curriculum_design: link del curriculum (requerido)
         * - link_delivery: link de la entrega (requerido)
         * - name_delivery: nombre de la entrega (requerido)
         * - email_delivery: email de la entrega (requerido)
         * - description: descripcion de la entrega (requerido)
         */
        $app->post('/solicitations', \App\Controllers\api\SolicitationsApiController::class . ':store');
        /*
        * Endpoint para actualizar una solicitud
        *
        * Datos esperados:
        * - seeds_id : id de la semilla (requerido)
        * - link_curriculum_design: link del curriculum (requerido)
        * - link_delivery: link de la entrega (requerido)
        * - name_delivery: nombre de la entrega (requerido)
        * - email_delivery: email de la entrega (requerido) 
        * - description: descripcion de la entrega (requerido)
        */
        $app->put('/solicitations/{id}', \App\Controllers\api\SolicitationsApiController::class . ':update');
        /**
         * Endpoint para eliminar una solicitud
         */
        $app->delete('/solicitations/{id}', \App\Controllers\api\SolicitationsApiController::class . ':delete');
		/**
		 * Endpoint para obtener todas las solicitudes
		 */
		$app->get('/solicitations', \App\Controllers\api\SolicitationsApiController::class . ':index');
		$app->post('/solicitudespedientesmes', \App\Controllers\api\SolicitationsApiController::class . ':SolicitudesPedientesMes');
		$app->post('/solicitudespedientes', \App\Controllers\api\SolicitationsApiController::class . ':SolicitudesPedientes');
		 
        
    });
};