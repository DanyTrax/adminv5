<?php

require_once "modelos/conexion.php";

echo "<h2>Creando tabla de historial de abonos</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Crear tabla para historial de abonos
    $sql = "
    CREATE TABLE IF NOT EXISTS `abonos_historial` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_venta` int(11) NOT NULL,
        `codigo_venta` int(11) NOT NULL,
        `monto_abono` decimal(10,2) NOT NULL,
        `fecha_abono` datetime NOT NULL,
        `id_vendedor_abono` int(11) NOT NULL,
        `nombre_vendedor_abono` varchar(255) NOT NULL,
        `medio_pago` varchar(50) DEFAULT NULL,
        `observaciones` text DEFAULT NULL,
        `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_id_venta` (`id_venta`),
        KEY `idx_codigo_venta` (`codigo_venta`),
        KEY `idx_fecha_abono` (`fecha_abono`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
    ";
    
    $stmt = $conexion->prepare($sql);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Tabla 'abonos_historial' creada exitosamente</p>";
        
        // Verificar estructura de la tabla
        $stmt = $conexion->prepare("DESCRIBE abonos_historial");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estructura de la tabla:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>" . $columna['Field'] . "</td>";
            echo "<td>" . $columna['Type'] . "</td>";
            echo "<td>" . $columna['Null'] . "</td>";
            echo "<td>" . $columna['Key'] . "</td>";
            echo "<td>" . $columna['Default'] . "</td>";
            echo "<td>" . $columna['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ Error al crear la tabla</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Próximos pasos:</h3>";
echo "<ol>";
echo "<li>Ejecutar este script para crear la tabla</li>";
echo "<li>Modificar el controlador de abonos para registrar en el historial</li>";
echo "<li>Actualizar el modal de detalle para mostrar el historial</li>";
echo "</ol>";

?>
