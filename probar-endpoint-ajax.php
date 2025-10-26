<?php
/*=============================================
PROBAR ENDPOINT AJAX DIRECTAMENTE
=============================================*/

echo "🔗 PROBANDO ENDPOINT AJAX DIRECTAMENTE\n";
echo "=====================================\n\n";

// Simular datos POST
$_POST = [
    "accion" => "registrar_descarga",
    "codigo_producto" => "AJAX" . date('His'),
    "descripcion_producto" => "Producto desde AJAX",
    "cantidad_descargada" => 1,
    "usuario_id" => 1,
    "usuario_nombre" => "Administrador",
    "sucursal_id" => 1,
    "sucursal_nombre" => "Sucursal 2",
    "transportador_id" => "0",
    "transportador_nombre" => "AJAX Test",
    "numero_despacho" => "",
    "observaciones" => "Prueba AJAX - " . date('Y-m-d H:i:s')
];

echo "📋 Datos POST:\n";
foreach($_POST as $key => $value) {
    echo "   $key: $value\n";
}

echo "\n🎮 Ejecutando endpoint AJAX...\n";

// Capturar output
ob_start();

try {
    // Incluir el endpoint AJAX
    include "ajax/registro-descargas-simple.ajax.php";
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Endpoint ejecutado\n";
    echo "📤 Respuesta: " . ($output ?: "Sin respuesta") . "\n";
    
    // Intentar decodificar JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "📊 Datos decodificados: " . json_encode($data) . "\n";
        
        if ($data['success']) {
            echo "✅ Registro exitoso\n";
        } else {
            echo "❌ Error: " . $data['error'] . "\n";
        }
    } else {
        echo "❌ Respuesta no es JSON válido\n";
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

echo "\n🔍 Verificando BD central...\n";

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