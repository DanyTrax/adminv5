<?php
/*=============================================
PROBAR ENDPOINT AJAX REAL
=============================================*/

echo "🔗 PROBANDO ENDPOINT AJAX REAL\n";
echo "==============================\n\n";

// Simular datos POST exactos del JavaScript
$_POST = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "REAL" . date('His'),
    "descripcion_producto" => "Producto desde endpoint real",
    "cantidad_descargada" => 1,
    "usuario_id" => "999",
    "usuario_nombre" => "Usuario Sistema",
    "sucursal_id" => "1",
    "sucursal_nombre" => "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "Debug Test",
    "numero_despacho" => "",
    "observaciones" => "Prueba endpoint real - " . date('Y-m-d H:i:s')
];

echo "📋 1. DATOS POST SIMULADOS:\n";
foreach($_POST as $key => $value) {
    echo "   $key: $value\n";
}

echo "\n🎮 2. PROBANDO CONTROLADOR DIRECTAMENTE:\n";
try {
    require_once "controladores/registro-descargas-simple.controlador.php";
    $controlador = new ControladorRegistroDescargasSimple();
    $resultado = $controlador->ctrRegistrarDescarga();
    
    echo "✅ Controlador ejecutado\n";
    echo "   Resultado: " . json_encode($resultado) . "\n";
    
    if ($resultado["success"]) {
        echo "✅ Registro exitoso\n";
    } else {
        echo "❌ Error en controlador: " . $resultado["error"] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error con controlador: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🔗 3. PROBANDO ENDPOINT AJAX REAL:\n";
try {
    // Capturar output
    ob_start();
    
    // Incluir el endpoint AJAX real
    include "ajax/registro-descargas-simple.ajax.php";
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Endpoint AJAX ejecutado\n";
    echo "📤 Respuesta: " . ($output ?: "Sin respuesta") . "\n";
    
    // Intentar decodificar JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "📊 Datos decodificados: " . json_encode($data) . "\n";
        
        if ($data['success']) {
            echo "✅ Endpoint funcionando\n";
        } else {
            echo "❌ Error: " . $data['error'] . "\n";
        }
    } else {
        echo "❌ Respuesta no es JSON válido\n";
        echo "📋 Respuesta raw: " . $output . "\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
} catch (Error $e) {
    ob_end_clean();
    echo "❌ Error fatal: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

echo "\n🔍 4. VERIFICANDO REGISTRO EN BD:\n";
try {
    require_once "api-transferencias/conexion-central.php";
    $conexionCentral = ConexionCentral::conectar();
    
    if ($conexionCentral) {
        echo "✅ Conexión central exitosa\n";
        
        // Verificar último registro
        $stmt = $conexionCentral->prepare("SELECT * FROM registro_descargas_stock_transito ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimo = $stmt->fetch();
        
        if ($ultimo) {
            echo "✅ Último registro:\n";
            echo "   ID: " . $ultimo['id'] . "\n";
            echo "   Código: " . $ultimo['codigo_producto'] . "\n";
            echo "   Sucursal: " . $ultimo['sucursal_nombre'] . "\n";
            echo "   Usuario: " . $ultimo['usuario_nombre'] . "\n";
            echo "   Fecha: " . $ultimo['fecha_descarga'] . "\n";
        } else {
            echo "❌ No hay registros\n";
        }
        
    } else {
        echo "❌ No se pudo conectar\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "\n";
}

echo "\n🎯 PRUEBA COMPLETADA\n";

?>
