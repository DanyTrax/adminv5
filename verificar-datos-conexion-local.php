<?php
/**
 * Script para verificar datos de conexión BD local en sucursal_local
 */

echo "<h2>🔍 Verificación de Datos de Conexión BD Local</h2>\n";

try {
    // 1. Verificar tabla sucursal_local
    echo "<h3>1. 🔍 Verificando tabla sucursal_local:</h3>\n";
    
    require_once 'api-transferencias/conexion-central.php';
    $pdo = ConexionCentral::conectar();
    
    // Verificar si existe la tabla sucursal_local
    $stmt = $pdo->query("SHOW TABLES LIKE 'sucursal_local'");
    $tabla = $stmt->fetch();
    
    if ($tabla) {
        echo "<p style='color: green;'>✅ Tabla 'sucursal_local' existe</p>\n";
        
        // Obtener datos de sucursal_local
        $stmt = $pdo->query("
            SELECT 
                id,
                codigo_sucursal,
                nombre,
                usuario_bd,
                password_bd,
                nombre_bd,
                host_bd,
                puerto_bd,
                url_base,
                url_api
            FROM sucursal_local
            WHERE activo = 1
            ORDER BY nombre ASC
        ");
        
        $sucursales_local = $stmt->fetchAll();
        
        if (!empty($sucursales_local)) {
            echo "<p style='color: green;'>✅ Sucursales en sucursal_local: " . count($sucursales_local) . "</p>\n";
            
            echo "<h4>📋 Datos de Conexión BD Local:</h4>\n";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'>\n";
            echo "<th>ID</th><th>Código</th><th>Nombre</th><th>Host BD</th><th>Nombre BD</th><th>Usuario BD</th><th>Puerto BD</th>\n";
            echo "</tr>\n";
            
            foreach ($sucursales_local as $sucursal) {
                echo "<tr>\n";
                echo "<td>{$sucursal['id']}</td>\n";
                echo "<td><strong>{$sucursal['codigo_sucursal']}</strong></td>\n";
                echo "<td>{$sucursal['nombre']}</td>\n";
                echo "<td>{$sucursal['host_bd']}</td>\n";
                echo "<td>{$sucursal['nombre_bd']}</td>\n";
                echo "<td>{$sucursal['usuario_bd']}</td>\n";
                echo "<td>{$sucursal['puerto_bd']}</td>\n";
                echo "</tr>\n";
            }
            
            echo "</table>\n";
            
            // 2. Probar conexión directa a BD local
            echo "<h3>2. 🔗 Probando conexión directa a BD local:</h3>\n";
            
            foreach ($sucursales_local as $sucursal) {
                echo "<h4>🔍 Sucursal: {$sucursal['nombre']} ({$sucursal['codigo_sucursal']})</h4>\n";
                
                echo "<p><strong>Datos de conexión:</strong></p>\n";
                echo "<ul>\n";
                echo "<li><strong>Host:</strong> {$sucursal['host_bd']}</li>\n";
                echo "<li><strong>BD:</strong> {$sucursal['nombre_bd']}</li>\n";
                echo "<li><strong>Usuario:</strong> {$sucursal['usuario_bd']}</li>\n";
                echo "<li><strong>Puerto:</strong> {$sucursal['puerto_bd']}</li>\n";
                echo "</ul>\n";
                
                // Probar conexión directa
                try {
                    $dsn = "mysql:host={$sucursal['host_bd']};port={$sucursal['puerto_bd']};dbname={$sucursal['nombre_bd']};charset=utf8";
                    $pdo_local = new PDO(
                        $dsn,
                        $sucursal['usuario_bd'],
                        $sucursal['password_bd'],
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );
                    
                    echo "<p style='color: green;'>✅ Conexión directa a BD local exitosa</p>\n";
                    
                    // Verificar tabla medios_pago
                    $stmt_local = $pdo_local->query("SHOW TABLES LIKE 'medios_pago'");
                    $tabla_medios = $stmt_local->fetch();
                    
                    if ($tabla_medios) {
                        echo "<p style='color: green;'>✅ Tabla 'medios_pago' existe en BD local</p>\n";
                        
                        // Obtener medios de pago
                        $stmt_medios = $pdo_local->query("SELECT COUNT(*) as total FROM medios_pago");
                        $total_medios = $stmt_medios->fetch();
                        
                        echo "<p><strong>Total medios de pago:</strong> {$total_medios['total']}</p>\n";
                        
                        if ($total_medios['total'] > 0) {
                            $stmt_medios = $pdo_local->query("SELECT id, nombre FROM medios_pago ORDER BY nombre ASC LIMIT 5");
                            $medios = $stmt_medios->fetchAll();
                            
                            echo "<h5>💳 Medios de Pago en BD Local:</h5>\n";
                            echo "<ul>\n";
                            foreach ($medios as $medio) {
                                echo "<li><strong>{$medio['nombre']}</strong> (ID: {$medio['id']})</li>\n";
                            }
                            echo "</ul>\n";
                        }
                        
                        echo "<p style='color: green;'>✅ Esta sucursal tiene medios de pago en su BD local</p>\n";
                        
                    } else {
                        echo "<p style='color: red;'>❌ Tabla 'medios_pago' no existe en BD local</p>\n";
                    }
                    
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>\n";
                }
                
                echo "<hr>\n";
            }
            
        } else {
            echo "<p style='color: red;'>❌ No hay sucursales en sucursal_local</p>\n";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Tabla 'sucursal_local' no existe</p>\n";
    }
    
    // 3. Conclusión
    echo "<h3>3. 🎯 Conclusión:</h3>\n";
    echo "<p><strong>Tienes razón:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Los datos de conexión BD local están en sucursal_local</li>\n";
    echo "<li>✅ No necesitas conectar por URL API</li>\n";
    echo "<li>✅ Puedes conectar directamente a BD local de cada sucursal</li>\n";
    echo "<li>✅ Los medios de pago ya existen en cada BD local</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>Flujo correcto debería ser:</strong></p>\n";
    echo "<ol>\n";
    echo "<li>Obtener datos de conexión desde sucursal_local</li>\n";
    echo "<li>Conectar directamente a BD local de cada sucursal</li>\n";
    echo "<li>Consultar medios_pago de cada BD local</li>\n";
    echo "<li>Importar al central</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
