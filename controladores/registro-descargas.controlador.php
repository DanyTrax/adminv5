<?php

require_once __DIR__ . "/../modelos/registro-descargas.modelo.php";

class ControladorRegistroDescargas {

    /*=============================================
    BORRAR TODOS LOS REGISTROS DE DESCARGAS
    =============================================*/
    static public function ctrBorrarTodosRegistrosDescargas() {
        if (isset($_POST["borrarTodosRegistrosDescargas"])) {
            header('Content-Type: application/json; charset=utf-8');
            if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"] != "admin") {
                echo json_encode(["success" => false, "title" => "Sin permisos", "error" => "Solo el usuario admin puede realizar esta acción"]);
                return;
            }

            $respuesta = ModeloRegistroDescargas::mdlBorrarTodosRegistrosDescargas();
            
            if ($respuesta == "ok") {
                echo json_encode([
                    "success" => true,
                    "title" => "Registros eliminados",
                    "message" => "Todos los registros de descargas han sido eliminados",
                    "redirect" => "registro-descargas-funcional"
                ]);
            } else {
                echo json_encode(["success" => false, "title" => "Error", "error" => "No se pudieron eliminar los registros: " . $respuesta]);
            }
        }
    }

    /*=============================================
    BORRAR UN REGISTRO DE DESCARGA (SOLO ADMIN)
    =============================================*/
    static public function ctrBorrarRegistroDescarga() {
        if (!isset($_POST["eliminarRegistroDescarga"]) || !is_numeric($_POST["eliminarRegistroDescarga"])) {
            return;
        }
        if (!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador") {
            echo json_encode(["success" => false, "message" => "Solo el perfil Administrador puede eliminar registros"]);
            return;
        }
        $id = (int) $_POST["eliminarRegistroDescarga"];
        $respuesta = ModeloRegistroDescargas::mdlBorrarRegistroDescarga($id);
        if ($respuesta == "ok") {
            echo json_encode(["success" => true, "message" => "Registro eliminado correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => $respuesta]);
        }
    }
}

?>
