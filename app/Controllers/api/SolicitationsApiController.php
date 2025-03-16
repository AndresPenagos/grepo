<?php
namespace App\Controllers\api;

use App\Models\Solicitudes;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class SolicitationsApiController
{
    public function store(Request $request, Response $response)
    {
		$data = $request->getParsedBody();
        $solicitations = Solicitudes::create([
            'id_programa' =>  			$data['id_programa'],
            'id_semilla' =>  			$data['id_semilla'],
            'enlace_entrega' =>  		$data['enlace_entrega'],
            'nombre_quien_entrega' =>  	$data['nombre_quien_entrega'],
            'correo' =>  				$data['correo'],
            'comentario' =>  			$data['comentario'],
            'created_at' => 			date('Y-m-d H:i:s'),
            'updated_at' => 			date('Y-m-d H:i:s')
            
        ]);
        //se radica el usuario que hizo la solicitud notificamos al administrador
        $gestores = Usuarios::where('rol', 'Administrador')->get();
        //avisamo gestor de la semilla que hay que crear una nueva semilla con este programa
        //recorre todos los gestores de semillas
        foreach ($gestores as $gestor) {
            //enviamos el correo
            $emailNotifications = new EmailNotifications();
            $emailNotifications->NotificationByEmail(
                $gestor->nombres.' '.$gestor->apellidos, 
                $gestor->email, 
                'Se ha creado una nueva solicitud '.$data['enlace_entrega'].' , por favor ingrese a la plataforma para gestionara asignaciones. ',
            );
        }
        return $response->withStatus(201)
        ->withJson([
            'message' => 'Solicitud creado exitosamente',
            'user' => $solicitations
        ]);
    }
    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que el programa exista
         */
        $solicitations = Solicitudes::find($id);
        if (!$solicitations) {
            return $response->withJson(['error' => 'Solicitud no encontrada'], 404);
        }
       
        $solicitations->update([
            'id_programa' =>  			$data['id_programa'],
            'id_semilla' =>  			$data['id_semilla'],
            'enlace_entrega' =>  		$data['enlace_entrega'],
            'nombre_quien_entrega' =>  	$data['nombre_quien_entrega'],
            'correo' =>  				$data['correo'],
            'comentario' =>  			$data['comentario'],
            'updated_at' => 			date('Y-m-d H:i:s'),
        ]);
        return $response->withStatus(200)
        ->withJson([
            'message' => 'Solicitud creada exitosamente',
            'user' => $solicitations
        ]);
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $solicitations = Solicitudes::find($id);
        if (!$solicitations) {
            return $response->withJson(['error' => 'Solicitud no encontrada'], 404);
        }
        $solicitations->delete();
        return $response->withJson(['message' => 'Solicitud eliminada correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
    
        // Obtén las solicitudes con relaciones cargadas
        $solicitations = Solicitudes::with(['programas', 'semillas','sofia','tareas'])
        ->join('programas', 'solicitudes.id_programa', '=', 'programas.id')
        ->join('semillas', 'solicitudes.id_semilla', '=', 'semillas.id')
        ->orderBy('solicitudes.id', 'desc')
        ->paginate($perPage, [
            'solicitudes.id',
            'solicitudes.id_programa',
            'solicitudes.id_semilla',
            'solicitudes.enlace_entrega',
            'solicitudes.nombre_quien_entrega',
            'solicitudes.correo',
            'solicitudes.comentario',
            'solicitudes.created_at',
            'solicitudes.updated_at'
        ], 'page', $page);

    
    return $response->withJson([
        'solicitudes' => $solicitations
    ], 200);
    
        
            
    }
    public function indexAll(Request $request, Response $response, $args) {
        $solicitations = Solicitudes::all();
        return $response->withJson([
            'solicitations' => $solicitations
        ], 200);
    }

    public function SolicitudesPedientesMes(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        
        $mes = $data["mes"];
        //si mes no esta definimos ponemos el del sistema
        if(!isset($mes)){
            $mes = date('n');
        }
        //generamos fecha inicio y fecha fin a partir del numero de mes
        $fecha_inicio = date('Y-'.$mes.'-01');
        $fecha_fin = date('Y-'.$mes.'-t');
        //Buscamos toda las solicitudes que no esten con tarea en 100 y que esten dentro de las fechas 
        $solicitudes = Solicitudes::with(['programas', 'semillas','tareas.usuarios', 'tareas.subtareas.usuarios'])
                                    ->where('created_at', '>=', $fecha_inicio)
                                    ->where('created_at', '<=', $fecha_fin)
                                    ->whereHas('tareas', function ($query) {
                                        $query->where('porcentaje', '!=', 100);
                                    })
                                    ->get();

        
        return $response->withJson([
            'solicitudes' => $solicitudes
        ], 200);
        
    }
    public function SolicitudesPedientes(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $solicitudes = Solicitudes::with(['programas', 'semillas','tareas.usuarios', 'tareas.subtareas.usuarios'])
                                    ->whereHas('tareas', function ($query) {
                                        $query->where('porcentaje', '!=', 100);
                                    })
                                    ->get();

        
        return $response->withJson([
            'solicitudes' => $solicitudes
        ], 200);
        
    }
}


