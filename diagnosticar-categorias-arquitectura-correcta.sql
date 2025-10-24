-- =============================================
-- DIAGNÓSTICO DE CATEGORÍAS - ARQUITECTURA CORRECTA
-- =============================================
-- 
-- Arquitectura:
-- - BD Central: categorias_central
-- - BD Local (sucursales): productos con campo categoria
-- - API: sincronización entre central y sucursales
--
-- Este script debe ejecutarse en la BD Central

-- =============================================
-- 1. VERIFICAR CATEGORÍAS CENTRALES
-- =============================================

-- Ver todas las categorías centrales
SELECT 
    id,
    categoria,
    descripcion,
    CASE 
        WHEN activo = 1 THEN 'Activa'
        ELSE 'Inactiva'
    END as estado,
    CASE 
        WHEN sincronizado = 1 THEN 'Sincronizada'
        ELSE 'No sincronizada'
    END as sincronizacion,
    fecha_creacion,
    fecha_actualizacion
FROM categorias_central 
ORDER BY categoria;

-- Contar categorías por estado
SELECT 
    CASE 
        WHEN activo = 1 THEN 'Activas'
        ELSE 'Inactivas'
    END as estado,
    COUNT(*) as total
FROM categorias_central 
GROUP BY activo;

-- Contar categorías por sincronización
SELECT 
    CASE 
        WHEN sincronizado = 1 THEN 'Sincronizadas'
        ELSE 'No sincronizadas'
    END as sincronizacion,
    COUNT(*) as total
FROM categorias_central 
GROUP BY sincronizado;

-- =============================================
-- 2. VERIFICAR SUCURSALES
-- =============================================

-- Ver sucursales activas
SELECT 
    id,
    nombre,
    host_bd,
    puerto_bd,
    nombre_bd,
    CASE 
        WHEN activo = 1 THEN 'Activa'
        ELSE 'Inactiva'
    END as estado
FROM sucursales 
WHERE activo = 1
ORDER BY nombre;

-- Contar sucursales
SELECT 
    CASE 
        WHEN activo = 1 THEN 'Activas'
        ELSE 'Inactivas'
    END as estado,
    COUNT(*) as total
FROM sucursales 
GROUP BY activo;

-- =============================================
-- 3. GENERAR COMANDOS PARA ANALIZAR SUCURSALES
-- =============================================

-- Este query genera comandos SQL para ejecutar en cada sucursal
-- (Copiar y ejecutar en cada sucursal)
SELECT CONCAT(
    '-- Analizar categorías en sucursal: ', nombre, '\n',
    '-- Conectar a: ', host_bd, ':', puerto_bd, '/', nombre_bd, '\n',
    '-- Usuario: ', usuario_bd, '\n\n',
    '-- Ver categorías en productos de esta sucursal:\n',
    'SELECT DISTINCT categoria, COUNT(*) as total_productos\n',
    'FROM productos \n',
    'WHERE categoria IS NOT NULL \n',
    '  AND categoria != \'\'\n',
    '  AND categoria != \'NULL\'\n',
    'GROUP BY categoria\n',
    'ORDER BY total_productos DESC;\n\n',
    '-- Ver productos sin categoría:\n',
    'SELECT COUNT(*) as productos_sin_categoria\n',
    'FROM productos \n',
    'WHERE categoria IS NULL \n',
    '   OR categoria = \'\'\n',
    '   OR categoria = \'NULL\';\n\n'
) as comando_analisis
FROM sucursales 
WHERE activo = 1
ORDER BY nombre;

-- =============================================
-- 4. GENERAR INSERT PARA CATEGORÍAS FALTANTES
-- =============================================

-- Este query genera comandos INSERT para categorías que podrían faltar
-- (Ejecutar después de analizar las sucursales)
-- Reemplazar 'CATEGORIA_EJEMPLO' con las categorías reales encontradas

