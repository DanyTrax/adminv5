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
CREAR NUEVO DESPACHO - VERSIÓN CON SUCURSAL CORRECTA
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
            
            // OBTENER NOMBRE REAL DE LA SUCURSAL DESDE BD LOCAL
            $nombreSucursalReal = $this->obtenerNombreSucursalLocal();
            
            // Preparar datos para el controlador
            $datos = array(
                "id_sucursal_origen" => $_SESSION["id_sucursal"] ?? 1,
                "sucursal_origen" => $nombreSucursalReal, // NOMBRE REAL DE LA SUCURSAL
                "nombre_sucursal_origen" => $nombreSucursalReal, // COMPATIBILIDAD
                "id_usuario_creador" => $_SESSION["id"] ?? 1,
                "nombre_usuario_creador" => $_SESSION["nombre"] ?? 'Usuario Sistema',
                "tipo_despacho" => $tipoDespacho,
                "productos_despacho" => $productosJson,
                "total_productos" => $totalProductos,
                "total_cantidad" => $totalCantidad,
                "detalle_adicional" => $observaciones,
                "id_solicitud_origen" => $idSolicitudOrigen
            );
            
            error_log("📦 Datos preparados - Sucursal: " . $nombreSucursalReal);
            
            // Crear el despacho usando el controlador
            $respuesta = ControladorDespachos::ctrCrearDespacho($datos);
            
            if($respuesta == "ok") {
                
                // Obtener el número del despacho recién creado
                $ultimoDespacho = ControladorDespachos::ctrObtenerUltimoDespacho();
                $numeroDespacho = $ultimoDespacho ? $ultimoDespacho["numero_despacho"] : "DEP" . date("YmdHis");
                
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho creado exitosamente desde " . $nombreSucursalReal,
                    "numero_despacho" => $numeroDespacho,
                    "id_despacho" => $ultimoDespacho ? $ultimoDespacho["id"] : null,
                    "productos_procesados" => count($productos),
                    "sucursal_origen" => $nombreSucursalReal
                ]);
                
            } else {
                throw new Exception("Error del controlador: " . $respuesta);
            }
            
        } catch(Exception $e) {
            error_log("❌ Error en ajaxCrearDespacho: " . $e->getMessage());
            
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
OBTENER NOMBRE REAL DE LA SUCURSAL - VERSIÓN MEJORADA
=============================================*/
private function obtenerNombreSucursalLocal() {
    
    try {
        require_once "../modelos/conexion.php";
        
        // Obtener el código de sucursal de la sesión
        $codigoSucursal = $_SESSION["codigo_sucursal"] ?? null;
        $idSucursal = $_SESSION["id_sucursal"] ?? null;
        
        error_log("🔍 Buscando sucursal - Código: " . ($codigoSucursal ?? 'null') . " ID: " . ($idSucursal ?? 'null'));
        
        $stmt = null;
        
        if($codigoSucursal) {
            // Buscar por código de sucursal
            $stmt = Conexion::conectar()->prepare("
                SELECT id, codigo_sucursal, nombre 
                FROM sucursal_local 
                WHERE codigo_sucursal = :codigo_sucursal AND activo = 1
                LIMIT 1
            ");
            $stmt->bindParam(":codigo_sucursal", $codigoSucursal);
            
        } elseif($idSucursal) {
            // Buscar por ID de sucursal
            $stmt = Conexion::conectar()->prepare("
                SELECT id, codigo_sucursal, nombre 
                FROM sucursal_local 
                WHERE id = :id_sucursal AND activo = 1
                LIMIT 1
            ");
            $stmt->bindParam(":id_sucursal", $idSucursal);
            
        } else {
            // Si no hay datos de sesión, tomar la sucursal principal o la primera activa
            $stmt = Conexion::conectar()->prepare("
                SELECT id, codigo_sucursal, nombre 
                FROM sucursal_local 
                WHERE activo = 1 
                ORDER BY es_principal DESC, id ASC 
                LIMIT 1
            ");
        }
        
        $stmt->execute();
        $sucursal = $stmt->fetch();
        
        if($sucursal && isset($sucursal["nombre"])) {
            
            // ACTUALIZAR VARIABLES DE SESIÓN si no están definidas
            if(!isset($_SESSION["id_sucursal"])) {
                $_SESSION["id_sucursal"] = $sucursal["id"];
            }
            if(!isset($_SESSION["codigo_sucursal"])) {
                $_SESSION["codigo_sucursal"] = $sucursal["codigo_sucursal"];
            }
            if(!isset($_SESSION["nombre_sucursal"])) {
                $_SESSION["nombre_sucursal"] = $sucursal["nombre"];
            }
            
            error_log("✅ Sucursal encontrada y variables actualizadas: " . $sucursal["nombre"]);
            return $sucursal["nombre"];
            
        } else {
            error_log("⚠️ No se encontró sucursal activa, usando fallback");
            return 'Sucursal Local';
        }
        
    } catch(Exception $e) {
        error_log("❌ Error obteniendo nombre de sucursal: " . $e->getMessage());
        return 'Sucursal Local';
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
ACEPTAR DESPACHO - VERSIÓN FINAL CORREGIDA
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
            
            error_log("🔍 Despacho encontrado: " . $despacho["numero_despacho"]);
            
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
                
                // 6. Actualizar estado del despacho - CORREGIDO
                error_log("🔍 Actualizando estado del despacho a 'en_transito'...");
                
                $stmtUpdate = $conexion->prepare("
                    UPDATE despachos SET 
                        estado = 'en_transito',
                        fecha_actualizacion = NOW(),
                        transportador_id = :transportador_id,
                        nombre_transportador = :nombre_transportador
                    WHERE id = :id
                ");
                
                $stmtUpdate->bindParam(":transportador_id", $_SESSION["id"]);
                $stmtUpdate->bindParam(":nombre_transportador", $_SESSION["nombre"]);
                $stmtUpdate->bindParam(":id", $idDespacho);
                
                if(!$stmtUpdate->execute()) {
                    $errorInfo = $stmtUpdate->errorInfo();
                    throw new Exception("Error al actualizar estado: " . print_r($errorInfo, true));
                }
                
                $filasAfectadas = $stmtUpdate->rowCount();
                error_log("✅ Filas afectadas en UPDATE: " . $filasAfectadas);
                
                // 7. Confirmar transacción
                $conexion->commit();
                
                error_log("✅ Despacho aceptado exitosamente - Nuevo estado: en_transito");
                
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho aceptado exitosamente. Estado cambiado a 'En Tránsito' y productos movidos al stock en tránsito."
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
CANCELAR DESPACHO - VERSIÓN COMPLETA MEJORADA
=============================================*/
public function ajaxCancelarDespacho() {
    
    if(isset($_POST["cancelarDespacho"])) {
        
        try {
            require_once "../controladores/despachos.controlador.php";
            require_once "../modelos/despachos.modelo.php";
            require_once "../modelos/productos.modelo.php";
            require_once "../api-transferencias/conexion-central.php";
            
            $idDespacho = $_POST["idDespacho"];
            $motivoCancelacion = $_POST["motivoCancelacion"] ?? "Cancelación solicitada por el usuario";
            
            error_log("🔍 Cancelando despacho ID: " . $idDespacho);
            error_log("📝 Motivo: " . $motivoCancelacion);
            
            // 1. Obtener el despacho
            $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
            if(!$despacho) {
                throw new Exception("Despacho no encontrado");
            }
            
            error_log("🔍 Despacho encontrado: " . $despacho["numero_despacho"] . " - Estado: " . $despacho["estado"]);
            
            // 2. Verificar que se pueda cancelar
            if($despacho["estado"] == "cancelado") {
                throw new Exception("El despacho ya está cancelado");
            }
            
            if($despacho["estado"] == "entregado") {
                throw new Exception("No se puede cancelar un despacho ya entregado");
            }
            
            // 3. Iniciar transacción
            $conexion = ConexionCentral::conectar();
            $conexion->beginTransaction();
            
            try {
                
                // 4. Si el despacho estaba en tránsito, devolver stock desde stock_transito
                if($despacho["estado"] == "en_transito") {
                    error_log("🔄 Despacho en tránsito - devolviendo stock desde stock_transito...");
                    $this->devolverStockDesdeTransito($despacho);
                }
                
                // 5. Si el despacho estaba pendiente, devolver stock local
                if($despacho["estado"] == "pendiente") {
                    error_log("🔄 Despacho pendiente - devolviendo stock local...");
                    $this->devolverStockLocal($despacho);
                }
                
                // 6. Actualizar estado del despacho con información de cancelación
                $stmtUpdate = $conexion->prepare("
                    UPDATE despachos SET 
                        estado = 'cancelado',
                        motivo_cancelacion = :motivo_cancelacion,
                        usuario_cancelacion = :usuario_cancelacion,
                        fecha_actualizacion = NOW()
                    WHERE id = :id
                ");
                
                $usuarioCancelacion = $_SESSION["nombre"] ?? 'Sistema';
                
                $stmtUpdate->bindParam(":motivo_cancelacion", $motivoCancelacion);
                $stmtUpdate->bindParam(":usuario_cancelacion", $usuarioCancelacion);
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
                
                error_log("✅ Despacho cancelado exitosamente - ID: " . $idDespacho);
                
                echo json_encode([
                    "success" => true,
                    "message" => "Despacho cancelado exitosamente. El stock ha sido devuelto al inventario.",
                    "despacho_cancelado" => $despacho["numero_despacho"],
                    "motivo" => $motivoCancelacion,
                    "usuario" => $usuarioCancelacion,
                    "fecha_cancelacion" => date("Y-m-d H:i:s")
                ]);
                
            } catch(Exception $e) {
                $conexion->rollBack();
                error_log("❌ Error en transacción de cancelación: " . $e->getMessage());
                throw $e;
            }
            
        } catch(Exception $e) {
            error_log("❌ Error cancelando despacho: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
}

/*=============================================
DEVOLVER STOCK DESDE STOCK EN TRÁNSITO
=============================================*/
private function devolverStockDesdeTransito($despacho) {
    
    try {
        require_once "../api-transferencias/conexion-central.php";
        require_once "../modelos/conexion.php";
        
        // 1. Obtener productos del stock en tránsito relacionados con este despacho
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT codigo_producto, cantidad_disponible 
            FROM stock_transito 
            WHERE id_despacho_origen = :id_despacho
        ");
        $stmt->bindParam(":id_despacho", $despacho["id"]);
        $stmt->execute();
        $productosTransito = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("🔍 Productos en tránsito a devolver: " . count($productosTransito));
        
        // 2. Devolver cada producto al stock local
        foreach($productosTransito as $productoTransito) {
            
            $codigoProducto = $productoTransito["codigo_producto"];
            $cantidadDevolver = $productoTransito["cantidad_disponible"];
            
            // Actualizar stock local
            $stmtLocal = Conexion::conectar()->prepare("
                UPDATE productos 
                SET stock = stock + :cantidad 
                WHERE codigo = :codigo
            ");
            $stmtLocal->bindParam(":cantidad", $cantidadDevolver);
            $stmtLocal->bindParam(":codigo", $codigoProducto);
            
            if(!$stmtLocal->execute()) {
                throw new Exception("Error devolviendo stock local para producto: " . $codigoProducto);
            }
            
            error_log("✅ Stock devuelto - Producto: " . $codigoProducto . " Cantidad: " . $cantidadDevolver);
        }
        
        // 3. Eliminar registros del stock en tránsito
        $stmtDelete = ConexionCentral::conectar()->prepare("
            DELETE FROM stock_transito 
            WHERE id_despacho_origen = :id_despacho
        ");
        $stmtDelete->bindParam(":id_despacho", $despacho["id"]);
        
        if(!$stmtDelete->execute()) {
            throw new Exception("Error eliminando registros de stock en tránsito");
        }
        
        $registrosEliminados = $stmtDelete->rowCount();
        error_log("✅ Registros eliminados del stock en tránsito: " . $registrosEliminados);
        
    } catch(Exception $e) {
        error_log("❌ Error devolviendo stock desde tránsito: " . $e->getMessage());
        throw $e;
    }
}

/*=============================================
DEVOLVER STOCK LOCAL (para despachos pendientes)
=============================================*/
private function devolverStockLocal($despacho) {
    
    try {
        require_once "../modelos/conexion.php";
        
        // 1. Parsear productos del despacho
        $productos = json_decode($despacho["productos_despacho"], true);
        if(!$productos) {
            throw new Exception("Error parseando productos del despacho");
        }
        
        error_log("🔍 Productos del despacho a devolver: " . count($productos));
        
        // 2. Devolver stock de cada producto
        foreach($productos as $producto) {
            
            $codigoProducto = $producto["codigo"];
            $cantidadDevolver = $producto["cantidad"];
            
            // Actualizar stock local
            $stmt = Conexion::conectar()->prepare("
                UPDATE productos 
                SET stock = stock + :cantidad 
                WHERE codigo = :codigo
            ");
            $stmt->bindParam(":cantidad", $cantidadDevolver);
            $stmt->bindParam(":codigo", $codigoProducto);
            
            if(!$stmt->execute()) {
                throw new Exception("Error devolviendo stock para producto: " . $codigoProducto);
            }
            
            error_log("✅ Stock local devuelto - Producto: " . $codigoProducto . " Cantidad: " . $cantidadDevolver);
        }
        
    } catch(Exception $e) {
        error_log("❌ Error devolviendo stock local: " . $e->getMessage());
        throw $e;
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

if(isset($_POST["eliminarDespacho"])) {
    $ajax = new AjaxDespachos();
    $ajax->ajaxEliminarDespacho();
}

/*=============================================
PROCESAR PETICIONES - AGREGAR ESTA LÍNEA
=============================================*/
if(isset($_POST["cancelarDespacho"])) {
    $cancelar = new AjaxDespachos();
    $cancelar->ajaxCancelarDespacho();
}
?>