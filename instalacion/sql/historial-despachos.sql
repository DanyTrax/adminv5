-- ============================================================
-- HISTORIAL DE DESPACHOS - Trazabilidad por evento
-- Ejecutar en la base de datos CENTRAL (epicosie_central)
-- ============================================================

CREATE TABLE IF NOT EXISTS historial_despachos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_despacho INT NOT NULL,
    numero_despacho VARCHAR(50) NOT NULL,
    evento VARCHAR(50) NOT NULL,
    estado_anterior VARCHAR(50) NULL,
    estado_nuevo VARCHAR(50) NULL,
    usuario_id INT NULL,
    usuario_nombre VARCHAR(100) NULL,
    observaciones TEXT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_despacho (id_despacho),
    INDEX idx_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
