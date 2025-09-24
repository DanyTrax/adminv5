<?php

session_start();
require_once "config.php";
require_once "api-transferencias/conexion-central.php";

echo "<h2>🔍 PRUEBA DE CONEXIÓN Y CREACIÓN DE SOLICITUD</h2>";

try {
    // ✅ PROBAR CONEXIÓN CENTRAL
    $conexionCentral = ConexionCentral::conectar();
    
    if($conexionCentral) {
        echo "<p>✅ <strong>Conexión central exitosa</strong></p>";
        
        // ✅ VERIFICAR TABLA
        $stmt = $conexionCentral->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
        $stmt->execute();
        
        if($stmt->fetch()) {
            echo "<p>✅ <strong>Tabla solicitudes_stock existe</strong></p>";
            
            // ✅ VERIFICAR ESTRUCTURA
            $stmt = $conexionCentral->prepare("DESCRIBE solicitudes_stock");
            $stmt->execute();
            $columnas = $stmt->fetchAll();
            
            echo "<p>✅ <strong>Estructura de la tabla:</strong></p>";
            echo "<ul>";
            foreach($columnas as $columna) {
                echo "<li>{$columna['Field']} - {$columna['Type']}</li>";
            }
            echo "</ul>";
            
            // ✅ CONTAR REGISTROS ACTUALES
            $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM solicitudes_stock");
            $stmt->execute();
            $total = $stmt->fetch();
            echo "<p>✅ <strong>Total de solicitudes actuales:</strong> {$total['total']}</p>";
            
            // ✅ PROBAR INSERT DE PRUEBA
            $numeroTest = "TEST" . date('YmdHis');
            
            $stmt = $conexionCentral->prepare("INSERT INTO solicitudes_stock (
                numero_solicitud,
                codigo_sucursal_solicitante,
                nombre_sucursal_solicitante,
                usuario_solicitante,
                nombre_usuario_solicitante,
                productos_solicitados,
                tipo_solicitud,
                detalle_adicional,
                total_productos,
                total_cantidad
            ) VALUES (
                :numero_solicitud,
                :codigo_sucursal,
                :nombre_sucursal,
                1,
                'Usuario Test',
                '[{\"codigo\":\"TEST001\",\"descripcion\":\"Producto Test\",\"cantidad\":1}]',
                'stock',
                'Solicitud de prueba',
                1,
                1
            )");
            
            $stmt->bindParam(":numero_solicitud", $numeroTest);
            $stmt->bindParam(":codigo_sucursal", CODIGO_SUCURSAL);
            $stmt->bindParam(":nombre_sucursal", NOMBRE_SUCURSAL);
            
            if($stmt->execute()) {
                echo "<p>✅ <strong>INSERT de prueba exitoso:</strong> {$numeroTest}</p>";
                
                // Eliminar registro de prueba
                $stmtDelete = $conexionCentral->prepare("DELETE FROM solicitudes_stock WHERE numero_solicitud = :numero");
                $stmtDelete->bindParam(":numero", $numeroTest);
                $stmtDelete->execute();
                echo "<p>✅ <strong>Registro de prueba eliminado</strong></p>";
                
            } else {
                echo "<p>❌ <strong>Error en INSERT de prueba</strong></p>";
                print_r($stmt->errorInfo());
            }
            
        } else {
            echo "<p>❌ <strong>Tabla solicitudes_stock NO existe</strong></p>";
            
            // ✅ CREAR TABLA SI NO EXISTE
            $sqlCrear = "
            CREATE TABLE solicitudes_stock (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero_solicitud VARCHAR(50) NOT NULL UNIQUE,
                codigo_sucursal_solicitante VARCHAR(20) NOT NULL,
                nombre_sucursal_solicitante VARCHAR(100) NOT NULL,
                usuario_solicitante INT NOT NULL,
                nombre_usuario_solicitante VARCHAR(100) NOT NULL,
                productos_solicitados JSON NOT NULL,
                tipo_solicitud ENUM('stock', 'remision') NOT NULL DEFAULT 'stock',
                codigo_remision VARCHAR(50) NULL,
                nombre_cliente_remision VARCHAR(200) NULL,
                detalle_adicional TEXT NULL,
                estado ENUM('pendiente', 'aprobado', 'cancelado') NOT NULL DEFAULT 'pendiente',
                fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                usuario_aprobacion INT NULL,
                nombre_usuario_aprobacion VARCHAR(100) NULL,
                fecha_aprobacion TIMESTAMP NULL,
                motivo_cancelacion TEXT NULL,
                total_productos INT NOT NULL DEFAULT 0,
                total_cantidad INT NOT NULL DEFAULT 0,
                observaciones_aprobacion TEXT NULL,
                INDEX idx_sucursal (codigo_sucursal_solicitante),
                INDEX idx_estado (estado),
                INDEX idx_fecha (fecha_solicitud)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if($conexionCentral->exec($sqlCrear)) {
                echo "<p>✅ <strong>Tabla solicitudes_stock creada exitosamente</strong></p>";
            } else {
                echo "<p>❌ <strong>Error creando tabla solicitudes_stock</strong></p>";
                print_r($conexionCentral->errorInfo());
            }
        }
        
    } else {
        echo "<p>❌ <strong>Error de conexión central</strong></p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ <strong>Excepción:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='crear-solicitud-stock'>← Volver a crear solicitud</a></p>";
echo "<p><a href='solicitudes-stock'>← Ver solicitudes</a></p>";