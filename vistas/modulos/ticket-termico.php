<?php
// Incluir directamente la extensión TCPDF
$codigo = $_GET["codigo"] ?? "";
$formato = $_GET["formato"] ?? "ticket";

// Cambiar a la carpeta de extensiones para que los require_once funcionen
chdir("../../extensiones/tcpdf/pdf/");

// Incluir el archivo directamente
include "ticket-termico.php";
?>
