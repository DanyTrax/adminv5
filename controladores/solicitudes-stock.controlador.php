<?php

class ControladorSolicitudesStock {

/*=============================================
CREAR SOLICITUD DE STOCK - SOLO AL RECIBIR POST
=============================================*/
static public function ctrCrearSolicitud(){

    // ✅ SOLO EJECUTAR SI SE RECIBIÓ UN POST CON productos_solicitados
    if(isset($_POST["productos_solicitados"]) && $_SERVER['REQUEST_METHOD'] === 'POST'){

        // ✅ LOG COMPLETO DE TODOS LOS DATOS RECIBIDOS
        error_log("=== CONTROLADOR DEBUG - CREANDO SOLICITUD ===");
        error_log("POST: " . json_encode($_POST));
        error_log("SESSION ID: " . (isset($_SESSION["id"]) ? $_SESSION["id"] : 'NO_SESSION'));
        
        // ✅ DEBUG ESPECÍFICO PARA REMISIÓN
        error_log("Tipo de solicitud recibido: " . (isset($_POST["tipo_solicitud"]) ? $_POST["tipo_solicitud"] : 'NO_DEFINIDO'));
        
        if(isset($_POST["tipo_solicitud"]) && $_POST["tipo_solicitud"] == "remision") {
            error_log("📋 SOLICITUD POR REMISIÓN DETECTADA");
            error_log("Código remisión: " . (isset($_POST["codigo_remision"]) ? $_POST["codigo_remision"] : 'NO_ENVIADO'));
            error_log("Nombre cliente: " . (isset($_POST["nombre_cliente_remision"]) ? $_POST["nombre_cliente_remision"] : 'NO_ENVIADO'));
            
            // ✅ VALIDAR QUE HAYA CÓDIGO DE REMISIÓN
            if(!isset($_POST["codigo_remision"]) || empty(trim($_POST["codigo_remision"]))) {
                error_log("❌ ERROR: Falta código de remisión para solicitud por remisión");
                echo '<script>
                    swal({
                        type: "error",
                        title: "Error",
                        text: "Debe seleccionar una remisión para este tipo de solicitud",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
                return;
            }
        }
        
        // ✅ OBTENER DATOS DE LA SUCURSAL DESDE BD LOCAL
        $datosSucursal = self::obtenerDatosSucursalLocal();
        
        if(!$datosSucursal) {
            error_log("❌ ERROR: No se pudieron obtener datos de sucursal");
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
            error_log("❌ ERROR: productos_solicitados está vacío o inválido");
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
            error_log("❌ ERROR: tipo_solicitud no definido");
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
            error_log("❌ ERROR: No se pudo generar número de solicitud");
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
            "codigo_remision" => isset($_POST["codigo_remision"]) && !empty(trim($_POST["codigo_remision"])) ? trim($_POST["codigo_remision"]) : null,
            "nombre_cliente_remision" => isset($_POST["nombre_cliente_remision"]) && !empty(trim($_POST["nombre_cliente_remision"])) ? trim($_POST["nombre_cliente_remision"]) : null,
            "detalle_adicional" => isset($_POST["detalle_adicional"]) && !empty(trim($_POST["detalle_adicional"])) ? trim($_POST["detalle_adicional"]) : null,
            "total_productos" => count($productos),
            "total_cantidad" => array_sum(array_column($productos, 'cantidad'))
        );

        // ✅ DEBUG: Verificar datos preparados
        error_log("Datos preparados para insertar: " . json_encode($datos));

        // ✅ INTENTAR CREAR SOLICITUD
        $respuesta = ModeloSolicitudesStock::mdlCrearSolicitud("solicitudes_stock", $datos);

        // ✅ DEBUG: Verificar respuesta del modelo
        error_log("Respuesta del modelo: " . $respuesta);

        if($respuesta == "ok"){

            error_log("✅ SOLICITUD CREADA EXITOSAMENTE: " . $numeroSolicitud);
            
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
            error_log("❌ ERROR AL CREAR SOLICITUD: " . $respuesta);

            echo '<script>
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error al crear la solicitud. Revise los logs del servidor para más detalles.",
                    showConfirmButton: true,
                    confirmButtonText: "Cerrar"
                });
            </script>';

        }
        
    }
    // ✅ SI NO HAY POST, NO HACER NADA (no mostrar errores)
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
CONTAR SOLICITUDES PENDIENTES - PARA NOTIFICACIONES
=============================================*/
static public function ctrContarSolicitudesPendientes() {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT COUNT(*) as total 
            FROM solicitudes_stock 
            WHERE estado = 'pendiente'
        ");
        
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        return $resultado['total'] ?? 0;
        
    } catch(Exception $e) {
        error_log("Error contando solicitudes pendientes: " . $e->getMessage());
        return 0;
    }
}

/*=============================================
OBTENER SOLICITUDES PENDIENTES RECIENTES - PARA NOTIFICACIONES
=============================================*/
static public function ctrObtenerSolicitudesPendientes($limite = 8) {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT 
                id,
                numero_solicitud,
                nombre_sucursal_solicitante,
                nombre_usuario_solicitante,
                tipo_solicitud,
                total_productos,
                total_cantidad,
                fecha_solicitud,
                detalle_adicional
            FROM solicitudes_stock 
            WHERE estado = 'pendiente'
            ORDER BY fecha_solicitud DESC 
            LIMIT :limite
        ");
        
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        $solicitudes = $stmt->fetchAll();
        
        // Formatear fechas para mejor visualización
        foreach($solicitudes as &$solicitud) {
            $solicitud['fecha_relativa'] = self::obtenerTiempoRelativo($solicitud['fecha_solicitud']);
            $solicitud['fecha_formateada'] = date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud']));
        }
        
        return $solicitudes;
        
    } catch(Exception $e) {
        error_log("Error obteniendo solicitudes pendientes: " . $e->getMessage());
        return [];
    }
}

/*=============================================
OBTENER TIEMPO RELATIVO - HELPER PARA NOTIFICACIONES
=============================================*/
static private function obtenerTiempoRelativo($fecha) {
    
    try {
        $timestamp = strtotime($fecha);
        $diferencia = time() - $timestamp;
        
        if($diferencia < 60) {
            return 'Hace unos segundos';
        } elseif($diferencia < 3600) {
            $minutos = floor($diferencia / 60);
            return 'Hace ' . $minutos . ' minuto' . ($minutos != 1 ? 's' : '');
        } elseif($diferencia < 86400) {
            $horas = floor($diferencia / 3600);
            return 'Hace ' . $horas . ' hora' . ($horas != 1 ? 's' : '');
        } elseif($diferencia < 2592000) {
            $dias = floor($diferencia / 86400);
            return 'Hace ' . $dias . ' día' . ($dias != 1 ? 's' : '');
        } else {
            return date('d/m/Y', $timestamp);
        }
        
    } catch(Exception $e) {
        return 'Fecha no disponible';
    }
}

/*=============================================
OBTENER ESTADÍSTICAS BÁSICAS - PARA DASHBOARD
=============================================*/
static public function ctrObtenerEstadisticas() {
    
    try {
        require_once __DIR__ . "/../api-transferencias/conexion-central.php";
        
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT 
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'aprobado' THEN 1 END) as aprobadas,
                COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as canceladas,
                COUNT(*) as total
            FROM solicitudes_stock
        ");
        
        $stmt->execute();
        $estadisticas = $stmt->fetch();
        
        return $estadisticas;
        
    } catch(Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        return [
            'pendientes' => 0,
            'aprobadas' => 0,
            'canceladas' => 0,
            'total' => 0
        ];
    }
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