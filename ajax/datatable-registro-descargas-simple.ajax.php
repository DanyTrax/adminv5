<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS SIMPLE - ULTRA SIMPLE
=============================================*/

// Respuesta simple para DataTable
echo json_encode([
    "draw" => intval($_POST["draw"] ?? 1),
    "recordsTotal" => 0,
    "recordsFiltered" => 0,
    "data" => []
]);
?>