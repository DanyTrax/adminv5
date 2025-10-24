<?php
/*=============================================
CORREGIR LÍNEA ESPECÍFICA SIN TOCAR EL RESTO
=============================================*/

echo "🔧 Corrigiendo línea específica sin tocar el resto...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";
echo "📏 Tamaño: " . filesize($archivo) . " bytes\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Verificar sintaxis actual
$llaves_abiertas = substr_count($contenido, '{');
$llaves_cerradas = substr_count($contenido, '}');
echo "📋 Llaves abiertas: $llaves_abiertas\n";
echo "📋 Llaves cerradas: $llaves_cerradas\n";

if($llaves_abiertas === $llaves_cerradas) {
    echo "✅ Sintaxis correcta - no se requiere corrección\n";
} else {
    echo "❌ Sintaxis incorrecta - aplicando corrección mínima\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Buscar y reemplazar solo la línea problemática
    $patron = '/registrarDescargaDirecta\(codigoProducto, cantidadDescargar, observaciones\);/';
    
    if(preg_match($patron, $contenido)) {
        echo "✅ Línea registrarDescargaDirecta encontrada\n";
        
        // Reemplazar solo esa línea con código de registro directo
        $codigo_registro = '// 🔗 REGISTRO DIRECTO - Registrar descarga en la tabla
                            console.log("🔗 Registrando descarga directamente:", codigoProducto, cantidadDescargar);
                            
                            // Obtener datos del usuario actual
                            var usuarioId = sessionStorage.getItem("id") || "0";
                            var usuarioNombre = sessionStorage.getItem("nombre") || "Usuario";
                            
                            // Obtener datos de la sucursal
                            var sucursalId = "1";
                            var sucursalNombre = "Local Pruebas";
                            
                            // Obtener datos del producto y transportador desde stockSeleccionado
                            var descripcionProducto = "";
                            var transportadorNombre = "";
                            var transportadorId = "0";
                            var numeroDespacho = "";
                            
                            if (typeof stockSeleccionado === "object" && stockSeleccionado !== null) {
                                descripcionProducto = stockSeleccionado.descripcion || "";
                                transportadorNombre = stockSeleccionado.transportador || "";
                                
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
                                    cantidad_descargada: cantidadDescargar,
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
                                success: function(respuestaRegistro) {
                                    if(respuestaRegistro.success) {
                                        console.log("✅ Descarga registrada en la tabla:", codigoProducto);
                                    } else {
                                        console.error("❌ Error al registrar descarga:", respuestaRegistro.error);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("❌ Error AJAX al registrar descarga:", error);
                                }
                            });';
        
        $nuevoContenido = preg_replace($patron, $codigo_registro, $contenido);
        
        if($nuevoContenido !== $contenido) {
            // Escribir archivo modificado
            if(file_put_contents($archivo, $nuevoContenido)) {
                echo "✅ Línea modificada correctamente\n";
                echo "📏 Nuevo tamaño: " . filesize($archivo) . " bytes\n";
                
                // Verificar sintaxis
                $llaves_abiertas_nuevas = substr_count($nuevoContenido, '{');
                $llaves_cerradas_nuevas = substr_count($nuevoContenido, '}');
                echo "📋 Llaves abiertas: $llaves_abiertas_nuevas\n";
                echo "📋 Llaves cerradas: $llaves_cerradas_nuevas\n";
                
                if($llaves_abiertas_nuevas === $llaves_cerradas_nuevas) {
                    echo "✅ Sintaxis correcta\n";
                } else {
                    echo "❌ Error de sintaxis\n";
                }
            } else {
                echo "❌ Error al escribir archivo\n";
            }
        } else {
            echo "❌ No se pudo modificar la línea\n";
        }
    } else {
        echo "❌ No se encontró la línea registrarDescargaDirecta\n";
    }
}

echo "\n🎯 Corrección de línea específica completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Probar descarga y registro\n";
?>
