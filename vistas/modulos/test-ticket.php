<?php
// Redirigir al archivo de prueba
$codigo = $_GET["codigo"] ?? "";

// Redirigir al archivo de prueba
header("Location: ../../test-ticket.php?codigo=" . urlencode($codigo));
exit();
?>
