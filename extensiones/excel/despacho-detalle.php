<?php

session_start();

require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

// Verificar que la sesión esté iniciada correctamente
if (!isset($_SESSION['perfil'])) {
    echo "Error: Sesión no iniciada";
    exit;
}

// Obtener ID del despacho
$idDespacho = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idDespacho || !is_numeric($idDespacho)) {
    echo "Error: ID de despacho inválido";
    exit;
}

try {
    // Obtener datos del despacho
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if (!$despacho) {
        echo "Error: Despacho no encontrado";
        exit;
    }
    
    // Decodificar productos
    $productos = json_decode($despacho["productos_despacho"], true);
    
    if (!$productos || !is_array($productos)) {
        echo "Error: No se pudieron obtener los productos del despacho";
        exit;
    }
    
    // Configurar nombre del archivo
    $nombreArchivo = 'Despacho_' . $despacho["numero_despacho"] . '_' . date('Y-m-d_H-i-s') . '.xls';
    
    // Configurar headers para Excel (igual que el sistema actual)
    header('Expires: 0');
    header('Cache-control: private');
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Cache-Control: cache, must-revalidate");
    header('Content-Description: File Transfer');
    header('Last-Modified: ' . date('D, d M Y H:i:s'));
    header("Pragma: public");
    header('Content-Disposition:; filename="' . $nombreArchivo . '"');
    header("Content-Transfer-Encoding: binary");
    
    // Generar contenido Excel usando el mismo método que el sistema
    echo utf8_decode("<table border='1'>");
    
    // TÍTULO PRINCIPAL
    echo utf8_decode("<tr><td colspan='5' style='font-weight:bold; text-align:center; font-size:16px; background-color:#4682B4; color:white;'>DETALLE DEL DESPACHO</td></tr>");
    echo utf8_decode("<tr><td colspan='5' style='font-weight:bold; text-align:center; font-size:14px;'>Número: " . $despacho["numero_despacho"] . "</td></tr>");
    echo utf8_decode("<tr><td colspan='5'></td></tr>"); // Línea vacía
    
    // INFORMACIÓN GENERAL
    echo utf8_decode("<tr><td colspan='5' style='font-weight:bold; text-align:center; background-color:#E6E6FA;'>INFORMACIÓN GENERAL</td></tr>");
    
    $info = [
        'Sucursal Origen' => $despacho["sucursal_origen"] ?? 'Sin especificar',
        'Creado por' => $despacho["nombre_usuario_creador"] ?? 'Sin especificar',
        'Fecha de Creación' => date('d/m/Y H:i', strtotime($despacho["fecha_creacion"])),
        'Estado' => strtoupper($despacho["estado"]),
        'Transportador' => $despacho["nombre_transportador"] ?? 'Sin asignar',
        'Total Productos' => $despacho["total_productos"] . ' productos',
        'Total Cantidad' => number_format($despacho["total_cantidad"]) . ' unidades'
    ];
    
    foreach ($info as $label => $value) {
        echo utf8_decode("<tr><td style='font-weight:bold; background-color:#F0F0F0;'>" . $label . "</td><td colspan='4'>" . $value . "</td></tr>");
    }
    
    echo utf8_decode("<tr><td colspan='5'></td></tr>"); // Línea vacía
    
    // TABLA DE PRODUCTOS
    echo utf8_decode("<tr><td colspan='5' style='font-weight:bold; text-align:center; background-color:#E6E6FA;'>PRODUCTOS A DESPACHAR</td></tr>");
    
    // Headers de productos
    echo utf8_decode("<tr>");
    echo utf8_decode("<td style='font-weight:bold; background-color:#4682B4; color:white; text-align:center;'>#</td>");
    echo utf8_decode("<td style='font-weight:bold; background-color:#4682B4; color:white; text-align:center;'>Código</td>");
    echo utf8_decode("<td style='font-weight:bold; background-color:#4682B4; color:white; text-align:center;'>Descripción del Producto</td>");
    echo utf8_decode("<td style='font-weight:bold; background-color:#4682B4; color:white; text-align:center;'>Cantidad</td>");
    echo utf8_decode("<td style='font-weight:bold; background-color:#4682B4; color:white; text-align:center;'>Observaciones</td>");
    echo utf8_decode("</tr>");
    
    // Datos de productos
    $contador = 1;
    $totalCantidad = 0;
    
    foreach ($productos as $producto) {
        echo utf8_decode("<tr>");
        echo utf8_decode("<td style='text-align:center;'>" . $contador . "</td>");
        echo utf8_decode("<td>" . $producto['codigo'] . "</td>");
        echo utf8_decode("<td>" . $producto['descripcion'] . "</td>");
        echo utf8_decode("<td style='text-align:center;'>" . number_format($producto['cantidad']) . "</td>");
        echo utf8_decode("<td>" . ($producto['observaciones'] ?? '') . "</td>");
        echo utf8_decode("</tr>");
        
        $totalCantidad += $producto['cantidad'];
        $contador++;
    }
    
    // Fila de totales
    echo utf8_decode("<tr>");
    echo utf8_decode("<td colspan='3' style='font-weight:bold; text-align:right; background-color:#DCDCDC;'>TOTAL:</td>");
    echo utf8_decode("<td style='font-weight:bold; text-align:center; background-color:#DCDCDC;'>" . number_format($totalCantidad) . "</td>");
    echo utf8_decode("<td style='background-color:#DCDCDC;'></td>");
    echo utf8_decode("</tr>");
    
    // DETALLE ADICIONAL (si existe)
    if (!empty($despacho["detalle_adicional"])) {
        echo utf8_decode("<tr><td colspan='5'></td></tr>"); // Línea vacía
        echo utf8_decode("<tr><td colspan='5' style='font-weight:bold; text-align:center; background-color:#E6E6FA;'>DETALLE ADICIONAL</td></tr>");
        echo utf8_decode("<tr><td colspan='5'>" . $despacho["detalle_adicional"] . "</td></tr>");
    }
    
    // INFORMACIÓN DEL DOCUMENTO
    echo utf8_decode("<tr><td colspan='5'></td></tr>"); // Línea vacía
    echo utf8_decode("<tr><td colspan='5' style='text-align:center; font-size:10px; color:#666;'>");
    echo utf8_decode("Documento generado el: " . date('d/m/Y H:i:s') . " | Sistema de Gestión - Despacho " . $despacho["numero_despacho"]);
    echo utf8_decode("</td></tr>");
    
    echo utf8_decode("</table>");
    
} catch (Exception $e) {
    echo "Error al generar Excel: " . $e->getMessage();
}

?>
