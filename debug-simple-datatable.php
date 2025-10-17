<?php
echo "<h2>🔍 Debug Simple del Datatable Stock en Tránsito</h2>";

// Test 1: Verificar que el archivo existe
echo "<h3>📁 Test 1: Verificación de archivos</h3>";
$archivoDataTable = "ajax/datatable-stock-transito.ajax.php";
if(file_exists($archivoDataTable)) {
    echo "<p>✅ El archivo <code>$archivoDataTable</code> existe</p>";
} else {
    echo "<p>❌ El archivo <code>$archivoDataTable</code> NO existe</p>";
    echo "<p>Archivos en ajax/: " . implode(", ", scandir("ajax/")) . "</p>";
}

// Test 2: Verificar conexión a BD central
echo "<h3>🔗 Test 2: Conexión a BD central</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "<p>✅ Conexión a BD central exitosa</p>";
    
    // Test directo de la consulta del datatable
    $stmt = $conexion->prepare("
        SELECT 
            codigo_producto,
            descripcion_producto,
            cantidad_disponible,
            nombre_transportador,
            sucursal_origen,
            numero_despacho_origen,
            fecha_carga
        FROM stock_transito 
        ORDER BY fecha_carga DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Registros encontrados:</strong> " . count($registros) . "</p>";
    
    if(count($registros) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Origen</th><th>Despacho</th><th>Fecha</th>";
        echo "</tr>";
        
        foreach($registros as $registro) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($registro['codigo_producto']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['descripcion_producto']) . "</td>";
            echo "<td>" . $registro['cantidad_disponible'] . "</td>";
            echo "<td>" . htmlspecialchars($registro['nombre_transportador']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['sucursal_origen']) . "</td>";
            echo "<td>" . htmlspecialchars($registro['numero_despacho_origen']) . "</td>";
            echo "<td>" . $registro['fecha_carga'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}

// Test 3: Simular petición POST
echo "<h3>📨 Test 3: Simulación de petición POST</h3>";
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;

echo "<p>Variables POST configuradas:</p>";
echo "<ul>";
echo "<li>draw: " . $_POST['draw'] . "</li>";
echo "<li>start: " . $_POST['start'] . "</li>";
echo "<li>length: " . $_POST['length'] . "</li>";
echo "</ul>";

// Test 4: Incluir archivo datatable y capturar errores
echo "<h3>🔄 Test 4: Ejecutar datatable con manejo de errores</h3>";

if(file_exists($archivoDataTable)) {
    
    // Activar reporte de errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<p>Ejecutando datatable...</p>";
    
    ob_start();
    try {
        include $archivoDataTable;
        $output = ob_get_contents();
    } catch(Exception $e) {
        $output = "ERROR: " . $e->getMessage();
    } catch(Error $e) {
        $output = "FATAL ERROR: " . $e->getMessage();
    }
    ob_end_clean();
    
    echo "<h4>📤 Salida capturada:</h4>";
    echo "<pre style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Intentar decodificar JSON
    if(!empty($output)) {
        $json = json_decode($output, true);
        if($json !== null) {
            echo "<h4>✅ JSON válido decodificado:</h4>";
            echo "<ul>";
            echo "<li><strong>draw:</strong> " . ($json['draw'] ?? 'N/A') . "</li>";
            echo "<li><strong>recordsTotal:</strong> " . ($json['recordsTotal'] ?? 'N/A') . "</li>";
            echo "<li><strong>recordsFiltered:</strong> " . ($json['recordsFiltered'] ?? 'N/A') . "</li>";
            echo "<li><strong>Registros en data:</strong> " . (isset($json['data']) ? count($json['data']) : 'N/A') . "</li>";
            echo "</ul>";
        } else {
            echo "<h4>❌ JSON inválido</h4>";
            echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
        }
    } else {
        echo "<h4>❌ Salida vacía</h4>";
    }
    
} else {
    echo "<p>❌ No se puede ejecutar - archivo no encontrado</p>";
}

// Test 5: Verificar logs de errores
echo "<h3>📝 Test 5: Verificar error_log</h3>";
if(file_exists('error_log')) {
    $errorLog = file_get_contents('error_log');
    $lineasRecientes = array_slice(explode("\n", $errorLog), -10);
    echo "<p>Últimas 10 líneas del error_log:</p>";
    echo "<pre style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; max-height: 200px; overflow: auto;'>";
    echo htmlspecialchars(implode("\n", $lineasRecientes));
    echo "</pre>";
} else {
    echo "<p>No se encontró archivo error_log</p>";
}
?>