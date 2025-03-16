<?php
declare(strict_types=1);

use App\Model\Tareas;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        
        $app->post('/tarea', \App\Controllers\api\TareasApiController::class . ':store');
        $app->put('/tarea/{id}', \App\Controllers\api\TareasApiController::class . ':update');
        $app->delete('/tarea/{id}', \App\Controllers\api\TareasApiController::class . ':delete');
        $app->get('/tarea/{id}', \App\Controllers\api\TareasApiController::class . ':get');
        $app->get('/tareas', \App\Controllers\api\TareasApiController::class . ':index');
        $app->get('/getResponsablesTareasGestoresRepositorio/{id}', \App\Controllers\api\TareasApiController::class . ':getGestoresResponsables');
        $app->get('/tareasasociadas', \App\Controllers\api\TareasApiController::class . ':getIdUsuario');
    });

};