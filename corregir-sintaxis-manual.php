<?php
/*=============================================
CORRECCIÓN MANUAL DE SINTAXIS
=============================================*/

echo "🔧 Aplicando corrección manual de sintaxis...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";

// Leer contenido
$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);

echo "📏 Total de líneas: " . count($lineas) . "\n";

// Crear backup
$backup = $archivo . '.backup.' . date('Y-m-d-H-i-s');
if(copy($archivo, $backup)) {
    echo "💾 Backup creado: $backup\n";
}

// Verificar sintaxis actual
$llaves_abiertas = substr_count($contenido, '{');
$llaves_cerradas = substr_count($contenido, '}');
echo "📋 Llaves abiertas: $llaves_abiertas\n";
echo "📋 Llaves cerradas: $llaves_cerradas\n";

// Corregir problemas específicos identificados
echo "🔧 Aplicando correcciones específicas...\n";

// Problema 1: Línea 510 - falta cerrar una llave antes de }).then
// Buscar la línea problemática y corregirla
for($i = 0; $i < count($lineas); $i++) {
    $linea = trim($lineas[$i]);
    
    // Buscar la línea problemática: }).then(function(result) {
    if(strpos($linea, '}).then(function(result) {') !== false) {
        echo "✅ Línea problemática encontrada en línea " . ($i + 1) . "\n";
        echo "📋 Contenido: " . $linea . "\n";
        
        // Verificar si la línea anterior tiene una llave de cierre
        $linea_anterior = isset($lineas[$i-1]) ? trim($lineas[$i-1]) : '';
        echo "📋 Línea anterior: " . $linea_anterior . "\n";
        
        // Si la línea anterior no termina con }, agregar una
        if(!empty($linea_anterior) && !preg_match('/\}$/', $linea_anterior)) {
            echo "🔧 Agregando llave de cierre en línea " . $i . "\n";
            $lineas[$i-1] = $linea_anterior . "}";
        }
        break;
    }
}

// Problema 2: Verificar balance en sección de inicio (líneas 1-100)
echo "🔧 Corrigiendo sección de inicio...\n";
$seccion_inicio = array_slice($lineas, 0, 100);
$contenido_inicio = implode("\n", $seccion_inicio);
$llaves_abiertas_inicio = substr_count($contenido_inicio, '{');
$llaves_cerradas_inicio = substr_count($contenido_inicio, '}');

echo "📋 Sección inicio - Llaves abiertas: $llaves_abiertas_inicio, cerradas: $llaves_cerradas_inicio\n";

if($llaves_abiertas_inicio > $llaves_cerradas_inicio) {
    $diferencia = $llaves_abiertas_inicio - $llaves_cerradas_inicio;
    echo "🔧 Agregando $diferencia llaves de cierre en sección de inicio\n";
    
    // Agregar llaves de cierre al final de la sección
    for($i = 0; $i < $diferencia; $i++) {
        $lineas[99] = $lineas[99] . "}";
    }
}

// Problema 3: Verificar balance en sección final (líneas 201-773)
echo "🔧 Corrigiendo sección final...\n";
$seccion_final = array_slice($lineas, 200);
$contenido_final = implode("\n", $seccion_final);
$llaves_abiertas_final = substr_count($contenido_final, '{');
$llaves_cerradas_final = substr_count($contenido_final, '}');

echo "📋 Sección final - Llaves abiertas: $llaves_abiertas_final, cerradas: $llaves_cerradas_final\n";

if($llaves_cerradas_final > $llaves_abiertas_final) {
    $diferencia = $llaves_cerradas_final - $llaves_abiertas_final;
    echo "🔧 Removiendo $diferencia llaves de cierre en sección final\n";
    
    // Remover llaves de cierre extra del final
    $ultima_linea = count($lineas) - 1;
    for($i = 0; $i < $diferencia; $i++) {
        $lineas[$ultima_linea] = rtrim($lineas[$ultima_linea], '}');
    }
}

// Escribir archivo corregido
$contenido_corregido = implode("\n", $lineas);

if(file_put_contents($archivo, $contenido_corregido)) {
    echo "✅ Archivo corregido\n";
    
    // Verificar sintaxis corregida
    $llaves_abiertas_corregidas = substr_count($contenido_corregido, '{');
    $llaves_cerradas_corregidas = substr_count($contenido_corregido, '}');
    echo "📋 Llaves abiertas: $llaves_abiertas_corregidas\n";
    echo "📋 Llaves cerradas: $llaves_cerradas_corregidas\n";
    
    if($llaves_abiertas_corregidas === $llaves_cerradas_corregidas) {
        echo "✅ Sintaxis corregida correctamente\n";
    } else {
        echo "❌ Aún hay problemas de sintaxis\n";
    }
    
    echo "📏 Nuevo tamaño: " . filesize($archivo) . " bytes\n";
} else {
    echo "❌ Error al escribir archivo\n";
}

echo "\n🎯 Corrección manual completada\n";
echo "🔧 Próximos pasos:\n";
echo "1. Probar stock-transito\n";
echo "2. Verificar que los botones funcionan\n";
echo "3. Probar descarga y registro\n";
?>
