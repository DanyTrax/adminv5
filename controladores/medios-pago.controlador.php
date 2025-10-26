<?php

class ControladorMediosPago {

    // MOSTRAR
    static public function ctrMostrarMediosPago(){
        $tabla = "medios_pago";
        return ModeloMediosPago::mdlMostrarMediosPago($tabla);
    }

    // CREAR
    static public function ctrCrearMedioPago(){
        if(isset($_POST["nuevoMedioPago"])){
            $tabla = "medios_pago";
            $datos = $_POST["nuevoMedioPago"];
            $respuesta = ModeloMediosPago::mdlCrearMedioPago($tabla, $datos);
            if($respuesta == "ok"){
                echo '<script>
                    swal({ type: "success", title: "El medio de pago ha sido guardado", showConfirmButton: true, confirmButtonText: "Cerrar" }).then(function(result){ if (result.value) { window.location = "medios-pago"; } });
                </script>';
            }
        }
    }

    // EDITAR
    static public function ctrEditarMedioPago(){
        if(isset($_POST["editarMedioPago"])){
            $tabla = "medios_pago";
            $datos = array("id"=>$_POST["idMedioPago"], "nombre"=>$_POST["editarMedioPago"]);
            $respuesta = ModeloMediosPago::mdlEditarMedioPago($tabla, $datos);
            if($respuesta == "ok"){
                echo '<script>
                    swal({ type: "success", title: "El medio de pago ha sido cambiado", showConfirmButton: true, confirmButtonText: "Cerrar" }).then(function(result){ if (result.value) { window.location = "medios-pago"; } });
                </script>';
            }
        }
    }

    // BORRAR
    static public function ctrBorrarMedioPago(){
        if(isset($_GET["idMedioPago"])){
            $tabla = "medios_pago";
            $datos = $_GET["idMedioPago"];
            $respuesta = ModeloMediosPago::mdlBorrarMedioPago($tabla, $datos);
            if($respuesta == "ok"){
                echo '<script>
                    swal({ type: "success", title: "El medio de pago ha sido borrado", showConfirmButton: true, confirmButtonText: "Cerrar" }).then(function(result){ if (result.value) { window.location = "medios-pago"; } });
                </script>';
            }
        }
    }
}