<?php
/*=============================================
CORREGIR FUNCIÓN DE DESCARGA EN LÍNEA 422
=============================================*/

echo "🔧 Corrigiendo función de descarga en línea 422...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);

echo "📏 Total de líneas: " . count($lineas) . "\n\n";

// Buscar la función en línea 422
$inicio_funcion = 421; // Línea 422 (índice 421)
$fin_funcion = 448; // Línea 449 (índice 448)

echo "🔍 Examinando función en líneas " . ($inicio_funcion + 1) . " a " . ($fin_funcion + 1) . "\n";

// Mostrar contenido actual
echo "📋 Contenido actual:\n";
for($i = $inicio_funcion; $i <= $fin_funcion; $i++) {
    echo "   " . ($i + 1) . ": " . $lineas[$i] . "\n";
}

echo "\n";

// Crear backup
$backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
if(copy($archivo, $backup)) {
    echo "💾 Backup creado: $backup\n";
}

// Crear nueva función con registro directo
$nueva_funcion = 'success: function(respuesta) {
                        if(respuesta.success) {
                            // 🔗 REGISTRO DIRECTO - Registrar descarga en el sistema
                            console.log("🔗 Registrando descarga directamente:", codigoProducto, cantidadDescargar);
                            
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
                            });
                            
                            swal({
                                type: "success",
                                title: "¡Descarga Exitosa!",
                                text: respuesta.message,
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            }).then(function(result) {
                                if(result.value) {
                                    cargarStockTransito();
                                    $("#modalDescargarStock").modal("hide");
                                }
                            });
                        } else {
                            swal({
                                type: "error",
                                title: "Error",
                                text: respuesta.message || "Error al descargar el producto",
                                showConfirmButton: true,
                                confirmButtonText: "Cerrar"
                            });
                        }
                    }';

// Reemplazar las líneas de la función
$nuevas_lineas = $lineas;
$nuevas_lineas[$inicio_funcion] = $nueva_funcion;

// Eliminar las líneas intermedias
for($i = $inicio_funcion + 1; $i <= $fin_funcion; $i++) {
    unset($nuevas_lineas[$i]);
}

// Reindexar array
$nuevas_lineas = array_values($nuevas_lineas);

// Escribir archivo modificado
$nuevo_contenido = implode("\n", $nuevas_lineas);

if(file_put_contents($archivo, $nuevo_contenido)) {
    echo "✅ Función de descarga corregida con registro directo\n";
    echo "📏 Nuevo tamaño: " . filesize($archivo) . " bytes\n";
    
    // Verificar sintaxis
    $llaves_abiertas = substr_count($nuevo_contenido, '{');
    $llaves_cerradas = substr_count($nuevo_contenido, '}');
    echo "📋 Llaves abiertas: $llaves_abiertas\n";
    echo "📋 Llaves cerradas: $llaves_cerradas\n";
    
    if($llaves_abiertas === $llaves_cerradas) {
        echo "✅ Sintaxis correcta\n";
    } else {
        echo "❌ Error de sintaxis\n";
    }
    
    // Mostrar contenido modificado
    echo "\n📋 Contenido modificado:\n";
    $nuevas_lineas_array = explode("\n", $nuevo_contenido);
    for($i = $inicio_funcion; $i < min($inicio_funcion + 20, count($nuevas_lineas_array)); $i++) {
        echo "   " . ($i + 1) . ": " . $nuevas_lineas_array[$i] . "\n";
    }
    
} else {
    echo "❌ Error al escribir archivo\n";
}

echo "\n🎯 Corrección de función de descarga completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Probar descarga y registro\n";
?>
