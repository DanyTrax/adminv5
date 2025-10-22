-- SCRIPT SQL PARA AGREGAR COLUMNAS DE BD A sucursal_local
-- Ejecutar este script en phpMyAdmin o terminal MySQL

-- Verificar estructura actual
DESCRIBE sucursal_local;

-- Agregar columnas de base de datos
ALTER TABLE sucursal_local ADD COLUMN usuario_bd VARCHAR(50) AFTER email;
ALTER TABLE sucursal_local ADD COLUMN password_bd VARCHAR(255) AFTER usuario_bd;
ALTER TABLE sucursal_local ADD COLUMN nombre_bd VARCHAR(100) AFTER password_bd;
ALTER TABLE sucursal_local ADD COLUMN host_bd VARCHAR(255) AFTER nombre_bd;
ALTER TABLE sucursal_local ADD COLUMN puerto_bd INT DEFAULT 3306 AFTER host_bd;

-- Verificar estructura final
DESCRIBE sucursal_local;

-- Mostrar datos actuales (si los hay)
SELECT * FROM sucursal_local;
