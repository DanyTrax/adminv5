<?php
echo "<h2>🔍 Debug AJAX Stock Tránsito</h2>";

// Mostrar error log reciente
$errorLog = "error_log";
if(file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -20); // Últimas 20 líneas
    
    echo "<h3>Últimos logs de error:</h3>";
    echo "<pre style='background:#f0f0f0; padding:10px; max-height:300px; overflow:auto;'>";
    foreach($recentLines as $line) {
        if(strpos($line, 'POST recibido') !== false) {
            echo "<span style='color:blue;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
}

// Test directo del AJAX
echo "<h3>Test directo del AJAX:</h3>";
$_POST['tabla'] = 'stock-transito';

ob_start();
include 'ajax/datatable-stock-transito.ajax.php';
$output = ob_get_clean();

echo "<h4>Salida:</h4>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";
echo htmlspecialchars($output);
echo "</pre>";

// Verificar si es JSON válido
$json = json_decode($output, true);
if($json !== null) {
    echo "<h4>✅ JSON válido - Registros: " . (isset($json['data']) ? count($json['data']) : 0) . "</h4>";
} else {
    echo "<h4>❌ JSON inválido - Error: " . json_last_error_msg() . "</h4>";
}
?>