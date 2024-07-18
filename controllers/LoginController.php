<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController
{
    public static function login(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                //verificar si existe el usuario
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado) {
                    $alertas = Usuario::setAlerta('error', 'Usuario no encontrado o no esta confirmado');
                } else {
                    //Usuario existe
                    if(password_verify($_POST['password'], $usuario->password)) {
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        header('location: /dashboard');
                    } else {
                        $alertas = Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }
            }

        }

        //Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout()
    {
        session_start();
        $_SESSION = [];
        header('location: /');
    }

    public static function crear(Router $router)
    {
        $usuario = new Usuario;
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);

            $existeUsuario = Usuario::where('email', $usuario->email);
            if($existeUsuario) {
                $alertas = Usuario::setAlerta('error', 'El usuario ya existe');
            } else {
                $alertas = $usuario->validarCuentaNueva();
            }

            //Si no hay alertas (Proceso de guardado)
            if(empty($alertas)) {
                //Hashear password
                $usuario->hashPassword();

                //Elimina password 2
                unset($usuario->password2);

                //Genera un token
                $usuario->crearToken();
                
                //Guardar Usuario
                $resultado = $usuario->guardar();

                //Enviar email
                $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                $email->enviarConfirmacion();

                if($resultado) {
                    header('location: /mensaje');
                }

            }

        }

        //Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router)
    {
        $alertas = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado) {
                    //Generar nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    
                    //Actualizar el usuario
                    $usuario->guardar();


                    //Enviar email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarReestablecimiento();

                    //Alerta
                    $alertas = Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                    
                } else {
                    $alertas = Usuario::setAlerta('error', 'Correo no encontrado o Correo no confirmado');
                }
            }

        }

        $router->render('auth/olvide', [
            'titulo' => 'Enviar Intrucciones',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router)
    {
        $alertas = [];
        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('location: /');

        $usuario = Usuario::where('token', $token);
        unset($usuario->password2);

        if(!$usuario) {
            $alertas = Usuario::setAlerta('error', 'El token no es Valido');
            $mostrar = false;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //Add nuevo pass
            $usuario->sincronizar($_POST);

            //validar pass
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                $usuario->hashPassword();
                $usuario->token = '';
                $resultado = $usuario->guardar();

                if($resultado) {
                    header('location: /');
                }
            }

        }

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Pass',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router)
    {
        $router->render('auth/mensaje');
    }

    public static function confirmar(Router $router)
    {
        $alertas = [];
        $token = s($_GET['token']);

        
        if(!$token) {
            header('location: /');
        }

        //Encontrar al usuario
        $usuario = Usuario::where('token', $token);

        if(!$usuario) {
            //Token no valido
            $alertas = Usuario::setAlerta('error', 'El token no es valido');
        } else {
            //Confirmar cuenta
            unset($usuario->password2);
            $usuario->token = '';
            $usuario->confirmado = 1;

            //Guardar enn DB
            $usuario->guardar();

            //exito
            $alertas = Usuario::setAlerta('exito', 'Usuario confirmado correctamente');
        }

        $router->render('auth/confirmar', [
            'titulo' => 'Cuenta Confirmada',
            'alertas' => $alertas
        ]);
    }
}
