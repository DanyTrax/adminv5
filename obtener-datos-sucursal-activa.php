<?php
/*=============================================
OBTENER DATOS DE SUCURSAL ACTIVA
=============================================*/

echo "🔍 Obteniendo datos de sucursal activa...\n\n";

// Incluir conexión
require_once "modelos/conexion.php";

try {
    $pdo = Conexion::conectar();
    echo "✅ Conexión a base de datos establecida\n";
    
    // Verificar tabla sucursales
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'sucursales'");
    $stmt->execute();
    $tabla_sucursales = $stmt->fetch();
    
    if($tabla_sucursales) {
        echo "✅ Tabla 'sucursales' existe\n";
        
        // Obtener todas las sucursales
        $stmt = $pdo->prepare("SELECT * FROM sucursales");
        $stmt->execute();
        $sucursales = $stmt->fetchAll();
        
        echo "📋 Sucursales encontradas:\n";
        foreach($sucursales as $sucursal) {
            echo "   - ID: " . $sucursal['id'] . " | Nombre: " . $sucursal['nombre'] . "\n";
            if(isset($sucursal['activa']) && $sucursal['activa'] == 1) {
                echo "     ✅ SUCURSAL ACTIVA\n";
            }
        }
        
        // Buscar sucursal activa
        $stmt = $pdo->prepare("SELECT * FROM sucursales WHERE activa = 1 LIMIT 1");
        $stmt->execute();
        $sucursal_activa = $stmt->fetch();
        
        if($sucursal_activa) {
            echo "\n🎯 Sucursal activa encontrada:\n";
            echo "   - ID: " . $sucursal_activa['id'] . "\n";
            echo "   - Nombre: " . $sucursal_activa['nombre'] . "\n";
            echo "   - Activa: " . $sucursal_activa['activa'] . "\n";
        } else {
            echo "\n⚠️ No se encontró sucursal activa\n";
        }
        
    } else {
        echo "❌ Tabla 'sucursales' NO existe\n";
    }
    
    // Verificar tabla usuarios
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'usuarios'");
    $stmt->execute();
    $tabla_usuarios = $stmt->fetch();
    
    if($tabla_usuarios) {
        echo "\n✅ Tabla 'usuarios' existe\n";
        
        // Obtener usuarios
        $stmt = $pdo->prepare("SELECT id, nombre, perfil FROM usuarios LIMIT 5");
        $stmt->execute();
        $usuarios = $stmt->fetchAll();
        
        echo "📋 Usuarios encontrados:\n";
        foreach($usuarios as $usuario) {
            echo "   - ID: " . $usuario['id'] . " | Nombre: " . $usuario['nombre'] . " | Perfil: " . $usuario['perfil'] . "\n";
        }
    } else {
        echo "\n❌ Tabla 'usuarios' NO existe\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Datos de sucursal obtenidos\n";
?>
