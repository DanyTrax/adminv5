<?php

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "=== DEBUG EXPORTACIÓN EXCEL DESPACHO ===<br>";
echo "ID Despacho: " . ($_GET['id'] ?? 'NO DEFINIDO') . "<br>";
echo "Sesión ID: " . ($_SESSION['id'] ?? 'NO DEFINIDO') . "<br>";
echo "Sesión Perfil: " . ($_SESSION['perfil'] ?? 'NO DEFINIDO') . "<br><br>";

// Verificar que el usuario esté logueado
if(!isset($_SESSION['id'])) {
    die('Error: Sesión no iniciada');
}

echo "✅ Sesión válida<br>";

try {
    // Incluir conexiones y modelos necesarios
    echo "🔍 Incluyendo archivos...<br>";
    
    require_once __DIR__ . "/../../../modelos/conexion.php";
    echo "✅ modelos/conexion.php incluido<br>";
    
    require_once __DIR__ . "/../../../api-transferencias/conexion-central.php";
    echo "✅ api-transferencias/conexion-central.php incluido<br>";
    
    require_once __DIR__ . "/../../../controladores/despachos.controlador.php";
    echo "✅ controladores/despachos.controlador.php incluido<br>";
    
    echo "✅ Todos los archivos incluidos correctamente<br><br>";

    // Obtener ID del despacho
    $idDespacho = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$idDespacho || !is_numeric($idDespacho)) {
        die('Error: ID de despacho inválido');
    }

    echo "✅ ID Despacho válido: " . $idDespacho . "<br>";

    // Obtener datos del despacho
    echo "🔍 Obteniendo datos del despacho...<br>";
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", $idDespacho);
    
    if (!$despacho) {
        die('Error: Despacho no encontrado');
    }
    
    echo "✅ Despacho encontrado: " . $despacho["numero_despacho"] . "<br>";
    echo "📊 Datos del despacho:<br>";
    echo "- Número: " . $despacho["numero_despacho"] . "<br>";
    echo "- Estado: " . $despacho["estado"] . "<br>";
    echo "- Sucursal: " . ($despacho["sucursal_origen"] ?? 'Sin especificar') . "<br>";
    echo "- Creador: " . ($despacho["nombre_usuario_creador"] ?? 'Sin especificar') . "<br>";
    echo "- Total productos: " . $despacho["total_productos"] . "<br>";
    echo "- Total cantidad: " . $despacho["total_cantidad"] . "<br>";
    
    // Decodificar productos
    echo "🔍 Decodificando productos...<br>";
    $productos = json_decode($despacho["productos_despacho"], true);
    
    if (!$productos || !is_array($productos)) {
        echo "❌ Error: No se pudieron obtener los productos del despacho<br>";
        echo "Raw productos_despacho: " . htmlspecialchars($despacho["productos_despacho"]) . "<br>";
        die();
    }
    
    echo "✅ Productos decodificados correctamente: " . count($productos) . " productos<br>";
    
    foreach ($productos as $i => $producto) {
        echo "- Producto " . ($i+1) . ": " . $producto['codigo'] . " - " . $producto['descripcion'] . " (Cant: " . $producto['cantidad'] . ")<br>";
    }
    
    echo "<br>✅ Todos los datos obtenidos correctamente<br>";
    echo "El archivo Excel debería generarse correctamente.<br>";
    
    // Probar generación de contenido Excel
    echo "<br>🔍 Probando generación de contenido Excel...<br>";
    
    $contenidoExcel = utf8_decode("<table border='1'>");
    $contenidoExcel .= utf8_decode("<tr><td colspan='5'>DETALLE DEL DESPACHO</td></tr>");
    $contenidoExcel .= utf8_decode("<tr><td colspan='5'>Número: " . $despacho["numero_despacho"] . "</td></tr>");
    $contenidoExcel .= utf8_decode("</table>");
    
    echo "✅ Contenido Excel generado correctamente<br>";
    echo "Longitud del contenido: " . strlen($contenidoExcel) . " caracteres<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
} catch (Error $e) {
    echo "❌ Error Fatal: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

?>
