<?php
require_once "../config.php";

class ControladorTransferencias{

    // Descuenta el stock de la sucursal de origen
    static public function ctrDespacharTransferencia($idTransferencia){
        $urlApi = API_URL . "obtener_items.php?id_transferencia=" . $idTransferencia;
        $respuestaJson = file_get_contents($urlApi);
        $items = json_decode($respuestaJson, true);

        if($items && count($items) > 0){
            foreach ($items as $item) {
                ModeloProductos::mdlActualizarStock("productos", $item['id_producto_origen'], $item['cantidad_enviada']);
            }
            return "ok";
        } else {
            return "error: no se encontraron los productos de la transferencia.";
        }
    }

    // Aumenta el stock en la sucursal de destino
    static public function ctrAgregarStock($idProducto, $cantidad){
        $tabla = "productos";
        return ModeloProductos::mdlAgregarStock($tabla, $idProducto, $cantidad);
    }
        static public function ctrRecibirTransferencia($datos){
        
        $idTransferencia = $datos['idTransferenciaHidden'];
        $cantidadesRecibidas = $datos['cantidadRecibida'];

        // 1. Obtenemos el manifiesto original desde la API Central para saber el ID local de cada producto.
        $urlApi = API_URL . "obtener_items.php?id_transferencia=" . $idTransferencia;
        $respuestaJson = file_get_contents($urlApi);
        $itemsOriginales = json_decode($respuestaJson, true);
        
        if($itemsOriginales && count($itemsOriginales) > 0){
            
            // 2. Recorremos el manifiesto original.
            foreach($itemsOriginales as $item){
                
                $idItemLocal = $item['id_producto_origen'];
                // Obtenemos la cantidad que el usuario reportó como recibida para este item.
                $cantidadARecibir = $cantidadesRecibidas[$item['id']] ?? 0;
                
                // Solo si se recibió una cantidad mayor a cero, la añadimos al stock.
                if($cantidadARecibir > 0){
                     // Llamamos a la función del modelo de productos para SUMAR el stock.
                     ModeloProductos::mdlAgregarStock("productos", $idItemLocal, $cantidadARecibir);
                }
            }

            return "ok";

        } else {
            return "error: no se pudo obtener el manifiesto original de la transferencia.";
        }
    }
    static public function ctrAgregarStockPorNombre($descripcion, $cantidad){
        $tabla = "productos";
        $item = "descripcion"; // Buscamos por nombre
        // Primero, verificamos que el producto exista
        $producto = ModeloProductos::mdlMostrarProductos($tabla, $item, $descripcion, "id");
        if($producto){
            // Si existe, sumamos el stock
            return ModeloProductos::mdlAgregarStock($tabla, $producto["id"], $cantidad);
        } else {
            return "error";
        }
    }
    // En controlador/transferencias.controlador.php
static public function ctrAgregarStockPorId($idProducto, $cantidad){
    $tabla = "productos";
    return ModeloProductos::mdlAgregarStock($tabla, $idProducto, $cantidad);
}
}