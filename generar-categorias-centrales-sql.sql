-- =============================================
-- GENERAR CATEGORÍAS CENTRALES DESDE PRODUCTOS
-- =============================================

-- Este script genera las categorías centrales basándose en las categorías
-- que ya existen en los productos de las sucursales

-- =============================================
-- 1. VERIFICAR CATEGORÍAS EXISTENTES EN PRODUCTOS
-- =============================================

-- Ver todas las categorías únicas que están siendo usadas en productos
SELECT DISTINCT 
    categoria,
    COUNT(*) as total_productos
FROM productos 
WHERE categoria IS NOT NULL 
  AND categoria != ''
  AND categoria != 'NULL'
GROUP BY categoria
ORDER BY total_productos DESC;

-- =============================================
-- 2. GENERAR INSERT PARA CATEGORÍAS CENTRALES
-- =============================================

-- Generar comandos INSERT para categorías que no existen en central
-- (Ejecutar este query y copiar los resultados)
SELECT CONCAT(
    'INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) VALUES (',
    QUOTE(categoria), ', ',
    QUOTE(''), ', ',
    '1, 0, NOW());'
) as sql_insert
FROM (
    SELECT DISTINCT categoria
    FROM productos 
    WHERE categoria IS NOT NULL 
      AND categoria != ''
      AND categoria != 'NULL'
) as categorias_productos
WHERE categoria NOT IN (
    SELECT categoria 
    FROM categorias_central
)
ORDER BY categoria;

-- =============================================
-- 3. VERIFICAR CATEGORÍAS QUE YA EXISTEN EN CENTRAL
-- =============================================

-- Categorías que ya están en central
SELECT 
    cc.categoria,
    cc.activo,
    cc.sincronizado,
    COUNT(p.id) as productos_asociados
FROM categorias_central cc
LEFT JOIN productos p ON cc.categoria = p.categoria
GROUP BY cc.categoria, cc.activo, cc.sincronizado
ORDER BY productos_asociados DESC;

-- =============================================
-- 4. COMPARAR CATEGORÍAS LOCAL vs CENTRAL
-- =============================================

-- Categorías en productos que NO están en central
SELECT 
    p.categoria,
    COUNT(p.id) as total_productos,
    'FALTA EN CENTRAL' as estado
FROM productos p
LEFT JOIN categorias_central cc ON p.categoria = cc.categoria
WHERE p.categoria IS NOT NULL 
  AND p.categoria != ''
  AND p.categoria != 'NULL'
  AND cc.categoria IS NULL
GROUP BY p.categoria
ORDER BY total_productos DESC;

-- Categorías en central que NO están en productos
SELECT 
    cc.categoria,
    cc.activo,
    'NO USADA EN PRODUCTOS' as estado
FROM categorias_central cc
LEFT JOIN productos p ON cc.categoria = p.categoria
WHERE p.categoria IS NULL
ORDER BY cc.categoria;

-- =============================================
-- 5. ESTADÍSTICAS DETALLADAS
-- =============================================

-- Resumen por categoría con estadísticas
SELECT 
    COALESCE(p.categoria, 'SIN CATEGORÍA') as categoria,
    COUNT(p.id) as total_productos,
    CASE 
        WHEN cc.categoria IS NOT NULL THEN 'EN CENTRAL'
        WHEN p.categoria IS NULL OR p.categoria = '' THEN 'SIN CATEGORÍA'
        ELSE 'FALTA EN CENTRAL'
    END as estado
FROM productos p
LEFT JOIN categorias_central cc ON p.categoria = cc.categoria
GROUP BY p.categoria, cc.categoria
ORDER BY total_productos DESC;

-- =============================================
-- 6. SCRIPT DE CORRECCIÓN COMPLETO
-- =============================================

-- Paso 1: Crear categorías faltantes
-- (Ejecutar el resultado del query de la sección 2)

-- Paso 2: Verificar que todas las categorías estén activas
UPDATE categorias_central 
SET activo = 1, sincronizado = 0 
WHERE categoria IN (
    SELECT DISTINCT categoria 
    FROM productos 
    WHERE categoria IS NOT NULL 
      AND categoria != ''
      AND categoria != 'NULL'
);

-- Paso 3: Marcar como no sincronizadas para forzar resincronización
UPDATE categorias_central 
SET sincronizado = 0 
WHERE categoria IN (
    SELECT DISTINCT categoria 
    FROM productos 
    WHERE categoria IS NOT NULL 
      AND categoria != ''
      AND categoria != 'NULL'
);

-- =============================================
-- 7. VERIFICACIÓN FINAL
-- =============================================

-- Verificar que todas las categorías de productos estén en central
SELECT 
    p.categoria,
    COUNT(p.id) as productos,
    CASE 
        WHEN cc.categoria IS NOT NULL THEN '✅ EN CENTRAL'
        ELSE '❌ FALTA EN CENTRAL'
    END as estado
FROM productos p
LEFT JOIN categorias_central cc ON p.categoria = cc.categoria
WHERE p.categoria IS NOT NULL 
  AND p.categoria != ''
  AND p.categoria != 'NULL'
GROUP BY p.categoria, cc.categoria
ORDER BY productos DESC;

-- =============================================
-- 8. COMANDOS DE LIMPIEZA (OPCIONAL)
-- =============================================

-- Eliminar categorías centrales que no se usan en productos
-- (CUIDADO: Solo ejecutar si estás seguro)
/*
DELETE FROM categorias_central 
WHERE categoria NOT IN (
    SELECT DISTINCT categoria 
    FROM productos 
    WHERE categoria IS NOT NULL 
      AND categoria != ''
      AND categoria != 'NULL'
);
*/

-- =============================================
-- 9. REPORTE FINAL
-- =============================================

-- Generar reporte final
SELECT 
    'Categorías en productos' as tipo,
    COUNT(DISTINCT categoria) as total
FROM productos 
WHERE categoria IS NOT NULL 
  AND categoria != ''
  AND categoria != 'NULL'

UNION ALL

SELECT 
    'Categorías en central' as tipo,
    COUNT(*) as total
FROM categorias_central

UNION ALL

SELECT 
    'Productos con categoría' as tipo,
    COUNT(*) as total
FROM productos 
WHERE categoria IS NOT NULL 
  AND categoria != ''
  AND categoria != 'NULL'

UNION ALL

SELECT 
    'Productos sin categoría' as tipo,
    COUNT(*) as total
FROM productos 
WHERE categoria IS NULL 
  OR categoria = ''
  OR categoria = 'NULL';
