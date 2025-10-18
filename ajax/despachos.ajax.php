<?php

session_start();

require_once "../modelos/conexion.php";
require_once "../api-transferencias/conexion-central.php";
require_once "../controladores/despachos.controlador.php";
require_once "../modelos/despachos.modelo.php";
require_once "../modelos/productos.modelo.php";

class AjaxDespachos {

    /*=============================================
    MOSTRAR DESPACHO INDIVIDUAL
    =============================================*/
    public $idDespacho;

    public function ajaxVerDespacho(){
        
        $item = "id";
        $valor = $this->idDespacho;
        
        $respuesta = ControladorDespachos::ctrMostrarDespachos($item, $valor);
        
        echo json_encode($respuesta);
    }

    /*=============================================
    ACEPTAR DESPACHO - CON MANEJO DE STOCK
    =============================================*/
    public function ajaxAceptarDespacho(){
        
        try {
            // 1. Obtener datos del despacho
            $despacho = ControladorDespachos::ctrMostrarDespachos("id", $this->idDespacho);
            
            if(!$despacho) {
                echo json_encode(["success" => false, "error" => "Despacho no encontrado"]);
                return;
            }
            
            // 2. Verificar que esté en estado pendiente
            if($despacho["estado"] != "pendiente") {
                echo json_encode(["success" => false, "error" => "Solo se pueden aceptar despachos pendientes"]);
                return;
            }
            
            // 3. Decodificar productos del despacho
            $productosDespacho = json_decode($despacho["productos_despacho"], true);
            
            if(!$productosDespacho || !is_array($productosDespacho)) {
                echo json_encode(["success" => false, "error" => "Error al procesar productos del despacho"]);
                return;
            }
            
            // 4. Verificar stock local disponible
            foreach($productosDespacho as $producto) {
                $stockDisponible = ModeloDespachos::mdlVerificarStockLocal($producto["codigo"], $producto["cantidad"]);
                if(!$stockDisponible) {
                    echo json_encode([
                        "success" => false, 
                        "error" => "Stock insuficiente para el producto: " . $producto["codigo"]
                    ]);
                    return;
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
                    "id" => $this->idDespacho,
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
                
                echo json_encode([
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
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }

    /*=============================================
    CANCELAR DESPACHO
    =============================================*/
    public function ajaxCancelarDespacho(){
        
        try {
            $despacho = ControladorDespachos::ctrMostrarDespachos("id", $this->idDespacho);
            
            if(!$despacho) {
                echo json_encode(["success" => false, "error" => "Despacho no encontrado"]);
                return;
            }
            
            // Solo se pueden cancelar despachos pendientes o aceptados
            if(!in_array($despacho["estado"], ["pendiente", "aceptado"])) {
                echo json_encode(["success" => false, "error" => "No se puede cancelar un despacho en estado: " . $despacho["estado"]]);
                return;
            }
            
            $datos = array(
                "id" => $this->idDespacho,
                "estado" => "cancelado",
                "motivo_cancelacion" => $this->motivoCancelacion ?? "Sin motivo especificado",
                "fecha_cancelacion" => date("Y-m-d H:i:s")
            );
            
            $respuesta = ModeloDespachos::mdlActualizarDespacho("despachos", $datos, "id", $this->idDespacho);
            
            if($respuesta == "ok") {
                echo json_encode(["success" => true, "message" => "Despacho cancelado correctamente"]);
            } else {
                echo json_encode(["success" => false, "error" => "Error al cancelar el despacho"]);
            }
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }

    /*=============================================
    ELIMINAR DESPACHO
    =============================================*/
    public function ajaxEliminarDespacho(){
        
        try {
            $despacho = ControladorDespachos::ctrMostrarDespachos("id", $this->idDespacho);
            
            if(!$despacho) {
                echo json_encode(["success" => false, "error" => "Despacho no encontrado"]);
                return;
            }
            
            // Solo se pueden eliminar despachos pendientes
            if($despacho["estado"] != "pendiente") {
                echo json_encode(["success" => false, "error" => "Solo se pueden eliminar despachos pendientes"]);
                return;
            }
            
            $respuesta = ModeloDespachos::mdlBorrarDespacho("despachos", "id", $this->idDespacho);
            
            if($respuesta == "ok") {
                echo json_encode(["success" => true, "message" => "Despacho eliminado correctamente"]);
            } else {
                echo json_encode(["success" => false, "error" => "Error al eliminar el despacho"]);
            }
            
        } catch(Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
    }

    /*=============================================
    MOSTRAR DESPACHOS
    =============================================*/
    public function ajaxMostrarDespachos(){
        
        $respuesta = ControladorDespachos::ctrMostrarDespachos(null, null);
        
        echo json_encode($respuesta);
    }
}

/*=============================================
VER DESPACHO
=============================================*/
if(isset($_POST["idDespacho"])){

    $verDespacho = new AjaxDespachos();
    $verDespacho -> idDespacho = $_POST["idDespacho"];
    $verDespacho -> ajaxVerDespacho();
}

/*=============================================
ACEPTAR DESPACHO
=============================================*/
if(isset($_POST["aceptarDespacho"])){

    $aceptarDespacho = new AjaxDespachos();
    $aceptarDespacho -> idDespacho = $_POST["aceptarDespacho"];
    $aceptarDespacho -> ajaxAceptarDespacho();
}

/*=============================================
CANCELAR DESPACHO
=============================================*/
if(isset($_POST["cancelarDespacho"])){

    $cancelarDespacho = new AjaxDespachos();
    $cancelarDespacho -> idDespacho = $_POST["cancelarDespacho"];
    $cancelarDespacho -> motivoCancelacion = $_POST["motivoCancelacion"];
    $cancelarDespacho -> ajaxCancelarDespacho();
}

/*=============================================
ELIMINAR DESPACHO
=============================================*/
if(isset($_POST["eliminarDespacho"])){

    $eliminarDespacho = new AjaxDespachos();
    $eliminarDespacho -> idDespacho = $_POST["eliminarDespacho"];
    $eliminarDespacho -> ajaxEliminarDespacho();
}

/*=============================================
MOSTRAR DESPACHOS
=============================================*/
if(isset($_POST["mostrarDespachos"])){

    $mostrarDespachos = new AjaxDespachos();
    $mostrarDespachos -> ajaxMostrarDespachos();
}