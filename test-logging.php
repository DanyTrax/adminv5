<?php
// Test para verificar si el logging funciona

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test de Logging\n";
echo "==================\n\n";

// Incluir el Logger
require_once "src/Logger.php";

echo "📝 Probando Logger...\n";

// Test 1: Log básico
Logger::info("Test de logging desde test-logging.php", "test-logging.php", "main");

echo "✅ Log básico enviado\n";

// Test 2: Verificar si el archivo se crea
$logFile = "logs/sistema.log";
if (file_exists($logFile)) {
    echo "✅ Archivo de log existe: $logFile\n";
    echo "📊 Tamaño: " . filesize($logFile) . " bytes\n";
    echo "📄 Contenido:\n";
    echo file_get_contents($logFile);
} else {
    echo "❌ Archivo de log NO existe: $logFile\n";
    
    // Verificar permisos del directorio
    $logDir = dirname($logFile);
    if (is_dir($logDir)) {
        echo "✅ Directorio logs existe\n";
        echo "📊 Permisos: " . substr(sprintf('%o', fileperms($logDir)), -4) . "\n";
    } else {
        echo "❌ Directorio logs NO existe\n";
        echo "🔧 Intentando crear directorio...\n";
        if (mkdir($logDir, 0755, true)) {
            echo "✅ Directorio creado exitosamente\n";
        } else {
            echo "❌ Error creando directorio\n";
        }
    }
}

// Test 3: Intentar escribir directamente
echo "\n🔧 Intentando escribir directamente...\n";
$testContent = "[" . date('Y-m-d H:i:s') . "] [TEST] [test-logging.php] [main] - Test directo\n";
if (file_put_contents($logFile, $testContent, FILE_APPEND | LOCK_EX)) {
    echo "✅ Escritura directa exitosa\n";
} else {
    echo "❌ Error en escritura directa\n";
    echo "📊 Error: " . error_get_last()['message'] . "\n";
}

// Test 4: Verificar contenido final
echo "\n📄 Contenido final del log:\n";
if (file_exists($logFile)) {
    echo file_get_contents($logFile);
} else {
    echo "❌ Archivo aún no existe\n";
}

echo "\n🏁 Test completado\n";
?>
