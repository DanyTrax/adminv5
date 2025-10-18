<?php
/**
 * Script para verificar y comparar la estructura de ambas bases de datos
 * epicosie_pruebas (local) vs epicosie_central (central)
 */

echo "<h2>🔍 Verificación de Estructura de Bases de Datos</h2>\n";
echo "<hr>\n";

echo "<h3>📋 Configuración de Conexiones</h3>\n";
echo "<strong>Base Local:</strong> epicosie_pruebas<br>\n";
echo "<strong>Base Central:</strong> epicosie_central<br>\n";
echo "<hr>\n";

// Función para obtener estructura de una tabla
function obtenerEstructuraTabla($conexion, $tabla) {
    try {
        $stmt = $conexion->query("DESCRIBE `$tabla`");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return false;
    }
}

// Función para obtener todas las tablas
function obtenerTablas($conexion) {
    try {
        $stmt = $conexion->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(Exception $e) {
        return false;
    }
}

// Función para comparar estructuras
function compararEstructuras($estructura1, $estructura2, $nombreTabla) {
    $diferencias = [];
    
    if(!$estructura1 || !$estructura2) {
        return ["error" => "No se pudo obtener estructura de una o ambas tablas"];
    }
    
    // Crear arrays indexados por nombre de campo
    $campos1 = [];
    $campos2 = [];
    
    foreach($estructura1 as $campo) {
        $campos1[$campo['Field']] = $campo;
    }
    
    foreach($estructura2 as $campo) {
        $campos2[$campo['Field']] = $campo;
    }
    
    // Comparar campos
    $todosCampos = array_unique(array_merge(array_keys($campos1), array_keys($campos2)));
    
    foreach($todosCampos as $nombreCampo) {
        if(!isset($campos1[$nombreCampo])) {
            $diferencias[] = "Campo '$nombreCampo' solo existe en base central";
        } elseif(!isset($campos2[$nombreCampo])) {
            $diferencias[] = "Campo '$nombreCampo' solo existe en base local";
        } else {
            $campo1 = $campos1[$nombreCampo];
            $campo2 = $campos2[$nombreCampo];
            
            $cambios = [];
            if($campo1['Type'] !== $campo2['Type']) {
                $cambios[] = "Tipo: {$campo1['Type']} vs {$campo2['Type']}";
            }
            if($campo1['Null'] !== $campo2['Null']) {
                $cambios[] = "Null: {$campo1['Null']} vs {$campo2['Null']}";
            }
            if($campo1['Key'] !== $campo2['Key']) {
                $cambios[] = "Key: {$campo1['Key']} vs {$campo2['Key']}";
            }
            if($campo1['Default'] !== $campo2['Default']) {
                $cambios[] = "Default: {$campo1['Default']} vs {$campo2['Default']}";
            }
            if($campo1['Extra'] !== $campo2['Extra']) {
                $cambios[] = "Extra: {$campo1['Extra']} vs {$campo2['Extra']}";
            }
            
            if(!empty($cambios)) {
                $diferencias[] = "Campo '$nombreCampo': " . implode(", ", $cambios);
            }
        }
    }
    
    return $diferencias;
}

echo "<h3>1. 🔌 Verificando Conexión a Base Local (epicosie_pruebas)</h3>\n";

$conexionLocal = null;
$tablasLocal = [];

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    echo "✅ <strong>Conexión local exitosa</strong><br>\n";
    
    $tablasLocal = obtenerTablas($conexionLocal);
    if($tablasLocal) {
        echo "📊 <strong>Total de tablas en local:</strong> " . count($tablasLocal) . "<br>\n";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error conexión local:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";

echo "<h3>2. 🔌 Verificando Conexión a Base Central (epicosie_central)</h3>\n";

$conexionCentral = null;
$tablasCentral = [];

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ <strong>Conexión central exitosa</strong><br>\n";
    
    $tablasCentral = obtenerTablas($conexionCentral);
    if($tablasCentral) {
        echo "📊 <strong>Total de tablas en central:</strong> " . count($tablasCentral) . "<br>\n";
    }
    
} catch(Exception $e) {
    echo "❌ <strong>Error conexión central:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";

if($conexionLocal && $conexionCentral) {
    
    echo "<h3>3. 📋 Comparación de Tablas</h3>\n";
    
    $tablasComunes = array_intersect($tablasLocal, $tablasCentral);
    $soloLocal = array_diff($tablasLocal, $tablasCentral);
    $soloCentral = array_diff($tablasCentral, $tablasLocal);
    
    echo "<h4>📊 Resumen:</h4>\n";
    echo "<ul>\n";
    echo "<li><strong>Tablas comunes:</strong> " . count($tablasComunes) . "</li>\n";
    echo "<li><strong>Solo en local:</strong> " . count($soloLocal) . "</li>\n";
    echo "<li><strong>Solo en central:</strong> " . count($soloCentral) . "</li>\n";
    echo "</ul>\n";
    
    if(!empty($soloLocal)) {
        echo "<h4>🔍 Tablas solo en local:</h4>\n";
        echo "<ul>\n";
        foreach($soloLocal as $tabla) {
            echo "<li>$tabla</li>\n";
        }
        echo "</ul>\n";
    }
    
    if(!empty($soloCentral)) {
        echo "<h4>🔍 Tablas solo en central:</h4>\n";
        echo "<ul>\n";
        foreach($soloCentral as $tabla) {
            echo "<li>$tabla</li>\n";
        }
        echo "</ul>\n";
    }
    
    echo "<hr>\n";
    
    echo "<h3>4. 🔍 Análisis Detallado de Tabla 'despachos'</h3>\n";
    
    if(in_array('despachos', $tablasComunes)) {
        echo "✅ <strong>Tabla 'despachos' existe en ambas bases</strong><br>\n";
        
        $estructuraLocal = obtenerEstructuraTabla($conexionLocal, 'despachos');
        $estructuraCentral = obtenerEstructuraTabla($conexionCentral, 'despachos');
        
        echo "<h4>📋 Estructura en Base Local:</h4>\n";
        if($estructuraLocal) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
            foreach($estructuraLocal as $campo) {
                echo "<tr>";
                echo "<td>{$campo['Field']}</td>";
                echo "<td>{$campo['Type']}</td>";
                echo "<td>{$campo['Null']}</td>";
                echo "<td>{$campo['Key']}</td>";
                echo "<td>{$campo['Default']}</td>";
                echo "<td>{$campo['Extra']}</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
        
        echo "<h4>📋 Estructura en Base Central:</h4>\n";
        if($estructuraCentral) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>\n";
            foreach($estructuraCentral as $campo) {
                echo "<tr>";
                echo "<td>{$campo['Field']}</td>";
                echo "<td>{$campo['Type']}</td>";
                echo "<td>{$campo['Null']}</td>";
                echo "<td>{$campo['Key']}</td>";
                echo "<td>{$campo['Default']}</td>";
                echo "<td>{$campo['Extra']}</td>";
                echo "</tr>\n";
            }
            echo "</table>\n";
        }
        
        echo "<h4>🔍 Comparación de Estructuras:</h4>\n";
        $diferencias = compararEstructuras($estructuraLocal, $estructuraCentral, 'despachos');
        
        if(empty($diferencias)) {
            echo "✅ <strong>Las estructuras son idénticas</strong><br>\n";
        } else {
            echo "⚠️ <strong>Diferencias encontradas:</strong><br>\n";
            echo "<ul>\n";
            foreach($diferencias as $diferencia) {
                echo "<li>$diferencia</li>\n";
            }
            echo "</ul>\n";
        }
        
    } else {
        echo "❌ <strong>Tabla 'despachos' no existe en ambas bases</strong><br>\n";
        
        if(in_array('despachos', $tablasLocal)) {
            echo "✅ Existe solo en base local<br>\n";
        }
        if(in_array('despachos', $tablasCentral)) {
            echo "✅ Existe solo en base central<br>\n";
        }
    }
    
    echo "<hr>\n";
    
    echo "<h3>5. 📊 Conteo de Registros en Tablas Importantes</h3>\n";
    
    $tablasImportantes = ['despachos', 'productos', 'usuarios', 'sucursales'];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Tabla</th><th>Local</th><th>Central</th><th>Diferencia</th></tr>\n";
    
    foreach($tablasImportantes as $tabla) {
        if(in_array($tabla, $tablasComunes)) {
            try {
                $stmt = $conexionLocal->query("SELECT COUNT(*) as total FROM `$tabla`");
                $localCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } catch(Exception $e) {
                $localCount = "Error";
            }
            
            try {
                $stmt = $conexionCentral->query("SELECT COUNT(*) as total FROM `$tabla`");
                $centralCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } catch(Exception $e) {
                $centralCount = "Error";
            }
            
            $diferencia = "";
            if(is_numeric($localCount) && is_numeric($centralCount)) {
                $diff = $centralCount - $localCount;
                $diferencia = $diff > 0 ? "+$diff" : "$diff";
            }
            
            echo "<tr>";
            echo "<td><strong>$tabla</strong></td>";
            echo "<td>$localCount</td>";
            echo "<td>$centralCount</td>";
            echo "<td>$diferencia</td>";
            echo "</tr>\n";
        }
    }
    
    echo "</table>\n";
    
} else {
    echo "<h3>❌ No se puede comparar - Conexiones fallidas</h3>\n";
    echo "<p>Verifica que ambas bases de datos estén configuradas correctamente en el servidor.</p>\n";
}

echo "<hr>\n";
echo "<h3>6. 💡 Recomendaciones</h3>\n";

if($conexionLocal && $conexionCentral) {
    echo "<ul>\n";
    echo "<li>✅ <strong>Ambas conexiones funcionan</strong> - El sistema puede usar cualquiera de las dos</li>\n";
    echo "<li>🔍 <strong>Verificar sincronización</strong> - Asegúrate de que los datos estén sincronizados</li>\n";
    echo "<li>🎯 <strong>Usar base central</strong> - Para despachos es recomendable usar la base central</li>\n";
    echo "<li>📝 <strong>Documentar diferencias</strong> - Si hay diferencias estructurales, documentarlas</li>\n";
    echo "</ul>\n";
} else {
    echo "<ul>\n";
    echo "<li>❌ <strong>Configurar bases de datos</strong> - Verificar credenciales en cPanel</li>\n";
    echo "<li>🔧 <strong>Crear bases faltantes</strong> - Si alguna base no existe, crearla</li>\n";
    echo "<li>📋 <strong>Verificar permisos</strong> - Asegurar que los usuarios tengan acceso</li>\n";
    echo "</ul>\n";
}

echo "<hr>\n";
echo "<p><strong>🎯 Análisis completado.</strong> Revisa los resultados para identificar posibles problemas.</p>\n";
?>
