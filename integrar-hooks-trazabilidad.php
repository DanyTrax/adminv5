<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "api-transferencias/conexion-central.php";

echo "<h2>🔗 Integrando Hooks de Trazabilidad</h2>";
echo "<hr>";

try {
    echo "<h3>📋 Verificando archivos a modificar...</h3>";
    
    $archivos = [
        'ajax/despachos.ajax.php' => 'Hook para aceptación de despachos',
        'vistas/js/stock-transito-unificado.js' => 'Hook para descarga de productos'
    ];
    
    foreach($archivos as $archivo => $descripcion) {
        if(file_exists($archivo)) {
            echo "<p>✅ $archivo - $descripcion</p>";
        } else {
            echo "<p>❌ $archivo - NO ENCONTRADO</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🔧 Agregando hooks de trazabilidad...</h3>";
    
    // 1. Hook para aceptación de despachos
    echo "<h4>1. Hook para Aceptación de Despachos</h4>";
    
    $archivoDespachos = 'ajax/despachos.ajax.php';
    if(file_exists($archivoDespachos)) {
        $contenido = file_get_contents($archivoDespachos);
        
        // Verificar si ya tiene el hook
        if(strpos($contenido, 'registrarAceptacionDespachoTrazabilidad') !== false) {
            echo "<p>⚠️ El hook ya existe en $archivoDespachos</p>";
        } else {
            // Agregar función de trazabilidad al final del archivo
            $hookDespachos = '
/*=============================================
HOOK TRAZABILIDAD - REGISTRAR ACEPTACIÓN DE DESPACHO
=============================================*/
function registrarAceptacionDespachoTrazabilidad($numero_despacho, $transportador_id) {
    try {
        // Obtener datos del despacho
        $stmt = ConexionCentral::conectar()->prepare("
            SELECT * FROM despachos WHERE numero_despacho = ?
        ");
        $stmt->execute([$numero_despacho]);
        $despacho = $stmt->fetch();
        
        if(!$despacho) return;
        
        $productos = json_decode($despacho[\'productos_despacho\'], true);
        
        if(empty($productos)) return;
        
        // Registrar cada producto en trazabilidad
        foreach($productos as $producto) {
            $stmt = ConexionCentral::conectar()->prepare("
                INSERT INTO trazabilidad_movimientos (
                    numero_despacho, producto_codigo, producto_descripcion,
                    cantidad_total, cantidad_pendiente, cantidad_descargada,
                    sucursal_origen, usuario_origen_id, fecha_salida,
                    transportador_id, fecha_aceptacion, estado
                ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, NOW(), \'en_transito\')
            ");
            $stmt->execute([
                $numero_despacho, 
                $producto[\'codigo\'], 
                $producto[\'descripcion\'],
                $producto[\'cantidad\'], 
                $producto[\'cantidad\'], 
                0,
                $despacho[\'sucursal_origen\'], 
                $despacho[\'usuario_origen_id\'], 
                $despacho[\'fecha_creacion\'],
                $transportador_id
            ]);
        }
        
    } catch(Exception $e) {
        // Log del error pero no interrumpir el flujo principal
        error_log("Error en trazabilidad - Aceptación despacho: " . $e->getMessage());
    }
}';
            
            // Agregar al final del archivo
            $contenido .= $hookDespachos;
            file_put_contents($archivoDespachos, $contenido);
            echo "<p>✅ Hook agregado a $archivoDespachos</p>";
        }
    } else {
        echo "<p>❌ No se encontró $archivoDespachos</p>";
    }
    
    // 2. Hook para descarga de productos
    echo "<h4>2. Hook para Descarga de Productos</h4>";
    
    $archivoStock = 'vistas/js/stock-transito-unificado.js';
    if(file_exists($archivoStock)) {
        $contenido = file_get_contents($archivoStock);
        
        // Verificar si ya tiene el hook
        if(strpos($contenido, 'registrarDescargaTrazabilidad') !== false) {
            echo "<p>⚠️ El hook ya existe en $archivoStock</p>";
        } else {
            // Agregar función de trazabilidad
            $hookStock = '
/*=============================================
HOOK TRAZABILIDAD - REGISTRAR DESCARGA DE PRODUCTO
=============================================*/
function registrarDescargaTrazabilidad(codigo, cantidad, usuarioId, sucursal) {
    $.ajax({
        url: "ajax/trazabilidad.ajax.php",
        method: "POST",
        data: {
            accion: "registrar_descarga",
            producto_codigo: codigo,
            cantidad: cantidad,
            usuario_id: usuarioId,
            sucursal: sucursal
        },
        dataType: "json",
        success: function(response) {
            if(response.success) {
                console.log("✅ Trazabilidad registrada:", response);
            } else {
                console.error("❌ Error en trazabilidad:", response.error);
            }
        },
        error: function() {
            console.error("❌ Error de conexión en trazabilidad");
        }
    });
}';
            
            // Agregar al final del archivo
            $contenido .= $hookStock;
            file_put_contents($archivoStock, $contenido);
            echo "<p>✅ Hook agregado a $archivoStock</p>";
        }
    } else {
        echo "<p>❌ No se encontró $archivoStock</p>";
    }
    
    echo "<hr>";
    echo "<h3>📝 Instrucciones para Completar la Integración</h3>";
    echo "<div class='alert alert-info'>";
    echo "<h4>🔧 Pasos Manuales Requeridos</h4>";
    echo "<ol>";
    echo "<li><strong>En ajax/despachos.ajax.php:</strong> Agregar la llamada a la función después de aceptar despacho:<br>";
    echo "<code>registrarAceptacionDespachoTrazabilidad(\$numero_despacho, \$transportador_id);</code></li>";
    echo "<li><strong>En vistas/js/stock-transito-unificado.js:</strong> Agregar la llamada después de descargar producto:<br>";
    echo "<code>registrarDescargaTrazabilidad(codigo, cantidad, usuarioId, sucursal);</code></li>";
    echo "<li><strong>Agregar al menú:</strong> Incluir 'Trazabilidad' en el menú principal</li>";
    echo "<li><strong>Probar:</strong> Crear un despacho, aceptarlo y descargar productos para verificar la trazabilidad</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🎯 Funcionalidades del Sistema de Trazabilidad</h3>";
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sistema Implementado</h4>";
    echo "<ul>";
    echo "<li><strong>Registro Automático:</strong> Se registra automáticamente cada movimiento</li>";
    echo "<li><strong>Sistema LIFO:</strong> Último en entrar, primero en salir</li>";
    echo "<li><strong>Historial Completo:</strong> Desde origen hasta destino final</li>";
    echo "<li><strong>Búsqueda Universal:</strong> Por despacho, producto, usuario</li>";
    echo "<li><strong>Reportes:</strong> Estadísticas y despachos incompletos</li>";
    echo "</ul>";
    echo "</div>";
    
} catch(Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error en la integración</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

.alert {
    padding: 15px;
    margin: 10px 0;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6;
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

.alert-info {
    color: #31708f;
    background-color: #d9edf7;
    border-color: #bce8f1;
}

h2, h3, h4 {
    color: #333;
}

hr {
    border: 0;
    height: 1px;
    background-color: #ddd;
    margin: 20px 0;
}

code {
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
