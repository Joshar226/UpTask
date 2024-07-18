<?php 
namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {


    public static function index(Router $router){
        session_start();
        isAuth();

        $proyectos = Proyecto::where('propietarioId', $_SESSION['id'], false);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router){
        session_start();
        $alertas = [];
        isAuth();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            //Validacion 
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)) {
                //Genrar url
                $proyecto->url = md5(uniqid());

                //Almacenar creador
                $proyecto->propietarioId = $_SESSION['id'];

                //Guardar Proyecto
                $proyecto->guardar();

                header('location: /proyecto?url=' . $proyecto->url);
                
            }


        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        $url = $_GET['url'];

        if(!$url) header('location: /dashboard');

        $proyecto = Proyecto::where('url', $url);

        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('location: /dashboard');
        }

        //Revisar si es propietario del proyecto


        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router){
        session_start();
        $alertas = [];
        isAuth();

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validarPerfil();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    //Usuario ya existe
                    $alertas = Usuario::setAlerta('error', 'Usuario ya existente');

                } else {
                    $usuario->guardar();

                    $alertas = Usuario::setAlerta('exito', 'Perfil actualizado correctamente');
    
                    $_SESSION['nombre'] = $usuario->nombre;
                    $_SESSION['email'] = $usuario->email;
                }
            }
        }



        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'alertas' => $alertas,
            'usuario' => $usuario
        ]);
    }

    public static function cambiar_password(Router $router) {
        session_start();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevoPassword();

            if(empty($alertas)) {
                $usuario->sincronizarPassword();

                $usuario->hashPassword();

                $resultado = $usuario->guardar();
                
                if($resultado) {
                    $alertas = $usuario::setAlerta('exito', 'Password actualizado con exito');
                }

            }
        }

        $router->render('dashboard/cambiar_password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
}