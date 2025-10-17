<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

class TablaProductosTransferencia{

  public function mostrarTabla(){
    $item = null;
    $valor = null;
    $orden = "id";
    $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

    // DataTables espera un objeto JSON con una clave "data"
    echo '{"data": '.json_encode($respuesta).'}';
  }
}

// Activamos la tabla sin verificar $_GET o $_POST
$activar = new TablaProductosTransferencia();
$activar -> mostrarTabla();