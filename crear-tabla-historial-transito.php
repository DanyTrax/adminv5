<?php
require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    // Crear tabla recepciones_stock_transito
    $sql = "CREATE TABLE IF NOT EXISTS recepciones_stock_transito (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha_recepcion DATETIME NOT NULL,
        usuario_receptor_id INT NOT NULL,
        usuario_receptor_nombre VARCHAR(100) NOT NULL,
        sucursal_receptor VARCHAR(100) NOT NULL,
        total_productos INT NOT NULL DEFAULT 0,
        total_cantidad INT NOT NULL DEFAULT 0,
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_fecha_recepcion (fecha_recepcion),
        INDEX idx_usuario_receptor (usuario_receptor_id),
        INDEX idx_sucursal_receptor (sucursal_receptor)
    )";
    
    $conexion->exec($sql);
    
    // Crear tabla detalle_recepciones_stock_transito
    $sql = "CREATE TABLE IF NOT EXISTS detalle_recepciones_stock_transito (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recepcion_id INT NOT NULL,
        id_stock_transito_original INT NOT NULL,
        codigo_producto VARCHAR(50) NOT NULL,
        descripcion_producto TEXT NOT NULL,
        cantidad_recibida INT NOT NULL,
        transportador_id INT NOT NULL,
        transportador_nombre VARCHAR(100) NOT NULL,
        sucursal_origen VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (recepcion_id) REFERENCES recepciones_stock_transito(id) ON DELETE CASCADE,
        INDEX idx_recepcion_id (recepcion_id),
        INDEX idx_codigo_producto (codigo_producto),
        INDEX idx_transportador (transportador_id)
    )";
    
    $conexion->exec($sql);
    
    echo "✅ Tablas creadas exitosamente:\n";
    echo "- recepciones_stock_transito\n";
    echo "- detalle_recepciones_stock_transito\n";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>