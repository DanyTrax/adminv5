<?php

// Iniciar buffer de salida para capturar warnings
ob_start();

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la sesión esté iniciada correctamente
if (!isset($_SESSION['perfil'])) {
    sendJsonResponse(["success" => false, "error" => "Sesión no iniciada. Por favor, inicie sesión nuevamente."]);
}

require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../controladores/despachos.controlador.php";
require_once __DIR__ . "/../modelos/despachos.modelo.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";
require_once __DIR__ . "/../src/Logger.php";

// Función helper para enviar JSON limpio
function sendJsonResponse($data) {
    // Limpiar buffer de warnings solo si hay contenido
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Solo enviar header si no se han enviado headers aún
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    echo json_encode($data);
    exit;
}

/*=============================================
VER DESPACHO
=============================================*/
if(isset($_POST["idDespacho"])){

    $item = "id";
    $valor = $_POST["idDespacho"];
    
    $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
    
    if($respuesta) {
        sendJsonResponse([
            "success" => true,
            "data" => $respuesta,
            "message" => "Despacho obtenido correctamente"
        ]);
    } else {
        sendJsonResponse([
            "success" => false,
            "error" => "No se pudo obtener el despacho"
        ]);
    }
}

/*=============================================
ACEPTAR DESPACHO - CON MANEJO DE STOCK
=============================================*/
if(isset($_POST["aceptarDespacho"])){

    try {
        $idDespacho = $_POST["aceptarDespacho"];
        
        Logger::ajax("ACEPTAR_DESPACHO", ["idDespacho" => $idDespacho], "despachos.ajax.php", "aceptarDespacho");
        
        // 1. Obtener datos del despacho
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            Logger::error("Despacho no encontrado: ID $idDespacho", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        Logger::info("Despacho encontrado: " . json_encode($despacho), "despachos.ajax.php", "aceptarDespacho");
        
        // 2. Verificar que esté en estado pendiente
        if($despacho["estado"] != "pendiente") {
            Logger::warning("Intento de aceptar despacho no pendiente: " . $despacho["estado"], "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Solo se pueden aceptar despachos pendientes"]);
        }
        
        // 2b. Solo puede aceptar la sucursal de origen (donde está el stock) o un Transportador/Administrador
        $perfil = isset($_SESSION["perfil"]) ? $_SESSION["perfil"] : '';
        $sucursalActual = ModeloDespachos::mdlObtenerSucursalLocal();
        $sucursalOrigen = isset($despacho["sucursal_origen"]) ? trim($despacho["sucursal_origen"]) : '';
        $esSucursalOrigen = ($sucursalActual !== '' && $sucursalOrigen !== '' && $sucursalActual === $sucursalOrigen);
        $puedeAceptar = ($perfil === "Administrador" || $perfil === "Transportador") || $esSucursalOrigen;
        if (!$puedeAceptar) {
            Logger::warning("Intento de aceptar despacho desde sucursal no autorizada. Actual: $sucursalActual, Origen: $sucursalOrigen", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Solo la sucursal de origen del despacho o un transportador/administrador pueden aceptar este despacho."]);
        }
        
        // 3. Decodificar productos del despacho
        $productosDespacho = json_decode($despacho["productos_despacho"], true);
        
        if(!$productosDespacho || !is_array($productosDespacho)) {
            Logger::error("Error al decodificar productos del despacho", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Error al procesar productos del despacho"]);
        }
        
        Logger::info("Productos del despacho: " . json_encode($productosDespacho), "despachos.ajax.php", "aceptarDespacho");
        
        // 4. Verificar stock local disponible
        foreach($productosDespacho as $producto) {
            $stockDisponible = ModeloDespachos::mdlVerificarStockLocal($producto["codigo"], $producto["cantidad"]);
            Logger::stock("VERIFICAR", $producto["codigo"], $producto["cantidad"], "despachos.ajax.php", "aceptarDespacho");
            
            if(!$stockDisponible) {
                Logger::error("Stock insuficiente para producto: " . $producto["codigo"], "despachos.ajax.php", "aceptarDespacho");
                sendJsonResponse([
                    "success" => false, 
                    "error" => "Stock insuficiente para el producto: " . $producto["codigo"]
                ]);
            }
        }
        
        // 5. Iniciar transacción
        $conexionLocal = Conexion::conectar();
        $conexionCentral = ConexionCentral::conectar();
        
        Logger::transaction("BEGIN", "despachos", [], "despachos.ajax.php", "aceptarDespacho");
        
        $conexionLocal->beginTransaction();
        $conexionCentral->beginTransaction();
        
        try {
            // 6. Descontar stock local
            Logger::info("Iniciando descuento de stock local para " . count($productosDespacho) . " productos", "despachos.ajax.php", "aceptarDespacho");
            $descuentoStock = ModeloDespachos::mdlDescontarStockLocal($productosDespacho);
            Logger::info("Resultado descuento stock local: " . ($descuentoStock ? 'true' : 'false'), "despachos.ajax.php", "aceptarDespacho");
            
            if(!$descuentoStock) {
                Logger::error("Error descontando stock local", "despachos.ajax.php", "aceptarDespacho");
                throw new Exception("Error descontando stock local");
            }
            
            // 7. Actualizar estado del despacho a "en_transito"
            $datosDespacho = array(
                "id" => $idDespacho,
                "estado" => "en_transito",
                "id_transportador" => $_SESSION["id"],
                "nombre_transportador" => $_SESSION["nombre"],
                "fecha_aceptacion" => date("Y-m-d H:i:s")
            );
            
            Logger::transaction("UPDATE", "despachos", $datosDespacho, "despachos.ajax.php", "aceptarDespacho");
            $actualizacionDespacho = ModeloDespachos::mdlActualizarEstadoDespacho("despachos", $datosDespacho);
            Logger::info("Resultado actualización despacho: " . ($actualizacionDespacho ? 'true' : 'false'), "despachos.ajax.php", "aceptarDespacho");
            
            if(!$actualizacionDespacho) {
                Logger::error("Error actualizando estado del despacho", "despachos.ajax.php", "aceptarDespacho");
                throw new Exception("Error actualizando estado del despacho");
            }
            
            // 8. Agregar productos al stock en tránsito con cronología
            Logger::info("Iniciando agregado a stock en tránsito para " . count($productosDespacho) . " productos", "despachos.ajax.php", "aceptarDespacho");
            
            // Obtener el siguiente orden de carga para este transportador
            $stmtOrden = $conexionCentral->prepare("
                SELECT COALESCE(MAX(orden_carga), 0) + 1 as siguiente_orden
                FROM stock_transito 
                WHERE transportador_id = ?
            ");
            $stmtOrden->execute([$_SESSION["id"]]);
            $siguienteOrden = $stmtOrden->fetch()['siguiente_orden'];
            
            // Crear cronología de esta carga (estructura consistente)
            $cronologiaCarga = [
                'fecha' => date('Y-m-d H:i:s'),
                'despacho' => $despacho["numero_despacho"],
                'sucursal_origen' => $despacho["sucursal_origen"],
                'cantidad_agregada' => array_sum(array_column($productosDespacho, 'cantidad')),
                'orden_carga' => $siguienteOrden,
                'productos' => count($productosDespacho)
            ];
            
            foreach($productosDespacho as $producto) {
                Logger::stock("AGREGAR_TRANSITO", $producto["codigo"], $producto["cantidad"], "despachos.ajax.php", "aceptarDespacho");
                
                // Verificar si el producto ya existe en stock_transito
                $stmtCheck = $conexionCentral->prepare("
                    SELECT id, cantidad_disponible, orden_carga, cronologia_carga
                    FROM stock_transito 
                    WHERE codigo_producto = ? AND transportador_id = ?
                    ORDER BY orden_carga DESC
                    LIMIT 1
                ");
                
                $stmtCheck->execute([
                    $producto["codigo"],
                    $_SESSION["id"]
                ]);
                
                $productoExistente = $stmtCheck->fetch();
                
                if($productoExistente) {
                    // Si existe, actualizar cronología y cantidad
                    $cronologiaActual = json_decode($productoExistente['cronologia_carga'], true) ?: [];
                    $cronologiaActual[] = [
                        'fecha' => date('Y-m-d H:i:s'),
                        'despacho' => $despacho["numero_despacho"],
                        'sucursal_origen' => $despacho["sucursal_origen"],
                        'cantidad_agregada' => $producto["cantidad"],
                        'orden_carga' => $siguienteOrden
                    ];
                    
                    $stmtUpdate = $conexionCentral->prepare("
                        UPDATE stock_transito 
                        SET cantidad_disponible = cantidad_disponible + ?,
                            cronologia_carga = ?,
                            fecha_actualizacion = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmtUpdate->execute([
                        $producto["cantidad"],
                        json_encode($cronologiaActual),
                        $productoExistente["id"]
                    ]);
                    
                    Logger::info("✅ Stock actualizado - Código: {$producto['codigo']}, Cantidad agregada: {$producto['cantidad']}, Orden: $siguienteOrden", "despachos.ajax.php", "aceptarDespacho");
                } else {
                    // Si no existe, crear nuevo registro con cronología
                    $stmtInsert = $conexionCentral->prepare("
                        INSERT INTO stock_transito (
                            codigo_producto, 
                            descripcion_producto, 
                            cantidad_disponible, 
                            numero_despacho_origen, 
                            id_despacho_origen,
                            transportador_id, 
                            nombre_transportador,
                            sucursal_origen,
                            orden_carga,
                            cronologia_carga,
                            sucursal_carga,
                            fecha_carga_original,
                            fecha_carga
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    
                    $stmtInsert->execute([
                        $producto["codigo"],
                        $producto["descripcion"],
                        $producto["cantidad"],
                        $despacho["numero_despacho"],
                        $idDespacho,
                        $_SESSION["id"],
                        $_SESSION["nombre"],
                        $despacho["sucursal_origen"],
                        $siguienteOrden,
                        json_encode($cronologiaCarga),
                        $despacho["sucursal_origen"]
                    ]);
                    
                    Logger::info("✅ Nuevo stock creado - Código: {$producto['codigo']}, Cantidad: {$producto['cantidad']}, Despacho: {$despacho['numero_despacho']}, Orden: $siguienteOrden", "despachos.ajax.php", "aceptarDespacho");
                }
            }
            
            // 9. Actualizar el despacho con el transportador_id
            $stmtActualizarDespacho = $conexionCentral->prepare("
                UPDATE despachos 
                SET estado = 'en_transito', 
                    transportador_id = ?, 
                    nombre_transportador = ?
                WHERE id = ?
            ");
            $stmtActualizarDespacho->execute([
                $_SESSION["id"],
                $_SESSION["nombre"] ?? "Transportador",
                $idDespacho
            ]);
            
            Logger::info("✅ Despacho actualizado con transportador_id: " . $_SESSION["id"], "despachos.ajax.php", "aceptarDespacho");
            
            // 10. Confirmar transacciones
            Logger::transaction("COMMIT", "despachos", [], "despachos.ajax.php", "aceptarDespacho");
            $conexionLocal->commit();
            $conexionCentral->commit();
            
            Logger::info("Despacho aceptado exitosamente: ID $idDespacho", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse([
                "success" => true, 
                "message" => "Despacho aceptado correctamente. Stock local descontado y productos agregados al stock en tránsito."
            ]);
            
        } catch(Exception $e) {
            // Rollback en caso de error
            Logger::error("Error en transacción, haciendo rollback: " . $e->getMessage(), "despachos.ajax.php", "aceptarDespacho");
            Logger::transaction("ROLLBACK", "despachos", [], "despachos.ajax.php", "aceptarDespacho");
            $conexionLocal->rollBack();
            $conexionCentral->rollBack();
            throw $e;
        }
        
    } catch(Exception $e) {
        Logger::error("Error general en aceptar despacho: " . $e->getMessage(), "despachos.ajax.php", "aceptarDespacho");
        sendJsonResponse(["success" => false, "error" => $e->getMessage()]);
    }
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
if(isset($_POST["cancelarDespacho"])){

    try {
        $idDespacho = $_POST["cancelarDespacho"];
        $motivoCancelacion = $_POST["motivoCancelacion"] ?? "Sin motivo especificado";
        
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        // Solo se pueden cancelar despachos pendientes
        if($despacho["estado"] != "pendiente") {
            sendJsonResponse(["success" => false, "error" => "Solo se pueden cancelar despachos pendientes. Estado actual: " . $despacho["estado"]]);
        }
        
        // Actualización directa sin usar mdlActualizarDespacho
        try {
            require_once __DIR__ . "/../api-transferencias/conexion-central.php";
            $conexion = ConexionCentral::conectar();
            
            error_log("🔍 CANCELAR DESPACHO - Estado ANTES de actualizar: " . $despacho["estado"]);
            
            // SQL directo para cancelar
            $sql = "UPDATE despachos SET estado = 'cancelado', motivo_cancelacion = ?, usuario_cancelacion = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $resultado = $stmt->execute([$motivoCancelacion, $_SESSION["nombre"] ?? "Usuario", $idDespacho]);
            
            error_log("🔍 CANCELAR DESPACHO - SQL ejecutado: " . $sql);
            error_log("🔍 CANCELAR DESPACHO - Resultado execute: " . ($resultado ? "true" : "false"));
            error_log("🔍 CANCELAR DESPACHO - Filas afectadas: " . $stmt->rowCount());
            
            if($resultado && $stmt->rowCount() > 0) {
                // Verificar directamente en la base de datos
                $stmt = $conexion->query("SELECT estado FROM despachos WHERE id = $idDespacho");
                $estadoActualizado = $stmt->fetchColumn();
                
                error_log("🔍 CANCELAR DESPACHO - Estado DESPUÉS de actualizar: " . $estadoActualizado);
                
                if($estadoActualizado == "cancelado") {
                    sendJsonResponse([
                        "success" => true, 
                        "message" => "Despacho cancelado correctamente",
                        "estado_actualizado" => $estadoActualizado
                    ]);
                } else {
                    sendJsonResponse([
                        "success" => false, 
                        "error" => "El despacho no se canceló correctamente. Estado actual: " . $estadoActualizado
                    ]);
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("❌ CANCELAR DESPACHO - Error SQL: " . print_r($errorInfo, true));
                sendJsonResponse([
                    "success" => false, 
                    "error" => "Error al ejecutar la actualización: " . $errorInfo[2]
                ]);
            }
            
        } catch(Exception $e) {
            error_log("❌ CANCELAR DESPACHO - Excepción: " . $e->getMessage());
            sendJsonResponse([
                "success" => false, 
                "error" => "Error al cancelar el despacho: " . $e->getMessage()
            ]);
        }
        
    } catch(Exception $e) {
        sendJsonResponse(["success" => false, "error" => $e->getMessage()]);
    }
}

/*=============================================
ELIMINAR DESPACHO
=============================================*/
if(isset($_POST["eliminarDespacho"])){

    try {
        $idDespacho = $_POST["eliminarDespacho"];
        
        error_log("🗑️ ELIMINAR DESPACHO - ID recibido: " . $idDespacho);
        error_log("🗑️ ELIMINAR DESPACHO - POST data: " . print_r($_POST, true));
        
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        // Verificar permisos de administrador para eliminar en cualquier estado
        $perfilUsuario = $_SESSION["perfil"] ?? "";
        
        if($perfilUsuario != "Administrador") {
            // Solo administradores pueden eliminar despachos en cualquier estado
            if($despacho["estado"] != "pendiente") {
                sendJsonResponse(["success" => false, "error" => "Solo los administradores pueden eliminar despachos que no estén pendientes"]);
            }
        }
        
        // Log de eliminación para auditoría
        error_log("🗑️ ELIMINAR DESPACHO - Usuario: " . ($_SESSION["nombre"] ?? "Desconocido") . " | Perfil: " . $perfilUsuario . " | Despacho: " . $despacho["numero_despacho"] . " | Estado: " . $despacho["estado"]);
        
        error_log("🗑️ ELIMINAR DESPACHO - Llamando a mdlBorrarDespacho...");
        
        $respuesta = ModeloDespachos::mdlBorrarDespacho("despachos", "id", $idDespacho);
        
        error_log("🗑️ ELIMINAR DESPACHO - Respuesta de mdlBorrarDespacho: " . $respuesta);
        
        if($respuesta == "ok") {
            error_log("✅ ELIMINAR DESPACHO - Eliminación exitosa");
            sendJsonResponse(["success" => true, "message" => "Despacho eliminado correctamente"]);
        } else {
            error_log("❌ ELIMINAR DESPACHO - Error en eliminación: " . $respuesta);
            sendJsonResponse(["success" => false, "error" => "Error al eliminar el despacho: " . $respuesta]);
        }
        
    } catch(Exception $e) {
        sendJsonResponse(["success" => false, "error" => $e->getMessage()]);
    }
}

/*=============================================
MOSTRAR DESPACHOS
=============================================*/
if(isset($_POST["mostrarDespachos"])){

    $respuesta = ControladorDespachos::ctrMostrarDespachos(null, null);
    
    sendJsonResponse($respuesta);
}

/*=============================================
OBTENER PRODUCTOS DEL DESPACHO
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "obtener_productos_despacho"){
    
    try {
        $numeroDespacho = $_POST["numero_despacho"] ?? "";
        
        if(empty($numeroDespacho)) {
            sendJsonResponse(["success" => false, "error" => "Número de despacho requerido"]);
        }
        
        // Obtener despacho por número
        $despacho = ControladorDespachos::ctrMostrarDespachos("numero_despacho", $numeroDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        // Decodificar productos del despacho
        $productos = json_decode($despacho["productos_despacho"], true);
        
        if(!$productos || !is_array($productos)) {
            sendJsonResponse(["success" => false, "error" => "No se pudieron obtener los productos del despacho"]);
        }
        
        sendJsonResponse([
            "success" => true,
            "productos" => $productos,
            "despacho" => [
                "numero_despacho" => $despacho["numero_despacho"],
                "sucursal_origen" => $despacho["sucursal_origen"],
                "fecha_creacion" => $despacho["fecha_creacion"],
                "estado" => $despacho["estado"]
            ]
        ]);
        
    } catch(Exception $e) {
        sendJsonResponse(["success" => false, "error" => $e->getMessage()]);
    }
}

/*=============================================
OBTENER ESTADÍSTICAS PARA TRANSPORTADOR
=============================================*/
if(isset($_POST["accion"]) && $_POST["accion"] == "obtener_estadisticas_transportador"){
    
    try {
        $transportadorId = $_SESSION["id"] ?? 0;
        
        if($transportadorId <= 0) {
            sendJsonResponse(["success" => false, "error" => "ID de transportador no válido"]);
        }
        
        // Obtener estadísticas separadas:
        // 1. Despachos pendientes sin asignar (que puede aceptar)
        // 2. Despachos asignados al transportador (que ya gestiona)
        
        // Despachos pendientes sin asignar
        $stmtPendientes = ConexionCentral::conectar()->prepare("
            SELECT COUNT(*) as cantidad
            FROM despachos 
            WHERE estado = 'pendiente' AND transportador_id IS NULL
        ");
        $stmtPendientes->execute();
        $pendientes = $stmtPendientes->fetch(PDO::FETCH_ASSOC);
        
        // Despachos asignados al transportador
        $stmtAsignados = ConexionCentral::conectar()->prepare("
            SELECT 
                estado,
                COUNT(*) as cantidad
            FROM despachos 
            WHERE transportador_id = ?
            GROUP BY estado
        ");
        $stmtAsignados->execute([$transportadorId]);
        $asignadosRaw = $stmtAsignados->fetchAll(PDO::FETCH_ASSOC);
        
        // Inicializar contadores
        $estadisticas = [
            'pendientes' => (int)$pendientes['cantidad'],
            'en_transito' => 0,
            'entregados' => 0,
            'cancelados' => 0
        ];
        
        // Procesar despachos asignados
        foreach($asignadosRaw as $estadistica) {
            switch($estadistica['estado']) {
                case 'aceptado':
                case 'en_transito':
                    $estadisticas['en_transito'] += (int)$estadistica['cantidad'];
                    break;
                case 'finalizado':
                case 'entregado':
                    $estadisticas['entregados'] += (int)$estadistica['cantidad'];
                    break;
                case 'cancelado':
                    $estadisticas['cancelados'] = (int)$estadistica['cantidad'];
                    break;
            }
        }
        
        sendJsonResponse([
            "success" => true,
            "estadisticas" => $estadisticas
        ]);
        
    } catch(Exception $e) {
        sendJsonResponse(["success" => false, "error" => $e->getMessage()]);
    }
}