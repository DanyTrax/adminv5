<?php
/**
 * Test directo del AJAX de Stock en Tránsito
 */

// Simular sesión
session_start();
$_SESSION["perfil"] = "Administrador";
$_SESSION["id"] = 1;
$_SESSION["nombre"] = "Administrador";

// Incluir el archivo AJAX
require_once "ajax/datatable-stock-transito.ajax.php";
?>
