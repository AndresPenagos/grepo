<?php
namespace App\Controllers\api;

use App\Models\Sofia;
use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class SofiaApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        //validamos que no exista la solicitud
        $sofia = Sofia::where('id_programa', $data['id_programa'])->where('id_semilla', $data['id_semilla'])->first();
        if ($sofia) {
            return $response->withJson(['error' => 'Ya existe una solicitud para esta semilla'], 404);
        }
        Sofia::create([
            'id_programa' => $data['id_programa'],
            'id_semilla' => $data['id_semilla'],
            'comentarios' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        //buscamos usuarios con rol de sofia
        $gestores = Usuarios::where('rol', 'Gestor de SofiaPlus')->get();
        //avisamo gestor de la semilla que hay que crear una nueva semilla con este programa
        //recorre todos los gestores de semillas
        foreach ($gestores as $gestor) {
            //enviamos el correo
            $emailNotifications = new EmailNotifications();
            $emailNotifications->NotificationByEmail(
                $gestor->nombres.' '.$gestor->apellidos, 
                $gestor->email, 
                'Se ha creado una nueva solicitud para el programa, por favor ingrese a la plataforma para gestionarla. ',
            );
        }
        return $response->withStatus(201)
            ->withJson([
                'message' => 'Solicitud creado exitosamente',
                'user' => $sofia
            ]);
        
        
    }
    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        $sofia = Sofia::find($id);
        if (!$sofia) {
            return $response->withJson(['error' => 'No se encuentra la solicitud'], 404);
        }
        $sofia->update([
            'comentarios' => $data['comentarios'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $sofia = Sofia::find($id);
        if (!$sofia) {
            return $response->withJson(['error' => 'No se encuentra la solicitud'], 404);
        }
        $sofia->delete();
    }
    public function index(Request $request, Response $response)
    {
    
        $sofia = Sofia::with('programas', 'programas.semillas')->orderBy('id', 'desc')->get();
        return $response->withJson([
            'sofia' => $sofia
        ], 200);
    }
    public function reporte(Request $request, Response $response){
        $sofia = Sofia::with('programas', 'programas.semillas')->orderBy('id', 'desc')->get();
        $spreadsheet = new Spreadsheet();
        // Obtener la hoja activa
        $sheet = $spreadsheet->getActiveSheet();
        // Escribir datos en la hoja
        $sheet->setCellValue('A1', 'Código programa');
        $sheet->getColumnDimension('A')->setWidth(22); 
        $sheet->setCellValue('B1', 'Código semilla');
        $sheet->getColumnDimension('B')->setWidth(22); 
        $sheet->setCellValue('C1', 'Tipo de programa');
        $sheet->getColumnDimension('C')->setWidth(22); 
        $sheet->setCellValue('D1', 'Nombre semilla');
        $sheet->getColumnDimension('D')->setWidth(40); 
        $sheet->setCellValue('E1', 'Horas');
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->setCellValue('F1', 'Versión semilla');
        $sheet->getColumnDimension('F')->setWidth(22); 
        $sheet->setCellValue('G1', 'Versión programa');
        $sheet->getColumnDimension('G')->setWidth(22); 
        $sheet->setCellValue('H1', 'Comentarios Sofia Plus');
        $sheet->getColumnDimension('H')->setWidth(32); 
        $sheet->setCellValue('I1', 'Fecha Sofia Plus');
        $sheet->getColumnDimension('I')->setWidth(22); 
        // Iterar semillas
        $row = 2;
        for ($i = 0; $i < count($sofia); $i++) {
            $sheet->setCellValue('A' . $row, $sofia[$i]->programas->codigo);
            $sheet->setCellValue('B' . $row, $sofia[$i]->programas->semillas[0]->codigo);
            $sheet->setCellValue('C' . $row, $sofia[$i]->programas->tipo);
            $sheet->setCellValue('D' . $row, $sofia[$i]->programas->nombre);
            $sheet->setCellValue('E' . $row, $sofia[$i]->programas->horas);
            $sheet->setCellValue('F' . $row, $sofia[$i]->programas->semillas[0]->version);
            $sheet->setCellValue('G' . $row, $sofia[$i]->programas->version);
            $sheet->setCellValue('H' . $row, $sofia[$i]->comentarios);
            $sheet->setCellValue('I' . $row, $sofia[$i]->created_at);
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


