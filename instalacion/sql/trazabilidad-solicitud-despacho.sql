-- ============================================================
-- TRAZABILIDAD: Solicitud → Despacho → Stock Tránsito → Descarga
-- Ejecutar en la base de datos CENTRAL (epicosie_central)
-- ============================================================

-- 1. Agregar id_solicitud_origen y numero_solicitud a stock_transito (para agrupar y trazabilidad)
ALTER TABLE stock_transito 
ADD COLUMN id_solicitud_origen INT NULL AFTER id_despacho_origen,
ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen,
ADD INDEX idx_solicitud_transportador (id_solicitud_origen, transportador_id, codigo_producto);

-- 2. Agregar id_solicitud_origen a registro_descargas_stock_transito (trazabilidad de origen)
ALTER TABLE registro_descargas_stock_transito 
ADD COLUMN id_solicitud_origen INT NULL AFTER numero_despacho,
ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen;

-- 3. Solicitudes: soportar estado 'finalizado' (cuando todos los productos ya fueron despachados)
-- Si la columna estado es ENUM, modificar. Si es VARCHAR, no hace falta.
-- ALTER TABLE solicitudes_stock MODIFY estado VARCHAR(20) DEFAULT 'pendiente';

-- Si ya ejecutó la migración anterior sin numero_solicitud en stock_transito, ejecutar:
-- ALTER TABLE stock_transito ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen;
