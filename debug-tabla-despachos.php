<?php
/**
 * Script específico para debuggear la tabla despachos en la base central
 */

echo "<h2>🔍 Debug Específico - Tabla Despachos</h2>\n";
echo "<hr>\n";

try {
    require_once "api-transferencias/conexion-central.php";
    $conexion = ConexionCentral::conectar();
    echo "✅ <strong>Conexión a base central exitosa</strong><br>\n";
    
    echo "<h3>1. 📋 Estructura de la tabla 'despachos'</h3>\n";
    
    $stmt = $conexion->query("DESCRIBE despachos");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
    foreach($estructura as $campo) {
        echo "<tr>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "<td>{$campo['Extra']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    echo "<h3>2. 📊 Conteo de registros</h3>\n";
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM despachos");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "📊 <strong>Total de despachos:</strong> $total<br>\n";
    
    if($total > 0) {
        echo "<h3>3. 📋 Primeros 3 despachos (para verificar estructura)</h3>\n";
        $stmt = $conexion->query("SELECT * FROM despachos ORDER BY id DESC LIMIT 3");
        $despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        if(!empty($despachos)) {
            // Mostrar encabezados
            echo "<tr>";
            foreach(array_keys($despachos[0]) as $campo) {
                echo "<th>$campo</th>";
            }
            echo "</tr>\n";
            
            // Mostrar datos
            foreach($despachos as $despacho) {
                echo "<tr>";
                foreach($despacho as $valor) {
                    $valorMostrar = is_null($valor) ? 'NULL' : (strlen($valor) > 50 ? substr($valor, 0, 50) . '...' : $valor);
                    echo "<td>$valorMostrar</td>";
                }
                echo "</tr>\n";
            }
        }
        echo "</table>\n";
    }
    
    echo "<h3>4. 🧪 Prueba de actualización simulada</h3>\n";
    
    // Simular la actualización que está fallando
    $datosPrueba = [
        "estado" => "en_transito",
        "observaciones" => "Prueba de actualización",
        "transportador_id" => 1,
        "nombre_transportador" => "Transportador Test"
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
    
    $valoresArray[] = 1; // ID de prueba
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
    
    echo "<h3>5. 🔍 Verificar permisos de usuario</h3>\n";
    
    $stmt = $conexion->query("SELECT USER() as usuario_actual, DATABASE() as base_actual");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "👤 <strong>Usuario actual:</strong> " . $info['usuario_actual'] . "<br>\n";
    echo "🗄️ <strong>Base de datos actual:</strong> " . $info['base_actual'] . "<br>\n";
    
    // Verificar permisos específicos
    $stmt = $conexion->query("SHOW GRANTS FOR CURRENT_USER()");
    $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h4>🔐 Permisos del usuario:</h4>\n";
    echo "<ul>\n";
    foreach($permisos as $permiso) {
        echo "<li>$permiso</li>\n";
    }
    echo "</ul>\n";
    
} catch(Exception $e) {
    echo "❌ <strong>Error general:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Debug completado.</strong> Revisa los resultados para identificar el problema específico.</p>\n";
?>
