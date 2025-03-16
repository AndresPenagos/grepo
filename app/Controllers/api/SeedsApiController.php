<?php
namespace App\Controllers\api;
use App\Models\Programas;
use App\Models\Semillas;
use App\Models\Solicitudes;
use App\Models\Usuarios;
use App\Models\EmailNotifications;

use Slim\Http\Request;
use Slim\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SeedsApiController{
    public function storeSofia(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $program = Programas::where('codigo', $data['codigo'])->first();
        /**
         * Validamos que la semilla no exista
         */
        if ($program) {
            return $response
                ->withStatus(400)
                ->withJson([
                    'message' => 'El programa ya existe!'
                ]);
        }
        //debsmos crear prograna
        $program = Programas::create([
            'nombre' =>                     $data['p_nombre'],
            'codigo' =>                     $data['p_codigo'],
            'version' =>                    $data['p_version'],
            'tipo' =>                       $data['p_tipo'],
            'horas' =>                      $data['p_horas'],
            'link_descripcion_programa' =>  $data['p_link_descripcion_programa'],
            'link_diseno_curricular' =>     $data['p_link_diseno_curricular'],
            'created_at' =>                 date('Y-m-d H:i:s'),
            'updated_at' =>                 date('Y-m-d H:i:s'),
       ]);
        //creamos la semilla
        $seeds = Semillas::create([
            'id_programa' =>                $program->id,
            'codigo' =>                     $data['s_codigo'],
            'version' =>                    $data['s_version'],
            'linea_produccion' =>           $data['s_linea_produccion'],
            'activa' =>                     '0',
            'video_programa' =>             $data['s_video_programa'],
            'codigo_interno' =>             $data['s_codigo_interno'],
            'updated_at' =>                 date('Y-m-d H:i:s'),
            'created_at' =>                 date('Y-m-d H:i:s')
        ]);
        
        //debenos crear la solicitud
        $solicitudes = Solicitudes::create([
            'id_programa' =>  			    $program->id,
            'id_semilla' =>  			    $seeds->id,
            'enlace_entrega' =>  		    $data['so_enlace_entrega'],
            'nombre_quien_entrega' =>  	    $data['so_nombre_quien_entrega'],
            'correo' =>  				    $data['so_correo'],
            'comentario' =>  			    $data['so_comentario'],
            'created_at' => 			    date('Y-m-d H:i:s'),
            'updated_at' => 			    date('Y-m-d H:i:s')
            
        ]);

        return $response->withStatus(201)
        ->withJson([
            'message' => 'Semilla creada correctamente!',
            'seeds' => $seeds,
            'program' => $program,
            'solicitations' => $solicitudes
        ]);

    }
    public function store(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $seeds = Semillas::create([
            'id_programa' =>                 $data['id_programa'],
            'codigo' =>                      $data['codigo'],
            'version' =>                     $data['version'],
            'linea_produccion' =>            $data['linea_produccion'],
            'activa' =>                      $data['activa'],
            'video_programa' =>              $data['video_programa'],
            'codigo_interno' =>              $data['codigo_interno'],
            'updated_at' =>                 date('Y-m-d H:i:s'),
            'created_at' =>                 date('Y-m-d H:i:s')
        ]);

        //notificamos gestores desarrolladores que se creeo una semilla
        //traemos el gestor de la semilla que tengan ese rol
        $gestores = Usuarios::where('rol', 'Gestor de repositorio desarrollador')->get();
        //recorre todos los gestores de semillas
        foreach ($gestores as $gestor) {
            //enviamos el correo
            $emailNotifications = new EmailNotifications();
            $emailNotifications->NotificationByEmail(
                $gestor->nombres.' '.$gestor->apellidos, 
                $gestor->email, 
                'Se ha creado una nueva semilla con el código: '.$data['codigo'].', por favor ingrese a la plataforma para verificar la información. ',
            );
        }
        return $response->withStatus(201)
        ->withJson([
            'message' => 'Semilla creada correctamente!',
            'seeds' => $seeds
        ]);

    }
    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que la categoría exista
         */
        $seeds = Semillas::find($id);
        if (!$seeds) {
            return $response->withJson(['error' => 'Seed not found'], 404);
        }
        /**
         * Validamos que el nombre no esté en uso por otra categoría
         */
        $existingSeeds = Semillas::where('codigo', $data['name'])->where('codigo', '!=', $id)->first();
        if ($existingSeeds && $existingSeeds->id != $id) {
            return $response->withJson(['error' => 'Name already in use'], 400);
        }
        $seeds->update([
            'id_programa' =>                 $data['id_programa'],
            'codigo' =>                      $data['codigo'],
            'version' =>                     $data['version'],
            'linea_produccion' =>            $data['linea_produccion'],
            'activa' =>                      $data['activa'],
            'video_programa' =>              $data['video_programa'],
            'codigo_interno' =>              $data['codigo_interno'],
            'updated_at' =>                  date('Y-m-d H:i:s'),
        ]);
        return $response->withJson([
            'message' => 'Category updated successfully',
            'category' => $seeds
        ]);
    }
    public function delete(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que la categoría exista
         */
        $seed = Semillas::find($id);
        if (!$seed) {
            return $response->withJson(['error' => 'Category not found'], 404);
        }
        $seed->delete();
    }
    public function index(Request $request, Response $response, $args) {
       
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $seeds = Semillas::orderBy('id', 'desc')->with('programas')->where('id_programa',$request->getParam('id_programa'))->paginate($perPage, ['*'], 'page', $page);
       
        return $response->withJson([
            'seeds' => $seeds
        ], 200);
    }
    public function indexAll(Request $request, Response $response, $args) {
        
        /*$seeds = Semillas::with('programas')->get();
        return $response->withJson([
            'seeds' => $seeds
        ], 200);*/
        $programs = Programas::with('semillas')->get();
        return $response->withJson([
            'programas' => $programs
        ], 200);
    }
    //listamos las semillas que se hallan finalizado en revisión y esten lista para publicación
    public function getByPublishSofia(Request $request, Response $response)
    {
        $seeds = Semillas::whereHas('program', function ($query) {
            $query->whereHas('seeds.solicitations', function ($query) {
                $query->where('states_id', 1);
            });
        })
        ->with(['program' => function ($query) {
            $query->select('id', 'name','type_program','id_code');
        }])
        ->orderBy('updated_at', 'desc')
        ->get(); 

        return $response->withJson([
            'seeds' => $seeds
        ]);
    }
    public function getModality(Request $request, Response $response, $args){
        $modality = Semillas::getModalityOptions();
        return $response->withJson([
            'modality' => $modality
        ], 200);
    }
    public function activateSofiaPlus(Request $request, Response $response){
        $data = $request->getParsedBody();
        $id = $data['id'];
        $seeds = Semillas::find($id);
        if (!$seeds) {
            return $response->withJson(['error' => 'Semilla no encontrada'], 404);
        }
        $seeds->update([
            'sofia_status' => 'Si',
            'sofia_date' => date('Y-m-d H:i:s'),
            'sofia_comments' => $data['sofia_comments'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson([
            'message' => 'Semilla actualizada correctamente',
            'seeds' => $seeds
        ]);
    }
    public function generateExcel(){
        $seeds = Semillas::whereHas('programas', function ($query) {
            $query->whereHas('semillas.solicitations', function ($query) {
                $query->where('states_id', 1);
            });
        })
        ->with(['programas' => function ($query) {
            $query->select('id', 'name','id_code');
        }])
        ->orderBy('updated_at', 'desc')
        ->get(); 
        // Crear un nuevo libro de Excel
        $spreadsheet = new Spreadsheet();
        // Obtener la hoja activa
        $sheet = $spreadsheet->getActiveSheet();
        // Escribir datos en la hoja
        $sheet->setCellValue('A1', 'Código programa');
        $sheet->getColumnDimension('A')->setWidth(22); 
        $sheet->setCellValue('B1', 'Código semilla');
        $sheet->getColumnDimension('B')->setWidth(22); 
        $sheet->setCellValue('C1', 'Código interno');
        $sheet->getColumnDimension('C')->setWidth(22); 
        $sheet->setCellValue('D1', 'Tipo de programa');
        $sheet->getColumnDimension('D')->setWidth(22); 
        $sheet->setCellValue('E1', 'Modalidad');
        $sheet->getColumnDimension('E')->setWidth(22); 
        $sheet->setCellValue('F1', 'Nombre semilla');
        $sheet->getColumnDimension('F')->setWidth(40); 
        $sheet->setCellValue('G1', 'Horas');
        $sheet->setCellValue('H1', 'Versión semilla');
        $sheet->getColumnDimension('H')->setWidth(22); 
        $sheet->setCellValue('I1', 'Versión programa');
        $sheet->getColumnDimension('I')->setWidth(22); 
        $sheet->setCellValue('J1', 'Activo Sofia Plus');
        $sheet->getColumnDimension('J')->setWidth(22); 
        $sheet->setCellValue('K1', 'Comentarios Sofia Plus');
        $sheet->getColumnDimension('K')->setWidth(32); 
        $sheet->setCellValue('L1', 'Fecha Sofia Plus');
        $sheet->getColumnDimension('L')->setWidth(22); 
        $sheet->setCellValue('M1', 'Descripción del programa');
        $sheet->getColumnDimension('M')->setWidth(32); 
        $sheet->setCellValue('N1', 'Video');
        $sheet->getColumnDimension('N')->setWidth(32); 
        $sheet->setCellValue('O1', 'Fecha creación');
        $sheet->getColumnDimension('O')->setWidth(32); 
        $sheet->setCellValue('P1', 'Fecha actualización');
        $sheet->getColumnDimension('P')->setWidth(32); 
        // Iterar semillas
        $row = 2;
        for ($i = 0; $i < count($seeds); $i++) {
            $sheet->setCellValue('A' . $row, $seeds[$i]->program->id_code);
            $sheet->setCellValue('B' . $row, $seeds[$i]->id_seeds);
            $sheet->setCellValue('C' . $row, $seeds[$i]->internal_code);
            $sheet->setCellValue('D' . $row, $seeds[$i]->program->type_program);
            $sheet->setCellValue('E' . $row, $seeds[$i]->modality);
            $sheet->setCellValue('F' . $row, $seeds[$i]->program->name);
            $sheet->setCellValue('G' . $row, $seeds[$i]->hours);
            $sheet->setCellValue('H' . $row, $seeds[$i]->version_seeds);
            $sheet->setCellValue('I' . $row, $seeds[$i]->version_program);
            $sheet->setCellValue('J' . $row, $seeds[$i]->sofia_status);
            $sheet->setCellValue('K' . $row, $seeds[$i]->sofia_comments);
            $sheet->setCellValue('L' . $row, $seeds[$i]->sofia_date);
            $sheet->setCellValue('M' . $row, $seeds[$i]->link_description_program);
            $sheet->setCellValue('N' . $row, $seeds[$i]->video_program);
            $sheet->setCellValue('O' . $row, $seeds[$i]->created_at);
            $sheet->setCellValue('P' . $row, $seeds[$i]->updated_at);
            $row++;
        }
        // Configurar las cabeceras para la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="reporte_sofia.xlsx"');
        header('Cache-Control: max-age=0');
        // Guardar el archivo Excel en la salida
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

}