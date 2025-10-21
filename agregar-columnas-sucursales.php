<?php
require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>🔧 AGREGAR COLUMNAS FALTANTES A SUCURSALES</h2>";

try {
    // Agregar columnas faltantes
    $sql = "
        ALTER TABLE sucursales 
        ADD COLUMN url_base VARCHAR(255) DEFAULT NULL,
        ADD COLUMN url_api VARCHAR(255) DEFAULT NULL,
        ADD COLUMN es_principal TINYINT(1) DEFAULT 0,
        ADD COLUMN fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD COLUMN usuario_bd VARCHAR(100) DEFAULT NULL,
        ADD COLUMN password_bd VARCHAR(255) DEFAULT NULL,
        ADD COLUMN nombre_bd VARCHAR(100) DEFAULT NULL,
        ADD COLUMN host_bd VARCHAR(255) DEFAULT 'localhost',
        ADD COLUMN puerto_bd INT(11) DEFAULT 3306
    ";
    
    $stmt = Conexion::conectar()->prepare($sql);
    $stmt->execute();
    
    echo "<p style='color: green;'>✅ Columnas agregadas exitosamente</p>";
    
    // Verificar la nueva estructura
    echo "<h3>📋 Nueva estructura de la tabla 'sucursales':</h3>";
    $stmt = Conexion::conectar()->prepare("DESCRIBE sucursales");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";
    
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
    
    // Actualizar datos de ejemplo
    echo "<h3>📊 Actualizando datos de ejemplo:</h3>";
    
    $sucursales = [
        [
            'id' => 1,
            'url_base' => 'https://pruebas.acrilicosinfinito.com',
            'url_api' => 'https://pruebas.acrilicosinfinito.com/api-transferencias',
            'es_principal' => 1,
            'usuario_bd' => 'epicosie_central',
            'password_bd' => 'central123',
            'nombre_bd' => 'epicosie_central',
            'host_bd' => 'localhost',
            'puerto_bd' => 3306
        ],
        [
            'id' => 2,
            'url_base' => 'https://sucursal2.ejemplo.com',
            'url_api' => 'https://sucursal2.ejemplo.com/api-transferencias',
            'es_principal' => 0,
            'usuario_bd' => 'epicosie_sucursal2',
            'password_bd' => 'sucursal2pass',
            'nombre_bd' => 'epicosie_sucursal2',
            'host_bd' => 'localhost',
            'puerto_bd' => 3306
        ],
        [
            'id' => 3,
            'url_base' => 'https://sucursal3.ejemplo.com',
            'url_api' => 'https://sucursal3.ejemplo.com/api-transferencias',
            'es_principal' => 0,
            'usuario_bd' => 'epicosie_sucursal3',
            'password_bd' => 'sucursal3pass',
            'nombre_bd' => 'epicosie_sucursal3',
            'host_bd' => 'localhost',
            'puerto_bd' => 3306
        ]
    ];
    
    foreach ($sucursales as $sucursal) {
        $sql = "UPDATE sucursales SET 
                url_base = :url_base,
                url_api = :url_api,
                es_principal = :es_principal,
                usuario_bd = :usuario_bd,
                password_bd = :password_bd,
                nombre_bd = :nombre_bd,
                host_bd = :host_bd,
                puerto_bd = :puerto_bd
                WHERE id = :id";
        
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':url_base', $sucursal['url_base']);
        $stmt->bindParam(':url_api', $sucursal['url_api']);
        $stmt->bindParam(':es_principal', $sucursal['es_principal']);
        $stmt->bindParam(':usuario_bd', $sucursal['usuario_bd']);
        $stmt->bindParam(':password_bd', $sucursal['password_bd']);
        $stmt->bindParam(':nombre_bd', $sucursal['nombre_bd']);
        $stmt->bindParam(':host_bd', $sucursal['host_bd']);
        $stmt->bindParam(':puerto_bd', $sucursal['puerto_bd']);
        $stmt->bindParam(':id', $sucursal['id']);
        $stmt->execute();
        
        echo "<p>✅ Sucursal ID {$sucursal['id']} actualizada</p>";
    }
    
    echo "<h3>🎉 PROCESO COMPLETADO</h3>";
    echo "<p>Las columnas necesarias han sido agregadas y los datos de ejemplo actualizados.</p>";
    echo "<p>Ahora la prueba de conexión debería funcionar correctamente.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
