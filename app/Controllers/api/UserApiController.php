<?php
namespace App\Controllers\api;

use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\ImageStorage;
use Illuminate\Pagination\Paginator;
use App\Models\EmailNotifications;


class UserApiController
{
    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $existingUser = Usuarios::where('email', $data['email'])->first();
        /**
         * Validamos que el usuario no exista
         */
        if ($existingUser) {
            return $response
                ->withStatus(400)
                ->withJson([
                    'message' => 'A user with that email already exists.'
                ]);
        }
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user = Usuarios::create([
            'nombres' =>       $data['nombres'],
            'apellidos' =>  $data['apellidos'],
            'email' =>      $data['email'],
            'telefono' =>      $data['telefono'],
            'rol' =>       $data['rol'],
            'password' =>   $password,
            'activo' =>     '1',
            'updated_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'foto' =>      'default.jpg'
        ]);

        $notification = new EmailNotifications();
        $notification->welcomeByEmail(
            $user->name.' '.$user->last_name,
            $user->email,
            $data['password']
        );

        $storage = new ImageStorage('users');
        $name_file = $storage->storeImage($request, 'foto', $user->id . '.jpg');
        if ($name_file !=null) {
            // Actualizamos el nombre de la imagen en la base de datos
            $user->update([
                'foto' => $name_file
            ]);
        }
        
        
        return $response->withStatus(201)
        ->withJson([
            'message' => 'user created successfully',
            'user' => $user
        ]);
    }
    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que el usuario exista
         */
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        /**
         * Validamos que el correo electrónico no esté en uso por otro usuario
         */
        $existingUser = Usuarios::where('email', $data['email'])->first();
        if ($existingUser && $existingUser->id != $id) {
            return $response->withJson(['error' => 'Email already in use'], 400);
        }
        $user->update([
            'nombres' =>       $data['nombres'],
            'apellidos' =>  $data['apellidos'],
            'email' =>      $data['email'],
            'telefono' =>      $data['telefono'],
            'rol' =>       $data['rol'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson($user, 200);
    }
    public function updatePassword(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        /**
         * Validamos que el usuario exista
         */
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found 2'], 404);
        }
        $user->update([
            'password' => md5($data['password']),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson($user, 200);
    }
    public function updateProfile (Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $_SESSION['user_id'];
        /**
         * Validamos que el usuario exista
         */
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        /**
         * Validamos que el correo electrónico no esté en uso por otro usuario
         */
        $existinguser = Usuarios::where('email', $data['email'])->first();
        if ($existinguser && $existinguser->id != $id) {
            return $response->withJson(['error' => 'Email already in use'], 400);
        }
        $user->update([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson($data, 200);
    }
    public function updatePhoto (Request $request, Response $response, $args)
    {
        $id = $_SESSION['user_id'];
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        $storage = new ImageStorage('users');
        $name_file = $storage->storeImage($request, 'foto', $user->id . '.jpg');

        

        if ($name_file !=null) {
            // Actualizamos el nombre de la imagen en la base de datos
            $user->update([
                'foto' => $name_file
            ]);
        }
        return $response->withJson($user, 200);
    }
    public function updateInfo (Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $_SESSION['user_id'];
        /**
         * Validamos que el usuario exista
         */
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        /**
         * Validamos que el correo electrónico no esté en uso por otro usuario
         */
        $existingUser = Usuarios::where('email', $data['email'])->first();
        if ($existingUser && $existingUser->id != $id) {
            return $response->withJson(['error' => 'Email already in use'], 400);
        }
        $user->update([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'telefono' =>      $data['telefono'],
            'country_code' =>      $data['country_code'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['user_name'] = $data['nombres'];
        $_SESSION['user_last_name'] = $data['apellidos'];
        $_SESSION['user_email'] =  $data['email'];
        $_SESSION['user_phone'] =  $data['telefono'];
        $_SESSION['user_country_code'] =  $data['country_code'];
        return $response->withJson($data, 200);
    }
    public function updatePhotoInfo (Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $_SESSION['user_id'];
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        $storage = new ImageStorage('users');

        $name_file = $storage->storeImage($request, 'photo', $user->id . '.jpg');
        
        

        if ($name_file !=null) {
            // Actualizamos el nombre de la imagen en la base de datos
            $_SESSION['user_photo'] = $name_file;
            $user->update([
                'foto' => $name_file,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        return $response->withJson($user, 200);
    }
    public function updatePasswordInfo(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $_SESSION['user_id'];
        /**
         * Validamos que el usuario exista
         */
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }
        /**
         * Validamos si el la contraseña antigua es correcta
         */	
        if (!password_verify($data['old_password'], $user->password)) {
            return $response->withJson(['error' => 'Old password is incorrect'], 400);
        }
        $password = password_hash($data['new1_password'], PASSWORD_BCRYPT);

        $user->update([
            'password' => $password,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $response->withJson($user, 200);
    }
    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $user = Usuarios::find($id);
        if (!$user) {
            return $response->withJson(['error' => 'User not found 3'], 404);
        }
        $user->delete();
        return $response->withJson(['message' => 'User deleted successfully'], 200);
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
    public function index(Request $request, Response $response, $args) {
        $users = Usuarios::all();
        $page = $request->getParam('page') ?? 1;
        $perPage = 6;
        $users = Usuarios::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $users->makeHidden('password');
        $users = $this->getPhotos($users);
        return $response->withJson([
            'users' => $users
        ], 200);
    }
    public function getRoles(Request $request, Response $response, $args){
        $roles = Usuarios::getRoleOptions();
        return $response->withJson([
            'roles' => $roles
        ], 200);
    }
    public function getGestoresRepositorio (Request $request, Response $response, $args){
        $roles = ['Gestor de repositorio', 'Gestor de repositorio desarrollador'];
        $gestores = Usuarios::whereIn('rol', $roles)->get();
        $gestores->makeHidden('password');
        $gestores = $this->getPhotos($gestores);
        return $response->withJson([
            'gestores' => $gestores
        ], 200);
    }
}


