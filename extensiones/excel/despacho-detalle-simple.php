<?php

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar que el usuario esté logueado
if(!isset($_SESSION['id'])) {
    die('Error: Sesión no iniciada');
}

// Incluir conexiones y modelos necesarios
require_once __DIR__ . "/../../../modelos/conexion.php";
require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
require_once __DIR__ . "/../../../controladores/despachos.controlador.php";

// Obtener ID del despacho
$idDespacho = isset($_GET['id']) ? $_GET['id'] : null;

if (!$idDespacho || !is_numeric($idDespacho)) {
    die('Error: ID de despacho inválido');
}

try {
    // Obtener datos del despacho
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if (!$despacho) {
        die('Error: Despacho no encontrado');
    }
    
    // Decodificar productos
    $productos = json_decode($despacho["productos_despacho"], true);
    
    if (!$productos || !is_array($productos)) {
        die('Error: No se pudieron obtener los productos del despacho');
    }
    
    // Configurar nombre del archivo
    $nombreArchivo = 'Despacho_' . $despacho["numero_despacho"] . '_' . date('Y-m-d_H-i-s') . '.xls';
    
    // Configurar headers para Excel
    header('Expires: 0');
    header('Cache-control: private');
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Cache-Control: cache, must-revalidate");
    header('Content-Description: File Transfer');
    header('Last-Modified: ' . date('D, d M Y H:i:s'));
    header("Pragma: public");
    header('Content-Disposition:; filename="' . $nombreArchivo . '"');
    header("Content-Transfer-Encoding: binary");
    
    // Generar contenido Excel simplificado
    echo "<table border='1'>";
    
    // TÍTULO PRINCIPAL
    echo "<tr><td colspan='5' style='font-weight:bold; text-align:center;'>DETALLE DEL DESPACHO</td></tr>";
    echo "<tr><td colspan='5' style='font-weight:bold; text-align:center;'>Número: " . htmlspecialchars($despacho["numero_despacho"]) . "</td></tr>";
    echo "<tr><td colspan='5'></td></tr>";
    
    // INFORMACIÓN GENERAL
    echo "<tr><td colspan='5' style='font-weight:bold; text-align:center;'>INFORMACIÓN GENERAL</td></tr>";
    
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
        echo "<tr><td style='font-weight:bold;'>" . htmlspecialchars($label) . "</td><td colspan='4'>" . htmlspecialchars($value) . "</td></tr>";
    }
    
    echo "<tr><td colspan='5'></td></tr>";
    
    // TABLA DE PRODUCTOS
    echo "<tr><td colspan='5' style='font-weight:bold; text-align:center;'>PRODUCTOS A DESPACHAR</td></tr>";
    
    // Headers de productos
    echo "<tr>";
    echo "<td style='font-weight:bold; text-align:center;'>#</td>";
    echo "<td style='font-weight:bold; text-align:center;'>Código</td>";
    echo "<td style='font-weight:bold; text-align:center;'>Descripción del Producto</td>";
    echo "<td style='font-weight:bold; text-align:center;'>Cantidad</td>";
    echo "<td style='font-weight:bold; text-align:center;'>Observaciones</td>";
    echo "</tr>";
    
    // Datos de productos
    $contador = 1;
    $totalCantidad = 0;
    
    foreach ($productos as $producto) {
        echo "<tr>";
        echo "<td style='text-align:center;'>" . $contador . "</td>";
        echo "<td>" . htmlspecialchars($producto['codigo']) . "</td>";
        echo "<td>" . htmlspecialchars($producto['descripcion']) . "</td>";
        echo "<td style='text-align:center;'>" . number_format($producto['cantidad']) . "</td>";
        echo "<td>" . htmlspecialchars($producto['observaciones'] ?? '') . "</td>";
        echo "</tr>";
        
        $totalCantidad += $producto['cantidad'];
        $contador++;
    }
    
    // Fila de totales
    echo "<tr>";
    echo "<td colspan='3' style='font-weight:bold; text-align:right;'>TOTAL:</td>";
    echo "<td style='font-weight:bold; text-align:center;'>" . number_format($totalCantidad) . "</td>";
    echo "<td></td>";
    echo "</tr>";
    
    // DETALLE ADICIONAL (si existe)
    if (!empty($despacho["detalle_adicional"])) {
        echo "<tr><td colspan='5'></td></tr>";
        echo "<tr><td colspan='5' style='font-weight:bold; text-align:center;'>DETALLE ADICIONAL</td></tr>";
        echo "<tr><td colspan='5'>" . htmlspecialchars($despacho["detalle_adicional"]) . "</td></tr>";
    }
    
    // INFORMACIÓN DEL DOCUMENTO
    echo "<tr><td colspan='5'></td></tr>";
    echo "<tr><td colspan='5' style='text-align:center; font-size:10px;'>";
    echo "Documento generado el: " . date('d/m/Y H:i:s') . " | Sistema de Gestión - Despacho " . htmlspecialchars($despacho["numero_despacho"]);
    echo "</td></tr>";
    
    echo "</table>";
    
} catch (Exception $e) {
    die('Error al generar Excel: ' . $e->getMessage());
} catch (Error $e) {
    die('Error Fatal: ' . $e->getMessage());
}

?>
