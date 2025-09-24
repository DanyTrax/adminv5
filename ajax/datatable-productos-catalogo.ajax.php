<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class TablaProductosCatalogo {

    /*=============================================
    MOSTRAR LA TABLA DE PRODUCTOS PARA CATÁLOGO DE SOLICITUDES
    =============================================*/
    public function mostrarTablaProductosCatalogo(){

        $productos = ControladorProductos::ctrMostrarProductos(null, null);

        if(count($productos) == 0){
            echo '{"data": []}';
            return;
        }

        $datosJson = '{
            "data": [';

        for($i = 0; $i < count($productos); $i++){

            /*=============================================
            TRAEMOS LA IMAGEN
            =============================================*/
            $imagen = "<div class='text-center'>";
            if($productos[$i]["imagen"] != "" && file_exists($productos[$i]["imagen"])){
                $imagen .= "<img src='".$productos[$i]["imagen"]."' width='40px' class='img-thumbnail'>";
            } else {
                $imagen .= "<img src='vistas/img/productos/default/anonymous.png' width='40px' class='img-thumbnail'>";
            }
            $imagen .= "</div>";

            /*=============================================
            STOCK CON COLORES (INFORMATIVO ÚNICAMENTE)
            =============================================*/
            $stock = intval($productos[$i]["stock"]);
            
            if($stock <= 0){
                $stockBtn = "<button class='btn btn-danger btn-xs' disabled>".$stock."</button>";
            } elseif($stock <= 10){
                $stockBtn = "<button class='btn btn-warning btn-xs'>".$stock."</button>";
            } else {
                $stockBtn = "<button class='btn btn-success btn-xs'>".$stock."</button>";
            }

            /*=============================================
            BOTÓN DE ACCIÓN - SIEMPRE HABILITADO (SIN IMPORTAR STOCK)
            =============================================*/
            // ✅ SIEMPRE MOSTRAR BOTÓN AGREGAR - NO IMPORTA EL STOCK
            $boton = "<button class='btn btn-success btn-xs btnAgregarProducto' ".
                    "idProducto='".$productos[$i]["id"]."' ".
                    "codigoProducto='".$productos[$i]["codigo"]."' ".
                    "descripcionProducto='".htmlspecialchars($productos[$i]["descripcion"], ENT_QUOTES)."' ".
                    "stockProducto='".$productos[$i]["stock"]."' ".
                    "title='Agregar a solicitud (sin restricción de stock)'>".
                    "<i class='fa fa-plus'></i> Agregar".
                    "</button>";

            // ✅ OPCIONAL: Agregar indicador visual de stock
            if($stock <= 0) {
                $boton .= "<br><small class='text-muted'><i class='fa fa-info-circle'></i> Sin stock actual</small>";
            } elseif($stock <= 10) {
                $boton .= "<br><small class='text-warning'><i class='fa fa-warning'></i> Stock bajo</small>";
            }

            $datosJson .='[
                "'.$imagen.'",
                "'.$productos[$i]["codigo"].'",
                "'.htmlspecialchars($productos[$i]["descripcion"], ENT_QUOTES).'",
                "'.$stockBtn.'",
                "'.$boton.'"
            ],';
        }

        $datosJson = substr($datosJson, 0, -1);
        $datosJson .= '] }';
        
        echo $datosJson;
    }
}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS CATÁLOGO
=============================================*/
$activarProductosCatalogo = new TablaProductosCatalogo();
$activarProductosCatalogo->mostrarTablaProductosCatalogo();