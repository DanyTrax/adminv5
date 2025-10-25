<?php
// Redirigir a la extensión TCPDF
$codigo = $_GET["codigo"] ?? "";
$formato = $_GET["formato"] ?? "ticket";

// Redirigir al archivo de extensión
header("Location: ../../extensiones/tcpdf/pdf/ticket-termico.php?codigo=" . urlencode($codigo) . "&formato=" . urlencode($formato));
exit();
?>
