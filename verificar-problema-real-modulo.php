<?php
/*=============================================
VERIFICAR PROBLEMA REAL DEL MÓDULO
=============================================*/

echo "🔍 VERIFICANDO PROBLEMA REAL DEL MÓDULO\n";
echo "======================================\n\n";

// 1. Verificar JavaScript actual
echo "📜 1. VERIFICANDO JAVASCRIPT ACTUAL:\n";
try {
    $contenidoJS = file_get_contents("vistas/js/stock-transito-unificado.js");
    
    if (strpos($contenidoJS, "ajax/registro-descargas-simple.ajax.php") !== false) {
        echo "✅ JavaScript usa endpoint original\n";
    } else {
        echo "❌ JavaScript NO usa endpoint original\n";
    }
    
    if (strpos($contenidoJS, "registrarDescargaOptimizada") !== false) {
        echo "✅ Función optimizada encontrada\n";
    } else {
        echo "❌ Función optimizada NO encontrada\n";
    }
    
    echo "📏 Tamaño del archivo JS: " . strlen($contenidoJS) . " caracteres\n";
    
} catch (Exception $e) {
    echo "❌ Error verificando JavaScript: " . $e->getMessage() . "\n";
}

// 2. Verificar endpoint AJAX
echo "\n🔗 2. VERIFICANDO ENDPOINT AJAX:\n";
try {
    $url = "ajax/registro-descargas-simple.ajax.php";
    
    if (file_exists($url)) {
        echo "✅ Archivo endpoint existe: $url\n";
        
        // Probar endpoint con datos de prueba
        $datosPrueba = [
            "accion" => "registrar_descarga",
            "codigo_producto" => "PRUEBA" . date('His'),
            "descripcion_producto" => "Producto de prueba",
            "cantidad_descargada" => 1,
            "usuario_id" => "999",
            "usuario_nombre" => "Usuario Sistema",
            "sucursal_id" => "1",
            "sucursal_nombre" => "Sucursal 2",
            "transportador_id" => "0",
            "transportador_nombre" => "Debug Test",
            "numero_despacho" => "",
            "observaciones" => "Prueba endpoint - " . date('Y-m-d H:i:s')
        ];
        
        // Simular $_POST
        $_POST = $datosPrueba;
        
        // Capturar output
        ob_start();
        include $url;
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "✅ Endpoint ejecutado\n";
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
        }
        
    } else {
        echo "❌ Archivo endpoint no existe: $url\n";
    }
} catch (Exception $e) {
    echo "❌ Error probando endpoint: " . $e->getMessage() . "\n";
}

// 3. Verificar datos de sucursal y usuario
echo "\n🏢 3. VERIFICANDO DATOS DE SUCURSAL Y USUARIO:\n";
try {
    // Verificar endpoint de sucursal
    $urlSucursal = "ajax/obtener-sucursal-actual.ajax.php";
    
    if (file_exists($urlSucursal)) {
        echo "✅ Endpoint sucursal existe: $urlSucursal\n";
        
        // Probar endpoint de sucursal
        ob_start();
        include $urlSucursal . "?accion=obtener_sucursal_actual";
        $outputSucursal = ob_get_contents();
        ob_end_clean();
        
        echo "📤 Respuesta sucursal: " . ($outputSucursal ?: "Sin respuesta") . "\n";
        
        $dataSucursal = json_decode($outputSucursal, true);
        if ($dataSucursal && $dataSucursal['success']) {
            echo "✅ Datos de sucursal obtenidos\n";
            echo "   Sucursal: " . $dataSucursal['sucursal']['nombre'] . "\n";
            echo "   ID: " . $dataSucursal['sucursal']['id'] . "\n";
        } else {
            echo "❌ Error obteniendo datos de sucursal\n";
        }
        
    } else {
        echo "❌ Endpoint sucursal no existe: $urlSucursal\n";
    }
    
    // Verificar endpoint de usuario
    $urlUsuario = "ajax/obtener-usuario-actual.ajax.php";
    
    if (file_exists($urlUsuario)) {
        echo "✅ Endpoint usuario existe: $urlUsuario\n";
        
        // Probar endpoint de usuario
        ob_start();
        include $urlUsuario;
        $outputUsuario = ob_get_contents();
        ob_end_clean();
        
        echo "📤 Respuesta usuario: " . ($outputUsuario ?: "Sin respuesta") . "\n";
        
        $dataUsuario = json_decode($outputUsuario, true);
        if ($dataUsuario && $dataUsuario['success']) {
            echo "✅ Datos de usuario obtenidos\n";
            echo "   Usuario: " . $dataUsuario['usuario']['nombre'] . "\n";
            echo "   ID: " . $dataUsuario['usuario']['id'] . "\n";
        } else {
            echo "❌ Error obteniendo datos de usuario\n";
        }
        
    } else {
        echo "❌ Endpoint usuario no existe: $urlUsuario\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error verificando datos: " . $e->getMessage() . "\n";
}

// 4. Verificar BD central
echo "\n🌐 4. VERIFICANDO BD CENTRAL:\n";
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
        echo "❌ No se pudo conectar a BD central\n";
    }
} catch (Exception $e) {
    echo "❌ Error con conexión central: " . $e->getMessage() . "\n";
}

echo "\n🎯 VERIFICACIÓN COMPLETADA\n";
echo "========================\n";

?>
