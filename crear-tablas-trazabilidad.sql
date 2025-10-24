-- =============================================
-- CREAR TABLAS PARA SISTEMA DE TRAZABILIDAD
-- =============================================

-- Tabla principal de movimientos de trazabilidad
CREATE TABLE trazabilidad_movimientos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_despacho VARCHAR(20),
    producto_codigo VARCHAR(50),
    producto_descripcion VARCHAR(255),
    cantidad_total INT,
    cantidad_pendiente INT,
    cantidad_descargada INT DEFAULT 0,
    
    -- ORIGEN
    sucursal_origen VARCHAR(100),
    usuario_origen_id INT,
    fecha_salida DATETIME,
    
    -- TRANSPORTE
    transportador_id INT,
    fecha_aceptacion DATETIME, -- FECHA CLAVE PARA LIFO
    
    -- DESTINO
    sucursal_destino VARCHAR(100),
    usuario_destino_id INT,
    fecha_llegada DATETIME,
    
    -- ESTADO
    estado ENUM('espera', 'en_transito', 'parcial', 'entregado', 'cancelado'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_numero_despacho (numero_despacho),
    INDEX idx_producto_codigo (producto_codigo),
    INDEX idx_fecha_aceptacion (fecha_aceptacion),
    INDEX idx_estado (estado)
);

-- Tabla de descargas individuales
CREATE TABLE trazabilidad_descargas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trazabilidad_movimiento_id INT,
    cantidad_descargada INT,
    usuario_descarga_id INT,
    sucursal_descarga VARCHAR(100),
    fecha_descarga DATETIME,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (trazabilidad_movimiento_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
    INDEX idx_trazabilidad_movimiento (trazabilidad_movimiento_id),
    INDEX idx_usuario_descarga (usuario_descarga_id),
    INDEX idx_fecha_descarga (fecha_descarga)
);

-- Tabla de relaciones entre despachos (para casos complejos)
CREATE TABLE trazabilidad_relaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trazabilidad_padre_id INT,
    trazabilidad_hijo_id INT,
    relacion_tipo ENUM('solicitud_despacho', 'despacho_stock', 'stock_descarga'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (trazabilidad_padre_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
    FOREIGN KEY (trazabilidad_hijo_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
    INDEX idx_padre (trazabilidad_padre_id),
    INDEX idx_hijo (trazabilidad_hijo_id)
);