-- Ejemplo de INSERT para categorías comunes:
INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) VALUES 
('ACCESORIOS', '', 1, 0, NOW()),
('BISAGRAS EN ACRILICO', '', 1, 0, NOW()),
('PLACAS DE ACRILICO', '', 1, 0, NOW()),
('TUBOS DE ACRILICO', '', 1, 0, NOW()),
('LAMINAS DE ACRILICO', '', 1, 0, NOW())
ON DUPLICATE KEY UPDATE 
    activo = 1, 
    sincronizado = 0, 
    fecha_actualizacion = NOW();

-- =============================================
-- 5. ACTIVAR TODAS LAS CATEGORÍAS PARA SINCRONIZACIÓN
-- =============================================

-- Marcar todas las categorías como activas y no sincronizadas
-- para forzar la sincronización
UPDATE categorias_central 
SET activo = 1, 
    sincronizado = 0, 
    fecha_actualizacion = NOW()
WHERE categoria IS NOT NULL 
  AND categoria != '';

-- =============================================
-- 6. VERIFICACIÓN FINAL
-- =============================================

-- Verificar estado final de categorías centrales
SELECT 
    categoria,
    CASE 
        WHEN activo = 1 THEN 'Activa'
        ELSE 'Inactiva'
    END as estado,
    CASE 
        WHEN sincronizado = 1 THEN 'Sincronizada'
        ELSE 'No sincronizada'
    END as sincronizacion,
    fecha_creacion,
    fecha_actualizacion
FROM categorias_central 
ORDER BY categoria;

-- Estadísticas finales
SELECT 
    'Categorías totales' as tipo,
    COUNT(*) as total
FROM categorias_central

UNION ALL

SELECT 
    'Categorías activas' as tipo,
    COUNT(*) as total
FROM categorias_central 
WHERE activo = 1

UNION ALL

SELECT 
    'Categorías sincronizadas' as tipo,
    COUNT(*) as total
FROM categorias_central 
WHERE sincronizado = 1

UNION ALL

SELECT 
    'Categorías no sincronizadas' as tipo,
    COUNT(*) as total
FROM categorias_central 
WHERE sincronizado = 0;

-- =============================================
-- 7. COMANDOS DE LIMPIEZA (OPCIONAL)
-- =============================================

-- Eliminar categorías duplicadas (si las hay)
-- CUIDADO: Solo ejecutar si estás seguro
/*
DELETE c1 FROM categorias_central c1
INNER JOIN categorias_central c2 
WHERE c1.id > c2.id 
  AND c1.categoria = c2.categoria;
*/

-- Eliminar categorías vacías o nulas
-- CUIDADO: Solo ejecutar si estás seguro
/*
DELETE FROM categorias_central 
WHERE categoria IS NULL 
   OR categoria = ''
   OR categoria = 'NULL';
*/

-- =============================================
-- 8. INSTRUCCIONES PARA EJECUTAR EN SUCURSALES
-- =============================================

-- Para cada sucursal, ejecutar estos comandos:
-- (Reemplazar los valores de conexión según corresponda)

/*
-- Conectar a sucursal específica
-- mysql -h HOST_SUCURSAL -P PUERTO -u USUARIO -p NOMBRE_BD

-- Ver todas las categorías en productos
SELECT DISTINCT categoria, COUNT(*) as total_productos
FROM productos 
WHERE categoria IS NOT NULL 
  AND categoria != ''
  AND categoria != 'NULL'
GROUP BY categoria
ORDER BY total_productos DESC;

-- Ver productos sin categoría
SELECT COUNT(*) as productos_sin_categoria
FROM productos 
WHERE categoria IS NULL 
   OR categoria = ''
   OR categoria = 'NULL';

-- Ver productos con categoría específica
SELECT codigo, descripcion, categoria
FROM productos 
WHERE categoria = 'CATEGORIA_ESPECIFICA'
LIMIT 10;
*/
