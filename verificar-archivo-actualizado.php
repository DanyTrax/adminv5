<?php
/**
 * Script para verificar que el archivo se actualizó correctamente
 */

echo "<h2>✅ Verificación de Archivo Actualizado</h2>\n";

try {
    // Verificar el contenido del archivo actualizado
    $archivo_path = 'api-transferencias/obtener-medios-pago-activos.php';
    
    if (!file_exists($archivo_path)) {
        echo "<p style='color: red;'>❌ Archivo no encontrado: $archivo_path</p>\n";
        exit;
    }
    
    $contenido = file_get_contents($archivo_path);
    
    echo "<h3>📄 Contenido del Archivo Actualizado:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 15px; border: 1px solid #ccc; font-family: monospace; font-size: 12px; line-height: 1.4; max-height: 400px; overflow-y: auto;'>\n";
    echo htmlspecialchars($contenido);
    echo "</pre>\n";
    
    // Verificar características importantes
    echo "<h3>🔍 Verificación de Características:</h3>\n";
    
    $caracteristicas = [
        'BD LOCAL' => strpos($contenido, 'BD LOCAL de esta sucursal') !== false,
        'Tabla medios_pago' => strpos($contenido, 'FROM medios_pago') !== false,
        'Filtro activo' => strpos($contenido, 'WHERE activo = 1') !== false,
        'Campo id' => strpos($contenido, 'id,') !== false,
        'Respuesta JSON' => strpos($contenido, 'json_encode') !== false,
        'Manejo de errores' => strpos($contenido, 'catch (PDOException') !== false
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>Característica</th><th>Estado</th>\n";
    echo "</tr>\n";
    
    foreach ($caracteristicas as $caracteristica => $encontrado) {
        echo "<tr>\n";
        echo "<td>$caracteristica</td>\n";
        echo "<td>" . ($encontrado ? '✅ Encontrado' : '❌ No encontrado') . "</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Verificar que NO tenga características incorrectas
    echo "<h3>🚫 Verificación de Características Incorrectas:</h3>\n";
    
    $incorrectas = [
        'BD Central' => strpos($contenido, 'base de datos central') !== false,
        'Tabla medios_pago_central' => strpos($contenido, 'medios_pago_central') !== false,
        'SELECT DISTINCT' => strpos($contenido, 'SELECT DISTINCT') !== false
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr style='background: #f0f0f0;'>\n";
    echo "<th>Característica Incorrecta</th><th>Estado</th>\n";
    echo "</tr>\n";
    
    foreach ($incorrectas as $incorrecta => $encontrado) {
        echo "<tr>\n";
        echo "<td>$incorrecta</td>\n";
        echo "<td>" . ($encontrado ? '❌ Encontrado (incorrecto)' : '✅ No encontrado (correcto)') . "</td>\n";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Resumen
    $total_correctas = array_sum($caracteristicas);
    $total_incorrectas = array_sum($incorrectas);
    
    echo "<h3>📊 Resumen:</h3>\n";
    echo "<p><strong>Características correctas:</strong> $total_correctas / " . count($caracteristicas) . "</p>\n";
    echo "<p><strong>Características incorrectas:</strong> $total_incorrectas / " . count($incorrectas) . "</p>\n";
    
    if ($total_correctas === count($caracteristicas) && $total_incorrectas === 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ ¡Archivo actualizado correctamente!</p>\n";
        echo "<p>El archivo ahora consulta la BD local de la sucursal.</p>\n";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Archivo necesita más correcciones</p>\n";
    }
    
    echo "<h3>🎯 Próximos Pasos:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Hacer commit de los cambios:</strong> git add api-transferencias/obtener-medios-pago-activos.php</li>\n";
    echo "<li><strong>Hacer push:</strong> git push origin main</li>\n";
    echo "<li><strong>Hacer pull en cPanel:</strong> git pull origin main</li>\n";
    echo "<li><strong>Probar la API:</strong> Debe devolver medios de pago de la BD local</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
