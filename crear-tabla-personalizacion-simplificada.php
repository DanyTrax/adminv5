<?php
/*=============================================
CREAR TABLA PERSONALIZACIÓN SIMPLIFICADA
=============================================*/

require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "✅ Conectado a la base de datos central\n\n";
    
    // Eliminar tabla existente si existe
    echo "🔄 Eliminando tabla existente...\n";
    try {
        $conexion->exec("DROP TABLE IF EXISTS personalizacion_colores");
        echo "✅ Tabla anterior eliminada\n";
    } catch (Exception $e) {
        echo "⚠️ No había tabla anterior\n";
    }
    
    // Crear nueva tabla simplificada
    echo "🔄 Creando tabla simplificada...\n";
    $sql = "
    CREATE TABLE personalizacion_colores (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre_configuracion VARCHAR(100) NOT NULL DEFAULT 'Mi Configuración',
        
        -- Gradiente del login (2 colores)
        login_gradient_start VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        login_gradient_end VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        
        -- Barra principal (navbar + logo - mismo color)
        navbar_color VARCHAR(7) NOT NULL DEFAULT '#3c8dbc',
        navbar_hover_color VARCHAR(7) NOT NULL DEFAULT '#2c3e50',
        
        -- Barra lateral
        sidebar_color VARCHAR(7) NOT NULL DEFAULT '#222d32',
        sidebar_hover_color VARCHAR(7) NOT NULL DEFAULT '#1a252f',
        sidebar_text_color VARCHAR(7) NOT NULL DEFAULT '#b8c7ce',
        
        -- Imágenes
        icono_pequeno VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/icono-blanco.png',
        logo_menu VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/logo-blanco-lineal.png',
        logo_login VARCHAR(255) NOT NULL DEFAULT 'vistas/img/plantilla/Infinito1.png',
        
        -- Control
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        usuario_creador INT(11) DEFAULT 1,
        
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $conexion->exec($sql);
    echo "✅ Tabla simplificada creada\n\n";
    
    // Insertar configuración por defecto
    echo "🔄 Insertando configuración por defecto...\n";
    $stmt = $conexion->prepare("
        INSERT INTO personalizacion_colores (
            nombre_configuracion,
            login_gradient_start,
            login_gradient_end,
            navbar_color,
            navbar_hover_color,
            sidebar_color,
            sidebar_hover_color,
            sidebar_text_color,
            icono_pequeno,
            logo_menu,
            logo_login,
            activo,
            usuario_creador
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    
    $stmt->execute([
        'Configuración Principal',
        '#3c8dbc',  // login_gradient_start
        '#2c3e50',  // login_gradient_end
        '#3c8dbc',  // navbar_color
        '#2c3e50',  // navbar_hover_color
        '#222d32',  // sidebar_color
        '#1a252f',  // sidebar_hover_color
        '#b8c7ce',  // sidebar_text_color
        'vistas/img/plantilla/icono-blanco.png',
        'vistas/img/plantilla/logo-blanco-lineal.png',
        'vistas/img/plantilla/Infinito1.png'
    ]);
    
    echo "✅ Configuración por defecto insertada\n\n";
    
    // Verificar estructura
    echo "🔍 Verificando estructura de la tabla...\n";
    $stmt = $conexion->prepare("DESCRIBE personalizacion_colores");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Columnas creadas:\n";
    foreach ($columnas as $columna) {
        echo "  - {$columna['Field']} ({$columna['Type']}) - {$columna['Default']}\n";
    }
    
    // Verificar datos
    echo "\n🔍 Verificando configuración insertada...\n";
    $stmt = $conexion->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "✅ Configuración activa encontrada:\n";
        echo "  - ID: {$config['id']}\n";
        echo "  - Nombre: {$config['nombre_configuracion']}\n";
        echo "  - Gradiente Login: {$config['login_gradient_start']} → {$config['login_gradient_end']}\n";
        echo "  - Barra Principal: {$config['navbar_color']} (hover: {$config['navbar_hover_color']})\n";
        echo "  - Barra Lateral: {$config['sidebar_color']} (hover: {$config['sidebar_hover_color']}, texto: {$config['sidebar_text_color']})\n";
        echo "  - Icono Pequeño: {$config['icono_pequeno']}\n";
        echo "  - Logo Menú: {$config['logo_menu']}\n";
        echo "  - Logo Login: {$config['logo_login']}\n";
    }
    
    echo "\n🎉 ¡Tabla simplificada creada exitosamente!\n";
    echo "📝 Ahora puedes usar el módulo de personalización simplificado\n";
    echo "🔧 Solo 3 colores principales + 3 imágenes + hover\n";
    echo "✅ Mucho más fácil de entender y usar\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Posibles soluciones:\n";
    echo "  1. Verifica que tienes permisos para crear tablas\n";
    echo "  2. Verifica la conexión a la base de datos\n";
    echo "  3. Verifica que la base de datos existe\n";
}
?>
