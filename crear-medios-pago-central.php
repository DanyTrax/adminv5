<?php
/*=============================================
CREAR TABLA CENTRAL DE MEDIOS DE PAGO
=============================================*/

// Incluir conexión central
require_once "api-transferencias/conexion-central.php";

try {
    $conexionCentral = ConexionCentral::conectar();
    
    // Crear tabla central de medios de pago
    $sqlCrearTabla = "
    CREATE TABLE IF NOT EXISTS medios_pago_central (
        id INT(11) NOT NULL AUTO_INCREMENT,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        tipo ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque', 'otro') NOT NULL DEFAULT 'otro',
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_codigo (codigo),
        INDEX idx_activo (activo),
        INDEX idx_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexionCentral->exec($sqlCrearTabla);
    echo "✅ Tabla medios_pago_central creada exitosamente\n";
    
    // Crear tabla de asignación por sucursal
    $sqlCrearAsignacion = "
    CREATE TABLE IF NOT EXISTS medios_pago_sucursal (
        id INT(11) NOT NULL AUTO_INCREMENT,
        medio_pago_id INT(11) NOT NULL,
        sucursal_id INT(11) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_medio_sucursal (medio_pago_id, sucursal_id),
        FOREIGN KEY (medio_pago_id) REFERENCES medios_pago_central(id) ON DELETE CASCADE,
        FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE CASCADE,
        INDEX idx_sucursal (sucursal_id),
        INDEX idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexionCentral->exec($sqlCrearAsignacion);
    echo "✅ Tabla medios_pago_sucursal creada exitosamente\n";
    
    // Insertar medios de pago básicos
    $mediosBasicos = [
        ['EFE001', 'Efectivo', 'Pago en efectivo', 'efectivo'],
        ['TAR001', 'Tarjeta Débito', 'Pago con tarjeta débito', 'tarjeta'],
        ['TAR002', 'Tarjeta Crédito', 'Pago con tarjeta crédito', 'tarjeta'],
        ['TRA001', 'Transferencia Bancaria', 'Transferencia bancaria', 'transferencia'],
        ['CHE001', 'Cheque', 'Pago con cheque', 'cheque'],
        ['OTR001', 'Otros', 'Otros medios de pago', 'otro']
    ];
    
    $stmtInsertar = $conexionCentral->prepare("
        INSERT IGNORE INTO medios_pago_central (codigo, nombre, descripcion, tipo) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($mediosBasicos as $medio) {
        $stmtInsertar->execute($medio);
    }
    
    echo "✅ Medios de pago básicos insertados\n";
    
    // Verificar tablas creadas
    $stmtVerificar = $conexionCentral->query("SHOW TABLES LIKE 'medios_pago_%'");
    $tablas = $stmtVerificar->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 Tablas creadas:\n";
    foreach ($tablas as $tabla) {
        echo "  ✅ $tabla\n";
    }
    
    // Verificar medios insertados
    $stmtMedios = $conexionCentral->query("SELECT COUNT(*) as total FROM medios_pago_central");
    $totalMedios = $stmtMedios->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "\n📊 Medios de pago disponibles: $totalMedios\n";
    
    echo "\n🎉 ¡Sistema de medios de pago central creado exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
