<?php
declare(strict_types=1);
use App\Model\Admin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Jenssegers\Blade\Blade;
use  App\Middlewares\SessionMiddleware;

return function (App $app) {
    $blade = new Blade(__DIR__ . '/../../../../resourses/views', __DIR__ . '/../../../../resourses/compiled');
    $container = $app->getContainer();
    $app->group('/admin', function ()  use ($app,$container,$blade)  {
       
        $app->get('/home', function ($request, $response, $args) use($blade) {
            if ($_SESSION['user_rol']=='Gestor de repositorio' or $_SESSION['user_rol']=='Administrador' or $_SESSION['user_rol'] == 'Gestor de repositorio desarrollador')
            {
                echo $blade->render('pages.user.app-home',['path'=>"dashboard"]);
            }
            elseif ($_SESSION['user_rol']=='Gestor de semillas')
            {
                return $response->withRedirect($this->router->pathFor('semillas'));
            }
            elseif ($_SESSION['user_rol']=='Gestor de SofiaPlus')
            {
                return $response->withRedirect($this->router->pathFor('sofia'));
            }
           
        });
       
        $app->get('/dashboard', function ($request, $response, $args) use($blade) {
            if ($_SESSION['user_rol']=='Gestor de repositorio' or $_SESSION['user_rol']=='Administrador'  or $_SESSION['user_rol'] == 'Gestor de repositorio desarrollador')
            {
                echo $blade->render('pages.user.app-home',['path'=>"dashboard"]);
            }
            elseif ($_SESSION['user_rol']=='Gestor de semillas')
            {
                return $response->withRedirect($this->router->pathFor('semillas'));
            }
            elseif ($_SESSION['user_rol']=='Gestor de SofiaPlus')
            {
                return $response->withRedirect($this->router->pathFor('sofia'));
            }
        });
       
        $app->get('/sofia', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-sofia',['path'=>"sofia"]);
        })->setName('sofia')->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador', 'Gestor de SofiaPlus','Gestor de repositorio desarrollador' ],'login-client'));

        $app->get('/program', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-program',['path'=>"program"]);
        })->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de repositorio desarrollador'],'login-admin','admin'));

        $app->get('/users', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-user',['path'=>"users"]);
        })->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de repositorio desarrollador'],'login-admin','admin'));
        $app->get('/seeds', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-seeds',['path'=>"seeds"]);
        })->setName('semillas')->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de semillas','Gestor de repositorio desarrollador'],'login-admin','admin'));;
        $app->get('/solicitations', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-solicitations',['path'=>"solicitations"]);
        })->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de repositorio desarrollador'],'login-admin','admin'));
        $app->get('/assignments', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-assignments',['path'=>"assignments"]);
        })->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de repositorio desarrollador'],'login-admin','admin'));
        $app->get('/profile', function ($request, $response, $args) use($blade) {
            echo $blade->render('pages.user.app-profile',[
                'path'=>"admin",
                'name' => $_SESSION['user_name'],
                'last_name' =>  $_SESSION['user_last_name'],
                'email' =>  $_SESSION['user_email'],
                'phone' =>  $_SESSION['user_phone'],
            ]);
        })->add( new SessionMiddleware($container,['Gestor de repositorio','Administrador','Gestor de semillas','Gestor de SofiaPlus','Gestor de repositorio desarrollador'],'login-admin','admin'));
        $app->get('/logout', function ($request, $response, $args) use($blade) {
            if ($_SESSION['user_role']=='admin'){
                session_destroy();
                return $response->withRedirect($this->router->pathFor('login-admin'));
            } else {
                session_destroy();
                return $response->withRedirect($this->router->pathFor('login-client'));
            }
            
        });
    });

    $app->get('/user/login', function ($request, $response, $args) use($blade) {
        echo $blade->render('pages.app-login', [
            'LOGIN' => 'admin',
            'PATH_SESSION' => '../api/v1/login/user',
            'PATH_RECOVER' => '../api/v1/login/user/recover',
            'PATH_HOME' => '../admin/home'
        ]);
    })->setName('login-admin');
    $app->get('/', function ($request, $response, $args) use($blade) {
        echo $blade->render('pages.app-init', [
            'LOGIN' => 'client',
            'PATH_SESSION' => '../api/v1/login/client',
            'PATH_RECOVER' => '../api/v1/login/client/recover',
            'PATH_HOME' => '../client/home'
        ]);
    })->setName('login-client');
};