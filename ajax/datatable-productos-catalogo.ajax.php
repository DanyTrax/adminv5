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
            STOCK CON COLORES
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
            BOTÓN DE ACCIÓN
            =============================================*/
            if($stock > 0) {
                $boton = "<button class='btn btn-primary btn-xs btnAgregarProducto' ".
                        "idProducto='".$productos[$i]["id"]."' ".
                        "codigoProducto='".$productos[$i]["codigo"]."' ".
                        "descripcionProducto='".htmlspecialchars($productos[$i]["descripcion"], ENT_QUOTES)."' ".
                        "stockProducto='".$productos[$i]["stock"]."' ".
                        "title='Agregar a solicitud'>".
                        "<i class='fa fa-plus'></i> Agregar".
                        "</button>";
            } else {
                $boton = "<button class='btn btn-default btn-xs' disabled title='Sin stock'>".
                        "<i class='fa fa-ban'></i> Sin stock".
                        "</button>";
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