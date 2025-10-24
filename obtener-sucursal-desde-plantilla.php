<?php
/*=============================================
OBTENER SUCURSAL DESDE PLANTILLA
=============================================*/

echo "🔍 Obteniendo datos de sucursal desde plantilla...\n\n";

// Incluir archivos necesarios
require_once "modelos/conexion.php";
require_once "config.php";

try {
    // Verificar si hay constantes definidas
    echo "📋 Constantes definidas:\n";
    if(defined('NOMBRE_SUCURSAL')) {
        echo "   - NOMBRE_SUCURSAL: " . NOMBRE_SUCURSAL . "\n";
    } else {
        echo "   - NOMBRE_SUCURSAL: NO definida\n";
    }
    
    if(defined('ID_SUCURSAL')) {
        echo "   - ID_SUCURSAL: " . ID_SUCURSAL . "\n";
    } else {
        echo "   - ID_SUCURSAL: NO definida\n";
    }
    
    // Verificar archivo config.php
    echo "\n📋 Contenido de config.php:\n";
    if(file_exists('config.php')) {
        $contenido_config = file_get_contents('config.php');
        
        // Buscar definiciones de sucursal
        if(strpos($contenido_config, 'NOMBRE_SUCURSAL') !== false) {
            echo "   ✅ NOMBRE_SUCURSAL encontrada en config.php\n";
        } else {
            echo "   ❌ NOMBRE_SUCURSAL NO encontrada en config.php\n";
        }
        
        if(strpos($contenido_config, 'ID_SUCURSAL') !== false) {
            echo "   ✅ ID_SUCURSAL encontrada en config.php\n";
        } else {
            echo "   ❌ ID_SUCURSAL NO encontrada en config.php\n";
        }
        
        // Mostrar líneas relevantes
        $lineas = explode("\n", $contenido_config);
        echo "\n📋 Líneas relevantes de config.php:\n";
        foreach($lineas as $num => $linea) {
            if(strpos($linea, 'SUCURSAL') !== false || strpos($linea, 'sucursal') !== false) {
                echo "   " . ($num + 1) . ": " . trim($linea) . "\n";
            }
        }
    } else {
        echo "   ❌ config.php no existe\n";
    }
    
    // Verificar plantilla.php
    echo "\n📋 Verificando plantilla.php:\n";
    if(file_exists('vistas/plantilla.php')) {
        $contenido_plantilla = file_get_contents('vistas/plantilla.php');
        
        // Buscar variables de sucursal en JavaScript
        if(strpos($contenido_plantilla, 'nombreSucursal') !== false) {
            echo "   ✅ nombreSucursal encontrada en plantilla.php\n";
        } else {
            echo "   ❌ nombreSucursal NO encontrada en plantilla.php\n";
        }
        
        if(strpos($contenido_plantilla, 'sucursalId') !== false) {
            echo "   ✅ sucursalId encontrada en plantilla.php\n";
        } else {
            echo "   ❌ sucursalId NO encontrada en plantilla.php\n";
        }
        
        // Mostrar líneas relevantes
        $lineas = explode("\n", $contenido_plantilla);
        echo "\n📋 Líneas relevantes de plantilla.php:\n";
        foreach($lineas as $num => $linea) {
            if(strpos($linea, 'sucursal') !== false || strpos($linea, 'Sucursal') !== false) {
                echo "   " . ($num + 1) . ": " . trim($linea) . "\n";
            }
        }
    } else {
        echo "   ❌ vistas/plantilla.php no existe\n";
    }
    
    // Verificar si hay información en la base de datos
    echo "\n📋 Verificando información en base de datos:\n";
    $pdo = Conexion::conectar();
    
    // Buscar en tabla usuarios si hay información de sucursal
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'usuarios'");
    $stmt->execute();
    if($stmt->fetch()) {
        $stmt = $pdo->prepare("DESCRIBE usuarios");
        $stmt->execute();
        $columnas = $stmt->fetchAll();
        
        $columnas_sucursal = [];
        foreach($columnas as $columna) {
            if(strpos(strtolower($columna['Field']), 'sucursal') !== false) {
                $columnas_sucursal[] = $columna['Field'];
            }
        }
        
        if(count($columnas_sucursal) > 0) {
            echo "   ✅ Columnas de sucursal en usuarios: " . implode(', ', $columnas_sucursal) . "\n";
        } else {
            echo "   ❌ No hay columnas de sucursal en usuarios\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Verificación completada\n";
?>
