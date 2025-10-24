<?php
// Script para debuggear la sincronización AJAX
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug Sincronización AJAX</h1>";

// Simular la petición AJAX
if (isset($_POST['accion']) && $_POST['accion'] == 'sincronizar') {
    echo "<h2>📡 Petición AJAX recibida correctamente</h2>";
    
    try {
        require_once "modelos/categorias-central.modelo.php";
        
        echo "<h3>🔄 Ejecutando sincronización...</h3>";
        $resultado = ModeloCategoriasCentral::mdlSincronizarCategoriasSucursales();
        
        echo "<h3>📊 Resultado:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        print_r($resultado);
        echo "</pre>";
        
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
        
    } catch (Exception $e) {
        echo "<h3>❌ Error:</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        
        $error_response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
        
        header('Content-Type: application/json');
        echo json_encode($error_response);
        exit;
    }
}

// Si no es petición AJAX, mostrar formulario de prueba
echo "<h2>🧪 Prueba Manual de Sincronización</h2>";
echo "<form method='POST'>";
echo "<input type='hidden' name='accion' value='sincronizar'>";
echo "<button type='submit' style='background: #5cb85c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Probar Sincronización</button>";
echo "</form>";

echo "<h2>📋 Información del Sistema:</h2>";
echo "<ul>";
echo "<li><strong>Método:</strong> " . $_SERVER['REQUEST_METHOD'] . "</li>";
echo "<li><strong>POST data:</strong> " . print_r($_POST, true) . "</li>";
echo "<li><strong>Archivo actual:</strong> " . __FILE__ . "</li>";
echo "<li><strong>Directorio:</strong> " . __DIR__ . "</li>";
echo "</ul>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
button { font-size: 16px; }
</style>";
?>
