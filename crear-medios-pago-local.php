<?php
/*=============================================
CREAR TABLA MEDIOS DE PAGO LOCAL
=============================================*/

// Incluir conexión local
require_once "modelos/conexion.php";

try {
    $conexion = Conexion::conectar();
    
    // Crear tabla medios_pago local
    $sqlCrearTabla = "
    CREATE TABLE IF NOT EXISTS medios_pago (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_nombre (nombre),
        INDEX idx_activo (activo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conexion->exec($sqlCrearTabla);
    echo "✅ Tabla medios_pago local creada exitosamente\n";
    
    // Insertar medios de pago básicos si no existen
    $mediosBasicos = [
        'Efectivo',
        'Tarjeta Débito',
        'Tarjeta Crédito',
        'Transferencia Bancaria',
        'Cheque',
        'Otros'
    ];
    
    $stmtVerificar = $conexion->prepare("SELECT COUNT(*) as total FROM medios_pago");
    $stmtVerificar->execute();
    $totalMedios = $stmtVerificar->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($totalMedios == 0) {
        $stmtInsertar = $conexion->prepare("INSERT INTO medios_pago (nombre) VALUES (?)");
        
        foreach ($mediosBasicos as $medio) {
            $stmtInsertar->execute([$medio]);
        }
        
        echo "✅ Medios de pago básicos insertados\n";
    } else {
        echo "✅ Medios de pago ya existen ($totalMedios encontrados)\n";
    }
    
    // Verificar tabla creada
    $stmtVerificar = $conexion->query("SHOW TABLES LIKE 'medios_pago'");
    $tabla = $stmtVerificar->fetch(PDO::FETCH_COLUMN);
    
    if ($tabla) {
        echo "✅ Tabla medios_pago verificada\n";
    }
    
    // Mostrar medios insertados
    $stmtMedios = $conexion->query("SELECT id, nombre FROM medios_pago WHERE activo = 1 ORDER BY nombre");
    $medios = $stmtMedios->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📋 Medios de pago disponibles:\n";
    foreach ($medios as $medio) {
        echo "  ✅ {$medio['id']} - {$medio['nombre']}\n";
    }
    
    echo "\n🎉 ¡Sistema de medios de pago local creado exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
