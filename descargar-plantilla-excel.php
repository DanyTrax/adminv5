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

// ENCABEZADOS DE LA PLANTILLA
$encabezados = [
    'ID',                    // Opcional: para actualizar productos existentes
    'CODIGO',                // Opcional: se genera automáticamente con prefijo de categoría
    'DESCRIPCION',           // OBLIGATORIO
    'ID_CATEGORIA',          // OBLIGATORIO - Ver referencia de categorías abajo
    'PRECIO_VENTA',          // OBLIGATORIO
    'ES_DIVISIBLE',          // Opcional: SI/NO
    'CODIGO_HIJO_MITAD',     // Opcional
    'CODIGO_HIJO_TERCIO',    // Opcional
    'CODIGO_HIJO_CUARTO'     // Opcional
];

fputcsv($output, $encabezados, ',');

// DATOS DE EJEMPLO
if (count($categorias) > 0) {
    $categoriaEjemplo = $categorias[0];
    $prefijoEjemplo = !empty($categoriaEjemplo['prefijo']) ? $categoriaEjemplo['prefijo'] : 'PROD';
    
    $ejemplo1 = [
        '',  // ID (vacío para nuevo producto)
        '',  // CODIGO (vacío - se generará automáticamente con prefijo)
        'Ejemplo Producto 1',
        $categoriaEjemplo['id'],  // ID_CATEGORIA
        '10000',
        'NO',
        '',
        '',
        ''
    ];
    
    $ejemplo2 = [
        '',  // ID
        '',  // CODIGO
        'Ejemplo Producto 2',
        $categoriaEjemplo['id'],  // ID_CATEGORIA
        '15000',
        'SI',
        '',
        '',
        ''
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
fputcsv($output, ['1. ID_CATEGORIA es OBLIGATORIO - Use el ID de la tabla de referencia'], ',');
fputcsv($output, ['2. CODIGO se genera automáticamente usando el prefijo de la categoría'], ',');
fputcsv($output, ['3. Si no especifica CODIGO, se generará: PREFIJO0001, PREFIJO0002, etc.'], ',');
fputcsv($output, ['4. DESCRIPCION y PRECIO_VENTA son obligatorios'], ',');
fputcsv($output, ['5. Para actualizar un producto existente, incluya el ID'], ',');

fclose($output);
exit;
