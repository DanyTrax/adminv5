<?php

session_start();

// Verificar permisos
if($_SESSION["perfil"] != "Administrador" && $_SESSION["perfil"] != "Transportador") {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

class AjaxDespachos {
    
    /*=============================================
    CARGAR PRODUCTOS DEL INVENTARIO LOCAL
    =============================================*/
    public function ajaxCargarInventario() {
        
        if(isset($_POST["cargarInventario"])) {
            
            try {
                require_once "../modelos/conexion.php";
                
                $stmt = Conexion::conectar()->prepare("
                    SELECT codigo, descripcion, stock, precio_venta 
                    FROM productos 
                    WHERE estado = 1 
                    ORDER BY descripcion ASC
                ");
                
                $stmt->execute();
                $productos = $stmt->fetchAll();
                
                echo json_encode($productos);
                
            } catch(Exception $e) {
                echo json_encode([]);
            }
        }
    }

    /*=============================================
    OBTENER DESPACHO PARA EDITAR
    =============================================*/
    public function ajaxObtenerDespachoEditar() {
        
        if(isset($_POST["idDespachoEditar"])) {
            
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            
            $item = "id";
            $valor = $_POST["idDespachoEditar"];
            
            $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
            
            // Solo permitir edición de despachos pendientes
            if($respuesta && $respuesta["estado"] == "pendiente") {
                echo json_encode($respuesta);
            } else {
                echo json_encode(["error" => "No se puede editar este despacho"]);
            }
        }
    }
    
    /*=============================================
    CREAR NUEVO DESPACHO - VERSIÓN CORREGIDA
    =============================================*/
    public function ajaxCrearDespacho() {
        
        if(isset($_POST["crearDespacho"])) {
            
            try {
                require_once "../controladores/despachos.controlador.php";
                require_once "../modelos/despachos.modelo.php";
                
                // Validar datos requeridos (NOMBRES CORREGIDOS)
                $productosJson = $_POST["productosDespacho"] ?? '';
                $totalProductos = $_POST["totalProductos"] ?? 0;
                $totalCantidad = $_POST["totalCantidad"] ?? 0;
                $tipoDespacho = $_POST["tipoDespacho"] ?? 'libre';
                $observaciones = $_POST["detalleAdicional"] ?? '';
                $idSolicitudOrigen = $_POST["idSolicitudOrigen"] ?? null;
                
                // Debug: Mostrar qué datos se están recibiendo
                error_log("🔍 AJAX Datos recibidos:");
                error_log("productosDespacho: " . $productosJson);
                error_log("totalProductos: " . $totalProductos);
                error_log("totalCantidad: " . $totalCantidad);
                
                if(empty($productosJson)) {
                    throw new Exception("No se enviaron productos para el despacho");
                }
                
                // Validar JSON de productos
                $productos = json_decode($productosJson, true);
                if(!$productos || !is_array($productos)) {
                    throw new Exception("Error en formato de productos: " . json_last_error_msg());
                }
                
                if(count($productos) === 0) {
                    throw new Exception("La lista de productos está vacía");
                }
                
                // Preparar datos para el controlador
                $datos = array(
                    "id_sucursal_origen" => $_SESSION["id_sucursal"] ?? 1,
                    "nombre_sucursal_origen" => $_SESSION["nombre_sucursal"] ?? 'Sucursal Local',
                    "id_usuario_creador" => $_SESSION["id"] ?? 1,
                    "nombre_usuario_creador" => $_SESSION["nombre"] ?? 'Usuario Sistema',
                    "tipo_despacho" => $tipoDespacho,
                    "productos_despacho" => $productosJson,
                    "total_productos" => $totalProductos,
                    "total_cantidad" => $totalCantidad,
                    "detalle_adicional" => $observaciones,
                    "id_solicitud_origen" => $idSolicitudOrigen
                );
                
                // Debug: Mostrar datos preparados para el controlador
                error_log("📦 Datos para controlador preparados correctamente");
                
                // Crear el despacho usando el controlador
                // Crear el despacho usando el controlador
                $respuesta = ControladorDespachos::ctrCrearDespacho($datos);

                // AGREGAR ESTE DEBUG TEMPORAL:
                error_log("🔍 Respuesta del controlador: " . print_r($respuesta, true));

                if($respuesta == "ok") {
                    
                    // DEBUG: Verificar si realmente se guardó
                    error_log("✅ Controlador devolvió OK, verificando BD...");
                    
                    // Intentar obtener el último despacho
                    try {
                        require_once "../api-transferencias/conexion-central.php";
                        $ultimoId = ConexionCentral::conectar()->lastInsertId();
                        error_log("🆔 Último ID insertado: " . $ultimoId);
                        
                        if($ultimoId > 0) {
                            error_log("✅ Despacho guardado en BD con ID: " . $ultimoId);
                        } else {
                            error_log("❌ No se insertó nada en la BD");
                        }
                    } catch(Exception $e) {
                        error_log("❌ Error verificando BD: " . $e->getMessage());
                    }
                    
                    // logs despues borrar...
                
                if($respuesta == "ok") {
                    
                    // Obtener el número del despacho recién creado
                    $ultimoDespacho = ControladorDespachos::ctrObtenerUltimoDespacho();
                    $numeroDespacho = $ultimoDespacho ? $ultimoDespacho["numero_despacho"] : "DEP" . date("YmdHis");
                    
                    echo json_encode([
                        "success" => true,
                        "message" => "Despacho creado exitosamente",
                        "numero_despacho" => $numeroDespacho,
                        "id_despacho" => $ultimoDespacho ? $ultimoDespacho["id"] : null,
                        "productos_procesados" => count($productos)
                    ]);
                    
                } else {
                    throw new Exception("Error del controlador: " . $respuesta);
                }
                
            } catch(Exception $e) {
                // Debug: Mostrar error completo
                error_log("❌ Error en ajaxCrearDespacho: " . $e->getMessage());
                error_log("❌ Trace: " . $e->getTraceAsString());
                
                echo json_encode([
                    "success" => false,
                    "error" => $e->getMessage(),
                    "debug_info" => [
                        "productos_recibidos" => !empty($productosJson),
                        "productos_count" => isset($productos) ? count($productos) : 0,
                        "total_productos" => $totalProductos,
                        "total_cantidad" => $totalCantidad
                    ]
                ]);
            }
        }
    }
}

// MANEJO DE PETICIONES POST
if(isset($_POST["cargarInventario"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxCargarInventario();
}

if(isset($_POST["idDespachoEditar"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxObtenerDespachoEditar();
}

if(isset($_POST["crearDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxCrearDespacho();
}

?>