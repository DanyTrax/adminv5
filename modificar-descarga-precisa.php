<?php
/*=============================================
MODIFICAR DESCARGA DE MANERA PRECISA
=============================================*/

echo "🎯 Modificando descarga de manera precisa...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Buscar la función específica que contiene cargarStockTransito
$patron = '/(success:\s*function\(respuesta\)\s*\{[^}]*if\(respuesta\.success\)[^}]*cargarStockTransito\(\)[^}]*\})/s';

if(preg_match($patron, $contenido, $matches)) {
    echo "✅ Función de descarga encontrada\n";
    echo "📋 Contenido original:\n";
    echo $matches[0] . "\n\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Reemplazar con la versión que incluye el hook
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
                            
                            // 🔗 HOOK - Registrar descarga en el sistema
                            registrarDescargaSimple(codigoProducto, cantidadDescargar, observaciones);
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
            echo "✅ Función de descarga modificada correctamente\n";
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
        '/(success:\s*function\(respuesta\)\s*\{[^}]*\})/s',
        '/(\.ajax\([^}]*success:[^}]*\})/s',
        '/(function.*descargar.*\{[^}]*\})/s'
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

echo "\n🎯 Modificación precisa completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar descarga en stock-transito\n";
echo "2. Verificar que se registra en la tabla\n";
echo "3. Revisar logs para confirmar\n";
?>
