<?php

// Se incluyen todos los archivos necesarios
require_once "../../controladores/ventas.controlador.php";
require_once "../../modelos/ventas.modelo.php";
require_once "../../controladores/clientes.controlador.php";
require_once "../../modelos/clientes.modelo.php";
require_once "../../controladores/usuarios.controlador.php";
require_once "../../modelos/usuarios.modelo.php";
require_once "../../controladores/contabilidad.controlador.php";
require_once "../../modelos/contabilidad.modelo.php";

// CAMBIO: Se lee los filtros de la URL
$fechaInicial = isset($_GET["fechaInicial"]) ? $_GET["fechaInicial"] : null;
$fechaFinal = isset($_GET["fechaFinal"]) ? $_GET["fechaFinal"] : null;
$medioPago = isset($_GET["medioPago"]) ? $_GET["medioPago"] : null;
$formaPago = isset($_GET["formaPago"]) ? $_GET["formaPago"] : null;

// CAMBIO: Se llama a la función de filtrado, pasándole los parámetros
$ventas = ControladorVentas::filterBy($fechaInicial, $fechaFinal, $medioPago, $formaPago);

// --- CÓDIGO PARA GENERAR EL EXCEL ---

// Se establece el nombre del archivo
$nombreArchivo = 'reporte-contabilidad';
if ($fechaInicial != null) {
    $nombreArchivo .= '_' . $fechaInicial . '_a_' . $fechaFinal;
}
header('Expires: 0');
header('Cache-control: private');
header("Content-type: application/vnd.ms-excel"); // Archivo de Excel
header("Cache-Control: cache, must-revalidate"); 
header('Content-Description: File Transfer');
header('Last-Modified: ' . date('D, d M Y H:i:s'));
header("Pragma: public"); 
header('Content-Disposition:; filename="' . $nombreArchivo . '.xls"');
header("Content-Transfer-Encoding: binary");

// Se crea la tabla HTML que se convertirá en Excel
echo utf8_decode("<table border='1'> 
        <tr> 
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>#</th> 
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>CÓDIGO FACTURA</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>CLIENTE</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>VENDEDOR</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>FORMA DE PAGO</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>NETO</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>TOTAL</th>
            <th style='font-weight:bold; background-color:#3c8dbc; color:white;'>FECHA VENTA</th>
        </tr>");

foreach ($ventas as $key => $row) {
    $cliente = ControladorClientes::ctrMostrarClientes("id", $row["id_cliente"]);
    $vendedor = ControladorUsuarios::ctrMostrarUsuarios("id", $row["id_vendedor"]);

    echo utf8_decode("<tr>
            <td>" . ($key + 1) . "</td>
            <td>" . $row['codigo'] . "</td>
            <td>" . $cliente['nombre'] . "</td>
            <td>" . $vendedor['nombre'] . "</td>
            <td>" . $row['metodo_pago'] . "</td>
            <td>" . (int) round($row['neto']) . "</td>
            <td>" . (int) round($row['total']) . "</td>
            <td>" . date('Y-m-d', strtotime($row['fecha_venta'])) . "</td>
        </tr>");
}

echo "</table>";