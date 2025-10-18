<?php
// Test de diagnóstico detallado para despachos

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Diagnóstico Detallado de Despachos\n";
echo "====================================\n\n";

// Test 1: Verificar archivos
echo "📁 Test 1: Verificar archivos necesarios\n";
echo "---------------------------------------\n";

$archivos = [
    "modelos/conexion.php",
    "api-transferencias/conexion-central.php", 
    "controladores/despachos.controlador.php",
    "modelos/despachos.modelo.php",
    "modelos/productos.modelo.php"
];

foreach($archivos as $archivo) {
    if(file_exists($archivo)) {
        echo "✅ $archivo - Existe\n";
    } else {
        echo "❌ $archivo - NO EXISTE\n";
    }
}

echo "\n";

// Test 2: Verificar conexiones
echo "🔌 Test 2: Verificar conexiones\n";
echo "-------------------------------\n";

try {
    require_once "modelos/conexion.php";
    $conexionLocal = Conexion::conectar();
    echo "✅ Conexión local - OK\n";
} catch(Exception $e) {
    echo "❌ Conexión local - Error: " . $e->getMessage() . "\n";
}

try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    echo "✅ Conexión central - OK\n";
} catch(Exception $e) {
    echo "❌ Conexión central - Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Verificar controlador
echo "🎮 Test 3: Verificar controlador\n";
echo "--------------------------------\n";

try {
    require_once "controladores/despachos.controlador.php";
    echo "✅ Controlador cargado - OK\n";
    
    // Verificar método
    if(method_exists('ControladorDespachos', 'ctrMostrarDespachos')) {
        echo "✅ Método ctrMostrarDespachos - Existe\n";
    } else {
        echo "❌ Método ctrMostrarDespachos - NO EXISTE\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error cargando controlador: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Verificar modelo
echo "📊 Test 4: Verificar modelo\n";
echo "--------------------------\n";

try {
    require_once "modelos/despachos.modelo.php";
    echo "✅ Modelo cargado - OK\n";
    
    // Verificar método
    if(method_exists('ModeloDespachos', 'mdlMostrarDespachos')) {
        echo "✅ Método mdlMostrarDespachos - Existe\n";
    } else {
        echo "❌ Método mdlMostrarDespachos - NO EXISTE\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error cargando modelo: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Probar consulta directa
echo "🔍 Test 5: Probar consulta directa\n";
echo "---------------------------------\n";

try {
    $conexionCentral = ConexionCentral::conectar();
    $stmt = $conexionCentral->prepare("SELECT COUNT(*) as total FROM despachos");
    $stmt->execute();
    $resultado = $stmt->fetch();
    echo "✅ Consulta directa - Total despachos: " . $resultado['total'] . "\n";
} catch(Exception $e) {
    echo "❌ Error en consulta directa: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Probar método del controlador
echo "🎯 Test 6: Probar método del controlador\n";
echo "---------------------------------------\n";

try {
    $respuesta = ControladorDespachos::ctrMostrarDespachos("id", 20);
    if($respuesta) {
        echo "✅ Controlador funcionando - Despacho encontrado\n";
        echo "📋 Datos: " . json_encode($respuesta) . "\n";
    } else {
        echo "⚠️ Controlador funcionando - Despacho no encontrado\n";
    }
} catch(Exception $e) {
    echo "❌ Error en controlador: " . $e->getMessage() . "\n";
}

echo "\n🏁 Diagnóstico completado\n";
?>
