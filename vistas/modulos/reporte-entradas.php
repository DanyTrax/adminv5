<?php
// Se incluyen todos los archivos necesarios
require_once "../../controladores/contabilidad.controlador.php";
require_once "../../modelos/contabilidad.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";

// Se establece el nombre del archivo
$nombreArchivo = 'reporte-entradas.xls';
header('Expires: 0');
header('Cache-control: private');
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Cache-Control: cache, must-revalidate"); 
header('Content-Description: File Transfer');
header('Last-Modified: ' . date('D, d M Y H:i:s'));
header("Pragma: public"); 
header('Content-Disposition:; filename="' . $nombreArchivo . '"');
header("Content-Transfer-Encoding: binary");

// Se leen los filtros de la URL
$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
$medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;

// Se llama a la función de filtrado con todos los parámetros
$entradas = ControladorContabilidad::filterBy($fechaInicial, $fechaFinal, $medioPago, 'Entrada');

// Se crea la tabla HTML que se convertirá en Excel
echo utf8_decode("
<table border='1'> 
    <tr> 
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>#</th> 
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Empresa</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Factura</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Fecha</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Descripción</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Valor</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Medio Pago</th>
        <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>Forma Pago</th>
    </tr>
");

foreach ($entradas as $key => $row) {
    $vendedor = ControladorUsuarios::ctrMostrarUsuarios('id', $row["id_vendedor"]);

    echo utf8_decode("
    <tr>
        <td>" . ($key + 1) . "</td>
        <td>" . $vendedor['empresa'] . "</td>
        <td>" . $row['factura'] . "</td>
        <td>" . date('Y-m-d', strtotime($row['fecha'])) . "</td>
        <td>" . $row['detalle'] . "</td>
        <td>" . $row['valor'] . "</td>
        <td>" . $row['medio_pago'] . "</td>
        <td>" . $row['forma_pago'] . "</td>
    </tr>");
}

echo "</table>";

?>