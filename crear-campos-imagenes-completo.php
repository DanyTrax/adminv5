<?php
/*=============================================
CREAR CAMPOS DE IMÁGENES Y ACTUALIZAR CONFIGURACIÓN
=============================================*/

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conectado a la base de datos central\n\n";
    
    // Paso 1: Agregar campos de imágenes a la tabla
    echo "🔄 Paso 1: Agregando campos de imágenes a la tabla...\n";
    
    $camposImagenes = [
        "logo_mini_imagen" => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/icono-blanco.png'",
        "logo_lg_imagen" => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/logo-blanco-lineal.png'",
        "login_logo_imagen" => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/Infinito1.png'",
        "favicon_imagen" => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/icono-negro.png'"
    ];
    
    foreach ($camposImagenes as $campo => $definicion) {
        try {
            $sql = "ALTER TABLE personalizacion_colores ADD COLUMN $campo $definicion";
            $conexion->exec($sql);
            echo "✅ Campo $campo agregado exitosamente\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️ Campo $campo ya existe\n";
            } else {
                echo "❌ Error agregando $campo: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🔄 Paso 2: Actualizando configuración existente...\n";
    
    // Paso 2: Actualizar configuración existente
    $stmt = $conexion->prepare("
        UPDATE personalizacion_colores 
        SET 
            logo_mini_imagen = 'vistas/img/plantilla/icono-blanco.png',
            logo_lg_imagen = 'vistas/img/plantilla/logo-blanco-lineal.png',
            login_logo_imagen = 'vistas/img/plantilla/Infinito1.png',
            favicon_imagen = 'vistas/img/plantilla/icono-negro.png'
        WHERE activo = 1
    ");
    
    $resultado = $stmt->execute();
    $filasAfectadas = $stmt->rowCount();
    
    echo "✅ Configuraciones actualizadas: $filasAfectadas filas\n";
    
    // Paso 3: Verificar configuración actualizada
    echo "\n🔄 Paso 3: Verificando configuración actualizada...\n";
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "📋 Configuración actualizada:\n";
        echo "  - ID: {$config['id']}\n";
        echo "  - Nombre: {$config['nombre_configuracion']}\n";
        echo "  - Logo Mini Imagen: {$config['logo_mini_imagen']}\n";
        echo "  - Logo LG Imagen: {$config['logo_lg_imagen']}\n";
        echo "  - Login Logo Imagen: {$config['login_logo_imagen']}\n";
        echo "  - Favicon Imagen: {$config['favicon_imagen']}\n";
        echo "  - Activo: " . ($config['activo'] ? 'Sí' : 'No') . "\n";
    }
    
    // Paso 4: Mostrar estructura de la tabla
    echo "\n🔄 Paso 4: Verificando estructura de la tabla...\n";
    $stmt = $conexion->prepare("DESCRIBE personalizacion_colores");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $camposImagen = array_filter($columns, function($col) {
        return strpos($col['Field'], '_imagen') !== false;
    });
    
    echo "📋 Campos de imágenes en la tabla:\n";
    foreach ($camposImagen as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) - {$column['Default']}\n";
    }
    
    echo "\n🎉 ¡Proceso completado exitosamente!\n";
    echo "📝 Los campos de imágenes han sido creados y la configuración actualizada\n";
    echo "🔧 Los warnings de 'Undefined array key' ya no deberían aparecer\n";
    echo "✅ El módulo de personalización ahora debería funcionar correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "  1. Verifica que tienes permisos para modificar la tabla\n";
    echo "  2. Verifica que la tabla personalizacion_colores existe\n";
    echo "  3. Verifica la conexión a la base de datos\n";
}
?>
