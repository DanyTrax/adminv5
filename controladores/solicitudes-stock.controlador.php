<?php

class ControladorSolicitudesStock {

    /*=============================================
    CREAR SOLICITUD DE STOCK
    =============================================*/
    static public function ctrCrearSolicitud(){

        if(isset($_POST["productos_solicitados"])){

            // ✅ DEBUG: Verificar datos recibidos
            error_log("=== DEBUG CREAR SOLICITUD ===");
            error_log("Productos recibidos: " . $_POST["productos_solicitados"]);
            error_log("Tipo solicitud: " . (isset($_POST["tipo_solicitud"]) ? $_POST["tipo_solicitud"] : 'NO_DEFINIDO'));
            
            // ✅ OBTENER DATOS DE LA SUCURSAL DESDE BD LOCAL
            $datosSucursal = self::obtenerDatosSucursalLocal();
            
            if(!$datosSucursal) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudieron obtener los datos de la sucursal",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            error_log("Datos de sucursal obtenidos: " . json_encode($datosSucursal));
            
            // ✅ VALIDAR QUE HAYA PRODUCTOS
            $productos = json_decode($_POST["productos_solicitados"], true);
            
            if(empty($productos) || !is_array($productos)) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No hay productos para solicitar",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // ✅ VALIDAR TIPO DE SOLICITUD
            if(!isset($_POST["tipo_solicitud"]) || empty($_POST["tipo_solicitud"])) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Debe seleccionar un tipo de solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // ✅ GENERAR NÚMERO DE SOLICITUD
            $numeroSolicitud = ModeloSolicitudesStock::mdlGenerarNumeroSolicitud("solicitudes_stock");
            
            error_log("Número de solicitud generado: " . $numeroSolicitud);
            
            if(empty($numeroSolicitud)) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al generar número de solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }

            // ✅ PREPARAR DATOS CON INFORMACIÓN DE SUCURSAL DESDE BD LOCAL
            $datos = array(
                "numero_solicitud" => $numeroSolicitud,
                "codigo_sucursal_solicitante" => $datosSucursal["codigo_sucursal"],
                "nombre_sucursal_solicitante" => $datosSucursal["nombre"],
                "usuario_solicitante" => $_SESSION["id"],
                "nombre_usuario_solicitante" => $_SESSION["nombre"],
                "productos_solicitados" => $_POST["productos_solicitados"],
                "tipo_solicitud" => $_POST["tipo_solicitud"],
                "codigo_remision" => isset($_POST["codigo_remision"]) ? $_POST["codigo_remision"] : null,
                "nombre_cliente_remision" => isset($_POST["nombre_cliente_remision"]) ? $_POST["nombre_cliente_remision"] : null,
                "detalle_adicional" => isset($_POST["detalle_adicional"]) ? $_POST["detalle_adicional"] : null,
                "total_productos" => count($productos),
                "total_cantidad" => array_sum(array_column($productos, 'cantidad'))
            );

            // ✅ DEBUG: Verificar datos preparados
            error_log("Datos preparados: " . json_encode($datos));

            // ✅ INTENTAR CREAR SOLICITUD
            $respuesta = ModeloSolicitudesStock::mdlCrearSolicitud("solicitudes_stock", $datos);

            // ✅ DEBUG: Verificar respuesta del modelo
            error_log("Respuesta del modelo: " . $respuesta);

            if($respuesta == "ok"){

                echo '<script>
                    swal({
                        type: "success",
                        title: "¡Solicitud creada!",
                        text: "La solicitud ' . $numeroSolicitud . ' se ha creado correctamente",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(function(result){
                        window.location = "solicitudes-stock";
                    });
                </script>';

            } else {

                // ✅ DEBUG: Error en la creación
                error_log("ERROR AL CREAR SOLICITUD: " . $respuesta);

                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Error al crear la solicitud. Revise los logs del servidor.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';

            }
        }
    }

    /*=============================================
    OBTENER DATOS DE LA SUCURSAL DESDE BD LOCAL
    =============================================*/
    static public function obtenerDatosSucursalLocal() {
        
        try {
            // ✅ USAR CONEXIÓN LOCAL PARA OBTENER DATOS DE SUCURSAL
            $stmt = Conexion::conectar()->prepare("SELECT codigo_sucursal, nombre FROM sucursal_local LIMIT 1");
            $stmt->execute();
            
            $sucursal = $stmt->fetch();
            
            if($sucursal) {
                return array(
                    "codigo_sucursal" => $sucursal["codigo_sucursal"],
                    "nombre" => $sucursal["nombre"]
                );
            } else {
                error_log("ERROR: No se encontraron datos en la tabla sucursal_local");
                return false;
            }
            
        } catch(Exception $e) {
            error_log("ERROR obteniendo datos de sucursal: " . $e->getMessage());
            return false;
        }
    }

    /*=============================================
    MOSTRAR SOLICITUDES - DESDE BASE CENTRAL
    =============================================*/
    static public function ctrMostrarSolicitudes($item, $valor) {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlMostrarSolicitudes($tabla, $item, $valor);
        
        return $respuesta;
    }

    /*=============================================
    MOSTRAR SOLICITUDES COMPLETAS - DESDE BASE CENTRAL
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
            
            // ✅ OBTENER USUARIO DE BASE LOCAL (NO CENTRAL)
            $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $_SESSION["id"]);
            
            if(!$usuario) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo obtener información del usuario",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
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
            
            // ✅ OBTENER USUARIO DE BASE LOCAL (NO CENTRAL)
            $usuario = ControladorUsuarios::ctrMostrarUsuarios("id", $_SESSION["id"]);
            
            if(!$usuario) {
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "No se pudo obtener información del usuario",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
            
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
    BUSCAR VENTAS PARA REMISIÓN - BASE LOCAL
    =============================================*/
    static public function ctrBuscarVentasRemision($busqueda) {
        
        $respuesta = ModeloSolicitudesStock::mdlBuscarVentasRemision($busqueda);
        return $respuesta;
    }

    /*=============================================
    OBTENER PRODUCTOS DE UNA VENTA - BASE LOCAL
    =============================================*/
    static public function ctrObtenerProductosVenta($codigoVenta) {
        
        $respuesta = ModeloSolicitudesStock::mdlObtenerProductosVenta($codigoVenta);
        return $respuesta;
    }

    /*=============================================
    CONTAR SOLICITUDES PENDIENTES PARA NOTIFICACIONES - BASE CENTRAL
    =============================================*/
    static public function ctrContarSolicitudesPendientes() {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlContarSolicitudesPendientes($tabla);
        
        return $respuesta;
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA NOTIFICACIONES - BASE CENTRAL
    =============================================*/
    static public function ctrObtenerSolicitudesPendientes($limite = 5) {
        
        $tabla = "solicitudes_stock";
        $respuesta = ModeloSolicitudesStock::mdlObtenerSolicitudesPendientes($tabla, $limite);
        
        return $respuesta;
    }

    /*=============================================
    MARCAR SOLICITUDES COMO VISTAS - BASE CENTRAL
    =============================================*/
    static public function ctrMarcarSolicitudesComoVistas($ids) {
        
        $tabla = "solicitudes_stock";
        $campo = "visto_por_transportador";
        
        if($_SESSION["perfil"] == "Administrador") {
            $campo = "visto_por_administrador";
        }
        
        $respuesta = ModeloSolicitudesStock::mdlMarcarComoVista($tabla, $campo, $ids);
        
        return $respuesta;
    }

    /*=============================================
    OBTENER ESTADÍSTICAS - BASE CENTRAL
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