<?php
/*=============================================
CREAR TABLA PERSONALIZACIÓN - USANDO CONEXIÓN EXISTENTE
=============================================*/

// Incluir el archivo de conexión central existente
require_once "api-transferencias/conexion-central.php";

try {
    // Usar la conexión existente del sistema
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conectado usando conexión existente del sistema\n\n";
    
    // Crear tabla personalizacion_colores
    $sql = "
    CREATE TABLE IF NOT EXISTS personalizacion_colores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_configuracion VARCHAR(100) NOT NULL DEFAULT 'Configuración Principal',
        navbar_color VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        navbar_text_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        navbar_hover_color VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        sidebar_color VARCHAR(7) NOT NULL DEFAULT '#222d32',
        sidebar_text_color VARCHAR(7) NOT NULL DEFAULT '#b8c7ce',
        sidebar_hover_color VARCHAR(7) NOT NULL DEFAULT '#1a252f',
        logo_mini_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        logo_lg_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        logo_background_color VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        icon_color VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        sidebar_toggle_hover_color VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        dropdown_hover_color VARCHAR(7) NOT NULL DEFAULT '#f5f5f5',
        button_primary_color VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        button_primary_hover_color VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        link_hover_color VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        active_menu_color VARCHAR(7) NOT NULL DEFAULT '#1a252f',
        active_menu_text_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        login_gradient_start VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        login_gradient_end VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        login_logo_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        login_text_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        usuario_creador INT,
        INDEX idx_activo (activo),
        INDEX idx_usuario_creador (usuario_creador)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
    ";
    
    $conexion->exec($sql);
    echo "✅ Tabla 'personalizacion_colores' creada exitosamente\n";
    
    // Verificar si ya existe una configuración
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM personalizacion_colores");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    if ($total == 0) {
        // Insertar configuración por defecto
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
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
        
        echo "✅ Configuración por defecto insertada\n";
    } else {
        echo "⚠️ Ya existe configuración en la tabla\n";
    }
    
    // Mostrar información de la tabla
    echo "\n📋 Información de la tabla:\n";
    $stmt = $conexion->prepare("SHOW TABLE STATUS LIKE 'personalizacion_colores'");
    $stmt->execute();
    $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tableInfo) {
        echo "  - Nombre: {$tableInfo['Name']}\n";
        echo "  - Motor: {$tableInfo['Engine']}\n";
        echo "  - Charset: {$tableInfo['Collation']}\n";
        echo "  - Filas: {$tableInfo['Rows']}\n";
    }
    
    // Mostrar configuración actual
    echo "\n🎨 Configuración actual:\n";
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "  - ID: {$config['id']}\n";
        echo "  - Nombre: {$config['nombre_configuracion']}\n";
        echo "  - Navbar: {$config['navbar_color']}\n";
        echo "  - Sidebar: {$config['sidebar_color']}\n";
        echo "  - Logo BG: {$config['logo_background_color']}\n";
        echo "  - Activo: " . ($config['activo'] ? 'Sí' : 'No') . "\n";
        echo "  - Creado: {$config['fecha_creacion']}\n";
    }
    
    echo "\n🎉 ¡Tabla creada exitosamente usando conexión del sistema!\n";
    echo "📝 El módulo de personalización de colores ya está listo para usar\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "  1. Verifica que el archivo 'api-transferencias/conexion-central.php' existe\n";
    echo "  2. Verifica que la clase 'ConexionCentral' está definida\n";
    echo "  3. Verifica que tienes permisos para crear tablas\n";
    echo "  4. Usa el script alternativo con conexión manual\n";
}
?>
