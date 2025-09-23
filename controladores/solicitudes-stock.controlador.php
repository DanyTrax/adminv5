<?php

class ControladorSolicitudesStock {

    /*=============================================
    CREAR SOLICITUD DE STOCK
    =============================================*/
    static public function ctrCrearSolicitud() {
        
        if(isset($_POST["productos_solicitados"])) {
            
            if(preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ\s\[\]{}",.:;-]+$/', $_POST["detalle_adicional"]) ||
               $_POST["detalle_adicional"] == "") {
                
                // Obtener información de la sucursal local
                $sucursalLocal = ControladorSucursales::ctrObtenerConfiguracionLocal();
                
                if(!$sucursalLocal) {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error de configuración",
                            text: "No se pudo obtener la información de la sucursal local",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "solicitudes-stock";
                            }
                        });
                    </script>';
                    return;
                }
                
                // Obtener información del usuario
                $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $_SESSION["id"]);
                
                // Generar número de solicitud
                $tabla = "solicitudes_stock";
                $numeroSolicitud = ModeloSolicitudesStock::mdlGenerarNumeroSolicitud($tabla);
                
                // Procesar productos solicitados
                $productosJson = $_POST["productos_solicitados"];
                $productos = json_decode($productosJson, true);
                
                $totalProductos = count($productos);
                $totalCantidad = 0;
                
                foreach($productos as $producto) {
                    $totalCantidad += intval($producto["cantidad"]);
                }
                
                // Preparar datos para inserción
                $datos = array(
                    "numero_solicitud" => $numeroSolicitud,
                    "codigo_sucursal_solicitante" => $sucursalLocal["codigo_sucursal"],
                    "nombre_sucursal_solicitante" => $sucursalLocal["nombre"],
                    "usuario_solicitante" => $_SESSION["id"],
                    "nombre_usuario_solicitante" => $usuario["nombre"],
                    "productos_solicitados" => $productosJson,
                    "tipo_solicitud" => $_POST["tipo_solicitud"],
                    "codigo_remision" => $_POST["codigo_remision"] ?? null,
                    "nombre_cliente_remision" => $_POST["nombre_cliente_remision"] ?? null,
                    "detalle_adicional" => $_POST["detalle_adicional"],
                    "total_productos" => $totalProductos,
                    "total_cantidad" => $totalCantidad
                );
                
                $respuesta = ModeloSolicitudesStock::mdlCrearSolicitud($tabla, $datos);
                
                if($respuesta == "ok") {
                    
                    // Registrar en el log
                    $datosLog = array(
                        "solicitud_id" => null, // Se obtiene del último insert
                        "usuario_id" => $_SESSION["id"],
                        "accion" => "creada",
                        "estado_anterior" => null,
                        "estado_nuevo" => "pendiente",
                        "comentario" => "Solicitud creada desde sucursal: " . $sucursalLocal["nombre"],
                        "ip_usuario" => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    );
                    
                    echo '<script>
                        swal({
                            type: "success",
                            title: "¡Solicitud creada correctamente!",
                            text: "Número de solicitud: ' . $numeroSolicitud . '",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "solicitudes-stock";
                            }
                        });
                    </script>';
                    
                } else {
                    echo '<script>
                        swal({
                            type: "error",
                            title: "Error al crear la solicitud",
                            text: "Intente nuevamente o contacte al administrador",
                            showConfirmButton: true,
                            confirmButtonText: "Cerrar"
                        }).then(function(result){
                            if(result.value){
                                window.location = "solicitudes-stock";
                            }
                        });
                    </script>';
                }
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "¡Error en los datos!",
                        text: "El detalle no puede contener caracteres especiales",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "solicitudes-stock";
                        }
                    });
                </script>';
            }
        }
    }

    /*=============================================
    MOSTRAR SOLICITUDES
    =============================================*/
    static public function ctrMostrarSolicitudes($item, $valor) {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlMostrarSolicitudes($tabla, $item, $valor);
        
        return $respuesta;
    }

    /*=============================================
    MOSTRAR SOLICITUDES COMPLETAS CON INFORMACIÓN DE USUARIOS
    =============================================*/
    static public function ctrMostrarSolicitudesCompletas() {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlMostrarSolicitudesCompletas($tabla);
        
        return $respuesta;
    }

    /*=============================================
    APROBAR SOLICITUD
    =============================================*/
    static public function ctrAprobarSolicitud() {
        
        if(isset($_POST["idSolicitudAprobar"])) {
            
            // Verificar permisos (solo transportador y administrador)
            if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "No tiene permisos para aprobar solicitudes",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
            $tabla = "solicitudes_stock";
            $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $_SESSION["id"]);
            
            $datos = array(
                "id" => $_POST["idSolicitudAprobar"],
                "estado" => "aprobado",
                "usuario_aprobacion" => $_SESSION["id"],
                "nombre_usuario_aprobacion" => $usuario["nombre"],
                "observaciones_aprobacion" => $_POST["observaciones_aprobacion"] ?? null,
                "motivo_cancelacion" => null
            );
            
            $respuesta = ModeloSolicitudesStock::mdlActualizarEstadoSolicitud($tabla, $datos);
            
            if($respuesta == "ok") {
                
                // Registrar en el log
                $datosLog = array(
                    "solicitud_id" => $_POST["idSolicitudAprobar"],
                    "usuario_id" => $_SESSION["id"],
                    "accion" => "aprobada",
                    "estado_anterior" => "pendiente",
                    "estado_nuevo" => "aprobado",
                    "comentario" => "Solicitud aprobada por " . $_SESSION["perfil"] . ": " . $usuario["nombre"],
                    "ip_usuario" => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                );
                
                ModeloSolicitudesStock::mdlRegistrarLog($datosLog);
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Solicitud aprobada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "solicitudes-stock";
                        }
                    });
                </script>';
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al aprobar la solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    CANCELAR SOLICITUD
    =============================================*/
    static public function ctrCancelarSolicitud() {
        
        if(isset($_POST["idSolicitudCancelar"])) {
            
            // Verificar permisos (solo transportador y administrador)
            if($_SESSION["perfil"] != "Transportador" && $_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "No tiene permisos para cancelar solicitudes",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
            $tabla = "solicitudes_stock";
            $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $_SESSION["id"]);
            
            $datos = array(
                "id" => $_POST["idSolicitudCancelar"],
                "estado" => "cancelado",
                "usuario_aprobacion" => $_SESSION["id"],
                "nombre_usuario_aprobacion" => $usuario["nombre"],
                "observaciones_aprobacion" => null,
                "motivo_cancelacion" => $_POST["motivo_cancelacion"] ?? "Cancelada por " . $_SESSION["perfil"]
            );
            
            $respuesta = ModeloSolicitudesStock::mdlActualizarEstadoSolicitud($tabla, $datos);
            
            if($respuesta == "ok") {
                
                // Registrar en el log
                $datosLog = array(
                    "solicitud_id" => $_POST["idSolicitudCancelar"],
                    "usuario_id" => $_SESSION["id"],
                    "accion" => "cancelada",
                    "estado_anterior" => "pendiente",
                    "estado_nuevo" => "cancelado",
                    "comentario" => "Solicitud cancelada por " . $_SESSION["perfil"] . ": " . $usuario["nombre"],
                    "ip_usuario" => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                );
                
                ModeloSolicitudesStock::mdlRegistrarLog($datosLog);
                
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Solicitud cancelada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "solicitudes-stock";
                        }
                    });
                </script>';
                
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al cancelar la solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    ELIMINAR SOLICITUD (Solo Administrador)
    =============================================*/
    static public function ctrEliminarSolicitud() {
        
        if(isset($_GET["idSolicitud"])) {
            
            // Verificar permisos (solo administrador)
            if($_SESSION["perfil"] != "Administrador") {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Sin permisos",
                        text: "Solo los administradores pueden eliminar solicitudes",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "solicitudes-stock";
                        }
                    });
                </script>';
                return;
            }
            
            $tabla = "solicitudes_stock";
            $datos = $_GET["idSolicitud"];
            
            // Registrar en el log antes de eliminar
            $datosLog = array(
                "solicitud_id" => $datos,
                "usuario_id" => $_SESSION["id"],
                "accion" => "eliminada",
                "estado_anterior" => "unknown",
                "estado_nuevo" => null,
                "comentario" => "Solicitud eliminada por administrador: " . $_SESSION["nombre"],
                "ip_usuario" => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            );
            
            ModeloSolicitudesStock::mdlRegistrarLog($datosLog);
            
            $respuesta = ModeloSolicitudesStock::mdlEliminarSolicitud($tabla, $datos);
            
            if($respuesta == "ok") {
                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Solicitud eliminada correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then(function(result){
                        if(result.value){
                            window.location = "solicitudes-stock";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error al eliminar la solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

    /*=============================================
    BUSCAR VENTAS PARA REMISIÓN
    =============================================*/
    static public function ctrBuscarVentasRemision($busqueda) {
        
        $respuesta = ModeloSolicitudesStock::mdlBuscarVentasRemision($busqueda);
        return $respuesta;
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA
    =============================================*/
    static public function ctrObtenerProductosVenta($codigoVenta) {
        
        $respuesta = ModeloSolicitudesStock::mdlObtenerProductosVenta($codigoVenta);
        return $respuesta;
    }

    /*=============================================
    CONTAR SOLICITUDES PENDIENTES PARA NOTIFICACIONES
    =============================================*/
    static public function ctrContarSolicitudesPendientes() {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlContarSolicitudesPendientes($tabla);
        
        return $respuesta;
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA NOTIFICACIONES
    =============================================*/
    static public function ctrObtenerSolicitudesPendientes($limite = 5) {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlObtenerSolicitudesPendientes($tabla, $limite);
        
        return $respuesta;
    }

    /*=============================================
    MARCAR SOLICITUDES COMO VISTAS
    =============================================*/
    static public function ctrMarcarSolicitudesComoVistas($ids) {
        
        $tabla = "solicitudes_stock";
        $campo = "visto_por_" . strtolower($_SESSION["perfil"]);
        
        if($_SESSION["perfil"] == "Transportador") {
            $campo = "visto_por_transportador";
        } elseif($_SESSION["perfil"] == "Administrador") {
            $campo = "visto_por_administrador";
        }
        
        $respuesta = ModeloSolicitudesStock::mdlMarcarComoVista($tabla, $campo, $ids);
        
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS
    =============================================*/
    static public function ctrObtenerEstadisticas() {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlObtenerEstadisticas($tabla);
        
        return $respuesta;
    }

    /*=============================================
    VERIFICAR PERMISOS
    =============================================*/
    static public function ctrVerificarPermisos($accion) {
        
        switch($accion) {
            case 'crear':
                return ($_SESSION["perfil"] != "Transportador");
                break;
                
            case 'aprobar':
            case 'cancelar':
                return ($_SESSION["perfil"] == "Transportador" || $_SESSION["perfil"] == "Administrador");
                break;
                
            case 'eliminar':
                return ($_SESSION["perfil"] == "Administrador");
                break;
                
            case 'ver':
                return true; // Todos pueden ver
                break;
                
            default:
                return false;
        }
    }

    /*=============================================
    VALIDAR ACCESO A CREAR SOLICITUDES
    =============================================*/
    static public function ctrValidarAccesoCrearSolicitud() {
        
        if($_SESSION["perfil"] == "Transportador") {
            echo '<script>
                swal({
                    type: "warning",
                    title: "Acceso restringido",
                    text: "Los transportadores no pueden crear solicitudes, solo aprobarlas o cancelarlas",
                    showConfirmButton: true,
                    confirmButtonText: "Entendido"
                }).then(function(result){
                    if(result.value){
                        window.location = "solicitudes-stock";
                    }
                });
            </script>';
            return false;
        }
        
        return true;
    }
}