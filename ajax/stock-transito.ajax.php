<?php

session_start();

require_once "../controladores/stock-transito.controlador.php";
require_once "../modelos/stock-transito.modelo.php";

class AjaxStockTransito {

    /*=============================================
    VALIDAR CANTIDAD PARA DESCARGA
    =============================================*/
    public function ajaxValidarCantidadDescarga() {
        
        if(isset($_POST["idStockTransito"]) && isset($_POST["cantidadSolicitar"])) {
            
            $idStockTransito = intval($_POST["idStockTransito"]);
            $cantidadSolicitar = intval($_POST["cantidadSolicitar"]);
            
            try {
                // Obtener stock actual
                $stock = ControladorStockTransito::ctrObtenerStockDisponible($idStockTransito);
                
                if(!$stock) {
                    echo json_encode([
                        "valido" => false,
                        "mensaje" => "Producto no encontrado",
                        "stock_disponible" => 0
                    ]);
                    return;
                }

                // Verificar solicitudes pendientes
                $cantidadSolicitadaPendiente = ControladorStockTransito::ctrObtenerCantidadSolicitadaPendiente(
                    $stock["codigo_producto"], 
                    $stock["transportador_id"]
                );

                $stockRealDisponible = $stock["cantidad_disponible"] - $cantidadSolicitadaPendiente;

                // ✅ VALIDACIONES CRÍTICAS
                $valido = true;
                $mensaje = "Cantidad válida";

                if($cantidadSolicitar <= 0) {
                    $valido = false;
                    $mensaje = "La cantidad debe ser mayor a 0";
                }
                else if($cantidadSolicitar > $stockRealDisponible) {
                    $valido = false;
                    $mensaje = "Solo hay {$stockRealDisponible} unidades disponibles";
                    
                    if($cantidadSolicitadaPendiente > 0) {
                        $mensaje .= " ({$cantidadSolicitadaPendiente} ya solicitadas)";
                    }
                }

                echo json_encode([
                    "valido" => $valido,
                    "mensaje" => $mensaje,
                    "stock_total" => $stock["cantidad_disponible"],
                    "stock_solicitado" => $cantidadSolicitadaPendiente,
                    "stock_disponible" => $stockRealDisponible,
                    "cantidad_solicitada" => $cantidadSolicitar
                ]);

            } catch(Exception $e) {
                echo json_encode([
                    "valido" => false,
                    "mensaje" => "Error interno: " . $e->getMessage(),
                    "stock_disponible" => 0
                ]);
            }
        }
    }

