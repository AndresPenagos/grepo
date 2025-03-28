<?php 
namespace App\Middlewares;
use Slim\Routing\RouteContext;
class SessionMiddleware
{
    private $roles=[];
    protected $container;
    private $pathlogin;
    private $user_role;
    public function __construct($container,$roles, $pathlogin)
    {
        $this->container = $container;
        $this->roles=$roles;
        $this->pathlogin=$pathlogin;
    }
    public function __invoke($request, $response, $next)
    {
        $router = $this->container->get('router');
        // Verificar si la sesión está activa
        if (!isset($_SESSION['user_id']) ) {
            // Redirigir al usuario a la página de inicio de sesión
            return $response->withRedirect($router->pathFor($this->pathlogin));
        }       
        //recorremos el array de roles para saber tiene permiso
        foreach ($this->roles as $role) {
            print_r($role);
            print_r($_SESSION['user_rol']);
            if($_SESSION['user_rol']==$role){
                $response = $next($request, $response);
                return $response;
            }
        }
        return $response->withRedirect($router->pathFor($this->pathlogin));
        return $response;
    }
}