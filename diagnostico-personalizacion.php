<?php
/*=============================================
DIAGNÓSTICO DEL MÓDULO DE PERSONALIZACIÓN
=============================================*/

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conectado a la base de datos central\n\n";
    
    // Verificar si la tabla existe
    echo "🔍 Verificando tabla personalizacion_colores...\n";
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'personalizacion_colores'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if (!$tablaExiste) {
        echo "❌ La tabla personalizacion_colores NO existe\n";
        echo "🔧 Necesitas ejecutar crear-campos-imagenes-completo.php primero\n";
        exit;
    }
    
    echo "✅ La tabla personalizacion_colores existe\n\n";
    
    // Verificar estructura de la tabla
    echo "🔍 Verificando estructura de la tabla...\n";
    $stmt = $conexion->prepare("DESCRIBE personalizacion_colores");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Columnas encontradas:\n";
    foreach ($columnas as $columna) {
        echo "  - {$columna['Field']} ({$columna['Type']}) - {$columna['Default']}\n";
    }
    echo "\n";
    
    // Verificar datos en la tabla
    echo "🔍 Verificando datos en la tabla...\n";
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM personalizacion_colores");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    echo "📊 Total de configuraciones: $total\n\n";
    
    if ($total > 0) {
        // Mostrar todas las configuraciones
        echo "🔍 Mostrando todas las configuraciones...\n";
        $stmt = $conexion->prepare("
            SELECT 
                id,
                nombre_configuracion,
                navbar_color,
                sidebar_color,
                activo,
                fecha_actualizacion,
                usuario_creador
            FROM personalizacion_colores 
            ORDER BY fecha_actualizacion DESC
        ");
        $stmt->execute();
        $configuraciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($configuraciones as $config) {
            echo "📋 Configuración ID: {$config['id']}\n";
            echo "   - Nombre: {$config['nombre_configuracion']}\n";
            echo "   - Navbar: {$config['navbar_color']}\n";
            echo "   - Sidebar: {$config['sidebar_color']}\n";
            echo "   - Activo: " . ($config['activo'] ? 'Sí' : 'No') . "\n";
            echo "   - Fecha: {$config['fecha_actualizacion']}\n";
            echo "   - Usuario: {$config['usuario_creador']}\n";
            echo "\n";
        }
        
        // Verificar configuración activa
        echo "🔍 Verificando configuración activa...\n";
        $stmt = $conexion->prepare("
            SELECT * FROM personalizacion_colores 
            WHERE activo = 1 
            ORDER BY fecha_actualizacion DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $configActiva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($configActiva) {
            echo "✅ Configuración activa encontrada:\n";
            echo "   - ID: {$configActiva['id']}\n";
            echo "   - Nombre: {$configActiva['nombre_configuracion']}\n";
            echo "   - Activo: " . ($configActiva['activo'] ? 'Sí' : 'No') . "\n";
        } else {
            echo "❌ No hay configuración activa\n";
        }
        
    } else {
        echo "❌ No hay configuraciones en la tabla\n";
        echo "🔧 Necesitas crear al menos una configuración\n";
    }
    
    // Verificar campos de imágenes
    echo "\n🔍 Verificando campos de imágenes...\n";
    $camposImagen = ['logo_mini_imagen', 'logo_lg_imagen', 'login_logo_imagen', 'favicon_imagen'];
    
    foreach ($camposImagen as $campo) {
        $stmt = $conexion->prepare("SHOW COLUMNS FROM personalizacion_colores LIKE '$campo'");
        $stmt->execute();
        $campoExiste = $stmt->fetch();
        
        if ($campoExiste) {
            echo "✅ Campo $campo existe\n";
        } else {
            echo "❌ Campo $campo NO existe\n";
        }
    }
    
    echo "\n🎯 DIAGNÓSTICO COMPLETADO\n";
    echo "📝 Si hay problemas, ejecuta crear-campos-imagenes-completo.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "  1. Verifica la conexión a la base de datos\n";
    echo "  2. Verifica que la tabla existe\n";
    echo "  3. Verifica los permisos de la base de datos\n";
}
?>
