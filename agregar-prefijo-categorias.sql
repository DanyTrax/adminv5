-- Script para agregar campo prefijo a la tabla categorias en BD central
-- Ejecutar este script en la base de datos central (epicosie_central)

-- Agregar columna prefijo a la tabla categorias
ALTER TABLE categorias 
ADD COLUMN prefijo VARCHAR(10) NULL DEFAULT NULL AFTER categoria;

-- Agregar índice para búsquedas rápidas
CREATE INDEX idx_prefijo ON categorias(prefijo);

-- Comentario: El prefijo se usará para generar códigos de productos
-- Ejemplo: Si la categoría es "LAMINADO DE ACRILICO" y el prefijo es "LAM"
-- Los productos tendrán códigos como: LAM0001, LAM0002, etc.
