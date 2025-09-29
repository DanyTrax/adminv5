<?php
session_start();

echo "<h2>🔍 Debug del Datatable Stock en Tránsito</h2>";

// Simular la petición que hace el datatable
$_POST['draw'] = 1;
$_POST['start'] = 0;
$_POST['length'] = 10;

// Capturar la salida del archivo datatable
ob_start();
include_once 'ajax/datatable-stock-transito.ajax.php';
$output = ob_get_clean();

echo "<h3>📊 Respuesta del datatable:</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
echo htmlspecialchars($output);
echo "</pre>";

// Intentar decodificar como JSON
$json = json_decode($output, true);

if($json) {
    echo "<h3>✅ JSON válido - Datos decodificados:</h3>";
    echo "<ul>";
    echo "<li><strong>draw:</strong> " . ($json['draw'] ?? 'No definido') . "</li>";
    echo "<li><strong>recordsTotal:</strong> " . ($json['recordsTotal'] ?? 'No definido') . "</li>";
    echo "<li><strong>recordsFiltered:</strong> " . ($json['recordsFiltered'] ?? 'No definido') . "</li>";
    echo "<li><strong>Registros de data:</strong> " . (isset($json['data']) ? count($json['data']) : 'No definido') . "</li>";
    echo "</ul>";
    
    if(isset($json['data']) && is_array($json['data']) && count($json['data']) > 0) {
        echo "<h4>📋 Primeros 3 registros:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>#</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Origen</th><th>Despacho</th></tr>";
        
        for($i = 0; $i < min(3, count($json['data'])); $i++) {
            $registro = $json['data'][$i];
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . ($registro[0] ?? 'N/A') . "</td>"; // código
            echo "<td>" . ($registro[1] ?? 'N/A') . "</td>"; // descripción
            echo "<td>" . ($registro[2] ?? 'N/A') . "</td>"; // cantidad
            echo "<td>" . ($registro[3] ?? 'N/A') . "</td>"; // transportador
            echo "<td>" . ($registro[4] ?? 'N/A') . "</td>"; // origen
            echo "<td>" . ($registro[5] ?? 'N/A') . "</td>"; // despacho
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ <strong>La consulta no devuelve registros en 'data'</strong></p>";
    }
    
} else {
    echo "<h3>❌ JSON inválido o vacío</h3>";
    echo "<p>Error JSON: " . json_last_error_msg() . "</p>";
}

// Test directo a la base de datos
echo "<h3>🔍 Test directo a la base de datos:</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    
    $stmt = ConexionCentral::conectar()->prepare("
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
    $registros = $stmt->fetchAll();
    
    echo "<p><strong>Registros encontrados:</strong> " . count($registros) . "</p>";
    
    if(count($registros) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Origen</th><th>Despacho</th><th>Fecha</th></tr>";
        
        foreach($registros as $registro) {
            echo "<tr>";
            echo "<td>{$registro['codigo_producto']}</td>";
            echo "<td>{$registro['descripcion_producto']}</td>";
            echo "<td>{$registro['cantidad_disponible']}</td>";
            echo "<td>{$registro['nombre_transportador']}</td>";
            echo "<td>{$registro['sucursal_origen']}</td>";
            echo "<td>{$registro['numero_despacho_origen']}</td>";
            echo "<td>{$registro['fecha_carga']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error en consulta directa: " . $e->getMessage() . "</p>";
}
?>