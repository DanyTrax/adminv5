<?php

session_start();

require_once "../controladores/registro-descargas.controlador.php";

// Si la petición es para borrar todos los registros de descargas
if(isset($_POST["borrarTodosRegistrosDescargas"])){
    ControladorRegistroDescargas::ctrBorrarTodosRegistrosDescargas();
}

?>
