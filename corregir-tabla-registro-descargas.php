<?php
/*=============================================
CORREGIR TABLA REGISTRO DE DESCARGAS
=============================================*/

echo "🔧 Corrigiendo tabla registro_descargas_stock_transito...\n\n";

// Incluir conexión
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();
    echo "✅ Conexión a base de datos establecida\n";
    
    // Verificar si la tabla existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'registro_descargas_stock_transito'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch();
    
    if($tabla_existe) {
        echo "✅ Tabla 'registro_descargas_stock_transito' existe\n";
        
        // Verificar columnas existentes
        $stmt = $pdo->prepare("DESCRIBE registro_descargas_stock_transito");
        $stmt->execute();
        $columnas = $stmt->fetchAll();
        
        echo "📋 Columnas existentes:\n";
        $columnas_existentes = [];
        foreach($columnas as $columna) {
            echo "   - " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
            $columnas_existentes[] = $columna['Field'];
        }
        
        // Verificar si falta la columna hora_descarga
        if(!in_array('hora_descarga', $columnas_existentes)) {
            echo "\n🔧 Agregando columna 'hora_descarga'...\n";
            $sql = "ALTER TABLE registro_descargas_stock_transito ADD COLUMN hora_descarga TIME DEFAULT NULL AFTER fecha_descarga";
            $pdo->exec($sql);
            echo "✅ Columna 'hora_descarga' agregada\n";
        } else {
            echo "✅ Columna 'hora_descarga' ya existe\n";
        }
        
        // Verificar otras columnas importantes
        $columnas_requeridas = [
            'fecha_descarga' => 'DATE',
            'hora_descarga' => 'TIME',
            'ip_usuario' => 'VARCHAR(45)',
            'user_agent' => 'TEXT',
            'created_at' => 'TIMESTAMP'
        ];
        
        foreach($columnas_requeridas as $columna => $tipo) {
            if(!in_array($columna, $columnas_existentes)) {
                echo "🔧 Agregando columna '$columna'...\n";
                $sql = "ALTER TABLE registro_descargas_stock_transito ADD COLUMN $columna $tipo DEFAULT NULL";
                $pdo->exec($sql);
                echo "✅ Columna '$columna' agregada\n";
            }
        }
        
        // Verificar estructura final
        echo "\n📋 Estructura final de la tabla:\n";
        $stmt = $pdo->prepare("DESCRIBE registro_descargas_stock_transito");
        $stmt->execute();
        $columnas_finales = $stmt->fetchAll();
        
        foreach($columnas_finales as $columna) {
            echo "   - " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
        }
        
        // Contar registros
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registro_descargas_stock_transito");
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        echo "\n📊 Total de registros: $total\n";
        
    } else {
        echo "❌ Tabla 'registro_descargas_stock_transito' NO existe\n";
        echo "🔧 Creando tabla...\n";
        
        $sql = "CREATE TABLE registro_descargas_stock_transito (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_producto VARCHAR(50) NOT NULL,
            descripcion_producto TEXT,
            cantidad_descargada INT NOT NULL,
            usuario_id INT,
            usuario_nombre VARCHAR(100),
            sucursal_id INT,
            sucursal_nombre VARCHAR(100),
            transportador_id INT,
            transportador_nombre VARCHAR(100),
            numero_despacho VARCHAR(50),
            observaciones TEXT,
            fecha_descarga DATE,
            hora_descarga TIME,
            ip_usuario VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Tabla creada exitosamente\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Tabla corregida\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar el módulo registro-descargas-simple\n";
echo "2. Verificar que la tabla se muestra correctamente\n";
echo "3. Probar el proceso de descarga\n";
?>
