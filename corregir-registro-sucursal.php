<?php
/*=============================================
CORREGIR REGISTRO CON DATOS DE SUCURSAL
=============================================*/

echo "🔧 Corrigiendo registro con datos de sucursal...\n\n";

$archivo_js = 'vistas/js/stock-transito-unificado.js';

if(file_exists($archivo_js)) {
    echo "📁 Archivo encontrado: $archivo_js\n";
    
    $contenido = file_get_contents($archivo_js);
    $contenido_original = $contenido;
    
    // Reemplazar la función registrarDescargaDirecta para obtener datos reales
    $nueva_funcion = 'function registrarDescargaDirecta(codigoProducto, cantidad, observaciones) {
    // Obtener datos del usuario actual desde variables globales
    var usuarioId = window.usuarioId || 1;
    var usuarioNombre = window.nombreUsuario || "Usuario";
    var sucursalId = window.sucursalId || 1;
    var sucursalNombre = window.nombreSucursal || "Sucursal";
    
    // Intentar obtener datos del DOM si las variables globales no están disponibles
    if ($("#usuarioId").length > 0) usuarioId = $("#usuarioId").val() || usuarioId;
    if ($("#usuarioNombre").length > 0) usuarioNombre = $("#usuarioNombre").val() || usuarioNombre;
    if ($("#sucursalId").length > 0) sucursalId = $("#sucursalId").val() || sucursalId;
    if ($("#sucursalNombre").length > 0) sucursalNombre = $("#sucursalNombre").val() || sucursalNombre;
    
    // Obtener información del producto desde stockSeleccionado
    var descripcionProducto = "";
    var transportadorId = null;
    var transportadorNombre = null;
    var numeroDespacho = null;
    
    if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
        descripcionProducto = stockSeleccionado.descripcion || "";
        transportadorNombre = stockSeleccionado.transportador || "";
        
        // Extraer transportador_id y numero_despacho de los detalles si están disponibles
        if (stockSeleccionado.detalles && Array.isArray(stockSeleccionado.detalles)) {
            // Buscar el primer detalle que tenga transportador_id
            for (var i = 0; i < stockSeleccionado.detalles.length; i++) {
                var detalle = stockSeleccionado.detalles[i];
                if (detalle.transportador_id) {
                    transportadorId = detalle.transportador_id;
                    break;
                }
            }
            
            // Buscar numero_despacho en los detalles
            for (var i = 0; i < stockSeleccionado.detalles.length; i++) {
                var detalle = stockSeleccionado.detalles[i];
                if (detalle.numero_despacho) {
                    numeroDespacho = detalle.numero_despacho;
                    break;
                }
            }
        }
    }
    
    console.log("📤 Datos para registro:", {
        codigoProducto,
        descripcionProducto,
        cantidad,
        usuarioId,
        usuarioNombre,
        sucursalId,
        sucursalNombre,
        transportadorId,
        transportadorNombre,
        numeroDespacho,
        observaciones
    });
    
    // Enviar registro directo por AJAX
    $.ajax({
        url: "ajax/registro-descargas-simple.ajax.php",
        method: "POST",
        data: {
            accion: "registrar_descarga",
            codigo_producto: codigoProducto,
            descripcion_producto: descripcionProducto,
            cantidad_descargada: cantidad,
            usuario_id: usuarioId,
            usuario_nombre: usuarioNombre,
            sucursal_id: sucursalId,
            sucursal_nombre: sucursalNombre,
            transportador_id: transportadorId,
            transportador_nombre: transportadorNombre,
            numero_despacho: numeroDespacho,
            observaciones: observaciones
        },
        dataType: "json",
        success: function(respuesta) {
            if(respuesta.success) {
                console.log("✅ Descarga registrada en la tabla:", codigoProducto);
            } else {
                console.error("❌ Error al registrar descarga:", respuesta.error);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Error AJAX al registrar descarga:", error);
            console.error("📋 Respuesta del servidor:", xhr.responseText);
        }
    });
}';
    
    // Buscar y reemplazar la función completa
    $patron = '/function registrarDescargaDirecta\([^}]+\}/s';
    $contenido = preg_replace($patron, $nueva_funcion, $contenido);
    
    // Solo escribir si hubo cambios
    if($contenido !== $contenido_original) {
        if(file_put_contents($archivo_js, $contenido)) {
            echo "✅ Función registrarDescargaDirecta corregida\n";
        } else {
            echo "❌ Error al escribir archivo\n";
        }
    } else {
        echo "ℹ️ Función ya estaba correcta\n";
    }
    
} else {
    echo "❌ Archivo no encontrado: $archivo_js\n";
}

echo "\n🎯 Registro corregido con datos de sucursal\n";
echo "🔧 Próximos pasos:\n";
echo "1. Ejecutar: obtener-datos-sucursal-activa.php\n";
echo "2. Probar una descarga nueva\n";
echo "3. Verificar que se registra en la tabla\n";
?>
