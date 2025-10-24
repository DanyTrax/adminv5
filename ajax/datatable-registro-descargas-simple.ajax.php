<?php
/*=============================================
DATATABLE REGISTRO DE DESCARGAS SIMPLE
=============================================*/

// Cargar controlador solo si no está cargado
if (!class_exists("ControladorRegistroDescargasSimple")) {
    require_once "../controladores/registro-descargas-simple.controlador.php";
}

$registroDescargas = new ControladorRegistroDescargasSimple();

// Obtener parámetros de DataTable
$start = $_POST["start"] ?? 0;
$length = $_POST["length"] ?? 10;
$search = $_POST["search"]["value"] ?? "";

// Filtros adicionales
$filtros = [
    "fecha_desde" => $_POST["fecha_desde"] ?? "",
    "fecha_hasta" => $_POST["fecha_hasta"] ?? "",
    "usuario_id" => $_POST["usuario_id"] ?? "",
    "sucursal_id" => $_POST["sucursal_id"] ?? "",
    "codigo_producto" => $_POST["codigo_producto"] ?? "",
    "busqueda_general" => $search
];

// Obtener registros
$registros = $registroDescargas->ctrObtenerRegistro($filtros);

// Preparar datos para DataTable
$data = [];
foreach($registros as $registro) {
    $data[] = [
        $registro["codigo_producto"],
        $registro["descripcion_producto"],
        $registro["cantidad_descargada"],
        $registro["usuario_nombre"],
        $registro["sucursal_nombre"],
        $registro["transportador_nombre"] ?? "N/A",
        $registro["numero_despacho"] ?? "N/A",
        $registro["observaciones"],
        date("d/m/Y H:i", strtotime($registro["fecha_descarga"]))
    ];
}

// Respuesta para DataTable
echo json_encode([
    "draw" => intval($_POST["draw"]),
    "recordsTotal" => count($registros),
    "recordsFiltered" => count($registros),
    "data" => $data
]);
?>