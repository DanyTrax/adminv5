<?php
/**
 * Debug completo de Stock en Tránsito
 * Verificar conexiones, datos y JavaScript
 */

echo "<h2>🔍 Debug Completo - Stock en Tránsito</h2>";

// Incluir conexiones
require_once "api-transferencias/conexion-central.php";

try {
    echo "<h3>📊 1. VERIFICAR CONEXIÓN CENTRAL</h3>";
    
    $conexion = ConexionCentral::conectar();
    if($conexion) {
        echo "✅ Conexión central exitosa<br>";
    } else {
        echo "❌ Error de conexión central<br>";
        exit;
    }
    
    echo "<h3>📊 2. VERIFICAR TABLA STOCK_TRANSITO</h3>";
    
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'stock_transito'");
    $stmt->execute();
    $tabla = $stmt->fetch();
    
    if($tabla) {
        echo "✅ Tabla stock_transito existe<br>";
    } else {
        echo "❌ Tabla stock_transito no existe<br>";
        exit;
    }
    
    echo "<h3>📊 3. VERIFICAR ESTRUCTURA DE TABLA</h3>";
    
    $stmt = $conexion->prepare("DESCRIBE stock_transito");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach($campos as $campo) {
        echo "<tr>";
        echo "<td>" . $campo['Field'] . "</td>";
        echo "<td>" . $campo['Type'] . "</td>";
        echo "<td>" . $campo['Null'] . "</td>";
        echo "<td>" . $campo['Key'] . "</td>";
        echo "<td>" . $campo['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>📊 4. VERIFICAR DATOS EN TABLA</h3>";
    
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM stock_transito WHERE cantidad_disponible > 0");
    $stmt->execute();
    $resultado = $stmt->fetch();
    $total = $resultado['total'];
    
    echo "📊 Total de registros con cantidad > 0: " . $total . "<br>";
    
    if($total > 0) {
        echo "<h4>Primeros 3 registros:</h4>";
        
        $stmt = $conexion->prepare("
            SELECT 
                id, codigo_producto, descripcion_producto, cantidad_disponible,
                nombre_transportador, sucursal_origen, fecha_carga
            FROM stock_transito 
            WHERE cantidad_disponible > 0 
            ORDER BY fecha_carga DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Transportador</th><th>Origen</th><th>Fecha</th></tr>";
        foreach($registros as $reg) {
            echo "<tr>";
            echo "<td>" . $reg['id'] . "</td>";
            echo "<td>" . $reg['codigo_producto'] . "</td>";
            echo "<td>" . substr($reg['descripcion_producto'], 0, 30) . "...</td>";
            echo "<td>" . $reg['cantidad_disponible'] . "</td>";
            echo "<td>" . $reg['nombre_transportador'] . "</td>";
            echo "<td>" . $reg['sucursal_origen'] . "</td>";
            echo "<td>" . $reg['fecha_carga'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>📊 5. PROBAR CONSULTA AJAX</h3>";
    
    // Simular la consulta que hace el AJAX
    $stmt = $conexion->prepare("
        SELECT 
            st.*,
            0 as cantidad_solicitada_pendiente,
            0 as solicitudes_pendientes
        FROM stock_transito st
        WHERE st.cantidad_disponible > 0 
        ORDER BY st.nombre_transportador ASC, st.codigo_producto ASC
    ");
    
    $inicio = microtime(true);
    $stmt->execute();
    $stockTransito = $stmt->fetchAll();
    $fin = microtime(true);
    $tiempo = ($fin - $inicio) * 1000;
    
    echo "✅ Consulta ejecutada en " . number_format($tiempo, 2) . " ms<br>";
    echo "📊 Registros obtenidos: " . count($stockTransito) . "<br>";
    
    if(count($stockTransito) > 0) {
        echo "<h4>Datos JSON simulados:</h4>";
        $data = [];
        foreach($stockTransito as $key => $value) {
            $data[] = [
                ($key + 1),
                $value["codigo_producto"],
                substr($value["descripcion_producto"], 0, 40),
                $value["cantidad_disponible"],
                $value["nombre_transportador"],
                $value["sucursal_origen"],
                date('d/m/Y H:i', strtotime($value["fecha_carga"])),
                '<span class="text-muted">-</span>',
                '<div class="btn-group"><button class="btn btn-warning btn-xs btnDescargaDirecta" data-id-stock="' . $value["id"] . '" data-codigo="' . htmlspecialchars($value["codigo_producto"]) . '" data-descripcion="' . htmlspecialchars($value["descripcion_producto"]) . '" data-cantidad="' . $value["cantidad_disponible"] . '" data-transportador="' . htmlspecialchars($value["nombre_transportador"]) . '" data-origen="' . htmlspecialchars($value["sucursal_origen"]) . '" data-despacho="' . htmlspecialchars($value["numero_despacho_origen"] ?? "N/A") . '"><i class="fa fa-download"></i></button></div>'
            ];
        }
        
        echo "<pre>";
        echo "Respuesta JSON que debería enviar el AJAX:\n";
        echo json_encode(["data" => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</pre>";
    }
    
    echo "<h3>📊 6. VERIFICAR ARCHIVO AJAX</h3>";
    
    $archivoAjax = "ajax/datatable-stock-transito.ajax.php";
    if(file_exists($archivoAjax)) {
        echo "✅ Archivo AJAX existe: " . $archivoAjax . "<br>";
        
        // Verificar si el archivo es ejecutable
        $contenido = file_get_contents($archivoAjax);
        if(strpos($contenido, 'class TablaStockTransito') !== false) {
            echo "✅ Clase TablaStockTransito encontrada<br>";
        } else {
            echo "❌ Clase TablaStockTransito no encontrada<br>";
        }
        
        if(strpos($contenido, 'mostrarTablaStockTransito') !== false) {
            echo "✅ Método mostrarTablaStockTransito encontrado<br>";
        } else {
            echo "❌ Método mostrarTablaStockTransito no encontrado<br>";
        }
        
    } else {
        echo "❌ Archivo AJAX no existe: " . $archivoAjax . "<br>";
    }
    
    echo "<h3>📊 7. VERIFICAR ARCHIVO JAVASCRIPT</h3>";
    
    $archivoJS = "vistas/js/stock-transito.js";
    if(file_exists($archivoJS)) {
        echo "✅ Archivo JavaScript existe: " . $archivoJS . "<br>";
        
        $contenidoJS = file_get_contents($archivoJS);
        if(strpos($contenidoJS, 'cargarTablaStockTransito') !== false) {
            echo "✅ Función cargarTablaStockTransito encontrada<br>";
        } else {
            echo "❌ Función cargarTablaStockTransito no encontrada<br>";
        }
        
        if(strpos($contenidoJS, 'DataTable') !== false) {
            echo "✅ DataTable encontrado en JavaScript<br>";
        } else {
            echo "❌ DataTable no encontrado en JavaScript<br>";
        }
        
    } else {
        echo "❌ Archivo JavaScript no existe: " . $archivoJS . "<br>";
    }
    
    echo "<h3>📊 8. PROBAR LLAMADA AJAX DIRECTA</h3>";
    
    echo "<button onclick='probarAjax()'>Probar AJAX</button>";
    echo "<div id='resultadoAjax'></div>";
    
    echo "<script>
    function probarAjax() {
        console.log('🧪 Probando AJAX...');
        
        $.ajax({
            url: 'ajax/datatable-stock-transito.ajax.php',
            method: 'POST',
            dataType: 'json',
            success: function(respuesta) {
                console.log('✅ Respuesta AJAX:', respuesta);
                $('#resultadoAjax').html('<pre>' + JSON.stringify(respuesta, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                console.error('❌ Error AJAX:', error);
                $('#resultadoAjax').html('<p style=\"color: red;\">Error: ' + error + '</p>');
            }
        });
    }
    </script>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<br><br>🎯 <strong>Debug completado</strong>";
?>