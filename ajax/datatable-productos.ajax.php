<?php

session_start();

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class TablaProductos{

    public function mostrarTablaProductos(){

        $item = null;
        $valor = null;
        $orden = "id";

        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);	

        if(count($productos) == 0){
            echo '{"data": []}';
            return;
        }
        
        $datosJsonArray = array();

        for($i = 0; $i < count($productos); $i++){
            
            $imagen = "<img src='".$productos[$i]["imagen"]."' width='40px'>";

            $item = "id";
            $valor = $productos[$i]["id_categoria"];
            $categorias = ControladorCategorias::ctrMostrarCategorias($item, $valor);

            if($productos[$i]["stock"] <= 10){
                $stock = "<button class='btn btn-danger'>".$productos[$i]["stock"]."</button>";
            } else if($productos[$i]["stock"] > 11 && $productos[$i]["stock"] <= 15){
                $stock = "<button class='btn btn-warning'>".$productos[$i]["stock"]."</button>";
            } else {
                $stock = "<button class='btn btn-success'>".$productos[$i]["stock"]."</button>";
            }
            
            $botones = "<div class='btn-group'>";
            if(isset($_SESSION["perfil"])){
                if($_SESSION["perfil"] == "Administrador"){
                    $botones .= "<button class='btn btn-warning btnEditarProducto' idProducto='".$productos[$i]["id"]."' data-toggle='modal' data-target='#modalEditarProducto'><i class='fa fa-pencil'></i></button>";
                }
                if(($_SESSION["perfil"] == "Administrador" || $_SESSION["perfil"] == "Vendedor") && $productos[$i]["es_divisible"] == 1){
                    $botones .= "<button class='btn btn-info btnDividirProducto' idProducto='".$productos[$i]["id"]."'><i class='fa fa-pie-chart'></i></button>";
                }
                if($_SESSION["perfil"] == "Administrador"){
                    $botones .= "<button class='btn btn-danger btnEliminarProducto' idProducto='".$productos[$i]["id"]."' codigo='".$productos[$i]["codigo"]."' imagen='".$productos[$i]["imagen"]."'><i class='fa fa-times'></i></button>";
                }
            }
            $botones .= "</div>";

            $datosJsonArray[] = array(
                ($i + 1),
                $imagen,
                $productos[$i]["codigo"],
                $productos[$i]["descripcion"],
                $categorias["categoria"],
                $stock,
                $productos[$i]["precio_venta"],
                $productos[$i]["fecha"],
                $botones
            );
        }

        $respuestaFinal = array("data" => $datosJsonArray);
        echo json_encode($respuestaFinal);
    }
}

$activarProductos = new TablaProductos();
$activarProductos -> mostrarTablaProductos();