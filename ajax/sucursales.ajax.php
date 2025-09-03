<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION)) {
    session_start();
}

require_once "../controladores/sucursales.controlador.php";
require_once "../modelos/sucursales.modelo.php";

class AjaxSucursales {

    // (Aquí irían los métodos que necesites, por ejemplo, para editar, cambiar estado, etc.)
    // El método ajaxTablaProductos es el más importante y se llama desde el JS.
    // Lo mantendremos como en el ejemplo anterior, que ya está corregido.

    public function ajaxTablaProductos() {
        // ... (El código corregido que te proporcioné en el mensaje anterior va aquí) ...
    }

    public function ajaxEditarSucursal() {
        // ... (El código original que tenías para esto) ...
    }
    
    // ... (Otros métodos necesarios como probar conexión, cambiar estado, etc.) ...
}

/*=============================================
ROUTER DE PETICIONES AJAX
=============================================*/
if (isset($_POST["accion"])) {
    $ajax = new AjaxSucursales();

    switch ($_POST["accion"]) {
        case "datatable":
            $ajax->ajaxTablaProductos();
            break;
        
        // Aquí puedes agregar más 'case' para otras acciones AJAX que mantuviste
    }
}

// Lógica para editar (si no usa el router de 'accion')
if (isset($_POST["idSucursal"])) {
    $editar = new AjaxSucursales();
    $editar->idSucursal = $_POST["idSucursal"];
    $editar->ajaxEditarSucursal();
}