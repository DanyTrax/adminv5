<?php
/*=============================================
VERIFICAR TABLAS DE SUCURSALES
=============================================*/

echo "🔍 Verificando tablas relacionadas con sucursales...\n\n";

// Incluir conexión
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();
    echo "✅ Conexión a base de datos establecida\n";
    
    // Obtener todas las tablas
    $stmt = $pdo->prepare("SHOW TABLES");
    $stmt->execute();
    $tablas = $stmt->fetchAll();
    
    echo "📋 Todas las tablas en la base de datos:\n";
    $tablas_sucursal = [];
    foreach($tablas as $tabla) {
        $nombre_tabla = array_values($tabla)[0];
        echo "   - $nombre_tabla\n";
        
        // Buscar tablas que contengan "sucursal"
        if(strpos(strtolower($nombre_tabla), 'sucursal') !== false) {
            $tablas_sucursal[] = $nombre_tabla;
        }
    }
    
    if(count($tablas_sucursal) > 0) {
        echo "\n🎯 Tablas relacionadas con sucursales:\n";
        foreach($tablas_sucursal as $tabla) {
            echo "   - $tabla\n";
            
            // Mostrar estructura de la tabla
            $stmt = $pdo->prepare("DESCRIBE $tabla");
            $stmt->execute();
            $columnas = $stmt->fetchAll();
            
            echo "     📋 Columnas:\n";
            foreach($columnas as $columna) {
                echo "        - " . $columna['Field'] . " (" . $columna['Type'] . ")\n";
            }
            
            // Mostrar algunos datos
            $stmt = $pdo->prepare("SELECT * FROM $tabla LIMIT 3");
            $stmt->execute();
            $datos = $stmt->fetchAll();
            
            if(count($datos) > 0) {
                echo "     📊 Datos de ejemplo:\n";
                foreach($datos as $dato) {
                    echo "        - " . json_encode($dato) . "\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "\n⚠️ No se encontraron tablas con 'sucursal' en el nombre\n";
    }
    
    // Verificar si hay información de sucursal en otras tablas
    echo "🔍 Buscando información de sucursal en otras tablas...\n";
    
    $tablas_a_verificar = ['usuarios', 'stock_transito', 'despachos'];
    foreach($tablas_a_verificar as $tabla) {
        if(in_array($tabla, array_column($tablas, 0))) {
            echo "\n📋 Verificando tabla: $tabla\n";
            
            $stmt = $pdo->prepare("DESCRIBE $tabla");
            $stmt->execute();
            $columnas = $stmt->fetchAll();
            
            $columnas_sucursal = [];
            foreach($columnas as $columna) {
                if(strpos(strtolower($columna['Field']), 'sucursal') !== false) {
                    $columnas_sucursal[] = $columna['Field'];
                }
            }
            
            if(count($columnas_sucursal) > 0) {
                echo "   ✅ Columnas de sucursal encontradas: " . implode(', ', $columnas_sucursal) . "\n";
                
                // Mostrar datos de sucursal
                foreach($columnas_sucursal as $columna) {
                    $stmt = $pdo->prepare("SELECT DISTINCT $columna FROM $tabla WHERE $columna IS NOT NULL LIMIT 5");
                    $stmt->execute();
                    $valores = $stmt->fetchAll();
                    
                    if(count($valores) > 0) {
                        echo "   📊 Valores únicos en $columna:\n";
                        foreach($valores as $valor) {
                            echo "      - " . $valor[$columna] . "\n";
                        }
                    }
                }
            } else {
                echo "   ❌ No se encontraron columnas de sucursal\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Verificación completada\n";
?>
