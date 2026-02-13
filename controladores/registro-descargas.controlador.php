<?php

require_once __DIR__ . "/../modelos/registro-descargas.modelo.php";

class ControladorRegistroDescargas {

    /*=============================================
    BORRAR TODOS LOS REGISTROS DE DESCARGAS
    =============================================*/
    static public function ctrBorrarTodosRegistrosDescargas() {
        if (isset($_POST["borrarTodosRegistrosDescargas"])) {
            // Verificar que el usuario sea "admin"
            if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"] != "admin") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "Solo el usuario admin puede realizar esta acción",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            $respuesta = ModeloRegistroDescargas::mdlBorrarTodosRegistrosDescargas();
            
            if ($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "Registros eliminados",
                        text: "Todos los registros de descargas han sido eliminados",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if (result.value) {
                            window.location = "registro-descargas-funcional";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudieron eliminar los registros: ' . $respuesta . '",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
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
