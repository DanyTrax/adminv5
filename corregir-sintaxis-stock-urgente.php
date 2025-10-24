<?php
/*=============================================
CORREGIR SINTAXIS STOCK URGENTE
=============================================*/

echo "🚨 Corrigiendo sintaxis de stock-transito URGENTE...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

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
    
    // Restaurar desde el backup original (antes de los cambios)
    $backup_original = 'vistas/js/stock-transito-unificado.js.backup.2025-10-24-18-15-10';
    
    if(file_exists($backup_original)) {
        if(copy($backup_original, $archivo)) {
            echo "✅ Archivo restaurado desde backup original\n";
            echo "📏 Tamaño restaurado: " . filesize($archivo) . " bytes\n";
            
            // Verificar sintaxis restaurada
            $contenido_restaurado = file_get_contents($archivo);
            $llaves_abiertas_rest = substr_count($contenido_restaurado, '{');
            $llaves_cerradas_rest = substr_count($contenido_restaurado, '}');
            echo "📋 Llaves abiertas: $llaves_abiertas_rest\n";
            echo "📋 Llaves cerradas: $llaves_cerradas_rest\n";
            
            if($llaves_abiertas_rest === $llaves_cerradas_rest) {
                echo "✅ Sintaxis corregida correctamente\n";
            } else {
                echo "❌ Aún hay problemas de sintaxis\n";
            }
        } else {
            echo "❌ Error al restaurar desde backup\n";
        }
    } else {
        echo "❌ Backup original no encontrado\n";
        echo "🔧 Buscando otros backups...\n";
        
        // Buscar otros backups
        $backups = glob($archivo . '.backup.*');
        if(!empty($backups)) {
            // Ordenar por fecha de modificación (más reciente primero)
            usort($backups, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backup_disponible = $backups[0];
            echo "📋 Backup disponible: " . basename($backup_disponible) . "\n";
            
            if(copy($backup_disponible, $archivo)) {
                echo "✅ Archivo restaurado desde backup disponible\n";
                echo "📏 Tamaño restaurado: " . filesize($archivo) . " bytes\n";
            } else {
                echo "❌ Error al restaurar desde backup disponible\n";
            }
        } else {
            echo "❌ No se encontraron backups\n";
        }
    }
} else {
    echo "✅ Sintaxis correcta - no se requiere corrección\n";
}

echo "\n🎯 Corrección de sintaxis completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Si funciona, aplicar corrección manual\n";
?>
