<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        
        $app->post('/reporte', \App\Controllers\api\ReporteApiController::class . ':store');
        $app->put('/reporte/{id}', \App\Controllers\api\ReporteApiController::class . ':update');
        $app->delete('/reporte/{id}', \App\Controllers\api\ReporteApiController::class . ':delete');
        $app->get('/reporte', \App\Controllers\api\ReporteApiController::class . ':index');
        $app->post('/reporteSession', \App\Controllers\api\ReporteApiController::class . ':getByUsuarioSession');
        $app->get('/actividades/reporte', \App\Controllers\api\ReporteApiController::class . ':reporte');
        $app->post('/reportes', \App\Controllers\api\ReporteApiController::class . ':reportes');
        $app->get('/reportesgestores', \App\Controllers\api\ReporteApiController::class . ':reporte_gestores');

    });

};