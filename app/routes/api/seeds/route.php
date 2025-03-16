<?php
declare(strict_types=1);

use App\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
return function (App $app) {
    $container = $app->getContainer();
    $app->group('/api/v1', function ()  use ($app,$container)  {
        /**
         * End point para guardar la semilla desde el formulario google sheets
		 * 
		 * Datos esperados:
		 * - id_seeds (requerido) id de la semilla
		 * - internal_code (requerido) codigo interno de la semilla
		 * - name (requerido) nombre de la semilla
		 * - hours (requerido) horas de la semilla
		 * - version_seeds (requerido) version de la semilla
		 * - version_program (requerido) version del programa
		 * - sofia_status (requerido) estado de la semilla en sofia
		 * - sofia_date (requerido) fecha de la semilla en sofia
		 * - sofia_comments (requerido) comentarios de la semilla en sofia
         */
        $app->post('/seeds', 'App\Controllers\api\SeedsApiController:storeSofia');
		/**
         * End point para guardar la semilla desde el formulario google sheets
		 * 
		 * Datos esperados:
		 * - program_id (requerido) id del programa
		 * - id_seeds (requerido) id de la semilla
		 * - internal_code (requerido) codigo interno de la semilla
		 * - hours (requerido) horas de la semilla
		 * - version_seeds (requerido) version de la semilla
		 * - version_program (requerido) version del programa
		 * - link_description_program (requerido) link de la descripcion del programa
		 * - link_description_seeds (requerido) link de la descripcion de la semilla
         */
		$app->post('/seed', 'App\Controllers\api\SeedsApiController:store');
		/**
         * End point para actualizar la semilla desde el formulario google sheets
		 * 
		 * Datos esperados:
		 * - program_id (requerido) id del programa
		 * - id_seeds (requerido) id de la semilla
		 * - internal_code (requerido) codigo interno de la semilla
		 * - hours (requerido) horas de la semilla
		 * - version_seeds (requerido) version de la semilla
		 * - version_program (requerido) version del programa
		 * - link_description_program (requerido) link de la descripcion del programa
		 * - link_description_seeds (requerido) link de la descripcion de la semilla
         */
		$app->put('/seed/{id}', 'App\Controllers\api\SeedsApiController:update');
		/**
		 * End point para eliminar la semilla
		 * 
		 * Datos esperados:
		 * - id (requerido) id de la semilla
		 */
		$app->delete('/seed/{id}', 'App\Controllers\api\SeedsApiController:delete');

		/**
		 * End point para listar semillas
		 */
		$app->get('/seeds', 'App\Controllers\api\SeedsApiController:index');
		/**
		 * End point para listar semillas
		 */
		$app->get('/seedsAll', 'App\Controllers\api\SeedsApiController:indexAll');
		/**
		 * End point para listar las modalitys de las semillas
		 */
		$app->get('/seeds/modalitys', 'App\Controllers\api\SeedsApiController:getModality');
		/**
		 * End point para listar semillas que estan listas para publicación
		 */
		$app->get('/seeds/sofia', 'App\Controllers\api\SeedsApiController:getByPublishSofia');
		/**
		 * End point para activar el estado de sofia
		 * 
		 * Datos esperados:
		 * -id (requerido) id de la semilla
		 * -sofia_status (requerido) estado de la semilla en sofia
		 * -sofiia_comments (requerido) comentarios de la semilla en sofia
		 */
		$app->put('/seeds/sofia', 'App\Controllers\api\SeedsApiController:activateSofiaPlus');
		/**
		 * End point para generar excel de semillas
		 * 
		 */
		$app->get('/seeds/excel', 'App\Controllers\api\SeedsApiController:generateExcel');
    });

};
