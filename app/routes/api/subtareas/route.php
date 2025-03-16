<?php
declare(strict_types=1);

use App\Model\Tareas;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        
        $app->post('/subtarea', \App\Controllers\api\SubTareasApiController::class . ':store');
        $app->put('/subtarea/{id}', \App\Controllers\api\SubTareasApiController::class . ':update');
        $app->delete('/subtarea/{id}', \App\Controllers\api\SubTareasApiController::class . ':delete');
        $app->get('/subtareas', \App\Controllers\api\SubTareasApiController::class . ':index');


    });

};