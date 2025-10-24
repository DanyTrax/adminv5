<?php
/*=============================================
BUSCAR FUNCIÓN DE DESCARGA POR LÍNEAS
=============================================*/

echo "🔍 Buscando función de descarga por líneas...\n\n";

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

// Buscar la función de descarga por líneas específicas
$funcion_encontrada = false;
$inicio_funcion = 0;
$fin_funcion = 0;

for($i = 0; $i < count($lineas); $i++) {
    $linea = trim($lineas[$i]);
    
    // Buscar inicio de función de descarga
    if(strpos($linea, 'success: function(respuesta)') !== false) {
        echo "🔍 Línea " . ($i + 1) . ": " . $linea . "\n";
        
        // Verificar si es la función de descarga (debe tener cargarStockTransito y modalDescargarStock)
        $j = $i;
        $es_funcion_descarga = false;
        
        while($j < count($lineas) && $j < $i + 20) { // Buscar en las siguientes 20 líneas
            $linea_actual = trim($lineas[$j]);
            
            if(strpos($linea_actual, 'cargarStockTransito') !== false) {
                echo "   ✅ Tiene cargarStockTransito en línea " . ($j + 1) . "\n";
                $es_funcion_descarga = true;
            }
            
            if(strpos($linea_actual, 'modalDescargarStock') !== false) {
                echo "   ✅ Tiene modalDescargarStock en línea " . ($j + 1) . "\n";
                $es_funcion_descarga = true;
            }
            
            $j++;
        }
        
        if($es_funcion_descarga) {
            echo "🎯 ¡FUNCIÓN DE DESCARGA ENCONTRADA!\n";
            echo "📋 Línea de inicio: " . ($i + 1) . "\n";
            
            $inicio_funcion = $i;
            $funcion_encontrada = true;
            
            // Buscar el final de la función
            $j = $i;
            $llaves_abiertas = 0;
            $llaves_cerradas = 0;
            
            while($j < count($lineas)) {
                $linea_actual = $lineas[$j];
                
                $llaves_abiertas += substr_count($linea_actual, '{');
                $llaves_cerradas += substr_count($linea_actual, '}');
                
                if($llaves_abiertas > 0 && $llaves_abiertas === $llaves_cerradas) {
                    $fin_funcion = $j;
                    echo "📋 Línea de fin: " . ($j + 1) . "\n";
                    break;
                }
                
                $j++;
            }
            
            break;
        }
    }
}

if($funcion_encontrada) {
    echo "\n📋 Contenido de la función de descarga:\n";
    echo "=====================================\n";
    
    for($i = $inicio_funcion; $i <= $fin_funcion; $i++) {
        echo "   " . ($i + 1) . ": " . $lineas[$i] . "\n";
    }
    
    echo "=====================================\n\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Modificar la función
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
        echo "✅ Función de descarga modificada correctamente\n";
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
    echo "❌ No se encontró la función de descarga\n";
}

echo "\n🎯 Búsqueda por líneas completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Probar descarga y registro\n";
?>
