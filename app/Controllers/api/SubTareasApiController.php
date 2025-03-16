<?php
namespace App\Controllers\api;

use App\Models\SubTareas;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class SubTareasApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        $tarea = SubTareas::create([
            'id_tarea' => $data['id_tarea'],
            'status' => $data['status'],
            'porcentaje' => $data['porcentaje'],
            'tarea' => $data['tarea'],
            'id_usuario' => $data['id_usuario'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_final' => $data['fecha_final'],
            'url' => $data['url'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            
        ]);

        //buscamos los datos del usuario 
        $usuario = Usuarios::find($data['id_usuario']);
        //enviamos el correo
        $emailNotifications = new EmailNotifications();
        $emailNotifications->NotificationByEmail(
            $usuario->nombres.' '.$usuario->apellidos, 
            $usuario->email, 
            'Se ha creado una nueva tarea '.$data['tarea'].' , por favor ingrese a la plataforma para gestionarla. ',
        );

        return $response->withStatus(201)
        ->withJson([
            'message' => 'tareaa creado exitosamente',
            'user' => $tarea
        ]);
    }
    public function update(Request $request, Response $response, $args)
    {
        
        $data = $request->getParsedBody();
        $id = $args['id'];
        
        $tarea = SubTareas::find($id);
        if (!$tarea) {
            return $response->withJson(['error' => 'Tarea no encontrada'], 404);
        }
        
        $tarea->id_tarea = $data['id_tarea'];
        $tarea->status = $data['status'];
        $tarea->porcentaje = $data['porcentaje'];
        $tarea->tarea = $data['tarea'];
        $tarea->id_usuario = $data['id_usuario'];
        $tarea->fecha_inicio = $data['fecha_inicio'];
        $tarea->fecha_final = $data['fecha_final'];
        $tarea->url = $data['url'];
        $tarea->updated_at = date('Y-m-d H:i:s');
        $tarea->save();
        
        return $response->withJson(['message' => 'Tarea actualizada correctamente'], 200);
        
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $tarea = SubTareas::find($id);
        if (!$tarea) {
            return $response->withJson(['error' => 'tarea no encontrada'], 404);
        }
        $tarea->delete();
        return $response->withJson(['message' => 'tarea eliminado correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $tarea = SubTareas::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $response->withJson([
            'tarea' => $tarea
        ], 200);
    }
    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $tarea = Tareas::find($id);
        if (!$tarea) {
            return $response->withJson(['error' => 'tareaa no encontrado'], 404);
        }
        return $response->withJson([
            'tarea' => $tarea
        ], 200);
    }
}


