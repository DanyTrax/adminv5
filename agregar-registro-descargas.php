<?php
/*=============================================
AGREGAR REGISTRO DE DESCARGAS AL ARCHIVO LIMPIO
=============================================*/

echo "🔧 Agregando función de registro de descargas...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";
echo "📏 Tamaño actual: " . filesize($archivo) . " bytes\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Función para registrar descarga
$funcionRegistro = '
// =============================================
// FUNCIÓN PARA REGISTRAR DESCARGA EN TABLA
// =============================================
function registrarDescargaSimple(codigoProducto, cantidad, observaciones) {
    console.log("🔗 Registrando descarga:", codigoProducto, cantidad);
    
    // Obtener datos del usuario actual
    var usuarioId = sessionStorage.getItem("id") || "0";
    var usuarioNombre = sessionStorage.getItem("nombre") || "Usuario";
    
    // Obtener datos de la sucursal
    var sucursalId = "1"; // Por defecto
    var sucursalNombre = "Local Pruebas"; // Por defecto
    
    // Obtener datos del producto y transportador desde stockSeleccionado
    var descripcionProducto = "";
    var transportadorNombre = "";
    var transportadorId = "0";
    var numeroDespacho = "";
    
    if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
        descripcionProducto = stockSeleccionado.descripcion || "";
        transportadorNombre = stockSeleccionado.transportador || "";
        
        // Extraer transportador_id y numero_despacho de los detalles
        if (stockSeleccionado.detalles) {
            var detalles = stockSeleccionado.detalles;
            if (detalles.transportador_id) {
                transportadorId = detalles.transportador_id;
            }
            if (detalles.numero_despacho) {
                numeroDespacho = detalles.numero_despacho;
            }
        }
    }
    
    // Hacer petición AJAX para registrar la descarga
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
        }
    });
}';

// Buscar donde insertar la función (antes del final del archivo)
$posicionInsercion = strrpos($contenido, '}');

if($posicionInsercion !== false) {
    // Insertar la función antes del último }
    $nuevoContenido = substr($contenido, 0, $posicionInsercion) . $funcionRegistro . "\n" . substr($contenido, $posicionInsercion);
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Escribir archivo modificado
    if(file_put_contents($archivo, $nuevoContenido)) {
        echo "✅ Función de registro agregada correctamente\n";
        echo "📏 Nuevo tamaño: " . filesize($archivo) . " bytes\n";
        
        // Verificar sintaxis
        $llaves_abiertas = substr_count($nuevoContenido, '{');
        $llaves_cerradas = substr_count($nuevoContenido, '}');
        echo "📋 Llaves abiertas: $llaves_abiertas\n";
        echo "📋 Llaves cerradas: $llaves_cerradas\n";
        
        if($llaves_abiertas === $llaves_cerradas) {
            echo "✅ Sintaxis correcta\n";
        } else {
            echo "❌ Error de sintaxis\n";
        }
    } else {
        echo "❌ Error al escribir archivo\n";
    }
} else {
    echo "❌ No se pudo encontrar posición para insertar\n";
}

echo "\n🎯 Función de registro agregada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar descarga en stock-transito\n";
echo "2. Verificar que se registra en la tabla\n";
echo "3. Revisar logs para confirmar\n";
?>
