<?php
/**
 * Script para probar la importación de medios de pago corregida
 */

echo "<h2>🧪 Prueba de Importación de Medios de Pago</h2>\n";

try {
    // 1. Simular datos de instalación
    echo "<h3>1. 🔧 Simulando datos de instalación:</h3>\n";
    
    // Datos de sucursal seleccionada
    $sucursales_seleccionadas = [
        ['id' => '17', 'nombre' => 'Local Pruebas']
    ];
    
    // Datos del central
    $datos_central = [
        'url_central' => 'https://pruebas2.acplasticos.com/',
        'host_bd' => 'localhost',
        'nombre_bd' => 'epicosie_central',
        'usuario_bd' => 'epicosie_central',
        'password_bd' => 'password_central'
    ];
    
    echo "<p style='color: green;'>✅ Sucursales seleccionadas: " . count($sucursales_seleccionadas) . "</p>\n";
    echo "<p><strong>Sucursal:</strong> {$sucursales_seleccionadas[0]['nombre']} (ID: {$sucursales_seleccionadas[0]['id']})</p>\n";
    
    // 2. Conectar a BD central para obtener datos de sucursales
    echo "<h3>2. 🏢 Obteniendo datos de sucursales desde BD central:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo_central = ConexionCentral::conectar();
    
    $stmt = $pdo_central->prepare("
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
        WHERE id = ? AND activo = 1
    ");
    
    $stmt->execute([$sucursales_seleccionadas[0]['id']]);
    $sucursal_data = $stmt->fetch();
    
    if (!$sucursal_data) {
        echo "<p style='color: red;'>❌ No se encontraron datos de la sucursal</p>\n";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Sucursal encontrada: {$sucursal_data['nombre']}</p>\n";
    echo "<p><strong>BD Local:</strong> {$sucursal_data['nombre_bd']} en {$sucursal_data['host_bd']}</p>\n";
    
    // 3. Conectar directamente a BD local de la sucursal
    echo "<h3>3. 💳 Obteniendo medios de pago de BD local:</h3>\n";
    
    try {
        $dsn = "mysql:host={$sucursal_data['host_bd']};port={$sucursal_data['puerto_bd']};dbname={$sucursal_data['nombre_bd']};charset=utf8";
        $pdo_sucursal = new PDO(
            $dsn,
            $sucursal_data['usuario_bd'],
            $sucursal_data['password_bd'],
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
        $medios_pago_sucursal = $stmt_medios->fetchAll();
        
        echo "<p style='color: green;'>✅ Medios de pago encontrados: " . count($medios_pago_sucursal) . "</p>\n";
        
        if (!empty($medios_pago_sucursal)) {
            echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 3px; margin: 10px 0;'>\n";
            echo "<h6>💳 Medios de Pago a Importar:</h6>\n";
            echo "<ul style='margin: 0; padding-left: 20px;'>\n";
            
            foreach ($medios_pago_sucursal as $medio) {
                echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
            }
            
            echo "</ul>\n";
            echo "</div>\n";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al conectar con BD local: " . $e->getMessage() . "</p>\n";
        exit;
    }
    
    // 4. Simular inserción en nueva instalación
    echo "<h3>4. 📥 Simulando inserción en nueva instalación:</h3>\n";
    
    // Simular conexión a nueva BD (usar BD central como ejemplo)
    $pdo_nueva = $pdo_central; // En realidad sería la BD de la nueva instalación
    
    $medios_importados = 0;
    $medios_omitidos = 0;
    
    foreach ($medios_pago_sucursal as $medio) {
        try {
            // Verificar si ya existe en la nueva instalación
            $stmt_check = $pdo_nueva->prepare("
                SELECT id FROM medios_pago 
                WHERE nombre = ?
            ");
            $stmt_check->execute([$medio['nombre']]);
            
            if ($stmt_check->fetch()) {
                echo "<p style='color: orange;'>⚠️ Medio de pago '{$medio['nombre']}' ya existe, omitiendo</p>\n";
                $medios_omitidos++;
                continue;
            }
            
            // Insertar en la nueva instalación (solo nombre)
            $stmt_insert = $pdo_nueva->prepare("
                INSERT INTO medios_pago (
                    nombre
                ) VALUES (?)
            ");
            
            $stmt_insert->execute([
                $medio['nombre']
            ]);
            
            $medios_importados++;
            echo "<p style='color: green;'>✅ Medio de pago importado: {$medio['nombre']}</p>\n";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Error al insertar '{$medio['nombre']}': " . $e->getMessage() . "</p>\n";
        }
    }
    
    // 5. Resumen
    echo "<h3>5. 🎯 Resumen de Importación:</h3>\n";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>\n";
    echo "<h6>📊 Estadísticas:</h6>\n";
    echo "<ul>\n";
    echo "<li><strong>Medios de pago encontrados:</strong> " . count($medios_pago_sucursal) . "</li>\n";
    echo "<li><strong>Medios importados:</strong> $medios_importados</li>\n";
    echo "<li><strong>Medios omitidos:</strong> $medios_omitidos</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    if ($medios_importados > 0) {
        echo "<p style='color: green;'>🎉 ¡Importación exitosa! Se importaron $medios_importados medios de pago.</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ No se importaron medios de pago nuevos.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
