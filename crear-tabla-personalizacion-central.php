<?php
/*=============================================
CREAR TABLA PERSONALIZACIÓN DE COLORES EN BD CENTRAL
=============================================*/

// Configuración de conexión a la base de datos central
$host = 'localhost';
$dbname = 'epicosie_central';
$username = 'epicosie_central';
$password = '=Nf?M#6A\'QU&.6c';

try {
    // Conectar a la base de datos central con charset específico
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Conectado a la base de datos central: $dbname\n\n";
    
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'personalizacion_colores' creada exitosamente\n";
    
    // Verificar si ya existe una configuración
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM personalizacion_colores");
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    if ($total == 0) {
        // Insertar configuración por defecto
        $stmt = $pdo->prepare("
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
    
    // Mostrar estructura de la tabla
    echo "\n📋 Estructura de la tabla 'personalizacion_colores':\n";
    $stmt = $pdo->prepare("DESCRIBE personalizacion_colores");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Default']}\n";
    }
    
    // Mostrar configuración actual
    echo "\n🎨 Configuración actual:\n";
    $stmt = $pdo->prepare("SELECT * FROM personalizacion_colores WHERE activo = 1 LIMIT 1");
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
    
    echo "\n🎉 ¡Tabla creada exitosamente!\n";
    echo "📝 Ahora puedes usar el módulo de personalización de colores\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\n🔧 Verifica los datos de conexión:\n";
    echo "  - Host: $host\n";
    echo "  - Base de datos: $dbname\n";
    echo "  - Usuario: $username\n";
    echo "  - Contraseña: " . (empty($password) ? 'VACÍA' : 'CONFIGURADA') . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
