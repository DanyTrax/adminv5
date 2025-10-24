<?php
/*=============================================
EXAMINAR FUNCIONES SUCCESS PARA ENCONTRAR LA CORRECTA
=============================================*/

echo "🔍 Examinando funciones success para encontrar la correcta...\n\n";

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

// Buscar todas las funciones success
$funciones_success = [];

for($i = 0; $i < count($lineas); $i++) {
    $linea = trim($lineas[$i]);
    
    if(strpos($linea, 'success: function(respuesta)') !== false) {
        echo "🔍 Función success encontrada en línea " . ($i + 1) . "\n";
        
        // Examinar las siguientes líneas para entender qué hace esta función
        $j = $i;
        $contenido_funcion = [];
        $llaves_abiertas = 0;
        $llaves_cerradas = 0;
        $es_funcion_descarga = false;
        
        while($j < count($lineas) && $j < $i + 50) { // Examinar las siguientes 50 líneas
            $linea_actual = $lineas[$j];
            $contenido_funcion[] = $linea_actual;
            
            $llaves_abiertas += substr_count($linea_actual, '{');
            $llaves_cerradas += substr_count($linea_actual, '}');
            
            // Verificar si es función de descarga
            if(strpos($linea_actual, 'cargarStockTransito') !== false) {
                $es_funcion_descarga = true;
            }
            if(strpos($linea_actual, 'modalDescargarStock') !== false) {
                $es_funcion_descarga = true;
            }
            if(strpos($linea_actual, 'Swal.fire') !== false) {
                $es_funcion_descarga = true;
            }
            
            if($llaves_abiertas > 0 && $llaves_abiertas === $llaves_cerradas) {
                break;
            }
            
            $j++;
        }
        
        echo "   📋 Líneas: " . ($i + 1) . " a " . ($j + 1) . "\n";
        echo "   📋 Es función de descarga: " . ($es_funcion_descarga ? "SÍ" : "NO") . "\n";
        
        // Mostrar contenido de la función
        echo "   📋 Contenido:\n";
        for($k = 0; $k < min(10, count($contenido_funcion)); $k++) {
            echo "      " . ($i + $k + 1) . ": " . trim($contenido_funcion[$k]) . "\n";
        }
        if(count($contenido_funcion) > 10) {
            echo "      ... (más líneas)\n";
        }
        
        if($es_funcion_descarga) {
            echo "   🎯 ¡ESTA ES LA FUNCIÓN DE DESCARGA!\n";
            $funciones_success[] = [
                'inicio' => $i,
                'fin' => $j,
                'es_descarga' => true,
                'contenido' => $contenido_funcion
            ];
        } else {
            $funciones_success[] = [
                'inicio' => $i,
                'fin' => $j,
                'es_descarga' => false,
                'contenido' => $contenido_funcion
            ];
        }
        
        echo "\n";
    }
}

echo "📊 Resumen de funciones encontradas:\n";
echo "=====================================\n";

foreach($funciones_success as $index => $funcion) {
    echo "Función " . ($index + 1) . ":\n";
    echo "  - Líneas: " . ($funcion['inicio'] + 1) . " a " . ($funcion['fin'] + 1) . "\n";
    echo "  - Es descarga: " . ($funcion['es_descarga'] ? "SÍ" : "NO") . "\n";
    echo "\n";
}

// Si encontramos la función de descarga, modificarla
$funcion_descarga = null;
foreach($funciones_success as $funcion) {
    if($funcion['es_descarga']) {
        $funcion_descarga = $funcion;
        break;
    }
}

if($funcion_descarga) {
    echo "🎯 Modificando función de descarga encontrada...\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Crear nueva función con registro directo
    $nueva_funcion = 'success: function(respuesta) {
                        if(respuesta.success) {
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                title: "¡Descarga exitosa!",
                                text: "El producto ha sido descargado correctamente",
                                icon: "success",
                                confirmButtonText: "Aceptar"
                            });
                            
                            // Actualizar la tabla
                            cargarStockTransito();
                            
                            // Cerrar modal
                            $("#modalDescargarStock").modal("hide");
                            
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
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: respuesta.mensaje || "Error al descargar el producto",
                                icon: "error",
                                confirmButtonText: "Aceptar"
                            });
                        }
                    }';
    
    // Reemplazar las líneas de la función
    $nuevas_lineas = $lineas;
    $nuevas_lineas[$funcion_descarga['inicio']] = $nueva_funcion;
    
    // Eliminar las líneas intermedias
    for($i = $funcion_descarga['inicio'] + 1; $i <= $funcion_descarga['fin']; $i++) {
        unset($nuevas_lineas[$i]);
    }
    
    // Reindexar array
    $nuevas_lineas = array_values($nuevas_lineas);
    
    // Escribir archivo modificado
    $nuevo_contenido = implode("\n", $nuevas_lineas);
    
    if(file_put_contents($archivo, $nuevo_contenido)) {
        echo "✅ Función de descarga modificada con registro directo\n";
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
    } else {
        echo "❌ Error al escribir archivo\n";
    }
} else {
    echo "❌ No se encontró función de descarga\n";
}

echo "\n🎯 Examinación y modificación completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Probar descarga y registro\n";
?>
