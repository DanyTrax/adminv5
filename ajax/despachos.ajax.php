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

require_once "modelos/conexion.php";
require_once "api-transferencias/conexion-central.php";
require_once "controladores/despachos.controlador.php";
require_once "modelos/despachos.modelo.php";
require_once "modelos/productos.modelo.php";

// Función helper para enviar JSON limpio
function sendJsonResponse($data) {
    ob_clean(); // Limpiar buffer de warnings
    
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
    
    sendJsonResponse($respuesta);
}

/*=============================================
ACEPTAR DESPACHO - CON MANEJO DE STOCK
=============================================*/
if(isset($_POST["aceptarDespacho"])){

    try {
        $idDespacho = $_POST["aceptarDespacho"];
        
        // 1. Obtener datos del despacho
        $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
        
        if(!$despacho) {
            sendJsonResponse(["success" => false, "error" => "Despacho no encontrado"]);
        }
        
        // 2. Verificar que esté en estado pendiente
        if($despacho["estado"] != "pendiente") {
            sendJsonResponse(["success" => false, "error" => "Solo se pueden aceptar despachos pendientes"]);
        }
        
        // 3. Decodificar productos del despacho
        $productosDespacho = json_decode($despacho["productos_despacho"], true);
        
        if(!$productosDespacho || !is_array($productosDespacho)) {
            sendJsonResponse(["success" => false, "error" => "Error al procesar productos del despacho"]);
        }
        
        // 4. Verificar stock local disponible
        foreach($productosDespacho as $producto) {
            $stockDisponible = ModeloDespachos::mdlVerificarStockLocal($producto["codigo"], $producto["cantidad"]);
            if(!$stockDisponible) {
                sendJsonResponse([
                    "success" => false, 
                    "error" => "Stock insuficiente para el producto: " . $producto["codigo"]
                ]);
            }
        }
        
        // 5. Iniciar transacción
        $conexionLocal = Conexion::conectar();
        $conexionCentral = ConexionCentral::conectar();
        
        $conexionLocal->beginTransaction();
        $conexionCentral->beginTransaction();
        
        try {
            // 6. Descontar stock local
            $descuentoStock = ModeloDespachos::mdlDescontarStockLocal($productosDespacho);
            if(!$descuentoStock) {
                throw new Exception("Error descontando stock local");
            }
            
            // 7. Actualizar estado del despacho a "aceptado"
            $datosDespacho = array(
                "id" => $idDespacho,
                "estado" => "aceptado",
                "id_transportador" => $_SESSION["id"],
                "nombre_transportador" => $_SESSION["nombre"],
                "fecha_aceptacion" => date("Y-m-d H:i:s")
            );
            
            $actualizacionDespacho = ModeloDespachos::mdlActualizarEstadoDespacho("despachos", $datosDespacho);
            if(!$actualizacionDespacho) {
                throw new Exception("Error actualizando estado del despacho");
            }
            
            // 8. Agregar productos al stock en tránsito
            foreach($productosDespacho as $producto) {
                $stmt = $conexionCentral->prepare("
                    INSERT INTO stock_transito (
                        codigo_producto, 
                        descripcion_producto, 
                        cantidad_disponible, 
                        numero_despacho_origen, 
                        transportador_id, 
                        nombre_transportador,
                        sucursal_origen,
                        fecha_creacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $producto["codigo"],
                    $producto["descripcion"],
                    $producto["cantidad"],
                    $despacho["numero_despacho"],
                    $_SESSION["id"],
                    $_SESSION["nombre"],
                    $despacho["sucursal_origen"]
                ]);
            }
            
            // 9. Confirmar transacciones
            $conexionLocal->commit();
            $conexionCentral->commit();
            
            sendJsonResponse([
                "success" => true, 
                "message" => "Despacho aceptado correctamente. Stock local descontado y productos agregados al stock en tránsito."
            ]);
            
        } catch(Exception $e) {
            // Rollback en caso de error
            $conexionLocal->rollBack();
            $conexionCentral->rollBack();
            throw $e;
        }
        
    } catch(Exception $e) {
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