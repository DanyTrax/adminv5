<?php
/**
 * EXPORTAR PRODUCTOS DEL CATÁLOGO MAESTRO
 * =======================================
 */

// Verificar que sea una descarga válida
if (!isset($_GET['plantilla']) || $_GET['plantilla'] !== 'catalogo-maestro') {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Configurar headers para Excel
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="catalogo-maestro-' . date('Y-m-d-H-i') . '.xls"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Conectar a base central
    $servidor = "localhost";
    $nombreBD = "epicosie_central";
    $usuario = "epicosie_central"; 
    $password = "=Nf?M#6A'QU&.6c";
    
    $dbCentral = new PDO(
        "mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4",
        $usuario,
        $password
    );
    $dbCentral->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener todos los productos del catálogo maestro con información de categoría
    $stmt = $dbCentral->prepare("
        SELECT 
            cm.id,
            cm.codigo,
            cm.descripcion,
            cm.id_categoria,
            c.categoria as nombre_categoria,
            cm.precio_venta,
            cm.es_divisible,
            cm.codigo_hijo_mitad,
            cm.codigo_hijo_tercio,
            cm.codigo_hijo_cuarto,
            cm.activo,
            DATE_FORMAT(cm.fecha_actualizacion, '%Y-%m-%d %H:%i:%s') as fecha_actualizacion
        FROM catalogo_maestro cm
        LEFT JOIN categorias c ON cm.id_categoria = c.id
        WHERE cm.activo = 1
        ORDER BY cm.codigo ASC
    ");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contenido Excel con productos reales
    echo '<!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <style>
    table { border-collapse: collapse; width: 100%; font-size: 12px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }
    .numero { text-align: right; }
    .centro { text-align: center; }
    </style>
    </head>
    <body>

    <h2>CATÁLOGO MAESTRO DE PRODUCTOS - EXPORTACIÓN</h2>
    <p><strong>Fecha de exportación:</strong> ' . date('Y-m-d H:i:s') . '</p>
    <p><strong>Total de productos:</strong> ' . count($productos) . '</p>

    <table>
    <tr>
    <th>ID</th>
    <th>CODIGO</th>
    <th>DESCRIPCION</th>
    <th>ID_CATEGORIA</th>
    <th>NOMBRE_CATEGORIA</th>
    <th>PRECIO_VENTA</th>
    <th>ES_DIVISIBLE</th>
    <th>CODIGO_HIJO_MITAD</th>
    <th>CODIGO_HIJO_TERCIO</th>
    <th>CODIGO_HIJO_CUARTO</th>
    <th>FECHA_ACTUALIZACION</th>
    </tr>';

    foreach ($productos as $producto) {
        echo '<tr>';
        echo '<td class="centro">' . $producto['id'] . '</td>';
        echo '<td>' . $producto['codigo'] . '</td>';
        echo '<td>' . $producto['descripcion'] . '</td>';
        echo '<td class="centro">' . $producto['id_categoria'] . '</td>';
        echo '<td>' . $producto['nombre_categoria'] . '</td>';
        echo '<td class="numero">' . number_format($producto['precio_venta'], 0, ',', '.') . '</td>';
        echo '<td class="centro">' . ($producto['es_divisible'] ? 'SI' : 'NO') . '</td>';
        echo '<td>' . ($producto['codigo_hijo_mitad'] ?: '') . '</td>';
        echo '<td>' . ($producto['codigo_hijo_tercio'] ?: '') . '</td>';
        echo '<td>' . ($producto['codigo_hijo_cuarto'] ?: '') . '</td>';
        echo '<td>' . $producto['fecha_actualizacion'] . '</td>';
        echo '</tr>';
    }

    echo '</table>

    <br><br>
    <h3>INSTRUCCIONES PARA IMPORTAR DE NUEVO:</h3>
    <ol>
    <li><strong>Edite los datos</strong> que necesite modificar directamente en esta tabla</li>
    <li><strong>NO modifique</strong> las columnas ID, CODIGO, NOMBRE_CATEGORIA (son de referencia)</li>
    <li><strong>Puede modificar:</strong> DESCRIPCION, ID_CATEGORIA, PRECIO_VENTA, ES_DIVISIBLE, CODIGO_HIJO_*</li>
    <li><strong>Guarde el archivo</strong> en formato Excel (.xls o .xlsx)</li>
    <li><strong>Use la función "Importar Excel"</strong> en el sistema para subir los cambios</li>
    <li><strong>Los productos se actualizarán</strong> automáticamente basándose en el ID</li>
    </ol>

    <h3>NOTAS IMPORTANTES:</h3>
    <ul>
    <li><strong>ES_DIVISIBLE:</strong> Use "SI" o "NO" (sin comillas)</li>
    <li><strong>PRECIO_VENTA:</strong> Solo números, sin puntos ni comas (ej: 15000)</li>
    <li><strong>ID_CATEGORIA:</strong> Debe existir en el sistema</li>
    <li><strong>CODIGO_HIJO_*:</strong> Deje vacío si no aplica</li>
    </ul>

    </body>
    </html>';

} catch (Exception $e) {
    echo '<h2>Error al exportar catálogo maestro</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
}

exit();
?>