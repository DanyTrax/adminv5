<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../controladores/productos.controlador.php";
require_once __DIR__ . "/../modelos/productos.modelo.php";

class TablaProductosCatalogo {

    /*=============================================
    MOSTRAR LA TABLA DE PRODUCTOS PARA CATÁLOGO DE SOLICITUDES
    =============================================*/
    public function mostrarTablaProductosCatalogo(){

        try {
            $productos = ControladorProductos::ctrMostrarProductos(null, null);

            if(!is_array($productos) || count($productos) == 0){
                echo json_encode(["data" => []]);
                return;
            }

            $data = [];

            foreach($productos as $p){

                /*=============================================
                TRAEMOS LA IMAGEN
                =============================================*/
                $imagen = "<div class='text-center'>";
                if(!empty($p["imagen"]) && file_exists($p["imagen"])){
                    $imagen .= "<img src='".$p["imagen"]."' width='40px' class='img-thumbnail'>";
                } else {
                    $imagen .= "<img src='vistas/img/productos/default/anonymous.png' width='40px' class='img-thumbnail'>";
                }
                $imagen .= "</div>";

                /*=============================================
                STOCK CON COLORES (INFORMATIVO ÚNICAMENTE)
                =============================================*/
                $stock = intval($p["stock"]);
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
                $descEsc = htmlspecialchars($p["descripcion"] ?? '', ENT_QUOTES, 'UTF-8');
                $codEsc = htmlspecialchars($p["codigo"] ?? '', ENT_QUOTES, 'UTF-8');
                $boton = "<button class='btn btn-success btn-xs btnAgregarProducto' ".
                        "idProducto='".intval($p["id"])."' ".
                        "codigoProducto='".$codEsc."' ".
                        "descripcionProducto='".$descEsc."' ".
                        "stockProducto='".intval($p["stock"])."' ".
                        "title='Agregar a solicitud'>".
                        "<i class='fa fa-plus'></i> Agregar".
                        "</button>";

                if($stock <= 0) {
                    $boton .= "<br><small class='text-muted'><i class='fa fa-info-circle'></i> Sin stock actual</small>";
                } elseif($stock <= 10) {
                    $boton .= "<br><small class='text-warning'><i class='fa fa-warning'></i> Stock bajo</small>";
                }

                $data[] = [$imagen, $codEsc, $descEsc, $stockBtn, $boton];
            }

            echo json_encode(["data" => $data]);

        } catch (Exception $e) {
            error_log("datatable-productos-catalogo: " . $e->getMessage());
            echo json_encode(["data" => [], "error" => "Error al cargar productos"]);
        }
    }
}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS CATÁLOGO
=============================================*/
$activarProductosCatalogo = new TablaProductosCatalogo();
$activarProductosCatalogo->mostrarTablaProductosCatalogo();