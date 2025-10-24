<?php
/*=============================================
BUSCAR Y MODIFICAR FUNCIÓN DE DESCARGA
=============================================*/

echo "🔍 Buscando y modificando función de descarga...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Buscar diferentes patrones de función de descarga
$patrones = [
    '/(success:\s*function\(respuesta\)\s*\{[^}]*if\(respuesta\.success\)[^}]*cargarStockTransito\(\)[^}]*\})/s',
    '/(success:\s*function\(respuesta\)\s*\{[^}]*if\(respuesta\.success\)[^}]*\})/s',
    '/(success:\s*function\(respuesta\)\s*\{[^}]*\})/s',
    '/(function.*descargar.*\{[^}]*success:[^}]*\})/s',
    '/(\.ajax\([^}]*success:[^}]*\})/s'
];

$funcionEncontrada = false;
$patronUsado = '';

foreach($patrones as $index => $patron) {
    if(preg_match($patron, $contenido, $matches)) {
        echo "✅ Función de descarga encontrada con patrón " . ($index + 1) . "\n";
        echo "📋 Contenido encontrado:\n";
        echo substr($matches[0], 0, 200) . "...\n\n";
        
        $funcionEncontrada = true;
        $patronUsado = $patron;
        break;
    }
}

if(!$funcionEncontrada) {
    echo "❌ No se encontró función de descarga con ningún patrón\n";
    echo "🔍 Buscando patrones alternativos...\n";
    
    // Buscar patrones más específicos
    if(strpos($contenido, 'cargarStockTransito') !== false) {
        echo "✅ Se encontró 'cargarStockTransito' en el archivo\n";
    }
    
    if(strpos($contenido, 'Swal.fire') !== false) {
        echo "✅ Se encontró 'Swal.fire' en el archivo\n";
    }
    
    if(strpos($contenido, 'modalDescargarStock') !== false) {
        echo "✅ Se encontró 'modalDescargarStock' en el archivo\n";
    }
    
    // Buscar líneas específicas
    $lineas = explode("\n", $contenido);
    echo "\n🔍 Buscando líneas relevantes...\n";
    
    for($i = 0; $i < count($lineas); $i++) {
        $linea = trim($lineas[$i]);
        if(strpos($linea, 'success:') !== false || 
           strpos($linea, 'cargarStockTransito') !== false ||
           strpos($linea, 'modalDescargarStock') !== false) {
            echo "   Línea " . ($i + 1) . ": " . $linea . "\n";
        }
    }
    
    exit;
}

// Crear backup
$backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
if(copy($archivo, $backup)) {
    echo "💾 Backup creado: $backup\n";
}

// Modificar la función encontrada
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

$nuevoContenido = preg_replace($patronUsado, $nuevaFuncion, $contenido);

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

echo "\n🎯 Búsqueda y modificación completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar descarga en stock-transito\n";
echo "2. Verificar que se registra en la tabla\n";
echo "3. Revisar logs para confirmar\n";
?>
