<?php
// Redirigir a la extensión TCPDF
$codigo = $_GET["codigo"] ?? "";
$formato = $_GET["formato"] ?? "factura";

// Redirigir al archivo de extensión
header("Location: ../../extensiones/tcpdf/pdf/factura-inteligente.php?codigo=" . urlencode($codigo) . "&formato=" . urlencode($formato));
exit();
?>
