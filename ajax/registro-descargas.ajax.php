<?php

session_start();

require_once "../controladores/registro-descargas.controlador.php";

// Si la petición es para borrar todos los registros de descargas
if(isset($_POST["borrarTodosRegistrosDescargas"])){
    ControladorRegistroDescargas::ctrBorrarTodosRegistrosDescargas();
    exit;
}

// Si la petición es para borrar un registro por ID (solo Admin)
if(isset($_POST["eliminarRegistroDescarga"])){
    header('Content-Type: application/json; charset=utf-8');
    ControladorRegistroDescargas::ctrBorrarRegistroDescarga();
    exit;
}

?>
