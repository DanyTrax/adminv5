<?php
// Script para probar directamente el endpoint AJAX de solicitudes
session_start();

// Simular sesión de administrador
$_SESSION['perfil'] = 'Administrador';
$_SESSION['nombre'] = 'Administrador';

echo "<h2>🧪 Prueba Directa del Endpoint AJAX de Solicitudes</h2>";

// Simular POST request
$_POST['buscarSolicitudes'] = true;
$_POST['termino'] = 'SOL000005';

echo "<h3>📥 Datos enviados:</h3>";
echo "buscarSolicitudes: " . ($_POST['buscarSolicitudes'] ? 'true' : 'false') . "<br>";
echo "termino: " . $_POST['termino'] . "<br><br>";

echo "<h3>📤 Respuesta del endpoint:</h3>";
echo "<pre>";

// Capturar la salida del endpoint
ob_start();
include 'ajax/productos-despacho.ajax.php';
$output = ob_get_clean();

echo $output;
echo "</pre>";

// Intentar decodificar JSON
$json = json_decode($output, true);
if ($json) {
    echo "<h3>📋 JSON decodificado:</h3>";
    echo "<pre>";
    print_r($json);
    echo "</pre>";
    
    if (isset($json['solicitudes']) && count($json['solicitudes']) > 0) {
        echo "<h3>✅ Solicitudes encontradas:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Usuario</th><th>Sucursal</th><th>Estado</th><th>Fecha</th></tr>";
        foreach ($json['solicitudes'] as $solicitud) {
            echo "<tr>";
            echo "<td>" . $solicitud['id'] . "</td>";
            echo "<td>" . $solicitud['numero_solicitud'] . "</td>";
            echo "<td>" . $solicitud['nombre_usuario_solicitante'] . "</td>";
            echo "<td>" . $solicitud['nombre_sucursal_solicitante'] . "</td>";
            echo "<td>" . $solicitud['estado'] . "</td>";
            echo "<td>" . $solicitud['fecha_solicitud'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h3>❌ No se encontraron solicitudes</h3>";
    }
} else {
    echo "<h3>❌ Error: No se pudo decodificar JSON</h3>";
    echo "JSON Error: " . json_last_error_msg() . "<br>";
}

// También probar con término más corto
echo "<br><h3>🧪 Prueba con término más corto (SOL):</h3>";
$_POST['termino'] = 'SOL';

ob_start();
include 'ajax/productos-despacho.ajax.php';
$output2 = ob_get_clean();

echo "<pre>";
echo $output2;
echo "</pre>";

$json2 = json_decode($output2, true);
if ($json2 && isset($json2['solicitudes'])) {
    echo "Solicitudes encontradas con 'SOL': " . count($json2['solicitudes']) . "<br>";
}
?>
