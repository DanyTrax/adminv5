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
}

?>
