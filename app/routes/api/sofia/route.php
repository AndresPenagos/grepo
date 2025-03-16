<?php
declare(strict_types=1);

use App\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        
    
		$app->post('/sofia', 'App\Controllers\api\SofiaApiController:store');
		
		$app->put('/sofia/{id}', 'App\Controllers\api\SofiaApiController:update');
	
		$app->delete('/sofia/{id}', 'App\Controllers\api\SofiaApiController:delete');

		$app->get('/sofia', 'App\Controllers\api\SofiaApiController:index');

		$app->get('/sofia/reporte', 'App\Controllers\api\SofiaApiController:reporte');
    });

};
