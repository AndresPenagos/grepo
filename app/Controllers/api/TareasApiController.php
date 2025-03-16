<?php
namespace App\Controllers\api;

use App\Models\Tareas;
use App\Models\SubTareas;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class TareasApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        //buscamos que no exista una tarea con la misma solicitud
        $tarea = Tareas::where('id_solicitud', '=', $data['id_solicitud'])->first();
        if ($tarea) {
            return $response->withJson(['error' => 'Ya existe una tarea con esta solicitud'], 404);
        } 

        $tarea = Tareas::create([
            'id_solicitud' => $data['id_solicitud'],
            'status' => $data['status'],
            'porcentaje' => $data['porcentaje'],
            'tarea' => $data['tarea'],
            'id_usuario' => $data['id_usuario'],
            'url' => $data['url'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            
        ]);
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
        
        $tarea = Tareas::find($id);
        if (!$tarea) {
            return $response->withJson(['error' => 'Tarea no encontrada'], 404);
        }
        
        
        $tarea->id_solicitud = $data['id_solicitud'];
        $tarea->status = $data['status'];
        $tarea->porcentaje = $data['porcentaje'];
        $tarea->tarea = $data['tarea'];
        $tarea->id_usuario = $data['id_usuario'];
        $tarea->url = $data['url'];
        $tarea->updated_at = date('Y-m-d H:i:s');
        $tarea->save();
        
        return $response->withJson(['message' => 'Tarea actualizada correctamente'], 200);
        
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $tarea = Tareas::find($id);
        if (!$tarea) {
            return $response->withJson(['error' => 'tarea no encontrada'], 404);
        }
        $tarea->delete();
        return $response->withJson(['message' => 'tarea eliminado correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $tarea = Tareas::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $response->withJson([
            'tarea' => $tarea
        ], 200);
    }
    public function get(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        // Obtener todas las tareas relacionadas con la solicitud
        $tareas = Tareas::where('id_solicitud', '=', $id)->get();
        // Para cada tarea, obtener sus sub-tareas
        foreach ($tareas as $tarea) {
            //buscamos el usuario responsable de la tarea
            $tarea->usuarios = Usuarios::where('id', '=', $tarea->id_usuario)->first();
            $tarea->usuarios->id_tarea = $tarea->id;
            //ponemos su foto en la url
            if($tarea->usuarios->foto == 'default.jpg' or $tarea->usuarios->foto == 'none' or $tarea->usuarios->foto == 'no-photo.jpg'){
                $tarea->usuarios->photo_url = $_ENV['APP_URL']. $_ENV['APP_LOCATION'].'/public/assets/img/default.jpg';
            }else{
                $tarea->usuarios->photo_url = $_ENV['APP_URL'].$_ENV['APP_LOCATION'].'/' . $_ENV['APP_STORAGE'] .'/'.'users' .'/'.  $tarea->usuarios->foto;
            }
            $tarea->subtareas = SubTareas::where('id_tarea', '=', $tarea->id)->get();
            // buscamos el usuario para la subtarea y se lo asociamos a la sub tarea
            foreach ($tarea->subtareas as $subtarea) {
                $subtarea->usuario = Usuarios::where('id', '=', $subtarea->id_usuario)->first();
                $subtarea->usuario->id_subtarea = $subtarea->id;
                //ponemos su foto en la url
                if($subtarea->usuario->foto == 'default.jpg' or $subtarea->usuario->foto == 'none' or $subtarea->usuario->foto == 'no-photo.jpg'){
                    $subtarea->usuario->photo_url = $_ENV['APP_URL']. $_ENV['APP_LOCATION'].'/public/assets/img/default.jpg';
                }else{
                    $subtarea->usuario->photo_url = $_ENV['APP_URL'].$_ENV['APP_LOCATION'].'/' . $_ENV['APP_STORAGE'] .'/'.'users' .'/'.  $subtarea->usuario->foto;
                }
            }
        }
        return $response->withJson([
            'tareas' => $tareas
        ], 200);
    }
    public function getIdUsuario(Request $request, Response $response, $args){

        $idUsuario = $_SESSION['user_id'];
        $tareasResult = [];
        $tareas = Tareas::with(['usuarios','solicitudes','solicitudes.programas','solicitudes.semillas','subtareas.usuarios'])
            ->where('id_usuario', '=', $idUsuario)
            ->where('status', '!=', 'Finalizado')
            ->orWhereHas('subtareas', function ($query) use ($idUsuario) {
                $query->where('subtareas.id_usuario', '=', $idUsuario);
            })
            ->get();
    
        // Buscamos el usuario responsable de la subtarea y si no coincide con el ID del usuario logueado makeHidden('subtareas');
    
        // Recorremos las tareas con key 
        foreach($tareas as $key1 => $tarea)
        {
            $filteredSubtareas = [];
            // Recorremos las subtareas de la tarea actual

    
            foreach($tareas[$key1]->subtareas as $key2 =>  $subtarea)
            {
    
                if(($subtarea->usuarios->id == $idUsuario))
                {
                    // Agregamos solo las subtareas que cumplen con la condición al nuevo array
                    $filteredSubtareas[] = $tareas[$key1]->subtareas[$key2] ;
                }
            }
            unset($tareas[$key1]->subtareas);
            // Asignar las subtareas filtradas de nuevo a $tarea->subtareas
            $tareas[$key1]->subtareas = $filteredSubtareas;
        }
    
        // Reindexar los índices de las tareas

    
        return $response->withJson([
            'tareas' => $tareas
        ], 200);
    }
    
    
    private function getPhotos($users){
        foreach ($users as $user) {
            if($user->foto == 'default.jpg' or $user->foto == 'none' or $user->foto == 'no-photo.jpg'){
                $user->photo_url = $_ENV['APP_URL']. $_ENV['APP_LOCATION'].'/public/assets/img/default.jpg';
            }else{
                $user->photo_url = $_ENV['APP_URL'].$_ENV['APP_LOCATION'].'/' . $_ENV['APP_STORAGE'] .'/'.'users' .'/'.  $user->foto;
            }
        }
        return $users;
    }
    public function getGestoresResponsables(Request $request, Response $response, $args)
    {
        // Buscamos los gestores utilizando Eloquent
        $id_tarea = $args['id'];
        
        $gestores = Usuarios::join('tareas', 'usuarios.id', '=', 'tareas.id_usuario')
            ->select('usuarios.foto','tareas.id', 'tareas.id_usuario', 'usuarios.nombres', 'usuarios.apellidos')
            ->where('tareas.id_solicitud', '=', $id_tarea)
            ->get();
    
        // Agregamos las URLs de las fotos utilizando la función getPhotos
        $gestores = $this->getPhotos($gestores);
    
        return $response->withJson([
            'gestores' => $gestores
        ], 200);
    }
    
}


