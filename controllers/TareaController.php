<?php 
namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController {

    public static function index() {
        session_start();

        $proyectoId = $_GET['url'];

        if(!$proyectoId) header('location: /dashboard');

        $proyecto = Proyecto::where('url', $proyectoId);

        if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
            header('location: /404');
        }

        $tareas = Tarea::where('proyectoId', $proyecto->id, false);
        
        echo json_encode(['tareas' => $tareas]);
    } 

    public static function crear() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();

            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al agregar la tarea'
                ];
                echo json_encode($respuesta);
                return;
            } 


            //Todo correcto
            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;
            $resultado = $tarea->guardar();
            $respuesta = [
                'tipo' => 'exito',
                'id' => $resultado['id'],
                'mensaje' => 'Tarea creada correctamente',
                'proyectoId' => $proyecto->id
            ];
            echo json_encode($respuesta);


        }
    } 

    public static function actualizar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            //validar proyecto exista

            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al actualizar la tarea'
                ];
                echo json_encode(['respuesta' => $respuesta]);
                return;
            }

            $tarea = new Tarea($_POST);
            $tarea->proyectoId = $proyecto->id;

            $resultado = $tarea->guardar();

            if($resultado) {
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $tarea->id,
                    'proyectoId' => $proyecto->id,
                    'mensaje' => 'Actualizado Correctamente'
                ];
                echo json_encode(['respuesta' => $respuesta]);
                return;
            }

        }
    } 

    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            //validar proyecto exista

            $proyecto = Proyecto::where('url', $_POST['proyectoId']);

            if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error al actualizar la tarea'
                ];
                echo json_encode(['respuesta' => $respuesta]);
                return;
            }

            $tarea = new Tarea($_POST);
            $resultado = $tarea->eliminar();

            $resultado = [
                'resultado' => $resultado,
                'tipo' => 'exito'
            ];

            echo json_encode($resultado);
        }
    } 
}