-- Script SQL para crear la tabla abonos_historial
-- Ejecutar este script en phpMyAdmin

CREATE TABLE IF NOT EXISTS `abonos_historial` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_venta` int(11) NOT NULL,
    `codigo_venta` int(11) NOT NULL,
    `monto_abono` decimal(10,2) NOT NULL,
    `fecha_abono` datetime NOT NULL,
    `id_vendedor_abono` int(11) NOT NULL,
    `nombre_vendedor_abono` varchar(255) NOT NULL,
    `medio_pago` varchar(50) DEFAULT NULL,
    `observaciones` text DEFAULT NULL,
    `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_id_venta` (`id_venta`),
    KEY `idx_codigo_venta` (`codigo_venta`),
    KEY `idx_fecha_abono` (`fecha_abono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
