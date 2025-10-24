<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "api-transferencias/conexion-central.php";

echo "<h2>🔧 Creando Tablas de Trazabilidad</h2>";
echo "<hr>";

try {
    $conexion = ConexionCentral::conectar();
    
    if (!$conexion) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<p>✅ Conexión a la base de datos establecida</p>";
    
    // Verificar si las tablas ya existen
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'trazabilidad_movimientos'");
    $stmt->execute();
    $tablaExiste = $stmt->fetch();
    
    if ($tablaExiste) {
        echo "<p>⚠️ Las tablas de trazabilidad ya existen. ¿Desea recrearlas?</p>";
        echo "<p><strong>ADVERTENCIA:</strong> Esto eliminará todos los datos existentes de trazabilidad.</p>";
        echo "<p><a href='?recrear=1' class='btn btn-danger'>Sí, Recrear Tablas</a> <a href='?' class='btn btn-success'>No, Cancelar</a></p>";
        
        if (isset($_GET['recrear']) && $_GET['recrear'] == '1') {
            echo "<hr>";
            echo "<h3>🗑️ Eliminando tablas existentes...</h3>";
            
            // Eliminar tablas en orden inverso (por las foreign keys)
            $conexion->exec("DROP TABLE IF EXISTS trazabilidad_relaciones");
            echo "<p>✅ Tabla trazabilidad_relaciones eliminada</p>";
            
            $conexion->exec("DROP TABLE IF EXISTS trazabilidad_descargas");
            echo "<p>✅ Tabla trazabilidad_descargas eliminada</p>";
            
            $conexion->exec("DROP TABLE IF EXISTS trazabilidad_movimientos");
            echo "<p>✅ Tabla trazabilidad_movimientos eliminada</p>";
        } else {
            echo "<p>❌ Operación cancelada</p>";
            exit;
        }
    }
    
    echo "<hr>";
    echo "<h3>🏗️ Creando tablas de trazabilidad...</h3>";
    
    // 1. Crear tabla trazabilidad_movimientos
    $sql1 = "CREATE TABLE trazabilidad_movimientos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        numero_despacho VARCHAR(20),
        producto_codigo VARCHAR(50),
        producto_descripcion VARCHAR(255),
        cantidad_total INT,
        cantidad_pendiente INT,
        cantidad_descargada INT DEFAULT 0,
        
        -- ORIGEN
        sucursal_origen VARCHAR(100),
        usuario_origen_id INT,
        fecha_salida DATETIME,
        
        -- TRANSPORTE
        transportador_id INT,
        fecha_aceptacion DATETIME, -- FECHA CLAVE PARA LIFO
        
        -- DESTINO
        sucursal_destino VARCHAR(100),
        usuario_destino_id INT,
        fecha_llegada DATETIME,
        
        -- ESTADO
        estado ENUM('espera', 'en_transito', 'parcial', 'entregado', 'cancelado'),
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_numero_despacho (numero_despacho),
        INDEX idx_producto_codigo (producto_codigo),
        INDEX idx_fecha_aceptacion (fecha_aceptacion),
        INDEX idx_estado (estado)
    )";
    
    $conexion->exec($sql1);
    echo "<p>✅ Tabla trazabilidad_movimientos creada</p>";
    
    // 2. Crear tabla trazabilidad_descargas
    $sql2 = "CREATE TABLE trazabilidad_descargas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        trazabilidad_movimiento_id INT,
        cantidad_descargada INT,
        usuario_descarga_id INT,
        sucursal_descarga VARCHAR(100),
        fecha_descarga DATETIME,
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (trazabilidad_movimiento_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
        INDEX idx_trazabilidad_movimiento (trazabilidad_movimiento_id),
        INDEX idx_usuario_descarga (usuario_descarga_id),
        INDEX idx_fecha_descarga (fecha_descarga)
    )";
    
    $conexion->exec($sql2);
    echo "<p>✅ Tabla trazabilidad_descargas creada</p>";
    
    // 3. Crear tabla trazabilidad_relaciones
    $sql3 = "CREATE TABLE trazabilidad_relaciones (
        id INT PRIMARY KEY AUTO_INCREMENT,
        trazabilidad_padre_id INT,
        trazabilidad_hijo_id INT,
        relacion_tipo ENUM('solicitud_despacho', 'despacho_stock', 'stock_descarga'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (trazabilidad_padre_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
        FOREIGN KEY (trazabilidad_hijo_id) REFERENCES trazabilidad_movimientos(id) ON DELETE CASCADE,
        INDEX idx_padre (trazabilidad_padre_id),
        INDEX idx_hijo (trazabilidad_hijo_id)
    )";
    
    $conexion->exec($sql3);
    echo "<p>✅ Tabla trazabilidad_relaciones creada</p>";
    
    echo "<hr>";
    echo "<h3>🎉 ¡Tablas creadas exitosamente!</h3>";
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sistema de Trazabilidad Listo</h4>";
    echo "<p>Las siguientes tablas han sido creadas:</p>";
    echo "<ul>";
    echo "<li><strong>trazabilidad_movimientos</strong> - Registro principal de movimientos</li>";
    echo "<li><strong>trazabilidad_descargas</strong> - Historial de descargas individuales</li>";
    echo "<li><strong>trazabilidad_relaciones</strong> - Relaciones entre despachos</li>";
    echo "</ul>";
    echo "<p><strong>Próximos pasos:</strong></p>";
    echo "<ol>";
    echo "<li>Agregar el módulo 'Trazabilidad' al menú principal</li>";
    echo "<li>Integrar los hooks con los módulos existentes</li>";
    echo "<li>Probar el sistema de trazabilidad</li>";
    echo "</ol>";
    echo "</div>";
    
    // Verificar que las tablas se crearon correctamente
    echo "<hr>";
    echo "<h3>🔍 Verificación de Tablas</h3>";
    
    $tablas = ['trazabilidad_movimientos', 'trazabilidad_descargas', 'trazabilidad_relaciones'];
    
    foreach($tablas as $tabla) {
        $stmt = $conexion->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabla]);
        $existe = $stmt->fetch();
        
        if($existe) {
            echo "<p>✅ Tabla <strong>$tabla</strong> existe</p>";
        } else {
            echo "<p>❌ Tabla <strong>$tabla</strong> NO existe</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error al crear las tablas</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

.alert {
    padding: 15px;
    margin: 10px 0;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6;
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

.btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 4px 2px;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid transparent;
}

.btn-danger {
    color: #fff;
    background-color: #d9534f;
    border-color: #d43f3a;
}

.btn-success {
    color: #fff;
    background-color: #5cb85c;
    border-color: #4cae4c;
}

h2, h3 {
    color: #333;
}

hr {
    border: 0;
    height: 1px;
    background-color: #ddd;
    margin: 20px 0;
}
</style>
