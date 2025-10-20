<?php
// Script para diagnosticar el problema de búsqueda de solicitudes en crear despacho
session_start();

echo "<h2>🔍 Diagnóstico de Crear Despacho - Búsqueda de Solicitudes</h2>";

// Verificar sesión
if (!isset($_SESSION['nombre'])) {
    echo "❌ No hay sesión activa<br>";
    exit;
}

echo "✅ Sesión activa: " . $_SESSION['nombre'] . "<br><br>";

// Verificar conexión a BD local
echo "<h3>🔗 Conexión BD Local:</h3>";
try {
    require_once "modelos/conexion.php";
    $pdo = Conexion::conectar();
    echo "✅ Conexión a BD Local exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error BD Local: " . $e->getMessage() . "<br>";
    exit;
}

// Verificar conexión a BD central
echo "<h3>🔗 Conexión BD Central:</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    $pdoCentral = ConexionCentral::conectar();
    echo "✅ Conexión a BD Central exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error BD Central: " . $e->getMessage() . "<br>";
    exit;
}

// Verificar tabla solicitudes_stock en BD central
echo "<h3>📋 Tabla solicitudes_stock en BD Central:</h3>";
try {
    $stmt = $pdoCentral->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    echo "📊 Total de solicitudes en BD Central: $total<br>";
    
    if ($total > 0) {
        // Mostrar algunas solicitudes
        $stmt = $pdoCentral->prepare("
            SELECT 
                id,
                numero_solicitud,
                nombre_usuario_solicitante,
                nombre_sucursal_solicitante,
                estado,
                fecha_solicitud
            FROM solicitudes_stock 
            ORDER BY fecha_solicitud DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $solicitudes = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Número</th><th>Usuario</th><th>Sucursal</th><th>Estado</th><th>Fecha</th></tr>";
        foreach ($solicitudes as $solicitud) {
            echo "<tr>";
            echo "<td>" . $solicitud['id'] . "</td>";
            echo "<td>" . $solicitud['numero_solicitud'] . "</td>";
            echo "<td>" . $solicitud['nombre_usuario_solicitante'] . "</td>";
            echo "<td>" . $solicitud['nombre_sucursal_solicitante'] . "</td>";
            echo "<td>" . $solicitud['estado'] . "</td>";
            echo "<td>" . $solicitud['fecha_solicitud'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
} catch (Exception $e) {
    echo "❌ Error consultando solicitudes: " . $e->getMessage() . "<br>";
}

// Probar el endpoint AJAX
echo "<h3>🧪 Prueba del Endpoint AJAX:</h3>";
echo "<form method='POST' action='ajax/productos-despacho.ajax.php' target='_blank'>";
echo "<input type='hidden' name='buscarSolicitudes' value='1'>";
echo "<input type='text' name='termino' placeholder='Término de búsqueda' value='SOL'>";
echo "<button type='submit'>Probar AJAX</button>";
echo "</form>";

// Verificar archivos JavaScript
echo "<h3>📁 Archivos JavaScript:</h3>";
$jsFiles = [
    'vistas/js/crear-despacho.js',
    'vistas/js/despachos.js'
];

foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}

// Verificar archivo de crear despacho
echo "<h3>📁 Archivo crear-despacho.php:</h3>";
if (file_exists('vistas/modulos/crear-despacho.php')) {
    echo "✅ crear-despacho.php existe<br>";
    
    // Verificar si incluye el JavaScript
    $content = file_get_contents('vistas/modulos/crear-despacho.php');
    if (strpos($content, 'crear-despacho.js') !== false) {
        echo "✅ Incluye crear-despacho.js<br>";
    } else {
        echo "❌ NO incluye crear-despacho.js<br>";
    }
} else {
    echo "❌ crear-despacho.php NO existe<br>";
}

echo "<br><h3>🔧 Próximos pasos:</h3>";
echo "1. Verificar que el endpoint AJAX funcione correctamente<br>";
echo "2. Verificar que el JavaScript se esté cargando<br>";
echo "3. Verificar que no haya errores en la consola del navegador<br>";
?>
