-- =============================================
-- CREAR TABLA DE REGISTRO DE DESCARGAS SIMPLE
-- =============================================

CREATE TABLE IF NOT EXISTS `registro_descargas_stock_transito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_producto` varchar(50) NOT NULL,
  `descripcion_producto` text,
  `cantidad_descargada` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_nombre` varchar(100) NOT NULL,
  `sucursal_id` int(11) NOT NULL,
  `sucursal_nombre` varchar(100) NOT NULL,
  `transportador_id` int(11) DEFAULT NULL,
  `transportador_nombre` varchar(100) DEFAULT NULL,
  `numero_despacho` varchar(50) DEFAULT NULL,
  `observaciones` text,
  `fecha_descarga` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_codigo_producto` (`codigo_producto`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_sucursal_id` (`sucursal_id`),
  KEY `idx_fecha_descarga` (`fecha_descarga`),
  KEY `idx_numero_despacho` (`numero_despacho`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
