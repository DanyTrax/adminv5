<?php

session_start();
require_once "config.php";
require_once "modelos/conexion.php";

echo "<h2>🔍 VERIFICAR TABLA SUCURSAL_LOCAL</h2>";

try {
    // ✅ VERIFICAR CONEXIÓN LOCAL
    $conexionLocal = Conexion::conectar();
    
    if($conexionLocal) {
        echo "<p>✅ <strong>Conexión local exitosa</strong></p>";
        
        // ✅ VERIFICAR SI TABLA EXISTE
        $stmt = $conexionLocal->prepare("SHOW TABLES LIKE 'sucursal_local'");
        $stmt->execute();
        
        if($stmt->fetch()) {
            echo "<p>✅ <strong>Tabla sucursal_local existe</strong></p>";
            
            // ✅ VERIFICAR ESTRUCTURA
            $stmt = $conexionLocal->prepare("DESCRIBE sucursal_local");
            $stmt->execute();
            $columnas = $stmt->fetchAll();
            
            echo "<p>✅ <strong>Estructura de la tabla:</strong></p>";
            echo "<ul>";
            foreach($columnas as $columna) {
                echo "<li>{$columna['Field']} - {$columna['Type']}</li>";
            }
            echo "</ul>";
            
            // ✅ VERIFICAR DATOS
            $stmt = $conexionLocal->prepare("SELECT * FROM sucursal_local");
            $stmt->execute();
            $sucursales = $stmt->fetchAll();
            
            echo "<p>✅ <strong>Datos en la tabla:</strong></p>";
            if(count($sucursales) > 0) {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Código Sucursal</th><th>Nombre</th><th>Otros campos...</th></tr>";
                foreach($sucursales as $sucursal) {
                    echo "<tr>";
                    foreach($sucursal as $campo => $valor) {
                        if(!is_numeric($campo)) { // Solo mostrar campos con nombre
                            echo "<td>{$valor}</td>";
                        }
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>❌ <strong>No hay datos en la tabla sucursal_local</strong></p>";
                
                // ✅ INSERTAR DATOS DE EJEMPLO
                echo "<p>🔧 <strong>Insertando datos de ejemplo...</strong></p>";
                
                $stmt = $conexionLocal->prepare("INSERT INTO sucursal_local (codigo_sucursal, nombre) VALUES ('SUC001', 'Sucursal Principal')");
                
                if($stmt->execute()) {
                    echo "<p>✅ <strong>Datos de ejemplo insertados</strong></p>";
                } else {
                    echo "<p>❌ <strong>Error insertando datos de ejemplo:</strong></p>";
                    print_r($stmt->errorInfo());
                }
            }
            
        } else {
            echo "<p>❌ <strong>Tabla sucursal_local NO existe</strong></p>";
            
            // ✅ CREAR TABLA SI NO EXISTE
            echo "<p>🔧 <strong>Creando tabla sucursal_local...</strong></p>";
            
            $sqlCrear = "
            CREATE TABLE sucursal_local (
                id INT AUTO_INCREMENT PRIMARY KEY,
                codigo_sucursal VARCHAR(20) NOT NULL UNIQUE,
                nombre VARCHAR(100) NOT NULL,
                direccion VARCHAR(200) NULL,
                telefono VARCHAR(50) NULL,
                email VARCHAR(100) NULL,
                activo TINYINT(1) DEFAULT 1,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if($conexionLocal->exec($sqlCrear)) {
                echo "<p>✅ <strong>Tabla sucursal_local creada</strong></p>";
                
                // Insertar datos de ejemplo
                $stmt = $conexionLocal->prepare("INSERT INTO sucursal_local (codigo_sucursal, nombre) VALUES ('SUC001', 'Sucursal Principal')");
                
                if($stmt->execute()) {
                    echo "<p>✅ <strong>Datos de ejemplo insertados</strong></p>";
                } else {
                    echo "<p>❌ <strong>Error insertando datos:</strong></p>";
                    print_r($stmt->errorInfo());
                }
                
            } else {
                echo "<p>❌ <strong>Error creando tabla:</strong></p>";
                print_r($conexionLocal->errorInfo());
            }
        }
        
    } else {
        echo "<p>❌ <strong>Error de conexión local</strong></p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ <strong>Excepción:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='crear-solicitud-stock'>← Volver a crear solicitud</a></p>";
echo "<p><a href='test-solicitud.php'>← Probar solicitud completa</a></p>";