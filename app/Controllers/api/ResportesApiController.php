<?php
namespace App\Controllers\api;

use App\Models\Reportes;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class ReportesApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        $reporte = Reportes::create([
            'id_subtarea' => $data['id_subtarea'],
            'horas' => $data['horas'],
            'dia' => $data['dia'],
            'descripcion' => $data['descripcion'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            
        ]);
        return $response->withStatus(201)
        ->withJson([
            'message' => 'reportea creado exitosamente',
            'user' => $reporte
        ]);
    }
    public function update(Request $request, Response $response, $args)
    {
        
        $data = $request->getParsedBody();
        $id = $args['id'];
        
        $reporte = Reportes::find($id);
        if (!$reporte) {
            return $response->withJson(['error' => 'reporte no encontrada'], 404);
        }
        
        $reporte->id_subtarea = $data['id_subtarea'];
        $reporte->horas = $data['horas'];
        $reporte->dia = $data['dia'];
        $reporte->descripcion = $data['descripcion'];
        $reporte->updated_at = date('Y-m-d H:i:s');
        $reporte->save();
        
        return $response->withJson(['message' => 'reporte actualizada correctamente'], 200);
        
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $reporte = Reportes::find($id);
        if (!$reporte) {
            return $response->withJson(['error' => 'reporte no encontrada'], 404);
        }
        $reporte->delete();
        return $response->withJson(['message' => 'reporte eliminado correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $reporte = Reportes::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $response->withJson([
            'reporte' => $reporte
        ], 200);
    }
    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $reporte = Reportes::find($id);
        if (!$reporte) {
            return $response->withJson(['error' => 'reportea no encontrado'], 404);
        }
        return $response->withJson([
            'reporte' => $reporte
        ], 200);
    }
}


