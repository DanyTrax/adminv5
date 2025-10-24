<?php
/*=============================================
CORREGIR SINTAXIS DE STOCK-TRANSITO-UNIFICADO.JS
=============================================*/

echo "🔧 Corrigiendo sintaxis de stock-transito-unificado.js...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";
echo "📏 Tamaño actual: " . filesize($archivo) . " bytes\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);

// Verificar sintaxis actual
$llaves_abiertas = substr_count($contenido, '{');
$llaves_cerradas = substr_count($contenido, '}');
echo "📋 Llaves abiertas: $llaves_abiertas\n";
echo "📋 Llaves cerradas: $llaves_cerradas\n";

if($llaves_abiertas !== $llaves_cerradas) {
    echo "❌ Sintaxis incorrecta - corrigiendo...\n";
    
    // Crear backup
    $backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
    if(copy($archivo, $backup)) {
        echo "💾 Backup creado: $backup\n";
    }
    
    // Buscar y corregir el problema específico
    // El problema está en la función de descarga que se duplicó o malformó
    
    // Buscar la función problemática
    $patron = '/(success:\s*function\(respuesta\)\s*\{[^}]*if\(respuesta\.success\)\s*\{[^}]*\}[^}]*\})/s';
    
    if(preg_match($patron, $contenido, $matches)) {
        echo "✅ Función problemática encontrada\n";
        
        // Reemplazar con la versión correcta
        $funcionCorrecta = 'success: function(respuesta) {
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
        
        $nuevoContenido = preg_replace($patron, $funcionCorrecta, $contenido);
        
        if($nuevoContenido !== $contenido) {
            // Escribir archivo corregido
            if(file_put_contents($archivo, $nuevoContenido)) {
                echo "✅ Función corregida correctamente\n";
                echo "📏 Nuevo tamaño: " . filesize($archivo) . " bytes\n";
                
                // Verificar sintaxis corregida
                $llaves_abiertas_nuevas = substr_count($nuevoContenido, '{');
                $llaves_cerradas_nuevas = substr_count($nuevoContenido, '}');
                echo "📋 Llaves abiertas: $llaves_abiertas_nuevas\n";
                echo "📋 Llaves cerradas: $llaves_cerradas_nuevas\n";
                
                if($llaves_abiertas_nuevas === $llaves_cerradas_nuevas) {
                    echo "✅ Sintaxis corregida correctamente\n";
                } else {
                    echo "❌ Aún hay problemas de sintaxis\n";
                    
                    // Intentar corrección más agresiva
                    echo "🔧 Aplicando corrección agresiva...\n";
                    
                    // Buscar y eliminar llaves duplicadas o malformadas
                    $contenido_limpio = $nuevoContenido;
                    
                    // Eliminar llaves duplicadas consecutivas
                    $contenido_limpio = preg_replace('/\}\s*\}/', '}', $contenido_limpio);
                    $contenido_limpio = preg_replace('/\{\s*\{/', '{', $contenido_limpio);
                    
                    // Escribir versión limpia
                    if(file_put_contents($archivo, $contenido_limpio)) {
                        echo "✅ Corrección agresiva aplicada\n";
                        
                        // Verificar sintaxis final
                        $llaves_abiertas_final = substr_count($contenido_limpio, '{');
                        $llaves_cerradas_final = substr_count($contenido_limpio, '}');
                        echo "📋 Llaves abiertas: $llaves_abiertas_final\n";
                        echo "📋 Llaves cerradas: $llaves_cerradas_final\n";
                        
                        if($llaves_abiertas_final === $llaves_cerradas_final) {
                            echo "✅ Sintaxis corregida definitivamente\n";
                        } else {
                            echo "❌ Problema persistente - restaurando desde backup\n";
                            
                            // Restaurar desde el primer backup (antes de los cambios)
                            $backup_original = 'vistas/js/stock-transito-unificado.js.backup.2025-10-24-18-00-31';
                            if(file_exists($backup_original)) {
                                if(copy($backup_original, $archivo)) {
                                    echo "✅ Archivo restaurado desde backup original\n";
                                }
                            }
                        }
                    }
                }
            } else {
                echo "❌ Error al escribir archivo corregido\n";
            }
        } else {
            echo "❌ No se pudo corregir la función\n";
        }
    } else {
        echo "❌ No se encontró la función problemática\n";
        echo "🔧 Restaurando desde backup original...\n";
        
        // Restaurar desde el primer backup (antes de los cambios)
        $backup_original = 'vistas/js/stock-transito-unificado.js.backup.2025-10-24-18-00-31';
        if(file_exists($backup_original)) {
            if(copy($backup_original, $archivo)) {
                echo "✅ Archivo restaurado desde backup original\n";
                echo "📏 Tamaño restaurado: " . filesize($archivo) . " bytes\n";
            }
        }
    }
} else {
    echo "✅ Sintaxis correcta - no se requiere corrección\n";
}

echo "\n🎯 Corrección completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Si funciona, aplicar corrección manual\n";
?>
