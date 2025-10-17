<?php
echo "<h2>🔍 Test AJAX Directo al Datatable</h2>";

// Limpiar buffer de salida para evitar headers enviados
if (ob_get_level()) {
    ob_end_clean();
}

// Iniciar sesión
session_start();

// Configurar variables POST como si fuera una petición DataTable real
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;
$_POST['search'] = ['value' => '', 'regex' => false];
$_POST['order'] = [['column' => 0, 'dir' => 'asc']];
$_POST['columns'] = [
    ['data' => 'codigo_producto', 'searchable' => true, 'orderable' => true],
    ['data' => 'descripcion_producto', 'searchable' => true, 'orderable' => true],
    ['data' => 'cantidad_disponible', 'searchable' => false, 'orderable' => true],
    ['data' => 'nombre_transportador', 'searchable' => true, 'orderable' => true],
    ['data' => 'sucursal_origen', 'searchable' => true, 'orderable' => true],
    ['data' => 'numero_despacho_origen', 'searchable' => true, 'orderable' => true],
    ['data' => 'fecha_carga', 'searchable' => false, 'orderable' => true],
    ['data' => 'acciones', 'searchable' => false, 'orderable' => false]
];

echo "<h3>📊 Variables POST configuradas:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<h3>🔄 Ejecutando datatable con captura completa de errores:</h3>";

// Activar captura completa de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Capturar TODO (salida y errores)
ob_start();
$errorOutput = '';

try {
    // Capturar errores también
    set_error_handler(function($severity, $message, $file, $line) use (&$errorOutput) {
        $errorOutput .= "ERROR [$severity]: $message en $file:$line\n";
    });
    
    // Ejecutar el archivo datatable
    include 'ajax/datatable-stock-transito.ajax.php';
    
    // Restaurar manejador de errores
    restore_error_handler();
    
} catch (Exception $e) {
    $errorOutput .= "EXCEPTION: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    $errorOutput .= "FATAL ERROR: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . "\n";
}

$output = ob_get_clean();

echo "<h4>📤 Salida del datatable:</h4>";
if (!empty($output)) {
    echo "<pre style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; max-height: 400px; overflow: auto;'>";
    echo "LONGITUD: " . strlen($output) . " caracteres\n";
    echo "CONTENIDO:\n" . htmlspecialchars($output);
    echo "</pre>";
    
    // Intentar decodificar JSON
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "<h4>✅ JSON decodificado exitosamente:</h4>";
        echo "<ul>";
        echo "<li><strong>draw:</strong> " . ($json['draw'] ?? 'N/A') . "</li>";
        echo "<li><strong>recordsTotal:</strong> " . ($json['recordsTotal'] ?? 'N/A') . "</li>";
        echo "<li><strong>recordsFiltered:</strong> " . ($json['recordsFiltered'] ?? 'N/A') . "</li>";
        echo "<li><strong>Registros en data:</strong> " . (isset($json['data']) ? count($json['data']) : 'N/A') . "</li>";
        echo "</ul>";
        
        if (isset($json['data']) && count($json['data']) > 0) {
            echo "<h5>📋 Primer registro:</h5>";
            echo "<pre>" . print_r($json['data'][0], true) . "</pre>";
        }
    } else {
        echo "<h4>❌ Error decodificando JSON:</h4>";
        echo "<p><strong>Error:</strong> " . json_last_error_msg() . "</p>";
        echo "<p><strong>Código de error:</strong> " . json_last_error() . "</p>";
        
        // Mostrar caracteres no imprimibles
        echo "<h5>🔍 Análisis de caracteres:</h5>";
        echo "<p>Primeros 200 caracteres en hexadecimal:</p>";
        echo "<pre style='font-family: monospace; background: #fff3cd; padding: 10px;'>";
        echo bin2hex(substr($output, 0, 200));
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>❌ <strong>No hay salida del datatable</strong></p>";
}

echo "<h4>⚠️ Errores capturados:</h4>";
if (!empty($errorOutput)) {
    echo "<pre style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px;'>";
    echo htmlspecialchars($errorOutput);
    echo "</pre>";
} else {
    echo "<p style='color: green;'>✅ No se capturaron errores PHP</p>";
}

// Test manual de la clase
echo "<h3>🧪 Test manual de la clase TablaStockTransito:</h3>";

try {
    require_once 'ajax/datatable-stock-transito.ajax.php';
    
    if (class_exists('TablaStockTransito')) {
        echo "<p>✅ Clase TablaStockTransito existe</p>";
        
        $tabla = new TablaStockTransito();
        echo "<p>✅ Instancia de TablaStockTransito creada</p>";
        
        if (method_exists($tabla, 'mostrarTablaStockTransito')) {
            echo "<p>✅ Método mostrarTablaStockTransito existe</p>";
            
            // Intentar llamar al método manualmente
            ob_start();
            $tabla->mostrarTablaStockTransito();
            $salidaManual = ob_get_clean();
            
            echo "<h4>📤 Salida del método manual:</h4>";
            if (!empty($salidaManual)) {
                echo "<pre style='background: #d4edda; padding: 10px; max-height: 300px; overflow: auto;'>";
                echo htmlspecialchars($salidaManual);
                echo "</pre>";
            } else {
                echo "<p>❌ Método no produjo salida</p>";
            }
        } else {
            echo "<p>❌ Método mostrarTablaStockTransito NO existe</p>";
        }
    } else {
        echo "<p>❌ Clase TablaStockTransito NO existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en test manual: " . $e->getMessage() . "</p>";
}
?>