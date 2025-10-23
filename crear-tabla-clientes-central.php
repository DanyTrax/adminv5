<?php
/**
 * SCRIPT PARA CREAR TABLA clientes_central EN SISTEMA CENTRAL
 * Para ejecutar en cPanel
 * 
 * IMPORTANTE: Este script debe ejecutarse desde el sistema CENTRAL
 * No desde una sucursal local
 */

echo "<h2>🚀 CREAR TABLA clientes_central EN SISTEMA CENTRAL</h2>";

try {
    // Verificar que estamos en el sistema central
    if (!file_exists("api-transferencias/conexion-central.php")) {
        throw new Exception("Este script debe ejecutarse desde el sistema CENTRAL, no desde una sucursal local");
    }
    
    require_once "api-transferencias/conexion-central.php";
    
    // Conectar a BD central
    $conexion = ConexionCentral::conectar();
    
    if (!$conexion) {
        throw new Exception("Error conectando a base de datos central");
    }
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Conexión a BD central exitosa</strong>";
    echo "</div>";
    
    // Verificar si la tabla ya existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'clientes_central'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if ($tablaExiste) {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
        echo "<strong>⚠️ La tabla clientes_central ya existe</strong><br>";
        echo "Se verificará su estructura...";
        echo "</div>";
    } else {
        echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
        echo "<strong>📋 La tabla clientes_central no existe, se creará ahora...</strong>";
        echo "</div>";
    }
    
    // SQL para crear tabla clientes_central
    $sql = "
    CREATE TABLE IF NOT EXISTS clientes_central (
        id_central INT PRIMARY KEY AUTO_INCREMENT,
        documento VARCHAR(20) NOT NULL,
        email VARCHAR(100) NULL,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NULL,
        direccion TEXT NULL,
        fecha_nacimiento DATE NULL,
        sucursales_asignadas TEXT NULL,
        id_local_principal INT NULL,
        sucursal_origen VARCHAR(50) NULL,
        activo TINYINT(1) DEFAULT 1,
        sincronizado TINYINT(1) DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_documento (documento),
        INDEX idx_email (email),
        INDEX idx_sucursal_origen (sucursal_origen)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
    ";
    
    echo "<h3>📋 Creando tabla clientes_central:</h3>";
    
    // Ejecutar SQL
    $conexion->exec($sql);
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<strong>✅ Tabla clientes_central creada exitosamente</strong>";
    echo "</div>";
    
    // Verificar estructura
    echo "<h3>📋 Verificando estructura de tabla:</h3>";
    
    $stmt = $conexion->prepare("DESCRIBE clientes_central");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Estructura de tabla clientes_central:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td><strong>{$columna['Field']}</strong></td>";
        echo "<td>{$columna['Type']}</td>";
        echo "<td>{$columna['Null']}</td>";
        echo "<td>{$columna['Key']}</td>";
        echo "<td>{$columna['Default']}</td>";
        echo "<td>{$columna['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Verificar índices
    echo "<h3>📋 Verificando índices:</h3>";
    
    $stmt = $conexion->prepare("SHOW INDEX FROM clientes_central");
    $stmt->execute();
    $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Índices de tabla clientes_central:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nombre</th><th>Único</th></tr>";
    foreach ($indices as $indice) {
        echo "<tr>";
        echo "<td>{$indice['Column_name']}</td>";
        echo "<td>{$indice['Index_type']}</td>";
        echo "<td>{$indice['Key_name']}</td>";
        echo "<td>" . ($indice['Non_unique'] == 0 ? 'Sí' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Verificar datos existentes
    echo "<h3>📋 Verificando datos existentes:</h3>";
    
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM clientes_central");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e7f3ff; padding: 10px; border-radius: 5px;'>";
    echo "<strong>Total de clientes centrales: {$total}</strong>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin-top: 20px;'>";
    echo "<strong>✅ PROCESO COMPLETADO EXITOSAMENTE</strong><br>";
    echo "La tabla clientes_central está lista para usar.<br>";
    echo "Ahora puedes acceder a la gestión de clientes centrales desde el menú.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<strong>💥 ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<p><em>Proceso completado - " . date('Y-m-d H:i:s') . "</em></p>";
?>
