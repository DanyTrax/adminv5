<?php
// Test que simula exactamente la petición del navegador

echo "🔍 Test Navegador Real - Simulación exacta del navegador\n";
echo "=====================================================\n\n";

// Simular headers exactos del navegador
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_SERVER['HTTP_ACCEPT'] = 'application/json, text/javascript, */*; q=0.01';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'es-ES,es;q=0.9,en;q=0.8';
$_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';
$_SERVER['HTTP_CONNECTION'] = 'keep-alive';
$_SERVER['HTTP_REFERER'] = 'https://pruebas.acrilicosinfinito.com/despachos';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

// Simular parámetros de DataTables
$_GET['_'] = time() * 1000; // Timestamp para evitar caché

echo "📝 Headers simulados:\n";
echo "   - REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   - X_REQUESTED_WITH: " . $_SERVER['HTTP_X_REQUESTED_WITH'] . "\n";
echo "   - ACCEPT: " . $_SERVER['HTTP_ACCEPT'] . "\n";
echo "   - REFERER: " . $_SERVER['HTTP_REFERER'] . "\n";
echo "   - Timestamp: " . $_GET['_'] . "\n\n";

// Simular sesión exacta del navegador
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Probar el archivo datatable
echo "🚀 Ejecutando ajax/datatable-despachos.ajax.php...\n";

try {
    // Capturar output y errores
    ob_start();
    
    // Incluir el archivo
    include 'ajax/datatable-despachos.ajax.php';
    
    $output = ob_get_clean();
    
    echo "✅ Archivo ejecutado exitosamente\n";
    echo "📤 Output completo:\n";
    echo $output . "\n\n";
    
    // Verificar si es JSON válido
    $json = json_decode($output, true);
    if($json !== null) {
        echo "✅ JSON válido - DataTables debería funcionar\n";
        echo "📋 Datos encontrados:\n";
        echo "   - Total registros: " . count($json['data']) . "\n";
        if(count($json['data']) > 0) {
            echo "   - Primer registro: " . $json['data'][0][1] . " (" . $json['data'][0][2] . ")\n";
        }
    } else {
        echo "❌ JSON inválido - DataTables no funcionará\n";
        echo "Error JSON: " . json_last_error_msg() . "\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n🏁 Test completado\n";
?>