<?php
/*=============================================
ACTUALIZAR CONFIGURACIÓN EXISTENTE CON CAMPOS DE IMÁGENES
=============================================*/

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conectado a la base de datos central\n\n";
    
    // Verificar configuración actual
    echo "🔍 Verificando configuración actual...\n";
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "📋 Configuración encontrada:\n";
        echo "  - ID: {$config['id']}\n";
        echo "  - Nombre: {$config['nombre_configuracion']}\n";
        echo "  - Logo Mini Imagen: " . (isset($config['logo_mini_imagen']) ? $config['logo_mini_imagen'] : 'NO DEFINIDO') . "\n";
        echo "  - Logo LG Imagen: " . (isset($config['logo_lg_imagen']) ? $config['logo_lg_imagen'] : 'NO DEFINIDO') . "\n";
        echo "  - Login Logo Imagen: " . (isset($config['login_logo_imagen']) ? $config['login_logo_imagen'] : 'NO DEFINIDO') . "\n";
        echo "  - Favicon Imagen: " . (isset($config['favicon_imagen']) ? $config['favicon_imagen'] : 'NO DEFINIDO') . "\n";
        
        // Actualizar configuración con campos de imágenes
        echo "\n🔄 Actualizando configuración con campos de imágenes...\n";
        
        $stmt = $conexion->prepare("
            UPDATE personalizacion_colores 
            SET 
                logo_mini_imagen = 'vistas/img/plantilla/icono-blanco.png',
                logo_lg_imagen = 'vistas/img/plantilla/logo-blanco-lineal.png',
                login_logo_imagen = 'vistas/img/plantilla/Infinito1.png',
                favicon_imagen = 'vistas/img/plantilla/icono-negro.png'
            WHERE id = ?
        ");
        
        $resultado = $stmt->execute([$config['id']]);
        
        if ($resultado) {
            echo "✅ Configuración actualizada exitosamente\n";
            
            // Verificar actualización
            echo "\n🔍 Verificando actualización...\n";
            $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE id = ?");
            $stmt->execute([$config['id']]);
            $configActualizada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "📋 Configuración actualizada:\n";
            echo "  - Logo Mini Imagen: {$configActualizada['logo_mini_imagen']}\n";
            echo "  - Logo LG Imagen: {$configActualizada['logo_lg_imagen']}\n";
            echo "  - Login Logo Imagen: {$configActualizada['login_logo_imagen']}\n";
            echo "  - Favicon Imagen: {$configActualizada['favicon_imagen']}\n";
            
        } else {
            echo "❌ Error al actualizar la configuración\n";
        }
        
    } else {
        echo "❌ No se encontró configuración activa\n";
        
        // Crear nueva configuración
        echo "🔄 Creando nueva configuración...\n";
        
        $stmt = $conexion->prepare("
            INSERT INTO personalizacion_colores (
                nombre_configuracion,
                navbar_color,
                navbar_text_color,
                navbar_hover_color,
                sidebar_color,
                sidebar_text_color,
                sidebar_hover_color,
                logo_mini_color,
                logo_lg_color,
                logo_background_color,
                logo_mini_imagen,
                logo_lg_imagen,
                login_logo_imagen,
                favicon_imagen,
                icon_color,
                sidebar_toggle_hover_color,
                dropdown_hover_color,
                button_primary_color,
                button_primary_hover_color,
                link_hover_color,
                active_menu_color,
                active_menu_text_color,
                login_gradient_start,
                login_gradient_end,
                login_logo_color,
                login_text_color,
                activo,
                usuario_creador
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
        ");
        
        $stmt->execute([
            'Configuración Principal',
            '#3c8dbc',  // navbar_color
            '#ffffff',  // navbar_text_color
            '#2c3e50',  // navbar_hover_color
            '#222d32',  // sidebar_color
            '#b8c7ce',  // sidebar_text_color
            '#1a252f',  // sidebar_hover_color
            '#ffffff',  // logo_mini_color
            '#ffffff',  // logo_lg_color
            '#3c8dbc',  // logo_background_color
            'vistas/img/plantilla/icono-blanco.png',  // logo_mini_imagen
            'vistas/img/plantilla/logo-blanco-lineal.png',  // logo_lg_imagen
            'vistas/img/plantilla/Infinito1.png',  // login_logo_imagen
            'vistas/img/plantilla/icono-negro.png',  // favicon_imagen
            '#3c8dbc',  // icon_color
            '#2c3e50',  // sidebar_toggle_hover_color
            '#f5f5f5',  // dropdown_hover_color
            '#3c8dbc',  // button_primary_color
            '#2c3e50',  // button_primary_hover_color
            '#2c3e50',  // link_hover_color
            '#1a252f',  // active_menu_color
            '#ffffff',  // active_menu_text_color
            '#3c8dbc',  // login_gradient_start
            '#2c3e50',  // login_gradient_end
            '#ffffff',  // login_logo_color
            '#ffffff'   // login_text_color
        ]);
        
        echo "✅ Nueva configuración creada exitosamente\n";
    }
    
    echo "\n🎉 ¡Proceso completado!\n";
    echo "📝 Los campos de imágenes ya están disponibles en tu configuración\n";
    echo "🔧 Los warnings de 'Undefined array key' ya no deberían aparecer\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
