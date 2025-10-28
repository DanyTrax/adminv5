<?php
/*=============================================
ACTUALIZAR CONFIGURACIÓN CON CAMPOS DE IMÁGENES
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
    
    // Verificar si los campos de imágenes existen
    echo "🔍 Verificando campos de imágenes...\n";
    $camposImagen = ['icono_pequeno', 'logo_menu', 'logo_login'];
    $camposFaltantes = [];
    
    foreach ($camposImagen as $campo) {
        $stmt = $conexion->prepare("SHOW COLUMNS FROM personalizacion_colores LIKE '$campo'");
        $stmt->execute();
        $campoExiste = $stmt->fetch();
        
        if (!$campoExiste) {
            $camposFaltantes[] = $campo;
            echo "❌ Campo $campo NO existe\n";
        } else {
            echo "✅ Campo $campo existe\n";
        }
    }
    
    // Agregar campos faltantes
    if (!empty($camposFaltantes)) {
        echo "\n🔄 Agregando campos faltantes...\n";
        
        $definicionesCampos = [
            'icono_pequeno' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/icono-blanco.png'",
            'logo_menu' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/logo-blanco-lineal.png'",
            'logo_login' => "VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/Infinito1.png'"
        ];
        
        foreach ($camposFaltantes as $campo) {
            try {
                $sql = "ALTER TABLE personalizacion_colores ADD COLUMN $campo {$definicionesCampos[$campo]}";
                $conexion->exec($sql);
                echo "✅ Campo $campo agregado exitosamente\n";
            } catch (Exception $e) {
                echo "❌ Error agregando $campo: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Actualizar configuración existente con valores por defecto
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
        echo "  - Activo: " . ($config['activo'] ? 'Sí' : 'No') . "\n";
    }
    
    // Verificar estructura final
    echo "\n🔍 Verificando estructura final de la tabla...\n";
    $stmt = $conexion->prepare("DESCRIBE personalizacion_colores");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $camposImagenFinal = array_filter($columnas, function($col) {
        return in_array($col['Field'], ['icono_pequeno', 'logo_menu', 'logo_login']);
    });
    
    echo "📋 Campos de imágenes en la tabla:\n";
    foreach ($camposImagenFinal as $column) {
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
