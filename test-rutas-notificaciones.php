<?php
session_start();

echo "<h2>🔍 PRUEBA DE RUTAS PARA NOTIFICACIONES</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f0f0f0;padding:10px;border-radius:5px;}</style>";

echo "<h3>1. Información del Sistema</h3>";
echo "<p><strong>Directorio actual:</strong> " . getcwd() . "</p>";
echo "<p><strong>Archivo actual:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Directorio del script:</strong> " . dirname(__FILE__) . "</p>";

echo "<h3>2. Verificar Archivos Necesarios</h3>";

$archivos_verificar = [
    'api-transferencias/conexion-central.php',
    'ajax/notificaciones-solicitudes.ajax.php',
    'controladores/solicitudes-stock.controlador.php'
];

foreach($archivos_verificar as $archivo) {
    $rutaCompleta = getcwd() . '/' . $archivo;
    if(file_exists($archivo)) {
        echo "<p class='success'>✅ $archivo - EXISTE</p>";
    } else {
        echo "<p class='error'>❌ $archivo - NO EXISTE</p>";
        echo "<p class='info'>   Buscado en: $rutaCompleta</p>";
    }
}

echo "<h3>3. Probar Conexión Desde Diferentes Rutas</h3>";

// Probar desde raíz
try {
    require_once 'api-transferencias/conexion-central.php';
    $conexion = ConexionCentral::conectar();
    if($conexion) {
        echo "<p class='success'>✅ Conexión desde raíz: OK</p>";
        
        // Probar consulta
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM solicitudes_stock WHERE estado = 'pendiente'");
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p class='info'>📊 Solicitudes pendientes encontradas: " . ($resultado['total'] ?? 0) . "</p>";
        
    } else {
        echo "<p class='error'>❌ Conexión falló</p>";
    }
} catch(Exception $e) {
    echo "<p class='error'>❌ Error conectando desde raíz: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Simular Petición AJAX</h3>";

// Simular el POST
$_POST['accion'] = 'obtener_pendientes';

// Capturar la salida
ob_start();
?>
<div style="border: 2px solid #007bff; padding: 10px; border-radius: 5px; background: #f8f9fa;">
    <h4>Resultado del AJAX:</h4>
    <pre><?php 
    try {
        include 'ajax/notificaciones-solicitudes.ajax.php';
    } catch(Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    ?></pre>
</div>
<?php
$salidaAjax = ob_get_clean();
echo $salidaAjax;

echo "<h3>5. Verificar Estructura JSON</h3>";
// Reset del output buffer para capturar solo JSON
ob_start();
$_POST['accion'] = 'obtener_pendientes';

try {
    include 'ajax/notificaciones-solicitudes.ajax.php';
    $jsonOutput = ob_get_clean();
    
    $decoded = json_decode($jsonOutput, true);
    if($decoded !== null) {
        echo "<p class='success'>✅ JSON válido generado</p>";
        echo "<pre>" . json_encode($decoded, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>❌ JSON inválido</p>";
        echo "<p>Error: " . json_last_error_msg() . "</p>";
        echo "<p>Output crudo:</p>";
        echo "<pre>" . htmlspecialchars($jsonOutput) . "</pre>";
    }
} catch(Exception $e) {
    ob_end_clean();
    echo "<p class='error'>❌ Error ejecutando AJAX: " . $e->getMessage() . "</p>";
}
?>