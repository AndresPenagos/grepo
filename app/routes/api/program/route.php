<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
         /**
         * Endpoint para crear un programa 
         *
         * Datos esperados:
         * - name: Nombre del usuario (requerido)
         * - id_code: código del programa (requerido)
         */
        $app->post('/program', \App\Controllers\api\ProgramApiController::class . ':store');
        /**
         * Endpoint para actualizar un programa 
         *
         * Datos esperados:
         * - name: Nombre del usuario (requerido)
         * - id_code: código del programa (requerido)
         */
        $app->put('/program/{id}', \App\Controllers\api\ProgramApiController::class . ':update');
        /**
         * Endpoint para eliminar programa
         * 
         * Datos esperados:
         * - id: id del usuario (requerido)
         */
        $app->delete('/program/{id}', \App\Controllers\api\ProgramApiController::class . ':delete');

        /**
         * Endpoint para listar los programas paginados
         *
         */
        $app->get('/program', \App\Controllers\api\ProgramApiController::class . ':index');
        /**
         * Endpoint para listar los programas sin paginar
         *
         */
        $app->get('/programs', \App\Controllers\api\ProgramApiController::class . ':indexAll');
        /**
         * Endpoint para listar los programas
         *
         */
        $app->get('/program/TypeProgram', \App\Controllers\api\ProgramApiController::class . ':getTypeProgram');
        
    });

};