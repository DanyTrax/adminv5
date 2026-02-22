-- ============================================================
-- STOCK_TRANSITO: columnas para trazabilidad por despacho
-- Ejecutar en base CENTRAL (epicosie_central)
-- ============================================================
-- Si las columnas ya existen, MySQL dará "Duplicate column" - el instalador lo ignora

ALTER TABLE stock_transito ADD COLUMN id_despacho_origen INT NULL DEFAULT NULL;

ALTER TABLE stock_transito ADD COLUMN numero_despacho_origen VARCHAR(50) NULL DEFAULT NULL;
