<?php
/*=============================================
MODIFICAR DESCARGA PARA REGISTRO DIRECTO
=============================================*/

echo "🔧 Modificando descarga para registro directo...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Buscar la función de descarga exitosa
$patron = '/(success:\s*function\(respuesta\)\s*\{[^}]*if\(respuesta\.success\)[^}]*cargarStockTransito\(\)[^}]*\$\(\"#modalDescargarStock\"\)\.modal\(\"hide\"\)[^}]*\})/s';

if(preg_match($patron, $contenido, $matches)) {
    echo "✅ Función de descarga encontrada\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Reemplazar con la versión que incluye registro directo
    $nuevaFuncion = 'success: function(respuesta) {
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
    
    $nuevoContenido = preg_replace($patron, $nuevaFuncion, $contenido);
    
    if($nuevoContenido !== $contenido) {
        // Escribir archivo modificado
        if(file_put_contents($archivo, $nuevoContenido)) {
            echo "✅ Función de descarga modificada con registro directo\n";
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
        echo "❌ No se pudo modificar la función\n";
    }
} else {
    echo "❌ No se encontró la función de descarga\n";
    echo "🔍 Buscando patrones alternativos...\n";
    
    // Buscar patrones más amplios
    $patrones_alternativos = [
        '/(success:\s*function\(respuesta\)\s*\{[^}]*cargarStockTransito\(\)[^}]*\})/s',
        '/(success:\s*function\(respuesta\)\s*\{[^}]*modalDescargarStock[^}]*\})/s',
        '/(success:\s*function\(respuesta\)\s*\{[^}]*Swal\.fire[^}]*\})/s'
    ];
    
    foreach($patrones_alternativos as $index => $patron_alt) {
        if(preg_match($patron_alt, $contenido, $matches_alt)) {
            echo "✅ Patrón alternativo " . ($index + 1) . " encontrado\n";
            echo "📋 Contenido:\n";
            echo substr($matches_alt[0], 0, 300) . "...\n\n";
        }
    }
    
    // Buscar líneas específicas
    $lineas = explode("\n", $contenido);
    echo "🔍 Buscando líneas relevantes...\n";
    
    for($i = 0; $i < count($lineas); $i++) {
        $linea = trim($lineas[$i]);
        if(strpos($linea, 'success:') !== false || 
           strpos($linea, 'cargarStockTransito') !== false ||
           strpos($linea, 'modalDescargarStock') !== false) {
            echo "   Línea " . ($i + 1) . ": " . $linea . "\n";
        }
    }
}

echo "\n🎯 Modificación con registro directo completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar descarga en stock-transito\n";
echo "2. Verificar que se registra en la tabla\n";
echo "3. Revisar logs para confirmar\n";
?>
