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
                
                // Validar datos requeridos
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
                $respuesta = ControladorDespachos::ctrCrearDespacho($datos);
                
                // DEBUG: Verificar respuesta del controlador
                error_log("🔍 Respuesta del controlador: " . print_r($respuesta, true));
                
                if($respuesta == "ok") {
                    
                    // DEBUG: Verificar si realmente se guardó
                    error_log("✅ Controlador devolvió OK, verificando BD...");
                    
                    try {
                        require_once "../api-transferencias/conexion-central.php";
                        $pdo = ConexionCentral::conectar();
                        $ultimoId = $pdo->lastInsertId();
                        error_log("🆔 Último ID insertado: " . $ultimoId);
                        
                        if($ultimoId > 0) {
                            error_log("✅ Despacho guardado en BD con ID: " . $ultimoId);
                        } else {
                            error_log("❌ No se insertó nada en la BD");
                        }
                    } catch(Exception $e) {
                        error_log("❌ Error verificando BD: " . $e->getMessage());
                    }
                    
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
    /*=============================================
VER DETALLES DEL DESPACHO
=============================================*/
public function ajaxVerDespacho() {
    
    if(isset($_POST["idDespacho"])) {
        
        try {
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            
            $item = "id";
            $valor = $_POST["idDespacho"];
            
            $despacho = ControladorDespachos::ctrMostrarDespachos($item, $valor);
            
            if($despacho) {
                echo json_encode($despacho);
            } else {
                echo json_encode(["error" => "Despacho no encontrado"]);
            }
            
        } catch(Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

/*=============================================
ACEPTAR DESPACHO - VERSIÓN CON MÁS DEBUG
=============================================*/
public function ajaxAceptarDespacho() {
    
    if(isset($_POST["aceptarDespacho"])) {
        
        try {
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            require_once "../modelos/productos.modelo.php";
            require_once "../api-transferencias/conexion-central.php";
            
            $idDespacho = $_POST["idDespacho"];
            
            error_log("🔍 Aceptando despacho ID: " . $idDespacho);
            
            // 1. Obtener el despacho
            $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
            if(!$despacho) {
                throw new Exception("Despacho no encontrado");
            }
            
            error_log("🔍 Despacho encontrado: " . $despacho["numero_despacho"] . " - Estado: " . $despacho["estado"]);
            
            // 2. Verificar que esté pendiente
            if($despacho["estado"] != "pendiente") {
                throw new Exception("El despacho no está en estado pendiente. Estado actual: " . $despacho["estado"]);
            }
            
            // 3. Parsear productos
            $productos = json_decode($despacho["productos_despacho"], true);
            if(!$productos) {
                throw new Exception("Error al leer productos del despacho: " . json_last_error_msg());
            }
            
            error_log("🔍 Productos a procesar: " . count($productos));
            
            // 4. Iniciar transacción
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            try {
                
                // 5. Descontar stock local y crear registros de stock en tránsito
                foreach($productos as $producto) {
                    
                    error_log("🔍 Procesando producto: " . $producto["codigo"] . " - Cantidad: " . $producto["cantidad"]);
                    
                    // Descontar del stock local
                    $this->descontarStockLocal($producto["codigo"], $producto["cantidad"]);
                    
                    // Agregar al stock en tránsito
                    $this->agregarStockTransito($despacho, $producto);
                }
                
                // 6. Actualizar estado del despacho - MÉTODO DIRECTO
                error_log("🔍 Actualizando estado del despacho a 'aceptado'...");
                
                $stmtUpdate = $conexion->prepare("
                    UPDATE despachos SET 
                        estado = 'aceptado',
                        fecha_aceptacion = NOW(),
                        transportador_id = :transportador_id,
                        nombre_transportador = :nombre_transportador
                    WHERE id = :id
                ");
                
                $stmtUpdate->bindParam(":transportador_id", $_SESSION["id"]);
                $stmtUpdate->bindParam(":nombre_transportador", $_SESSION["nombre"]);
                $stmtUpdate->bindParam(":id", $idDespacho);
                
                if(!$stmtUpdate->execute()) {
                    $errorInfo = $stmtUpdate->errorInfo();
                    throw new Exception("Error al actualizar estado del despacho: " . print_r($errorInfo, true));
                }
                
                $filasAfectadas = $stmtUpdate->rowCount();
                error_log("✅ Filas afectadas en UPDATE: " . $filasAfectadas);
                
                if($filasAfectadas == 0) {
                    throw new Exception("No se actualizó ninguna fila. Verificar ID del despacho.");
                }
                
                // 7. Confirmar transacción
                $conexion->commit();
                
                error_log("✅ Despacho aceptado exitosamente - ID: " . $idDespacho);
                
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho aceptado exitosamente. Los productos se han movido al stock en tránsito y el estado ha sido actualizado."
                ]);
                
            } catch(Exception $e) {
                $conexion->rollBack();
                error_log("❌ Error en transacción: " . $e->getMessage());
                throw $e;
            }
            
        } catch(Exception $e) {
            error_log("❌ Error aceptando despacho: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
public function ajaxCancelarDespacho() {
    
    if(isset($_POST["cancelarDespacho"])) {
        
        try {
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            
            $idDespacho = $_POST["idDespacho"];
            $motivo = $_POST["motivoCancelacion"] ?? "Sin motivo especificado";
            
            // Actualizar estado del despacho
            $datosUpdate = array(
                "estado" => "cancelado",
                "motivo_cancelacion" => $motivo,
                "fecha_cancelacion" => date("Y-m-d H:i:s"),
                "usuario_cancelacion" => $_SESSION["nombre"]
            );
            
            $respuesta = ModeloDespachos::mdlActualizarDespacho("despachos", $datosUpdate, "id", $idDespacho);
            
            if($respuesta == "ok") {
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho cancelado exitosamente"
                ]);
            } else {
                throw new Exception("Error al cancelar el despacho");
            }
            
        } catch(Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
}

/*=============================================
ELIMINAR DESPACHO
=============================================*/
public function ajaxEliminarDespacho() {
    
    if(isset($_POST["eliminarDespacho"])) {
        
        try {
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            
            $idDespacho = $_POST["idDespacho"];
            
            // Verificar que sea administrador
            if($_SESSION["perfil"] != "Administrador") {
                throw new Exception("Solo los administradores pueden eliminar despachos");
            }
            
            $respuesta = ModeloDespachos::mdlBorrarDespacho("despachos", "id", $idDespacho);
            
            if($respuesta == "ok") {
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho eliminado exitosamente"
                ]);
            } else {
                throw new Exception("Error al eliminar el despacho");
            }
            
        } catch(Exception $e) {
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
}

/*=============================================
FUNCIONES AUXILIARES
=============================================*/
private function descontarStockLocal($codigoProducto, $cantidad) {
    
    require_once "../modelos/conexion.php";
    
    $stmt = Conexion::conectar()->prepare("
        UPDATE productos 
        SET stock = stock - :cantidad 
        WHERE codigo = :codigo AND stock >= :cantidad
    ");
    
    $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
    $stmt->bindParam(":codigo", $codigoProducto, PDO::PARAM_STR);
    
    if(!$stmt->execute()) {
        throw new Exception("Error al descontar stock del producto: " . $codigoProducto);
    }
    
    if($stmt->rowCount() == 0) {
        throw new Exception("Stock insuficiente para el producto: " . $codigoProducto);
    }
}

private function agregarStockTransito($despacho, $producto) {
    
    require_once "../api-transferencias/conexion-central.php";
    
    $stmt = ConexionCentral::conectar()->prepare("
        INSERT INTO stock_transito (
            codigo_producto,
            descripcion_producto,
            cantidad_disponible,
            transportador_id,
            nombre_transportador,
            sucursal_origen,
            id_despacho_origen,
            numero_despacho_origen,
            fecha_carga,
            observaciones
        ) VALUES (
            :codigo_producto,
            :descripcion_producto,
            :cantidad_disponible,
            :transportador_id,
            :nombre_transportador,
            :sucursal_origen,
            :id_despacho_origen,
            :numero_despacho_origen,
            NOW(),
            :observaciones
        )
    ");
    
    $stmt->bindParam(":codigo_producto", $producto["codigo"]);
    $stmt->bindParam(":descripcion_producto", $producto["descripcion"]);
    $stmt->bindParam(":cantidad_disponible", $producto["cantidad"]);
    $stmt->bindParam(":transportador_id", $_SESSION["id"]);
    $stmt->bindParam(":nombre_transportador", $_SESSION["nombre"]);
    $stmt->bindParam(":sucursal_origen", $despacho["sucursal_origen"]);
    $stmt->bindParam(":id_despacho_origen", $despacho["id"]);
    $stmt->bindParam(":numero_despacho_origen", $despacho["numero_despacho"]);
    $observaciones = $producto["observacion"] ?? "Producto agregado desde despacho " . $despacho["numero_despacho"];
    $stmt->bindParam(":observaciones", $observaciones);
    
    if(!$stmt->execute()) {
        $errorInfo = $stmt->errorInfo();
        error_log("❌ Error SQL en stock_transito: " . print_r($errorInfo, true));
        throw new Exception("Error al agregar producto al stock en tránsito: " . $producto["codigo"]);
    }
    
    error_log("✅ Producto agregado al stock en tránsito: " . $producto["codigo"]);
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
// MANEJADORES DE PETICIONES POST (agregar al final)
if(isset($_POST["idDespacho"]) && !isset($_POST["aceptarDespacho"]) && !isset($_POST["cancelarDespacho"]) && !isset($_POST["eliminarDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxVerDespacho();
}

if(isset($_POST["aceptarDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxAceptarDespacho();
}

if(isset($_POST["cancelarDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxCancelarDespacho();
}

if(isset($_POST["eliminarDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxEliminarDespacho();
}
?>