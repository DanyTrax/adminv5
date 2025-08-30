<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Cambiamos los archivos que requerimos
require_once "../controladores/reportes.controlador.php";
require_once "../modelos/reportes.modelo.php";

header('Content-Type: application/json');

$params = $_REQUEST;
$fechaInicial = isset($params['fechaInicial']) && !empty($params['fechaInicial']) ? $params['fechaInicial'] : null;
$fechaFinal = isset($params['fechaFinal']) && !empty($params['fechaFinal']) ? $params['fechaFinal'] : null;

// Usamos el nuevo controlador
$ventasData = ControladorReportes::ctrObtenerVentasServerSide($params, $fechaInicial, $fechaFinal);
$recordsFiltered = ControladorReportes::ctrContarVentasFiltradas($params, $fechaInicial, $fechaFinal);
$recordsTotal = ControladorReportes::ctrContarTotalVentas($fechaInicial, $fechaFinal);

$jsonData = array(
    "draw"            => intval($params['draw']),
    "recordsTotal"    => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data"            => $ventasData
);

echo json_encode($jsonData);