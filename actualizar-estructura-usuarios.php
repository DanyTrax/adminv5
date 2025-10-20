<?php
/**
 * SCRIPT PARA ACTUALIZAR ESTRUCTURA DE USUARIOS Y SUCURSALES
 * 
 * Este script agrega las columnas necesarias para manejar
 * la relación entre usuarios y sucursales de manera adecuada.
 */

// Configuración de conexión
require_once "config.php";

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Actualizando Estructura de Usuarios y Sucursales</h2>";
    
    // 1. Agregar columnas a la tabla usuarios
    echo "<h3>1. Agregando columnas a tabla 'usuarios'...</h3>";
    
    $alteraciones = [
        "ALTER TABLE usuarios ADD COLUMN sucursal_id INT NULL COMMENT 'ID de la sucursal principal del usuario'",
        "ALTER TABLE usuarios ADD COLUMN es_transportador BOOLEAN DEFAULT FALSE COMMENT 'Indica si el usuario es transportador'",
        "ALTER TABLE usuarios ADD COLUMN sucursales_permitidas TEXT NULL COMMENT 'JSON con IDs de sucursales permitidas para transportadores'",
        "ALTER TABLE usuarios ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del usuario'",
        "ALTER TABLE usuarios ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización'"
    ];
    
    foreach ($alteraciones as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ " . substr($sql, 0, 50) . "...<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠️ Columna ya existe: " . substr($sql, 0, 50) . "...<br>";
            } else {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // 2. Crear tabla de relación usuario-sucursal
    echo "<h3>2. Creando tabla 'usuario_sucursal'...</h3>";
    
    $createTable = "
    CREATE TABLE IF NOT EXISTS usuario_sucursal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        sucursal_id INT NOT NULL,
        fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        activo BOOLEAN DEFAULT TRUE,
        observaciones TEXT NULL,
        INDEX idx_usuario (usuario_id),
        INDEX idx_sucursal (sucursal_id),
        UNIQUE KEY unique_usuario_sucursal (usuario_id, sucursal_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ";
    
    try {
        $pdo->exec($createTable);
        echo "✅ Tabla 'usuario_sucursal' creada exitosamente<br>";
    } catch (PDOException $e) {
        echo "⚠️ Tabla ya existe o error: " . $e->getMessage() . "<br>";
    }
    
    // 3. Crear tabla de configuración de sucursales
    echo "<h3>3. Creando tabla 'configuracion_sucursal'...</h3>";
    
    $createConfigTable = "
    CREATE TABLE IF NOT EXISTS configuracion_sucursal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sucursal_id INT NOT NULL,
        nombre_sucursal VARCHAR(100) NOT NULL,
        codigo_sucursal VARCHAR(20) NOT NULL,
        direccion TEXT,
        telefono VARCHAR(20),
        email VARCHAR(100),
        activa BOOLEAN DEFAULT TRUE,
        es_principal BOOLEAN DEFAULT FALSE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_codigo (codigo_sucursal),
        INDEX idx_activa (activa)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ";
    
    try {
        $pdo->exec($createConfigTable);
        echo "✅ Tabla 'configuracion_sucursal' creada exitosamente<br>";
    } catch (PDOException $e) {
        echo "⚠️ Tabla ya existe o error: " . $e->getMessage() . "<br>";
    }
    
    // 4. Migrar datos existentes
    echo "<h3>4. Migrando datos existentes...</h3>";
    
    // Obtener sucursal principal (si existe)
    $stmt = $pdo->query("SELECT * FROM sucursal_local LIMIT 1");
    $sucursalLocal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sucursalLocal) {
        // Insertar configuración de sucursal
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO configuracion_sucursal 
            (sucursal_id, nombre_sucursal, codigo_sucursal, direccion, telefono, email, es_principal, activa)
            VALUES (1, ?, ?, ?, ?, ?, 1, 1)
        ");
        
        $stmt->execute([
            $sucursalLocal['nombre'],
            $sucursalLocal['codigo_sucursal'],
            $sucursalLocal['direccion'],
            $sucursalLocal['telefono'],
            $sucursalLocal['email']
        ]);
        
        echo "✅ Configuración de sucursal local migrada<br>";
        
        // Actualizar usuarios existentes con sucursal_id = 1
        $stmt = $pdo->prepare("UPDATE usuarios SET sucursal_id = 1 WHERE sucursal_id IS NULL");
        $stmt->execute();
        
        echo "✅ Usuarios existentes asignados a sucursal principal<br>";
        
        // Marcar transportadores existentes
        $stmt = $pdo->prepare("UPDATE usuarios SET es_transportador = 1 WHERE perfil = 'Transportador'");
        $stmt->execute();
        
        echo "✅ Usuarios transportadores marcados<br>";
    }
    
    // 5. Crear índices adicionales
    echo "<h3>5. Creando índices adicionales...</h3>";
    
    $indices = [
        "CREATE INDEX idx_usuarios_sucursal ON usuarios(sucursal_id)",
        "CREATE INDEX idx_usuarios_transportador ON usuarios(es_transportador)",
        "CREATE INDEX idx_usuarios_perfil ON usuarios(perfil)"
    ];
    
    foreach ($indices as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ " . substr($sql, 0, 40) . "...<br>";
        } catch (PDOException $e) {
            echo "⚠️ Índice ya existe: " . substr($sql, 0, 40) . "...<br>";
        }
    }
    
    echo "<h3>🎉 ¡Actualización completada exitosamente!</h3>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ul>";
    echo "<li>Actualizar el formulario de creación de usuarios</li>";
    echo "<li>Modificar la lógica de autenticación</li>";
    echo "<li>Crear interfaz para asignar sucursales a usuarios</li>";
    echo "<li>Implementar validaciones de acceso por sucursal</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error en la actualización:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
