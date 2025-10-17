<?php
session_start();

echo "<h2>🧪 Test Ajax Stock Tránsito Simple</h2>";

echo "<h3>Datos de sesión:</h3>";
echo "<ul>";
echo "<li>Perfil: " . ($_SESSION["perfil"] ?? "NO DEFINIDO") . "</li>";
echo "<li>ID: " . ($_SESSION["id"] ?? "NO DEFINIDO") . "</li>";
echo "</ul>";

echo "<h3>Test de conexión a BD:</h3>";
try {
    require_once "api-transferencias/conexion-central.php";
    
    $stmt = ConexionCentral::conectar()->prepare("SELECT COUNT(*) as total FROM stock_transito");
    $stmt->execute();
    $resultado = $stmt->fetch();
    
    echo "<p>✅ Conexión exitosa - Total registros: " . $resultado["total"] . "</p>";
    
    // Probar consulta del datatable
    $stmt2 = ConexionCentral::conectar()->prepare("
        SELECT * FROM stock_transito 
        WHERE cantidad_disponible > 0 
        ORDER BY fecha_carga DESC 
        LIMIT 5
    ");
    $stmt2->execute();
    $registros = $stmt2->fetchAll();
    
    echo "<p>✅ Consulta específica exitosa - " . count($registros) . " registros con cantidad > 0</p>";
    
    if(count($registros) > 0) {
        echo "<h4>Primeros registros:</h4>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th></tr>";
        foreach($registros as $reg) {
            echo "<tr>";
            echo "<td>" . $reg["id"] . "</td>";
            echo "<td>" . $reg["codigo_producto"] . "</td>";
            echo "<td>" . substr($reg["descripcion_producto"], 0, 30) . "...</td>";
            echo "<td>" . $reg["cantidad_disponible"] . "</td>";
            echo "<td>" . $reg["nombre_transportador"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test del archivo AJAX:</h3>";

// Ejecutar el ajax directamente
ob_start();
try {
    include "ajax/datatable-stock-transito.ajax.php";
    $salida = ob_get_clean();
    
    echo "<h4>✅ Salida del AJAX:</h4>";
    echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars($salida);
    echo "</pre>";
    
    // Verificar si es JSON válido
    $json = json_decode($salida, true);
    if($json) {
        echo "<h4>✅ JSON válido - Registros: " . (isset($json['data']) ? count($json['data']) : 0) . "</h4>";
    } else {
        echo "<h4>❌ JSON inválido - Error: " . json_last_error_msg() . "</h4>";
    }
    
} catch(Exception $e) {
    ob_end_clean();
    echo "<p>❌ Error ejecutando AJAX: " . $e->getMessage() . "</p>";
}
?>