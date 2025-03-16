<?php
namespace App\Controllers\api;

use App\Models\Revisions;
use App\Models\ApplicationsRevisions;
use App\Models\ApplicationsRevisionsDetails;
use App\Models\Solicitations;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Eloquent\Model;

use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class RevisionsApiController
{
    
    public function store(Request $request, Response $response, $args) {
       
        $data = $request->getParsedBody();
        // Iniciamos la transacción
        DB::beginTransaction();
        try {
            // 1. Guardamos la revisión y obtenemos el id
            $revision = new Revisions();
            $revision = $revision->create([
                'name' => $data['name'],
                'link' => $data['link'],
                'general_comments' => $data['general_comments'],
                'start_date' => $data['start_date'],
                'proposed_end_date' => $data['proposed_end_date'],
                'actual_end_date' => $data['actual_end_date'],
            ]);
            // 2. Guardamos la aplicación de la revisión usando el id obtenido en el paso 1 y el id de la solicitud
            $applicationRevision = new ApplicationsRevisions();
            $applicationRevision = $applicationRevision::create([
                'revisions_id' => $revision->id,
                'solicitations_id' => $data['solicitations_id'],
            ]);
            // 3. Guardamos los gestores relacionados usando el id obtenido en el paso 2
            $users = json_decode($data['users']);
            foreach ($users as $user) {
                $applicationRevisionDetails = new ApplicationsRevisionsDetails();
                $applicationRevisionDetails = $applicationRevisionDetails::create([
                    'applications_revisions_id' => $applicationRevision->id,
                    'users_id' => $user->id,
                    'manager' => $user->manager,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            // Commit de la transacción si todas las operaciones se completaron correctamente
            DB::commit();
            return $response->withJson([
                'message' => 'Revisión guardada correctamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            print_r($e->getMessage());
            return $response->withStatus(500);
        }
    }
    
    public function getSolicitationsNotRevisions(Request $request, Response $response, $args) {
        $seeds = Solicitations::withoutApplicationsRevisions()->with('seeds.program')->get();
        return $response->withJson([
            'solicitations' => $seeds
        ], 200);
    }

    public function getSolicitationsNotRevisionsByProgram(Request $request, Response $response, $args) {
        // Obtener el ID del programa desde los parámetros de la solicitud
        $programId = $args['program_id']; // Suponiendo que el ID del programa se pasa como un parámetro llamado 'program_id'
        // Filtrar las solicitudes por el ID del programa
        $seeds = Solicitations::withoutApplicationsRevisions()->whereHas('seeds.program', function ($query) use ($programId) {
            $query->where('id', $programId);
        })->with('seeds.program')->get();
        return $response->withJson([
            'solicitations' => $seeds
        ], 200);
    }
    public function getSolicitationsRevisionsByProgram(Request $request, Response $response, $args) {
        $programId = $args['program_id'];
        $revisions = Revisions::with([
            'applications_revisions', 
            'applications_revisions.applications_revisions_details.user',
            'applications_revisions.solicitations.seeds.program'
            ])->whereHas('applications_revisions.solicitations.seeds.program', function($query) use ($programId){
                $query->where('id', $programId);
        })->get();
        //revisamos las url de los user 
        foreach ($revisions as $revision) {
            foreach ($revision->applications_revisions as $application_revision) {
                foreach ($application_revision->applications_revisions_details as $application_revision_detail) {
                    User::getPhoto($application_revision_detail->user);
                }
            }
        }
        return $response->withJson([
            'revisions' => $revisions
        ], 200);
    }    
    public function getRevisionsByStatus(Request $request, Response $response, $args){
        $status = $request->getParam('status');
        $perPage = 6;
        $page = $request->getParam('page') ?? 1;
        
        $revisions = Revisions::with([
            'applications_revisions', 
            'applications_revisions.applications_revisions_details.user',
            'applications_revisions.solicitations.seeds.program'
            ])->whereHas('applications_revisions.solicitations.seeds', function($query) use ($status){
                $query->where('states_id', $status);
            })->paginate($perPage, ['*'], 'page', $page);

        //revisamos las url de los user 
        foreach ($revisions as $revision) {
            foreach ($revision->applications_revisions as $application_revision) {
                foreach ($application_revision->applications_revisions_details as $application_revision_detail) {
                    User::getPhoto($application_revision_detail->user);
                }
            }
        }
        return $response->withJson([
            'revisions' => $revisions
        ], 200);
    }
}


