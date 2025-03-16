<?php
namespace App\Controllers\api;

use App\Models\Programas;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class ProgramApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $existingUser = Programas::where('codigo', $data['codigo'])->first();
        /**
         * Validamos que el usuario no exista
         */
        if ($existingUser) {
            return $response
                ->withStatus(400)
                ->withJson([
                    'message' => 'Programa ya existe!'
                ]);
        }
        $program = Programas::create([
            'nombre' =>  $data['nombre'],
            'codigo' =>       $data['codigo'],
            'version' =>  $data['version'],
            'tipo' =>  $data['tipo'],
            'horas' =>  $data['horas'],
            'link_descripcion_programa' =>  $data['link_descripcion_programa'],
            'link_diseno_curricular' =>  $data['link_diseno_curricular'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            
        ]);
        //traemos el gestor de la semilla que tengan ese rol
        $gestores = Usuarios::where('rol', 'Gestor de semillas')->get();
        //avisamo gestor de la semilla que hay que crear una nueva semilla con este programa
        //recorre todos los gestores de semillas
        foreach ($gestores as $gestor) {
            //enviamos el correo
            $emailNotifications = new EmailNotifications();
            $emailNotifications->NotificationByEmail(
                $gestor->nombres.' '.$gestor->apellidos, 
                $gestor->email, 
                'Se ha creado un nuevo programa: '.$data['nombre']. ' con el código: '.$data['codigo'].', por favor ingrese a la plataforma para crear la semilla correspondiente. ',
            );
        }
       

        return $response->withStatus(201)
        ->withJson([
            'message' => 'Programa creado exitosamente',
            'user' => $program
        ]);
    }
    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que el programa exista
         */
        $programa = Programas::find($id);
        if (!$programa) {
            return $response->withJson(['error' => 'Programa no encontrado'], 404);
        }
        /**
         * Validamos que el id_code no esté en uso por otro programa
         */
        $existingprograma = Programas::where('codigo', $data['codigo'])->first();
        if ($existingprograma && $existingprograma->id != $id) {
            return $response->withJson(['error' => 'codigo ya existe'], 400);
        }
        $programa->update([
            'nombre' =>  $data['nombre'],
            'codigo' =>       $data['codigo'],
            'version' =>  $data['version'],
            'tipo' =>  $data['tipo'],
            'horas' =>  $data['horas'],
            'link_descripcion_programa' =>  $data['link_descripcion_programa'],
            'link_diseno_curricular' =>  $data['link_diseno_curricular'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson($programa, 200);
    }
    public function getTypeProgram(Request $request, Response $response, $args) {
        $options = Programas::getTypeProgramOptions();

        return $response->withJson([
            'tipo' => $options
        ], 200);
    }
    
    
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $program = Programas::find($id);
        if (!$program) {
            return $response->withJson(['error' => 'Programa no encontrado'], 404);
        }
        $program->delete();
        return $response->withJson(['message' => 'Programa eliminado correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 5;
        $program = Programas::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $response->withJson([
            'program' => $program
        ], 200);
    }
    public function indexAll(Request $request, Response $response, $args) {
        $program = Programas::all();
        return $response->withJson([
            'programas' => $program
        ], 200);
    }
}


