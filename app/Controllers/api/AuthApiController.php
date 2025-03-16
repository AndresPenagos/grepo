<?php
namespace App\Controllers\api;

use App\Models\Usuarios;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\EmailNotifications;
class AuthApiController
{
    public function loginUser(Request $request, Response $response)
    {
        $email = $request->getParam('email');
        $password = $request->getParam('password');
        $user = Usuarios::where('email', $email)->first();    
        if (!$user) {
            return $response->withJson(['error' => 'Email not found'], 404);
        }
        if ( !password_verify(($password),  $user->password) ) {
            return $response->withJson(['error' => 'Incorrect password'], 401);
        }
        //creamos las variables de sesssion
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->nombres;
        $_SESSION['user_last_name'] = $user->apellidos;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_photo'] = $user->foto;
        $_SESSION['user_phone'] = $user->telefono;
        $_SESSION['user_rol']   = $user->rol;
        $_SESSION['updated_at'] = $user->updated_at;
        //guardamos el ultimo acceso en last_acces
        $user->ultimo_acceso = date('Y-m-d H:i:s');
        $user->save();

        return $response->withJson(['success' => 'Login successful']);
    }
    public function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
    public function recoverPasswordUser(Request $request, Response $response){
        $email = $request->getParam('email');
        $user = Usuarios::where('email', $email)->first();
        if (!$user) {
            return $response->withJson(['error' => 'Email not found'], 404);
        }
        $newPassword = $this->generateRandomString();
        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->save();
        $notification = new EmailNotifications();
        $notification->RecoverPasswordByEmail(
            $user->nombres.' '.$user->apellidos,
            $user->email,
            $newPassword
        );
        return $response->withJson(['success' => 'Password recovered'. $newPassword],200);
    }
}
