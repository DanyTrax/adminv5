<?php
session_start();

// Verificar permisos
if(!isset($_SESSION["perfil"]) || $_SESSION["perfil"] != "Administrador"){
    die("Acceso denegado");
}

require_once "api-transferencias/conexion-central.php";

// Obtener categorías con sus IDs y prefijos
$pdo = ConexionCentral::conectar();
$stmt = $pdo->prepare("SELECT id, categoria, prefijo FROM categorias ORDER BY categoria ASC");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar headers para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="plantilla-importacion-productos.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Abrir output stream
$output = fopen('php://output', 'w');

// Agregar BOM para UTF-8 (Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// ENCABEZADOS DE LA PLANTILLA - SOLO 3 CAMPOS OBLIGATORIOS
$encabezados = [
    'DESCRIPCION',           // OBLIGATORIO
    'ID_CATEGORIA',          // OBLIGATORIO - Ver referencia de categorías abajo
    'PRECIO_VENTA'           // OBLIGATORIO
];

fputcsv($output, $encabezados, ',');

// DATOS DE EJEMPLO
if (count($categorias) > 0) {
    $categoriaEjemplo = $categorias[0];
    
    $ejemplo1 = [
        'Ejemplo Producto 1',
        $categoriaEjemplo['id'],  // ID_CATEGORIA
        '10000'
    ];
    
    $ejemplo2 = [
        'Ejemplo Producto 2',
        $categoriaEjemplo['id'],  // ID_CATEGORIA
        '15000'
    ];
    
    fputcsv($output, $ejemplo1, ',');
    fputcsv($output, $ejemplo2, ',');
}

// LÍNEA EN BLANCO
fputcsv($output, [], ',');

// REFERENCIA DE CATEGORÍAS
fputcsv($output, ['=== REFERENCIA DE CATEGORÍAS ==='], ',');
fputcsv($output, ['ID', 'CATEGORIA', 'PREFIJO'], ',');

foreach ($categorias as $categoria) {
    $prefijo = !empty($categoria['prefijo']) ? $categoria['prefijo'] : 'Sin prefijo';
    fputcsv($output, [
        $categoria['id'],
        $categoria['categoria'],
        $prefijo
    ], ',');
}

// LÍNEA EN BLANCO
fputcsv($output, [], ',');

// INSTRUCCIONES
fputcsv($output, ['=== INSTRUCCIONES ==='], ',');
fputcsv($output, ['1. Solo se requieren 3 campos: DESCRIPCION, ID_CATEGORIA y PRECIO_VENTA'], ',');
fputcsv($output, ['2. El CODIGO se genera automáticamente usando el prefijo de la categoría'], ',');
fputcsv($output, ['3. Ejemplo: Si la categoría tiene prefijo "LAM", se generará LAM0001, LAM0002, etc.'], ',');
fputcsv($output, ['4. Todos los productos importados se crean como ACTIVOS automáticamente'], ',');
fputcsv($output, ['5. Use el ID_CATEGORIA de la tabla de referencia de categorías'], ',');

fclose($output);
exit;
