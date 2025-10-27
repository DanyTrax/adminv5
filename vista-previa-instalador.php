<?php
/**
 * Script para mostrar medios de pago en el instalador
 */

echo "<h2>💳 Vista Previa de Medios de Pago</h2>\n";

try {
    // 1. Obtener sucursales activas desde BD central
    echo "<h3>1. 🏢 Obteniendo sucursales activas:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            codigo_sucursal,
            nombre,
            url_base,
            url_api,
            usuario_bd,
            password_bd,
            nombre_bd,
            host_bd,
            puerto_bd
        FROM sucursales
        WHERE activo = 1
        ORDER BY nombre ASC
    ");
    
    $stmt->execute();
    $sucursales = $stmt->fetchAll();
    
    if (empty($sucursales)) {
        echo "<p style='color: red;'>❌ No hay sucursales activas</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Sucursales activas encontradas: " . count($sucursales) . "</p>\n";
    
    // 2. Mostrar medios de pago de cada sucursal
    echo "<h3>2. 💳 Medios de Pago por Sucursal:</h3>\n";
    
    foreach ($sucursales as $sucursal) {
        echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px; border-radius: 5px;'>\n";
        echo "<h4>🏢 {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
        
        echo "<p><strong>BD Local:</strong> {$sucursal['nombre_bd']} en {$sucursal['host_bd']}</p>\n";
        
        try {
            // Conectar directamente a BD local de la sucursal
            $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
            $pdo_sucursal = new PDO(
                $dsn,
                $sucursal['usuario_bd'],
                $sucursal['password_bd'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Obtener medios de pago de la BD local
            $stmt_medios = $pdo_sucursal->prepare("
                SELECT 
                    id,
                    nombre
                FROM medios_pago
                ORDER BY nombre ASC
            ");
            
            $stmt_medios->execute();
            $medios_pago = $stmt_medios->fetchAll();
            
            if (!empty($medios_pago)) {
                echo "<p style='color: green;'>✅ Medios de pago encontrados: " . count($medios_pago) . "</p>\n";
                
                echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
                echo "<h5>💳 Lista de Medios de Pago:</h5>\n";
                echo "<ul style='margin: 0; padding-left: 20px;'>\n";
                
                foreach ($medios_pago as $medio) {
                    echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                }
                
                echo "</ul>\n";
                echo "</div>\n";
                
                // Mostrar checkbox para selección
                echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
                echo "<label style='display: flex; align-items: center; cursor: pointer;'>\n";
                echo "<input type='checkbox' id='sucursal_{$sucursal['id']}' value='{$sucursal['id']}' style='margin-right: 10px;'>\n";
                echo "<strong>✅ Seleccionar esta sucursal para importar</strong>\n";
                echo "</label>\n";
                echo "</div>\n";
                
            } else {
                echo "<p style='color: orange;'>⚠️ No hay medios de pago en esta sucursal</p>\n";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al conectar con BD local: " . $e->getMessage() . "</p>\n";
        }
        
        echo "</div>\n";
    }
    
    // 3. Resumen y botones
    echo "<h3>3. 🎯 Resumen de Importación:</h3>\n";
    
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h4>📋 Instrucciones:</h4>\n";
    echo "<ol>\n";
    echo "<li><strong>Revisar medios de pago:</strong> Verificar que los medios de pago mostrados son correctos</li>\n";
    echo "<li><strong>Seleccionar sucursales:</strong> Marcar las sucursales que desea importar</li>\n";
    echo "<li><strong>Confirmar importación:</strong> Los medios de pago se importarán al central</li>\n";
    echo "<li><strong>Evitar duplicados:</strong> El sistema evitará importar medios duplicados</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    echo "<div style='text-align: center; margin: 20px 0;'>\n";
    echo "<button onclick='procesarImportacion()' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>\n";
    echo "🚀 Procesar Importación\n";
    echo "</button>\n";
    echo "</div>\n";
    
    // 4. JavaScript para manejar la selección
    echo "<script>\n";
    echo "function procesarImportacion() {\n";
    echo "    const sucursalesSeleccionadas = [];\n";
    echo "    \n";
    echo "    // Obtener sucursales seleccionadas\n";
    echo "    document.querySelectorAll('input[type=\"checkbox\"]:checked').forEach(checkbox => {\n";
    echo "        sucursalesSeleccionadas.push({\n";
    echo "            id: checkbox.value,\n";
    echo "            nombre: checkbox.closest('div').querySelector('h4').textContent\n";
    echo "        });\n";
    echo "    });\n";
    echo "    \n";
    echo "    if (sucursalesSeleccionadas.length === 0) {\n";
    echo "        alert('Por favor seleccione al menos una sucursal para importar');\n";
    echo "        return;\n";
    echo "    }\n";
    echo "    \n";
    echo "    // Mostrar confirmación\n";
    echo "    const mensaje = '¿Está seguro de importar medios de pago de las siguientes sucursales?\\n\\n' +\n";
    echo "        sucursalesSeleccionadas.map(s => '- ' + s.nombre).join('\\n');\n";
    echo "    \n";
    echo "    if (confirm(mensaje)) {\n";
    echo "        // Aquí se procesaría la importación real\n";
    echo "        alert('Importación procesada: ' + sucursalesSeleccionadas.length + ' sucursales seleccionadas');\n";
    echo "    }\n";
    echo "}\n";
    echo "</script>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
