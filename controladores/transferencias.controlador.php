<?php
require_once "../config.php";

class ControladorTransferencias{

    // ✅ MÉTODO MODIFICADO: Descuenta el stock usando CÓDIGO del producto
    static public function ctrDespacharTransferencia($idTransferencia){
        $urlApi = API_URL . "obtener_items.php?id_transferencia=" . $idTransferencia;
        $respuestaJson = file_get_contents($urlApi);
        $items = json_decode($respuestaJson, true);

        if($items && count($items) > 0){
            foreach ($items as $item) {
                // ✅ CAMBIO CLAVE: Buscar producto por CÓDIGO en lugar de ID
                if (!empty($item['codigo_producto'])) {
                    // Buscar el producto local por su código
                    $productoLocal = ModeloProductos::mdlMostrarProductos("productos", "codigo", $item['codigo_producto'], "id");
                    
                    if ($productoLocal) {
                        // Descontar stock usando el ID local encontrado
                        ModeloProductos::mdlActualizarStock("productos", $productoLocal['id'], $item['cantidad_enviada']);
                    } else {
                        // Log de error: producto no encontrado localmente
                        error_log("Producto con código {$item['codigo_producto']} no encontrado en sucursal local");
                    }
                } else {
                    // Fallback: usar el método anterior si no hay código (compatibilidad)
                    ModeloProductos::mdlActualizarStock("productos", $item['id_producto_origen'], $item['cantidad_enviada']);
                }
            }
            return "ok";
        } else {
            return "error: no se encontraron los productos de la transferencia.";
        }
    }

    // ✅ MÉTODO MEJORADO: Recibir transferencia usando código
    static public function ctrRecibirTransferencia($datos){
        
        $idTransferencia = $datos['idTransferenciaHidden'];
        $cantidadesRecibidas = $datos['cantidadRecibida'];

        // 1. Obtenemos el manifiesto desde la API Central
        $urlApi = API_URL . "obtener_items.php?id_transferencia=" . $idTransferencia;
        $respuestaJson = file_get_contents($urlApi);
        $itemsOriginales = json_decode($respuestaJson, true);
        
        if($itemsOriginales && count($itemsOriginales) > 0){
            
            // 2. Recorremos el manifiesto original
            foreach($itemsOriginales as $item){
                
                $cantidadARecibir = $cantidadesRecibidas[$item['id']] ?? 0;
                
                if($cantidadARecibir > 0){
                    // ✅ CAMBIO CLAVE: Buscar producto local por CÓDIGO
                    if (!empty($item['codigo_producto'])) {
                        $productoLocal = ModeloProductos::mdlMostrarProductos("productos", "codigo", $item['codigo_producto'], "id");
                        
                        if ($productoLocal) {
                            // Agregar stock usando el ID local
                            ModeloProductos::mdlAgregarStock("productos", $productoLocal['id'], $cantidadARecibir);
                        } else {
                            error_log("Producto con código {$item['codigo_producto']} no encontrado para recepción");
                        }
                    } else {
                        // Fallback: usar método anterior
                        ModeloProductos::mdlAgregarStock("productos", $item['id_producto_origen'], $cantidadARecibir);
                    }
                }
            }

            return "ok";

        } else {
            return "error: no se pudo obtener el manifiesto original de la transferencia.";
        }
    }

    // Mantener métodos existentes...
    static public function ctrAgregarStock($idProducto, $cantidad){
        $tabla = "productos";
        return ModeloProductos::mdlAgregarStock($tabla, $idProducto, $cantidad);
    }
    
    static public function ctrAgregarStockPorNombre($descripcion, $cantidad){
        $tabla = "productos";
        $item = "descripcion";
        $producto = ModeloProductos::mdlMostrarProductos($tabla, $item, $descripcion, "id");
        if($producto){
            return ModeloProductos::mdlAgregarStock($tabla, $producto["id"], $cantidad);
        } else {
            return "error";
        }
    }
    
    static public function ctrAgregarStockPorId($idProducto, $cantidad){
        $tabla = "productos";
        return ModeloProductos::mdlAgregarStock($tabla, $idProducto, $cantidad);
    }
}