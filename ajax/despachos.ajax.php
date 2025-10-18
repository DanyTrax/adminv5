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
            
            // 8. Agregar productos al stock en tránsito
            Logger::info("Iniciando agregado a stock en tránsito para " . count($productosDespacho) . " productos", "despachos.ajax.php", "aceptarDespacho");
            foreach($productosDespacho as $producto) {
                Logger::stock("AGREGAR_TRANSITO", $producto["codigo"], $producto["cantidad"], "despachos.ajax.php", "aceptarDespacho");
                // Verificar si el producto ya existe en stock_transito
                $stmtCheck = $conexionCentral->prepare("
                    SELECT id, cantidad_disponible 
                    FROM stock_transito 
                    WHERE codigo_producto = ? AND transportador_id = ? AND sucursal_origen = ?
                ");
                
                $stmtCheck->execute([
                    $producto["codigo"],
                    $_SESSION["id"],
                    $despacho["sucursal_origen"]
                ]);
                
                $productoExistente = $stmtCheck->fetch();
                
                if($productoExistente) {
                    // Si existe, sumar a la cantidad existente
                    $stmtUpdate = $conexionCentral->prepare("
                        UPDATE stock_transito 
                        SET cantidad_disponible = cantidad_disponible + ?,
                            fecha_actualizacion = NOW()
                        WHERE id = ?
                    ");
                    
                    $stmtUpdate->execute([
                        $producto["cantidad"],
                        $productoExistente["id"]
                    ]);
                } else {
                    // Si no existe, crear nuevo registro
                    $stmtInsert = $conexionCentral->prepare("
                        INSERT INTO stock_transito (
                            codigo_producto, 
                            descripcion_producto, 
                            cantidad_disponible, 
                            numero_despacho_origen, 
                            transportador_id, 
                            nombre_transportador,
                            sucursal_origen,
                            fecha_carga
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    
                    $stmtInsert->execute([
                        $producto["codigo"],
                        $producto["descripcion"],
                        $producto["cantidad"],
                        $despacho["numero_despacho"],
                        $_SESSION["id"],
                        $_SESSION["nombre"],
                        $despacho["sucursal_origen"]
                    ]);
                }
            }
            
            // 9. Confirmar transacciones
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
        
        // Solo se pueden cancelar despachos pendientes o aceptados
        if(!in_array($despacho["estado"], ["pendiente", "aceptado"])) {
            sendJsonResponse(["success" => false, "error" => "No se puede cancelar un despacho en estado: " . $despacho["estado"]]);
        }
        
        $datos = array(
            "id" => $idDespacho,
            "estado" => "cancelado",
            "motivo_cancelacion" => $motivoCancelacion,
            "fecha_cancelacion" => date("Y-m-d H:i:s")
        );
        
        $respuesta = ModeloDespachos::mdlActualizarDespacho("despachos", $datos, "id", $idDespacho);
        
        if($respuesta == "ok") {
            sendJsonResponse(["success" => true, "message" => "Despacho cancelado correctamente"]);
        } else {
            sendJsonResponse(["success" => false, "error" => "Error al cancelar el despacho"]);
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
        
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        // Solo se pueden eliminar despachos pendientes
        if($despacho["estado"] != "pendiente") {
            sendJsonResponse(["success" => false, "error" => "Solo se pueden eliminar despachos pendientes"]);
        }
        
        $respuesta = ModeloDespachos::mdlBorrarDespacho("despachos", "id", $idDespacho);
        
        if($respuesta == "ok") {
            sendJsonResponse(["success" => true, "message" => "Despacho eliminado correctamente"]);
        } else {
            sendJsonResponse(["success" => false, "error" => "Error al eliminar el despacho"]);
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