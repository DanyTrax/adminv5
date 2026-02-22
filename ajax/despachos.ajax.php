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

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../controladores/despachos.controlador.php";
require_once __DIR__ . "/../modelos/despachos.modelo.php";
require_once __DIR__ . "/../modelos/sucursales.modelo.php";
require_once __DIR__ . "/../modelos/solicitudes-stock.modelo.php";
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
VER DESPACHO (solo cuando NO es cambiarEstado - evitar conflicto)
=============================================*/
if(isset($_POST["idDespacho"]) && !isset($_POST["cambiarEstado"])){

    $item = "id";
    $valor = $_POST["idDespacho"];
    
    $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
    
    if($respuesta) {
        // Enriquecer con numero_solicitud si viene de solicitud de stock
        if(!empty($respuesta["id_solicitud_origen"])) {
            try {
                $stmt = ConexionCentral::conectar()->prepare("SELECT numero_solicitud FROM solicitudes_stock WHERE id = ?");
                $stmt->execute([$respuesta["id_solicitud_origen"]]);
                $respuesta["numero_solicitud"] = $stmt->fetchColumn() ?: null;
            } catch(Exception $e) {
                $respuesta["numero_solicitud"] = null;
            }
        } else {
            $respuesta["numero_solicitud"] = null;
        }
        // Obtener historial del despacho
        $respuesta["historial"] = ModeloDespachos::mdlObtenerHistorialDespacho($respuesta["id"]);
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
        
        // 2b. Solo Administrador y Transportador pueden aceptar
        $perfil = isset($_SESSION["perfil"]) ? $_SESSION["perfil"] : '';
        $puedeAceptar = ($perfil === "Administrador" || $perfil === "Transportador");
        if (!$puedeAceptar) {
            Logger::warning("Intento de aceptar despacho sin permiso. Perfil: $perfil", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Solo el perfil Administrador o Transportador puede aceptar despachos."]);
        }
        
        // 3. Decodificar productos del despacho
        $productosDespacho = json_decode($despacho["productos_despacho"], true);
        
        if(!$productosDespacho || !is_array($productosDespacho)) {
            Logger::error("Error al decodificar productos del despacho", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Error al procesar productos del despacho"]);
        }
        
        Logger::info("Productos del despacho: " . json_encode($productosDespacho), "despachos.ajax.php", "aceptarDespacho");
        
        // 4. Obtener conexión a la sucursal que despacha (sucursal_origen) - NO la del transportador actual
        $sucursalOrigen = $despacho["sucursal_origen"] ?? '';
        $conexionSucursalOrigen = null;
        
        if (empty($sucursalOrigen)) {
            Logger::error("Despacho sin sucursal_origen", "despachos.ajax.php", "aceptarDespacho");
            sendJsonResponse(["success" => false, "error" => "Despacho sin sucursal de origen"]);
        }
        
        $nombreSucursalActual = defined('NOMBRE_SUCURSAL') ? NOMBRE_SUCURSAL : '';
        if ($sucursalOrigen === $nombreSucursalActual) {
            $conexionSucursalOrigen = Conexion::conectar();
            Logger::info("Usando BD local (sucursal_origen = sucursal actual)", "despachos.ajax.php", "aceptarDespacho");
        } else {
            $sucursalConfig = ModeloSucursales::mdlObtenerSucursalPorNombre($sucursalOrigen);
            if (!$sucursalConfig || empty($sucursalConfig['host_bd']) || empty($sucursalConfig['nombre_bd'])) {
                Logger::error("No se pudo conectar a sucursal origen: $sucursalOrigen", "despachos.ajax.php", "aceptarDespacho");
                sendJsonResponse(["success" => false, "error" => "No se pudo conectar a la sucursal que despacha ($sucursalOrigen)"]);
            }
            try {
                $puerto = $sucursalConfig['puerto_bd'] ?? 3306;
                $dsn = "mysql:host={$sucursalConfig['host_bd']};dbname={$sucursalConfig['nombre_bd']};port=$puerto;charset=utf8mb4";
                $conexionSucursalOrigen = new PDO($dsn, $sucursalConfig['usuario_bd'], $sucursalConfig['password_bd']);
                $conexionSucursalOrigen->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                Logger::info("Conectado a BD de sucursal origen: $sucursalOrigen", "despachos.ajax.php", "aceptarDespacho");
            } catch (Exception $e) {
                Logger::error("Error conectando a $sucursalOrigen: " . $e->getMessage(), "despachos.ajax.php", "aceptarDespacho");
                sendJsonResponse(["success" => false, "error" => "No se pudo conectar a la sucursal $sucursalOrigen"]);
            }
        }
        
        // 5. Verificar stock en la sucursal que despacha
        foreach($productosDespacho as $producto) {
            $stockDisponible = ModeloDespachos::mdlVerificarStockEnSucursal($conexionSucursalOrigen, $producto["codigo"], $producto["cantidad"]);
            Logger::stock("VERIFICAR", $producto["codigo"], $producto["cantidad"], "despachos.ajax.php", "aceptarDespacho");
            
            if(!$stockDisponible) {
                Logger::error("Stock insuficiente para producto: " . $producto["codigo"] . " en $sucursalOrigen", "despachos.ajax.php", "aceptarDespacho");
                sendJsonResponse([
                    "success" => false, 
                    "error" => "Stock insuficiente para el producto " . $producto["codigo"] . " en la sucursal $sucursalOrigen"
                ]);
            }
        }
        
        // 6. Iniciar transacciones
        $conexionCentral = ConexionCentral::conectar();
        
        Logger::transaction("BEGIN", "despachos", [], "despachos.ajax.php", "aceptarDespacho");
        
        $conexionSucursalOrigen->beginTransaction();
        $conexionCentral->beginTransaction();
        
        try {
            // 7. Descontar stock en la sucursal que despacha (sucursal_origen)
            Logger::info("Descontando stock en sucursal origen ($sucursalOrigen) para " . count($productosDespacho) . " productos", "despachos.ajax.php", "aceptarDespacho");
            $descuentoStock = ModeloDespachos::mdlDescontarStockEnSucursal($conexionSucursalOrigen, $productosDespacho);
            Logger::info("Resultado descuento stock local: " . ($descuentoStock ? 'true' : 'false'), "despachos.ajax.php", "aceptarDespacho");
            
            if(!$descuentoStock) {
                Logger::error("Error descontando stock en sucursal origen: $sucursalOrigen", "despachos.ajax.php", "aceptarDespacho");
                throw new Exception("Error descontando stock en la sucursal $sucursalOrigen");
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
            
            // 8. Agregar productos al stock en tránsito - UN REGISTRO POR PRODUCTO POR DESPACHO (trazabilidad)
            Logger::info("Iniciando agregado a stock en tránsito para " . count($productosDespacho) . " productos", "despachos.ajax.php", "aceptarDespacho");
            
            $idSolicitudOrigen = !empty($despacho["id_solicitud_origen"]) ? (int)$despacho["id_solicitud_origen"] : null;
            $numeroSolicitud = null;
            if ($idSolicitudOrigen) {
                $stmtNum = $conexionCentral->prepare("SELECT numero_solicitud FROM solicitudes_stock WHERE id = ?");
                $stmtNum->execute([$idSolicitudOrigen]);
                $numeroSolicitud = $stmtNum->fetchColumn() ?: null;
            }
            
            $sucursalOrigenDespacho = $despacho["sucursal_origen"] ?? $despacho["nombre_sucursal_origen"] ?? '';
            
            foreach($productosDespacho as $producto) {
                Logger::stock("AGREGAR_TRANSITO", $producto["codigo"], $producto["cantidad"], "despachos.ajax.php", "aceptarDespacho");
                
                $descripcion = $producto["descripcion"] ?? $producto["descripcion_producto"] ?? "";
                $obs = "Despacho: " . $despacho["numero_despacho"] . " | Origen: " . $sucursalOrigenDespacho;
                
                try {
                    // INSERT con columnas de trazabilidad (id_solicitud_origen, numero_solicitud)
                    $stmtInsert = $conexionCentral->prepare("
                        INSERT INTO stock_transito (
                            codigo_producto, descripcion_producto, cantidad_disponible,
                            transportador_id, nombre_transportador, sucursal_origen,
                            numero_despacho_origen, id_despacho_origen,
                            id_solicitud_origen, numero_solicitud,
                            observaciones, fecha_carga
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtInsert->execute([
                        $producto["codigo"],
                        $descripcion,
                        (int)$producto["cantidad"],
                        $_SESSION["id"],
                        $_SESSION["nombre"] ?? "Transportador",
                        $sucursalOrigenDespacho,
                        $despacho["numero_despacho"],
                        $idDespacho,
                        $idSolicitudOrigen,
                        $numeroSolicitud,
                        $obs
                    ]);
                } catch (Exception $e) {
                    // Fallback: si columnas no existen, usar INSERT mínimo
                    if (strpos($e->getMessage(), 'Unknown column') !== false) {
                        $stmtInsert = $conexionCentral->prepare("
                            INSERT INTO stock_transito (
                                codigo_producto, descripcion_producto, cantidad_disponible,
                                transportador_id, nombre_transportador, sucursal_origen,
                                numero_despacho_origen, id_despacho_origen,
                                observaciones
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtInsert->execute([
                            $producto["codigo"],
                            $descripcion,
                            (int)$producto["cantidad"],
                            $_SESSION["id"],
                            $_SESSION["nombre"] ?? "Transportador",
                            $sucursalOrigenDespacho,
                            $despacho["numero_despacho"],
                            $idDespacho,
                            $obs
                        ]);
                    } else {
                        throw $e;
                    }
                }
                
                Logger::info("✅ Stock tránsito creado - Código: {$producto['codigo']}, Despacho: {$idDespacho}", "despachos.ajax.php", "aceptarDespacho");
            }
            
            // Verificar si la solicitud debe pasar a finalizado (todos los productos ya despachados)
            if ($idSolicitudOrigen) {
                ModeloSolicitudesStock::mdlVerificarYFinalizarSolicitud($idSolicitudOrigen, $idDespacho, $conexionCentral);
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
            
            // Registrar en historial
            ModeloDespachos::mdlRegistrarHistorialDespacho(
                $idDespacho, $despacho["numero_despacho"],
                "aceptado", "pendiente", "en_transito",
                "Despacho aceptado. Transportador: " . ($_SESSION["nombre"] ?? "Transportador")
            );
            
            Logger::info("✅ Despacho actualizado con transportador_id: " . $_SESSION["id"], "despachos.ajax.php", "aceptarDespacho");
            
            // 10. Confirmar transacciones
            Logger::transaction("COMMIT", "despachos", [], "despachos.ajax.php", "aceptarDespacho");
            $conexionSucursalOrigen->commit();
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
            $conexionSucursalOrigen->rollBack();
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
            
            if($resultado && $stmt->rowCount() > 0) {
                ModeloDespachos::mdlRegistrarHistorialDespacho(
                    $idDespacho, $despacho["numero_despacho"],
                    "cancelado", "pendiente", "cancelado",
                    $motivoCancelacion
                );
            }
            
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
CAMBIAR ESTADO DE DESPACHO (SOLO ADMIN - CUALQUIER ESTADO)
=============================================*/
if(isset($_POST["cambiarEstado"])){

    try {
        $perfilUsuario = $_SESSION["perfil"] ?? "";
        
        if($perfilUsuario != "Administrador") {
            sendJsonResponse(["success" => false, "error" => "Solo el perfil Administrador puede cambiar el estado de un despacho en cualquier momento"]);
        }

        $idDespacho = $_POST["idDespacho"] ?? null;
        $nuevoEstado = $_POST["nuevoEstado"] ?? null;
        $observaciones = $_POST["observaciones"] ?? "";
        
        if(empty($idDespacho) || empty($nuevoEstado)) {
            sendJsonResponse(["success" => false, "error" => "Datos incompletos: idDespacho y nuevoEstado son requeridos"]);
        }

        $estadosPermitidos = ["pendiente", "aceptado", "en_transito", "entregado", "finalizado", "cancelado"];
        if(!in_array($nuevoEstado, $estadosPermitidos)) {
            sendJsonResponse(["success" => false, "error" => "Estado inválido. Permitidos: pendiente, aceptado, en_transito, entregado, finalizado, cancelado"]);
        }

        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }

        // Normalizar entregado/finalizado
        if($nuevoEstado == "finalizado") {
            $nuevoEstado = "entregado";
        }

        $datos = ["estado" => $nuevoEstado];

        if($nuevoEstado == "cancelado" && !empty($observaciones)) {
            $datos["motivo_cancelacion"] = $observaciones;
            $datos["usuario_cancelacion"] = $_SESSION["nombre"] ?? "Administrador";
        } elseif(!empty($observaciones)) {
            $datos["observaciones"] = $observaciones;
        }

        $respuesta = ModeloDespachos::mdlActualizarDespacho("despachos", $datos, "id", $idDespacho);

        if($respuesta == "ok") {
            ModeloDespachos::mdlRegistrarHistorialDespacho(
                $idDespacho, $despacho["numero_despacho"],
                "cambio_estado", $despacho["estado"], $nuevoEstado,
                $observaciones
            );
            sendJsonResponse([
                "success" => true,
                "message" => "Estado del despacho actualizado correctamente",
                "nuevoEstado" => $nuevoEstado
            ]);
        } else {
            sendJsonResponse(["success" => false, "error" => "Error al actualizar el estado: " . $respuesta]);
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