    /*=============================================
    OBTENER SOLICITUDES PENDIENTES PARA TRANSPORTADOR
    =============================================*/
    public function ajaxObtenerSolicitudesPendientes() {
        
        if(isset($_POST["transportadorId"]) && $_SESSION["perfil"] == "Transportador") {
            
            $transportadorId = intval($_POST["transportadorId"]);
            
            // Verificar que es el transportador correcto
            if($transportadorId != $_SESSION["id"]) {
                echo json_encode([
                    "error" => "Sin permisos para ver estas solicitudes"
                ]);
                return;
            }

            try {
                $solicitudes = ControladorStockTransito::ctrObtenerSolicitudesPendientes($transportadorId);
                
                echo json_encode([
                    "success" => true,
                    "solicitudes" => $solicitudes,
                    "total" => count($solicitudes)
                ]);

            } catch(Exception $e) {
                echo json_encode([
                    "error" => "Error obteniendo solicitudes: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER HISTORIAL DE PRODUCTO
    =============================================*/
    public function ajaxObtenerHistorialProducto() {
        
        if(isset($_POST["codigoProducto"])) {
            
            $codigoProducto = $_POST["codigoProducto"];
            $transportadorId = $_POST["transportadorId"] ?? null;
            
            try {
                require_once "../api-transferencias/conexion-central.php";
                
                $sql = "SELECT * FROM historico_transito 
                        WHERE codigo_producto = :codigo_producto";
                
                $parametros = [":codigo_producto" => $codigoProducto];
                
                // Si es transportador, solo su historial
                if($_SESSION["perfil"] == "Transportador" || $transportadorId) {
                    $sql .= " AND transportador_id = :transportador_id";
                    $parametros[":transportador_id"] = $transportadorId ?? $_SESSION["id"];
                }
                
                $sql .= " ORDER BY fecha_movimiento DESC LIMIT 20";
                
                $stmt = ConexionCentral::conectar()->prepare($sql);
                
                foreach($parametros as $key => $valor) {
                    $stmt->bindValue($key, $valor);
                }
                
                $stmt->execute();
                $historial = $stmt->fetchAll();
                
                echo json_encode([
                    "success" => true,
                    "historial" => $historial,
                    "codigo_producto" => $codigoProducto
                ]);

            } catch(Exception $e) {
                echo json_encode([
                    "error" => "Error obteniendo historial: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER DETALLES DE SOLICITUD
    =============================================*/
    public function ajaxObtenerDetallesSolicitud() {
        
        if(isset($_POST["idSolicitud"])) {
            
            $idSolicitud = intval($_POST["idSolicitud"]);
            
            try {
                $solicitud = ModeloStockTransito::mdlObtenerSolicitudDescarga($idSolicitud);
                
                if($solicitud) {
                    
                    // Verificar permisos
                    if($_SESSION["perfil"] == "Transportador" && $solicitud["transportador_id"] != $_SESSION["id"]) {
                        echo json_encode([
                            "error" => "Sin permisos para ver esta solicitud"
                        ]);
                        return;
                    }
                    
                    echo json_encode([
                        "success" => true,
                        "solicitud" => $solicitud
                    ]);
                    
                } else {
                    echo json_encode([
                        "error" => "Solicitud no encontrada"
                    ]);
                }

            } catch(Exception $e) {
                echo json_encode([
                    "error" => "Error obteniendo solicitud: " . $e->getMessage()
                ]);
            }
        }
    }

    /*=============================================
    OBTENER STOCK FILTRADO POR TRANSPORTADOR
    =============================================*/
    public function ajaxFiltrarPorTransportador() {
        
        if(isset($_POST["transportadorId"])) {
            
            $transportadorId = $_POST["transportadorId"] === "" ? null : intval($_POST["transportadorId"]);
            
            try {
                $stock = ControladorStockTransito::ctrMostrarStockTransito(null, null, $transportadorId);
                
                // Procesar datos para respuesta
                $stockProcesado = [];
                foreach($stock as $item) {
                    $stockProcesado[] = [
                        "id" => $item["id"],
                        "codigo_producto" => $item["codigo_producto"],
                        "descripcion_producto" => $item["descripcion_producto"],
                        "cantidad_disponible" => $item["cantidad_disponible"],
                        "transportador_id" => $item["transportador_id"],
                        "nombre_transportador" => $item["nombre_transportador"],
                        "sucursal_origen" => $item["sucursal_origen"],
                        "fecha_carga" => $item["fecha_carga"]
                    ];
                }
                
                echo json_encode([
                    "success" => true,
                    "stock" => $stockProcesado,
                    "total" => count($stockProcesado)
                ]);

            } catch(Exception $e) {
                echo json_encode([
                    "error" => "Error filtrando stock: " . $e->getMessage()
                ]);
            }
        }
    }
}

/*=============================================
PROCESAR PETICIONES AJAX
=============================================*/
if(isset($_POST["idStockTransito"]) && isset($_POST["cantidadSolicitar"])) {
    $validarCantidad = new AjaxStockTransito();
    $validarCantidad->ajaxValidarCantidadDescarga();
}

if(isset($_POST["transportadorId"]) && isset($_POST["obtenerSolicitudes"])) {
    $obtenerSolicitudes = new AjaxStockTransito();
    $obtenerSolicitudes->ajaxObtenerSolicitudesPendientes();
}

if(isset($_POST["codigoProducto"]) && isset($_POST["obtenerHistorial"])) {
    $obtenerHistorial = new AjaxStockTransito();
    $obtenerHistorial->ajaxObtenerHistorialProducto();
}

if(isset($_POST["idSolicitud"]) && isset($_POST["obtenerDetalles"])) {
    $obtenerDetalles = new AjaxStockTransito();
    $obtenerDetalles->ajaxObtenerDetallesSolicitud();
}

if(isset($_POST["transportadorId"]) && isset($_POST["filtrarStock"])) {
    $filtrarStock = new AjaxStockTransito();
    $filtrarStock->ajaxFiltrarPorTransportador();
}

/*=============================================
DESCARGAR STOCK DIRECTO
=============================================*/
if(isset($_POST["descargarStockDirecto"])) {
    
    try {
        $idStockTransito = $_POST["idStockTransito"];
        $cantidadDescargar = intval($_POST["cantidadDescargar"]);
        $observaciones = $_POST["observaciones"] ?? "";
        
        error_log("📥 DESCARGA DIRECTA - ID Stock: $idStockTransito, Cantidad: $cantidadDescargar");
        
        // Obtener información del stock en tránsito
        $stock = ControladorStockTransito::ctrObtenerStockDisponible($idStockTransito);
        
        if(!$stock) {
            echo json_encode(["success" => false, "error" => "Producto no encontrado en stock en tránsito"]);
            return;
        }
        
        // Verificar que la cantidad sea válida
        if($cantidadDescargar <= 0 || $cantidadDescargar > $stock["cantidad_disponible"]) {
            echo json_encode(["success" => false, "error" => "Cantidad inválida"]);
            return;
        }
        
        // Obtener información de la sesión
        $usuarioId = $_SESSION["id"] ?? 1;
        $nombreUsuario = $_SESSION["nombre"] ?? "Usuario";
        $sucursalDestino = $_SESSION["sucursal"] ?? "Sucursal";
        
        // Ejecutar descarga directa
        $resultado = ControladorStockTransito::ctrDescargarStockDirecto(
            $idStockTransito,
            $cantidadDescargar,
            $usuarioId,
            $nombreUsuario,
            $sucursalDestino,
            $observaciones
        );
        
        if($resultado["success"]) {
            echo json_encode([
                "success" => true,
                "message" => "Descarga realizada correctamente. " . $resultado["message"]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => $resultado["error"]
            ]);
        }
        
    } catch(Exception $e) {
        error_log("❌ Error en descarga directa: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "error" => "Error al procesar la descarga: " . $e->getMessage()
        ]);
    }
}

?>