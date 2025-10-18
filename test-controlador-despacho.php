<?php
// Test específico para el controlador de despachos

echo "🔍 Test Controlador Despacho - Verificar controlador específico\n";
echo "============================================================\n\n";

// Simular sesión
session_start();
$_SESSION['iniciarSesion'] = 'ok';
$_SESSION['perfil'] = 'Administrador';
$_SESSION['id'] = 1;
$_SESSION['nombre'] = 'Administrador';

echo "📋 Sesión simulada:\n";
echo "   - perfil: " . $_SESSION['perfil'] . "\n";
echo "   - id: " . $_SESSION['id'] . "\n";
echo "   - nombre: " . $_SESSION['nombre'] . "\n\n";

// Test 1: Verificar controlador directamente
echo "🔍 Test 1: Verificar controlador directamente\n";
echo "-------------------------------------------\n";

try {
    require_once "controladores/despachos.controlador.php";
    echo "✅ Controlador cargado exitosamente\n";
    
    $despacho = ControladorDespachos::ctrMostrarDespachos("id", 21);
    
    if($despacho) {
        echo "✅ Despacho obtenido a través del controlador:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Número: " . $despacho['numero_despacho'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n\n";
    } else {
        echo "❌ Despacho no obtenido a través del controlador\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despacho a través del controlador: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
}

// Test 2: Verificar modelo directamente
echo "🔍 Test 2: Verificar modelo directamente\n";
echo "--------------------------------------\n";

try {
    require_once "modelos/despachos.modelo.php";
    echo "✅ Modelo cargado exitosamente\n";
    
    $despacho = ModeloDespachos::mdlMostrarDespachos("despachos", "id", 21);
    
    if($despacho) {
        echo "✅ Despacho obtenido a través del modelo:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Número: " . $despacho['numero_despacho'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n\n";
    } else {
        echo "❌ Despacho no obtenido a través del modelo\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error obteniendo despacho a través del modelo: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
}

// Test 3: Verificar conexión central directamente
echo "🔍 Test 3: Verificar conexión central directamente\n";
echo "------------------------------------------------\n";

try {
    require_once "api-transferencias/conexion-central.php";
    echo "✅ Conexión central cargada exitosamente\n";
    
    $conexion = ConexionCentral::conectar();
    echo "✅ Conexión establecida exitosamente\n";
    
    $stmt = $conexion->prepare("SELECT * FROM despachos WHERE id = 21");
    $stmt->execute();
    $despacho = $stmt->fetch();
    
    if($despacho) {
        echo "✅ Despacho obtenido directamente:\n";
        echo "   - ID: " . $despacho['id'] . "\n";
        echo "   - Número: " . $despacho['numero_despacho'] . "\n";
        echo "   - Estado: " . $despacho['estado'] . "\n\n";
    } else {
        echo "❌ Despacho no obtenido directamente\n\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
} catch(Error $e) {
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
}

echo "🏁 Test completado\n";
?>
