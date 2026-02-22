-- ============================================================
-- TRAZABILIDAD: Solicitud → Despacho → Stock Tránsito → Descarga
-- Ejecutar en la base de datos CENTRAL (epicosie_central)
-- ============================================================

-- 0. despachos: id_solicitud_origen (requerido al aceptar despachos)
ALTER TABLE despachos ADD COLUMN id_solicitud_origen INT NULL DEFAULT NULL;

-- 1. stock_transito: id_solicitud_origen
ALTER TABLE stock_transito ADD COLUMN id_solicitud_origen INT NULL AFTER id_despacho_origen;

-- 2. stock_transito: numero_solicitud
ALTER TABLE stock_transito ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen;

-- 3. stock_transito: índice
ALTER TABLE stock_transito ADD INDEX idx_solicitud_transportador (id_solicitud_origen, transportador_id, codigo_producto);

-- 4. registro_descargas_stock_transito: id_solicitud_origen
ALTER TABLE registro_descargas_stock_transito ADD COLUMN id_solicitud_origen INT NULL AFTER numero_despacho;

-- 5. registro_descargas_stock_transito: numero_solicitud
ALTER TABLE registro_descargas_stock_transito ADD COLUMN numero_solicitud VARCHAR(50) NULL AFTER id_solicitud_origen;
