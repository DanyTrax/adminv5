<?php
// Incluir directamente el archivo de prueba
$codigo = $_GET["codigo"] ?? "";

// Cambiar al directorio raíz para que los require_once funcionen
chdir("../../");

// Incluir el archivo directamente
include "test-ticket.php";
?>
