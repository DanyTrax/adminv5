<?php
/*=============================================
VERIFICAR SINTAXIS DE JAVASCRIPT
=============================================*/

echo "🔍 Verificando sintaxis de stock-transito-unificado.js...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(file_exists($archivo)) {
    echo "📁 Archivo encontrado: $archivo\n";
    echo "📏 Tamaño: " . filesize($archivo) . " bytes\n";
    echo "📏 Líneas: " . count(file($archivo)) . "\n";
    
    $contenido = file_get_contents($archivo);
    $lineas = explode("\n", $contenido);
    
    echo "\n🔍 Verificando sintaxis básica...\n";
    
    // Verificar llaves balanceadas
    $llaves_abiertas = substr_count($contenido, '{');
    $llaves_cerradas = substr_count($contenido, '}');
    echo "📋 Llaves abiertas: $llaves_abiertas\n";
    echo "📋 Llaves cerradas: $llaves_cerradas\n";
    
    if($llaves_abiertas === $llaves_cerradas) {
        echo "✅ Llaves balanceadas correctamente\n";
    } else {
        echo "❌ Llaves NO balanceadas\n";
    }
    
    // Verificar paréntesis balanceados
    $parentesis_abiertos = substr_count($contenido, '(');
    $parentesis_cerrados = substr_count($contenido, ')');
    echo "📋 Paréntesis abiertos: $parentesis_abiertos\n";
    echo "📋 Paréntesis cerrados: $parentesis_cerrados\n";
    
    if($parentesis_abiertos === $parentesis_cerrados) {
        echo "✅ Paréntesis balanceados correctamente\n";
    } else {
        echo "❌ Paréntesis NO balanceados\n";
    }
    
    // Verificar comillas balanceadas
    $comillas_simples = substr_count($contenido, "'");
    $comillas_dobles = substr_count($contenido, '"');
    echo "📋 Comillas simples: $comillas_simples\n";
    echo "📋 Comillas dobles: $comillas_dobles\n";
    
    // Verificar últimas líneas
    echo "\n📋 Últimas 10 líneas del archivo:\n";
    $total_lineas = count($lineas);
    for($i = max(0, $total_lineas - 10); $i < $total_lineas; $i++) {
        echo "   " . ($i + 1) . ": " . trim($lineas[$i]) . "\n";
    }
    
    // Buscar posibles problemas
    echo "\n🔍 Buscando posibles problemas...\n";
    
    // Buscar líneas con solo }
    for($i = 0; $i < count($lineas); $i++) {
        $linea = trim($lineas[$i]);
        if($linea === '}' && $i > 0) {
            $linea_anterior = trim($lineas[$i-1]);
            if(empty($linea_anterior) || $linea_anterior === '{') {
                echo "⚠️ Línea " . ($i + 1) . ": Llave de cierre sin contenido\n";
            }
        }
    }
    
    // Buscar funciones sin cerrar
    $funciones_abiertas = 0;
    for($i = 0; $i < count($lineas); $i++) {
        $linea = trim($lineas[$i]);
        if(strpos($linea, 'function') !== false && strpos($linea, '{') !== false) {
            $funciones_abiertas++;
        }
        if($linea === '}') {
            $funciones_abiertas--;
        }
    }
    
    if($funciones_abiertas === 0) {
        echo "✅ Todas las funciones están cerradas correctamente\n";
    } else {
        echo "❌ Hay $funciones_abiertas funciones sin cerrar\n";
    }
    
} else {
    echo "❌ Archivo no encontrado: $archivo\n";
}

echo "\n🎯 Verificación completada\n";
?>
