<?php
// Script para agregar campos de cronología a stock_transito
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Agregar Cronología a Stock en Tránsito</h2>";

try {
    require_once "api-transferencias/conexion-central.php";
    
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión a base central exitosa<br><br>";
    
    // 1. Verificar si los campos ya existen
    echo "<h3>🔍 Verificando estructura actual</h3>";
    $stmt = $conexionCentral->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $camposExistentes = array_column($campos, 'Field');
    $camposNecesarios = ['orden_carga', 'cronologia_carga', 'sucursal_carga', 'fecha_carga_original'];
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Existe</th><th>Acción</th></tr>";
    
    foreach($camposNecesarios as $campo) {
        $existe = in_array($campo, $camposExistentes);
        $accion = $existe ? '✅ Ya existe' : '➕ Agregar';
        $color = $existe ? 'green' : 'orange';
        
        echo "<tr>";
        echo "<td>$campo</td>";
        echo "<td>" . ($existe ? 'Sí' : 'No') . "</td>";
        echo "<td style='color: $color; font-weight: bold;'>$accion</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 2. Agregar campos faltantes
    $camposFaltantes = array_diff($camposNecesarios, $camposExistentes);
    
    if(!empty($camposFaltantes)) {
        echo "<h3>➕ Agregando campos faltantes</h3>";
        
        foreach($camposFaltantes as $campo) {
            $sql = "";
            switch($campo) {
                case 'orden_carga':
                    $sql = "ALTER TABLE stock_transito ADD COLUMN orden_carga INT NOT NULL DEFAULT 1 AFTER transportador_id";
                    break;
                case 'cronologia_carga':
                    $sql = "ALTER TABLE stock_transito ADD COLUMN cronologia_carga TEXT NULL AFTER orden_carga";
                    break;
                case 'sucursal_carga':
                    $sql = "ALTER TABLE stock_transito ADD COLUMN sucursal_carga VARCHAR(100) NULL AFTER cronologia_carga";
                    break;
                case 'fecha_carga_original':
                    $sql = "ALTER TABLE stock_transito ADD COLUMN fecha_carga_original TIMESTAMP NULL AFTER sucursal_carga";
                    break;
            }
            
            if($sql) {
                try {
                    $stmt = $conexionCentral->prepare($sql);
                    $stmt->execute();
                    echo "✅ Campo '$campo' agregado correctamente<br>";
                } catch(Exception $e) {
                    echo "❌ Error agregando '$campo': " . $e->getMessage() . "<br>";
                }
            }
        }
    } else {
        echo "✅ Todos los campos necesarios ya existen<br>";
    }
    
    // 3. Actualizar registros existentes con datos por defecto
    echo "<h3>🔄 Actualizando registros existentes</h3>";
    
    $sql = "
        UPDATE stock_transito 
        SET 
            orden_carga = 1,
            cronologia_carga = CONCAT('Carga inicial: ', sucursal_origen, ' - ', DATE_FORMAT(fecha_carga, '%Y-%m-%d %H:%i:%s')),
            sucursal_carga = sucursal_origen,
            fecha_carga_original = fecha_carga
        WHERE orden_carga = 1 OR orden_carga IS NULL
    ";
    
    $stmt = $conexionCentral->prepare($sql);
    $stmt->execute();
    $filasAfectadas = $stmt->rowCount();
    
    echo "✅ $filasAfectadas registros actualizados con datos por defecto<br><br>";
    
    // 4. Verificar estructura final
    echo "<h3>📋 Estructura final de stock_transito</h3>";
    $stmt = $conexionCentral->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach($campos as $campo) {
        echo "<tr>";
        echo "<td>" . $campo['Field'] . "</td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . $campo['Default'] . "</td>";
        echo "<td>" . $campo['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    echo "<h3>🎉 Cronología implementada correctamente</h3>";
    echo "<p>Ahora el sistema puede manejar:</p>";
    echo "<ul>";
    echo "<li>✅ Orden de carga por transportador</li>";
    echo "<li>✅ Cronología de aceptaciones</li>";
    echo "<li>✅ Jerarquía de descarga (LIFO)</li>";
    echo "<li>✅ Trazabilidad completa de movimientos</li>";
    echo "</ul>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
