<?php

// Script simple para crear la tabla abonos_historial
// Ejecutar este archivo en el servidor

require_once "modelos/conexion.php";

echo "<h2>Creando tabla abonos_historial</h2>";

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
        echo "<p style='color: green; font-size: 18px;'>✅ <strong>Tabla 'abonos_historial' creada exitosamente</strong></p>";
        
        // Verificar que la tabla existe
        $stmt = $conexion->prepare("SHOW TABLES LIKE 'abonos_historial'");
        $stmt->execute();
        $tablaExiste = $stmt->fetch();
        
        if ($tablaExiste) {
            echo "<p style='color: green;'>✅ La tabla existe y está lista para usar</p>";
            
            // Mostrar estructura
            $stmt = $conexion->prepare("DESCRIBE abonos_historial");
            $stmt->execute();
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Estructura de la tabla:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($columnas as $columna) {
                echo "<tr>";
                echo "<td><strong>" . $columna['Field'] . "</strong></td>";
                echo "<td>" . $columna['Type'] . "</td>";
                echo "<td>" . $columna['Null'] . "</td>";
                echo "<td>" . $columna['Key'] . "</td>";
                echo "<td>" . $columna['Default'] . "</td>";
                echo "<td>" . $columna['Extra'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<p style='color: red;'>❌ Error: La tabla no se creó correctamente</p>";
        }
        
    } else {
        echo "<p style='color: red; font-size: 18px;'>❌ <strong>Error al crear la tabla</strong></p>";
        $errorInfo = $stmt->errorInfo();
        echo "<p style='color: red;'>Detalles del error: " . implode(' - ', $errorInfo) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Instrucciones:</h3>";
echo "<ol>";
echo "<li>Sube este archivo al servidor</li>";
echo "<li>Ejecuta: <code>https://tu-dominio.com/crear-abonos-historial-simple.php</code></li>";
echo "<li>Verifica que aparezca el mensaje de éxito</li>";
echo "<li>Elimina el archivo después de ejecutarlo</li>";
echo "</ol>";

echo "<p style='background-color: #e7f3ff; padding: 10px; border-left: 4px solid #2196F3;'>";
echo "<strong>Nota:</strong> Una vez que veas el mensaje de éxito, puedes eliminar este archivo del servidor por seguridad.";
echo "</p>";

?>
