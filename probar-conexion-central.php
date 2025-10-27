<?php
/**
 * Script de prueba para verificar conexión a BD central
 */

echo "<h2>🧪 Prueba de Conexión a BD Central</h2>\n";

try {
    // Incluir conexion-central
    echo "<h3>1. 🔍 Incluyendo conexion-central.php:</h3>\n";
    
    if (file_exists('api-transferencias/conexion-central.php')) {
        echo "<p style='color: green;'>✅ Archivo conexion-central.php encontrado</p>\n";
        require_once 'api-transferencias/conexion-central.php';
    } else {
        echo "<p style='color: red;'>❌ Archivo conexion-central.php no encontrado</p>\n";
        exit;
    }
    
    // Conectar a BD central
    echo "<h3>2. 🔗 Conectando a BD Central:</h3>\n";
    
    try {
        $pdo = ConexionCentral::conectar();
        echo "<p style='color: green;'>✅ Conexión a BD Central exitosa</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>\n";
        exit;
    }
    
    // Verificar tabla sucursales
    echo "<h3>3. 📋 Verificando tabla sucursales:</h3>\n";
    
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'sucursales'");
        $tabla = $stmt->fetch();
        
        if ($tabla) {
            echo "<p style='color: green;'>✅ Tabla 'sucursales' existe</p>\n";
        } else {
            echo "<p style='color: red;'>❌ Tabla 'sucursales' no existe</p>\n";
            exit;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al verificar tabla: " . $e->getMessage() . "</p>\n";
        exit;
    }
    
    // Obtener sucursales
    echo "<h3>4. 🏢 Obteniendo sucursales:</h3>\n";
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM sucursales WHERE activo = 1");
        $resultado = $stmt->fetch();
        
        echo "<p style='color: green;'>✅ Sucursales activas encontradas: {$resultado['total']}</p>\n";
        
        // Mostrar sucursales
        $stmt = $pdo->query("
            SELECT 
                id,
                codigo_sucursal,
                nombre,
                url_base,
                url_api,
                activo
            FROM sucursales
            WHERE activo = 1
            ORDER BY nombre ASC
        ");
        
        $sucursales = $stmt->fetchAll();
        
        if (!empty($sucursales)) {
            echo "<h4>📋 Lista de Sucursales:</h4>\n";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
            echo "<tr style='background: #f0f0f0;'>\n";
            echo "<th>ID</th><th>Código</th><th>Nombre</th><th>URL Base</th><th>URL API</th>\n";
            echo "</tr>\n";
            
            foreach ($sucursales as $sucursal) {
                echo "<tr>\n";
                echo "<td>{$sucursal['id']}</td>\n";
                echo "<td><strong>{$sucursal['codigo_sucursal']}</strong></td>\n";
                echo "<td>{$sucursal['nombre']}</td>\n";
                echo "<td>{$sucursal['url_base']}</td>\n";
                echo "<td>{$sucursal['url_api']}</td>\n";
                echo "</tr>\n";
            }
            
            echo "</table>\n";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error al obtener sucursales: " . $e->getMessage() . "</p>\n";
    }
    
    echo "<h3>5. 🎯 Conclusión:</h3>\n";
    echo "<p style='color: green; font-weight: bold;'>✅ Conexión a BD Central funcionando correctamente</p>\n";
    echo "<p>Ahora los scripts de verificación deberían funcionar.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>\n";
}
?>
