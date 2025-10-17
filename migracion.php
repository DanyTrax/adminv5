<?php

// Habilitamos la visualización de errores para este script
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requerimos la conexión a la base de datos
// Asegúrate de que la ruta sea correcta según tu proyecto
require_once "modelos/conexion.php";

echo "<h1>Iniciando migración de productos...</h1>";

try {
    $db = Conexion::conectar();
    
    // 1. Obtenemos todas las ventas de la tabla original
    $stmtVentas = $db->prepare("SELECT id, productos FROM ventas");
    $stmtVentas->execute();
    $ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

    // 2. Preparamos la consulta para insertar en la nueva tabla
    $stmtInsert = $db->prepare(
        "INSERT INTO venta_productos (id_venta, descripcion, cantidad, total) 
         VALUES (:id_venta, :descripcion, :cantidad, :total)"
    );

    $productosInsertados = 0;

    // 3. Recorremos cada venta
    foreach ($ventas as $venta) {
        
        echo "Procesando Venta ID: " . $venta['id'] . "<br>";
        
        // 4. Decodificamos el JSON de la columna 'productos'
        $productosArray = json_decode($venta['productos'], true);

        // Verificamos que el JSON sea válido y no esté vacío
        if (is_array($productosArray)) {
            
            // 5. Recorremos cada producto dentro del JSON
            foreach ($productosArray as $producto) {
                
                // Limpiamos el valor 'total' de símbolos '$' y comas ','
                $totalLimpio = str_replace(['$', ','], '', $producto['total']);
                // Nos aseguramos de que sea un número de tipo float
                $totalNumerico = floatval($totalLimpio);

                // 6. Insertamos el producto en la nueva tabla 'venta_productos'
                $stmtInsert->execute([
                    ':id_venta'    => $venta['id'],
                    ':descripcion' => $producto['descripcion'],
                    ':cantidad'    => $producto['cantidad'],
                    ':total'       => $totalNumerico
                ]);
                $productosInsertados++;
            }
        } else {
            echo " - Sin productos válidos en esta venta.<br>";
        }
    }

    echo "<h2>¡Migración completada con éxito!</h2>";
    echo "<h3>Se insertaron " . $productosInsertados . " registros de productos en la nueva tabla.</h3>";

} catch (Exception $e) {
    echo "<h2>¡ERROR DURANTE LA MIGRACIÓN!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

?>