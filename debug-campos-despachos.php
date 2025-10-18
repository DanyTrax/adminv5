<?php
/**
 * Script para verificar los campos exactos de la tabla despachos
 */

echo "<h2>🔍 Debug - Campos de Tabla Despachos</h2>\n";
echo "<hr>\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión a base central exitosa</strong><br>\n";
    
    echo "<h3>📋 Estructura completa de la tabla 'despachos'</h3>\n";
    
    $stmt = $conexion->query("DESCRIBE despachos");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach($estructura as $campo) {
        $color = "";
        if($campo['Field'] == 'estado') $color = "background-color: #d4edda;";
        if($campo['Field'] == 'motivo_cancelacion') $color = "background-color: #f8d7da;";
        if($campo['Field'] == 'usuario_cancelacion') $color = "background-color: #f8d7da;";
        
        echo "<tr style='$color'>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>🔍 Verificar campos específicos para cancelación</h3>\n";
    
    $camposNecesarios = ['estado', 'motivo_cancelacion', 'usuario_cancelacion'];
    $camposExistentes = array_column($estructura, 'Field');
    
    echo "<ul>\n";
    foreach($camposNecesarios as $campo) {
        if(in_array($campo, $camposExistentes)) {
            echo "<li>✅ <strong>$campo</strong> - Existe</li>\n";
        } else {
            echo "<li>❌ <strong>$campo</strong> - NO EXISTE</li>\n";
        }
    }
    echo "</ul>\n";
    
    echo "<h3>🧪 Prueba de actualización simulada</h3>\n";
    
    // Simular la actualización que está fallando
    $datosPrueba = [
        "estado" => "cancelado",
        "motivo_cancelacion" => "Prueba de cancelación",
        "usuario_cancelacion" => "Usuario Test"
    ];
    
    echo "<h4>📝 Datos de prueba:</h4>\n";
    echo "<pre>" . print_r($datosPrueba, true) . "</pre>\n";
    
    // Construir SQL como lo hace la función
    $campos = [];
    $valoresArray = [];
    
    foreach($datosPrueba as $key => $value) {
        $campos[] = "`$key` = ?";
        $valoresArray[] = $value;
    }
    
    $valoresArray[] = 21; // ID de prueba
    $sql = "UPDATE `despachos` SET " . implode(", ", $campos) . " WHERE `id` = ?";
    
    echo "<h4>🔍 SQL generado:</h4>\n";
    echo "<pre>$sql</pre>\n";
    
    echo "<h4>📊 Valores:</h4>\n";
    echo "<pre>" . print_r($valoresArray, true) . "</pre>\n";
    
    // Intentar la actualización
    try {
        $stmt = $conexion->prepare($sql);
        $resultado = $stmt->execute($valoresArray);
        
        if($resultado) {
            echo "✅ <strong>Prueba de actualización exitosa</strong><br>\n";
            echo "📊 <strong>Filas afectadas:</strong> " . $stmt->rowCount() . "<br>\n";
        } else {
            echo "❌ <strong>Prueba de actualización falló</strong><br>\n";
            $errorInfo = $stmt->errorInfo();
            echo "🔍 <strong>Error PDO:</strong> " . print_r($errorInfo, true) . "<br>\n";
        }
    } catch(Exception $e) {
        echo "❌ <strong>Excepción en actualización:</strong> " . $e->getMessage() . "<br>\n";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error general:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Análisis completado.</strong></p>\n";
?>
