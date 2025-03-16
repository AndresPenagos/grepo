<?php
namespace App\Controllers\api;

use App\Models\Reportes;
use App\Models\SubTareas;
use App\Models\Tareas;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Types\Type;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ReporteApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        Reportes::create([
            'id_subtarea' => $data['id_subtarea'],
            'id_usuario' => $_SESSION['user_id'],
            'horas' => $data['horas'],
            'dia' => $data['dia'],
            'descripcion' => $data['descripcion'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        //actualizamos la subtarea
        $subtarea = SubTareas::find($data['id_subtarea']);
        $subtarea->porcentaje = $data['porcentaje'];
        $subtarea->status = $data['status'];
        $subtarea->save();

        //actualizamos porcentaje de la tarea recorriendo todas las subptareas
        $subtareas = SubTareas::where('id_tarea', '=', $subtarea->id_tarea)->get();
        $porcentaje = 0;
        foreach ($subtareas as $subtarea) {
            $porcentaje += $subtarea->porcentaje;
        }
        $porcentaje = $porcentaje / count($subtareas);
        $tarea = Tareas::find($subtarea->id_tarea);
        $tarea->porcentaje = $porcentaje;
        $tarea->save();


        return $response->withStatus(201);
    }
    public function update(Request $request, Response $response, $args)
    {
        //no se va utilizar por ahora v2

    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $reporte = Reportes::find($id);
        if (!$reporte) {
            return $response->withJson(['error' => 'Reporte no encontrado'], 404);
        }
        $reporte->delete();
        return $response->withJson(['message' => 'Reporte eliminado correctamente'], 200);
    }
    public function index(Request $request, Response $response, $args) {
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $reporte = Reportes::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        return $response->withJson([
            'reporte' => $reporte
        ], 200);
    }
    public function getByUsuarioSession(Request $request, Response $response, $args) {
       
        $data = $request->getParsedBody();
        //si desde y hasta no estan definidos se toma el mes actual
        if(!isset($data["desde"]) || !isset($data["hasta"])){
            $data["desde"] = date('Y-m-01');
            $data["hasta"] = date('Y-m-t');
        }
        
        $reporte = Reportes::where('id_usuario', '=', $_SESSION['user_id'])
        ->where("dia",">=",$data["desde"])
        ->where("dia","<=",$data["hasta"])
        ->orderBy('dia', 'desc')->get();
        return $response->withJson([
            'reportes' => $reporte
        ], 200);
    }
    public function reporte(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        //si desde y hasta no estan definidos se toma el mes actual
        if(!isset($data["desde"]) || !isset($data["hasta"])){
            $data["desde"] = date('Y-m-01');
            $data["hasta"] = date('Y-m-t');
        }
        
        $reporte = Reportes::with(['subtareas.tareas.solicitudes.programas','subtareas.tareas.solicitudes.semillas'])->where('id_usuario', '=', $_SESSION['user_id'])
        ->where("dia",">=",$data["desde"])
        ->where("dia","<=",$data["hasta"])
        ->orderBy('dia', 'desc')->get();
      

        $spreadsheet = new Spreadsheet();
        // Obtener la hoja activa
        $sheet = $spreadsheet->getActiveSheet();
        // Escribir datos en la hoja
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->getColumnDimension('A')->setWidth(22); 
        $sheet->setCellValue('B1', 'Programa');
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->setCellValue('C1', 'Código semilla');
        $sheet->getColumnDimension('C')->setWidth(22); 
        $sheet->setCellValue('D1', 'Tipo de programa');
        $sheet->getColumnDimension('D')->setWidth(22); 
        $sheet->setCellValue('E1', 'Horas');
        $sheet->getColumnDimension('E')->setWidth(22); 
        $sheet->setCellValue('F1', 'Detalles actividad');
        $sheet->getColumnDimension('F')->setWidth(22); 

        $row = 2;
        for ($i = 0; $i < count($reporte); $i++) {
            $sheet->setCellValue('A' . $row, $reporte[$i]->dia);
            
            $sheet->setCellValue('B' . $row, $reporte[$i]->subtareas->tareas->solicitudes->programas->nombre);
            $sheet->setCellValue('C' . $row, $reporte[$i]->subtareas->tareas->solicitudes->semillas->codigo);
            $sheet->setCellValue('D' . $row, $reporte[$i]->subtareas->tareas->solicitudes->programas->tipo);
            $sheet->setCellValue('E' . $row, $reporte[$i]->horas);
            $sheet->setCellValue('F' . $row, $reporte[$i]->descripcion);
            $row++;
        }
        // Configurar las cabeceras para la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="reporte_actividades.xlsx"');
        header('Cache-Control: max-age=0');
        // Guardar el archivo Excel en la salida
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    }
    public function reporte_gestores(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        //si desde y hasta no estan definidos se toma el mes actual
        $mes = $data["mes"];
        //si mes no esta definimos ponemos el del sistema
        if(!isset($mes)){
            $mes = date('n');
        }
        $data["desde"] = date('Y-'.$mes.'-01');
        $data["hasta"] = date('Y-'.$mes.'-t');
        $row = 2;
        $spreadsheet = new Spreadsheet();
        // Obtener la hoja activa
        $sheet = $spreadsheet->getActiveSheet();
        $roles = ['Gestor de repositorio', 'Gestor de repositorio desarrollador'];
        //obtenemos a todos los gestores de repositorio
        $usuarios = Usuarios::whereIn('rol', $roles)->orderby("nombres","desc")->get();
        //recorremos los usuarios y consultamos sus reportes en el mes sumamos la horas y lo guardamos en un array
        for ($j = 0; $j < count($usuarios); $j++) {
            
            $reporte = Reportes::with(['subtareas.tareas.solicitudes.programas','subtareas.tareas.solicitudes.semillas'])->where('id_usuario', '=', $usuarios[$j]->id)
                ->where("dia",">=",$data["desde"])
                ->where("dia","<=",$data["hasta"])
                ->orderBy('dia', 'desc')->get();

            // Escribir datos en la hoja
            $sheet->setCellValue('A1', 'Fecha');
            $sheet->getColumnDimension('A')->setWidth(22); 
            $sheet->setCellValue('B1', 'Gestor');
            $sheet->getColumnDimension('B')->setWidth(22);
            $sheet->setCellValue('C1', 'Programa');
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->setCellValue('D1', 'Código semilla');
            $sheet->getColumnDimension('D')->setWidth(22); 
            $sheet->setCellValue('E1', 'Tipo de programa');
            $sheet->getColumnDimension('E')->setWidth(22); 
            $sheet->setCellValue('F1', 'Horas');
            $sheet->getColumnDimension('F')->setWidth(22); 
            $sheet->setCellValue('G1', 'Detalles actividad');
            $sheet->getColumnDimension('G')->setWidth(22); 
            for ($i = 0; $i < count($reporte); $i++) {
                $sheet->setCellValue('A' . $row, $reporte[$i]->dia);
                $sheet->setCellValue('B' . $row, $usuarios[$j]->nombres.' '.$usuarios[$j]->apellidos);
                $sheet->setCellValue('C' . $row, $reporte[$i]->subtareas->tareas->solicitudes->programas->nombre);
                $sheet->setCellValue('D' . $row, $reporte[$i]->subtareas->tareas->solicitudes->semillas->codigo);
                $sheet->setCellValue('E' . $row, $reporte[$i]->subtareas->tareas->solicitudes->programas->tipo);
                $sheet->setCellValue('F' . $row, $reporte[$i]->horas);
                $sheet->setCellValue('G' . $row, $reporte[$i]->descripcion);
                $row++;
            }            
        }

        
        // Configurar las cabeceras para la descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="reporte_actividades.xlsx"');
        header('Cache-Control: max-age=0');
        // Guardar el archivo Excel en la salida
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    }
    public function reportes (Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        
        $mes = $data["mes"];
        //si mes no esta definimos ponemos el del sistema
        if(!isset($mes)){
            $mes = date('n');
        }
        $roles = ['Gestor de repositorio', 'Gestor de repositorio desarrollador'];
        //Buscamos a todos los usuarios con rol gestor de repositorio
        $usuarios = Usuarios::whereIn('rol', $roles)->orderby("nombres","desc")->get();
        //generamos fecha inicio y fecha fin a partir del numero de mes
        $fecha_inicio = date('Y-'.$mes.'-01');
        $fecha_fin = date('Y-'.$mes.'-t');
        //recorremos los usuarios y consultamos sus reportes en el mes sumamos la horas y lo guardamos en un array
        for ($i = 0; $i < count($usuarios); $i++) {
            //establecemos la foto del usuario
            if($usuarios[$i]->foto == 'default.jpg' or $usuarios[$i]->foto == 'none' or $usuarios[$i]->foto == 'no-photo.jpg'){
                $usuarios[$i]->photo_url = $_ENV['APP_URL']. $_ENV['APP_LOCATION'].'/public/assets/img/default.jpg';
            }else{
                $usuarios[$i]->photo_url = $_ENV['APP_URL'].$_ENV['APP_LOCATION'].'/' . $_ENV['APP_STORAGE'] .'/'.'users' .'/'.  $usuarios[$i]->foto;
            }

            $reportes = Reportes::where('id_usuario', '=', $usuarios[$i]->id)
            ->where("dia",">=",$fecha_inicio)
            ->where("dia","<=",$fecha_fin)
            ->get();
            $horas = 0;
            for ($j = 0; $j < count($reportes); $j++) {
                $horas += $reportes[$j]->horas;
            }
            $usuarios[$i]->horas = $horas;
        }
        
        //retornamos el array
        return $response->withJson([
            'usuarios' => $usuarios
        ], 200);
    }
}


