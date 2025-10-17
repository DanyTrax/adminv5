<?php
session_start();

// Habilitar logs
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log');
error_reporting(E_ALL);

echo "<h2>🔍 Test Logs Crear Despacho</h2>";

// Log de prueba
error_log("🧪 TEST: Logs funcionando desde test-crear-despacho-logs.php - " . date('Y-m-d H:i:s'));

// Simular lo que pasa en crear-despacho
if ($_POST) {
    error_log("📥 POST recibido en test: " . print_r($_POST, true));
    echo "<p>✅ Datos POST logueados</p>";
}

// Mostrar logs si existen
$errorLog = __DIR__ . '/error_log';
if (file_exists($errorLog)) {
    echo "<h3>📜 Últimas líneas del log:</h3>";
    $lines = file($errorLog);
    $recent = array_slice($lines, -10);
    echo "<pre style='background: #f8f9fa; padding: 10px;'>";
    echo htmlspecialchars(implode('', $recent));
    echo "</pre>";
}

// Formulario de prueba
?>
<form method="POST">
    <input type="hidden" name="test" value="1">
    <button type="submit">Probar Log</button>
</form>