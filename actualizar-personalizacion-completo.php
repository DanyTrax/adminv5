<?php
/*=============================================
ACTUALIZAR CONFIGURACIÓN Y CAMPOS DE IMÁGENES
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
        echo "🔧 Necesitas ejecutar crear-tabla-personalizacion-simplificada.php primero\n";
        exit;
    }
    
    echo "✅ La tabla personalizacion_colores existe\n\n";
    
    // Agregar campos de imágenes si no existen
    echo "🔄 Agregando campos de imágenes...\n";
    
    $camposImagen = [
        'icono_pequeno' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/icono-blanco.png'",
        'logo_menu' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/logo-blanco-lineal.png'",
        'logo_login' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/Infinito1.png'"
    ];
    
    foreach ($camposImagen as $campo => $definicion) {
        try {
            // Verificar si el campo ya existe
            $stmt = $conexion->prepare("SHOW COLUMNS FROM personalizacion_colores LIKE '$campo'");
            $stmt->execute();
            $campoExiste = $stmt->fetch();
            
            if (!$campoExiste) {
                $sql = "ALTER TABLE personalizacion_colores ADD COLUMN $campo $definicion";
                $conexion->exec($sql);
                echo "✅ Campo $campo agregado exitosamente\n";
            } else {
                echo "⚠️ Campo $campo ya existe\n";
            }
        } catch (Exception $e) {
            echo "❌ Error con $campo: " . $e->getMessage() . "\n";
        }
    }
    
    // Actualizar configuración existente
    echo "\n🔄 Actualizando configuración existente...\n";
    $stmt = $conexion->prepare("
        UPDATE personalizacion_colores 
        SET 
            icono_pequeno = COALESCE(icono_pequeno, 'vistas/img/plantilla/icono-blanco.png'),
            logo_menu = COALESCE(logo_menu, 'vistas/img/plantilla/logo-blanco-lineal.png'),
            logo_login = COALESCE(logo_login, 'vistas/img/plantilla/Infinito1.png')
        WHERE activo = 1
    ");
    
    $resultado = $stmt->execute();
    $filasAfectadas = $stmt->rowCount();
    
    echo "✅ Configuraciones actualizadas: $filasAfectadas filas\n";
    
    // Verificar configuración actualizada
    echo "\n🔍 Verificando configuración actualizada...\n";
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "📋 Configuración actualizada:\n";
        echo "  - ID: {$config['id']}\n";
        echo "  - Nombre: {$config['nombre_configuracion']}\n";
        echo "  - Icono Pequeño: {$config['icono_pequeno']}\n";
        echo "  - Logo Menú: {$config['logo_menu']}\n";
        echo "  - Logo Login: {$config['logo_login']}\n";
    }
    
    // Crear directorio para imágenes personalizadas
    echo "\n🔄 Creando directorio para imágenes personalizadas...\n";
    $directorio = "vistas/img/personalizacion/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
        echo "✅ Directorio $directorio creado\n";
    } else {
        echo "⚠️ Directorio $directorio ya existe\n";
    }
    
    echo "\n🎉 ¡Proceso completado exitosamente!\n";
    echo "📝 Los campos de imágenes han sido creados y la configuración actualizada\n";
    echo "🔧 Los warnings de 'Undefined array key' ya no deberían aparecer\n";
    echo "✅ El módulo de personalización ahora debería funcionar correctamente\n";
    echo "\n📋 PRÓXIMOS PASOS:\n";
    echo "1. Reemplaza controladores/personalizacion-colores-simplificado.controlador.php\n";
    echo "2. Reemplaza vistas/modulos/personalizacion-colores-simplificado.php\n";
    echo "3. Prueba hacer clic en las imágenes para subirlas\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "  1. Verifica que tienes permisos para modificar la tabla\n";
    echo "  2. Verifica que la tabla personalizacion_colores existe\n";
    echo "  3. Verifica la conexión a la base de datos\n";
}
?>
