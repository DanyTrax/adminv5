<?php

class ControladorDespachos {

    /*=============================================
    CREAR DESPACHO
    =============================================*/
    static public function ctrCrearDespacho() {

        if(isset($_POST["crearDespacho"])) {

            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["detalleAdicional"]) &&
               !empty($_POST["productosDespacho"])) {

                // Verificar stock local antes de crear
                $productos = json_decode($_POST["productosDespacho"], true);
                $stockSuficiente = true;
                
                foreach($productos as $producto) {
                    if(!ModeloDespachos::mdlVerificarStockLocal($producto["codigo"], $producto["cantidad"])) {
                        $stockSuficiente = false;
                        break;
                    }
                }

                if(!$stockSuficiente) {
                    echo '<div class="alert alert-danger">Error: Stock insuficiente para algunos productos</div>';
                    return;
                }

                // Obtener información de la sucursal local
                require_once "controladores/sucursales.controlador.php";
                $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();

                $tabla = "despachos";
                $datos = array(
                    "numero_despacho" => ModeloDespachos::mdlGenerarNumeroDespacho(),
                    "id_solicitud_origen" => !empty($_POST["idSolicitudOrigen"]) ? $_POST["idSolicitudOrigen"] : null,
                    "nombre_sucursal_origen" => $sucursalLocal["nombre"],
                    "id_usuario_creador" => $_SESSION["id"],
                    "nombre_usuario_creador" => $_SESSION["nombre"],
                    "productos_despacho" => $_POST["productosDespacho"],
                    "total_productos" => $_POST["totalProductos"],
                    "total_cantidad" => $_POST["totalCantidad"],
                    "detalle_adicional" => $_POST["detalleAdicional"]
                );

                $respuesta = ModeloDespachos::mdlCrearDespacho($tabla, $datos);

                if($respuesta != "error") {
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Despacho creado correctamente!",
                            text: "Número: ' . $datos["numero_despacho"] . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result) {
                            if (result.value) {
                                window.location = "despachos";
                            }
                        });
                    </script>';
                } else {
                    echo '<div class="alert alert-danger">Error al crear el despacho: ' . $respuesta . '</div>';
                }

            } else {
                echo '<div class="alert alert-danger">Error: Complete todos los campos correctamente</div>';
            }
        }
    }

    /*=============================================
    MOSTRAR DESPACHOS
    =============================================*/
    static public function ctrMostrarDespachos($item, $valor) {

        $tabla = "despachos";
        $respuesta = ModeloDespachos::mdlMostrarDespachos($tabla, $item, $valor);
        return $respuesta;
    }

    /*=============================================
    ACEPTAR DESPACHO (SOLO TRANSPORTADORES)
    =============================================*/
    static public function ctrAceptarDespacho() {

        if(isset($_POST["aceptarDespacho"]) && $_SESSION["perfil"] == "Transportador") {

            $idDespacho = $_POST["idDespacho"];
            
            // Obtener despacho
            $despacho = self::ctrMostrarDespachos("id", $idDespacho);
            
            if($despacho) {
                // 1. Actualizar estado del despacho
                $datos = array(
                    "id" => $idDespacho,
                    "estado" => "aceptado",
                    "id_transportador" => $_SESSION["id"],
                    "nombre_transportador" => $_SESSION["nombre"],
                    "fecha_aceptacion" => date('Y-m-d H:i:s')
                );

                $actualizado = ModeloDespachos::mdlActualizarEstadoDespacho("despachos", $datos);

                if($actualizado) {
                    // 2. Descontar stock local
                    $productos = json_decode($despacho["productos_despacho"], true);
                    $stockDescontado = ModeloDespachos::mdlDescontarStockLocal($productos);

                    if($stockDescontado) {
                        // 3. Agregar a stock en tránsito
                        require_once "modelos/stock-transito.modelo.php";
                        $agregarTransito = ModeloStockTransito::mdlAgregarStockTransito($productos, $despacho, $_SESSION);

                        if($agregarTransito) {
                            echo '<script>
                                swal({
                                    type: "success",
                                    title: "¡Despacho aceptado!",
                                    text: "Los productos han sido cargados a su stock en tránsito",
                                    showConfirmButton: true,
                                    confirmButtonText: "Cerrar"
                                }).then(function(result) {
                                    if (result.value) {
                                        window.location = "despachos";
                                    }
                                });
                            </script>';
                        } else {
                            echo '<div class="alert alert-danger">Error al agregar productos al stock en tránsito</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">Error al descontar stock local</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Error al actualizar el estado del despacho</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Despacho no encontrado</div>';
            }
        }
    }

    /*=============================================
    CONTAR DESPACHOS PENDIENTES PARA TRANSPORTADORES
    =============================================*/
    static public function ctrContarDespachosPendientes() {
        
        try {
            require_once "api-transferencias/conexion-central.php";
            
            $stmt = ConexionCentral::conectar()->prepare("
                SELECT COUNT(*) as total 
                FROM despachos 
                WHERE estado = 'pendiente'
            ");
            
            $stmt->execute();
            $resultado = $stmt->fetch();
            
            return $resultado['total'] ?? 0;
            
        } catch(Exception $e) {
            return 0;
        }
    }
    /*=============================================
BORRAR DESPACHO
=============================================*/
static public function mdlBorrarDespacho($tabla, $id) {
    
    try {
        require_once "api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

    } catch(Exception $e) {
        return "error";
    }
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
static public function mdlCancelarDespacho($tabla, $datos) {
    
    try {
        require_once "api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            UPDATE $tabla 
            SET estado = :estado, 
                motivo_cancelacion = :motivo_cancelacion 
            WHERE id = :id
        ");

        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        $stmt->bindParam(":motivo_cancelacion", $datos["motivo_cancelacion"], PDO::PARAM_STR);
        $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);

        if($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }

    } catch(Exception $e) {
        return "error";
    }
}

/*=============================================
OBTENER TRANSPORTADORES ACTIVOS
=============================================*/
static public function mdlObtenerTransport
}