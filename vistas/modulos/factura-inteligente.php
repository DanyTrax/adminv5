<?php
// Incluir directamente la extensión TCPDF
$codigo = $_GET["codigo"] ?? "";
$formato = $_GET["formato"] ?? "factura";

// Cambiar a la carpeta de extensiones para que los require_once funcionen
chdir("../../extensiones/tcpdf/pdf/");

// Incluir el archivo directamente
include "factura-inteligente.php";
?>
