-- =============================================
-- DIAGNÓSTICO DE CATEGORÍAS Y PRODUCTOS
-- =============================================

-- 1. VERIFICAR ESTRUCTURA DE TABLAS
-- =============================================

-- Verificar si existe tabla categorias en sucursal
SHOW TABLES LIKE 'categorias';

-- Verificar estructura de tabla categorias
DESCRIBE categorias;

-- Verificar si existe tabla productos
SHOW TABLES LIKE 'productos';

-- Verificar estructura de tabla productos
DESCRIBE productos;

-- =============================================
-- 2. REVISAR CATEGORÍAS EXISTENTES EN SUCURSAL
-- =============================================

-- Ver todas las categorías que existían en la sucursal
SELECT 
    id,
    categoria,
    DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion,
    CASE 
        WHEN activo = 1 THEN 'Activa'
        ELSE 'Inactiva'
    END as estado
FROM categorias 
ORDER BY categoria;

-- Contar categorías por estado
SELECT 
    CASE 
        WHEN activo = 1 THEN 'Activas'
        ELSE 'Inactivas'
    END as estado,
    COUNT(*) as total
FROM categorias 
GROUP BY activo;

-- =============================================
-- 3. REVISAR PRODUCTOS Y SUS CATEGORÍAS
-- =============================================

-- Ver productos con sus categorías (si hay relación)
SELECT 
    p.id,
    p.codigo,
    p.descripcion,
    p.categoria,
    c.categoria as nombre_categoria,
    CASE 
        WHEN p.activo = 1 THEN 'Activo'
        ELSE 'Inactivo'
    END as estado_producto
FROM productos p
LEFT JOIN categorias c ON p.categoria = c.categoria
ORDER BY p.codigo
LIMIT 20;

-- Contar productos por categoría
SELECT 
    p.categoria,
    c.categoria as nombre_categoria,
    COUNT(*) as total_productos
FROM productos p
LEFT JOIN categorias c ON p.categoria = c.categoria
GROUP BY p.categoria, c.categoria
ORDER BY total_productos DESC;

-- Productos sin categoría asignada
SELECT 
    COUNT(*) as productos_sin_categoria
FROM productos 
WHERE categoria IS NULL OR categoria = '';

-- Productos con categoría pero sin coincidencia en tabla categorias
SELECT 
    p.categoria,
    COUNT(*) as total_productos
FROM productos p
LEFT JOIN categorias c ON p.categoria = c.categoria
WHERE p.categoria IS NOT NULL 
  AND p.categoria != ''
  AND c.categoria IS NULL
GROUP BY p.categoria
ORDER BY total_productos DESC;

-- =============================================
-- 4. REVISAR CATEGORÍAS CENTRALES
-- =============================================

-- Ver categorías en tabla central (si existe)
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
    DATE_FORMAT(fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion
FROM categorias_central 
ORDER BY categoria;

-- =============================================
-- 5. COMPARAR CATEGORÍAS LOCAL vs CENTRAL
-- =============================================

-- Categorías que están en local pero no en central
SELECT DISTINCT
    c.categoria,
    COUNT(p.id) as productos_asociados
FROM categorias c
LEFT JOIN productos p ON c.categoria = p.categoria
LEFT JOIN categorias_central cc ON c.categoria = cc.categoria
WHERE cc.categoria IS NULL
GROUP BY c.categoria
ORDER BY productos_asociados DESC;

-- Categorías que están en central pero no en local
SELECT 
    cc.categoria,
    cc.descripcion,
    cc.activo
FROM categorias_central cc
LEFT JOIN categorias c ON cc.categoria = c.categoria
WHERE c.categoria IS NULL
ORDER BY cc.categoria;

-- =============================================
-- 6. ESTADÍSTICAS GENERALES
-- =============================================

-- Resumen general
SELECT 
    'Categorías locales' as tipo,
    COUNT(*) as total
FROM categorias
UNION ALL
SELECT 
    'Productos totales' as tipo,
    COUNT(*) as total
FROM productos
UNION ALL
SELECT 
    'Productos con categoría' as tipo,
    COUNT(*) as total
FROM productos 
WHERE categoria IS NOT NULL AND categoria != ''
UNION ALL
SELECT 
    'Productos sin categoría' as tipo,
    COUNT(*) as total
FROM productos 
WHERE categoria IS NULL OR categoria = '';

-- =============================================
-- 7. SUGERENCIAS DE CORRECCIÓN
-- =============================================

-- Generar INSERT para categorías locales que no están en central
SELECT CONCAT(
    'INSERT INTO categorias_central (categoria, descripcion, activo, sincronizado, fecha_creacion) VALUES (',
    QUOTE(c.categoria), ', ',
    QUOTE(COALESCE(c.descripcion, '')), ', ',
    c.activo, ', 0, NOW());'
) as sql_insert
FROM categorias c
LEFT JOIN categorias_central cc ON c.categoria = cc.categoria
WHERE cc.categoria IS NULL
ORDER BY c.categoria;
