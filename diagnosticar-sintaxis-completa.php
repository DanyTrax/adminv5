<?php
/*=============================================
DIAGNOSTICAR SINTAXIS COMPLETA
=============================================*/

echo "🔍 Diagnosticando sintaxis completa de stock-transito...\n\n";

$archivo = 'vistas/js/stock-transito-unificado.js';

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

echo "📁 Archivo encontrado: $archivo\n";
echo "📏 Tamaño: " . filesize($archivo) . " bytes\n\n";

// Leer contenido actual
$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);

echo "📏 Total de líneas: " . count($lineas) . "\n\n";

// Verificar sintaxis básica
$llaves_abiertas = substr_count($contenido, '{');
$llaves_cerradas = substr_count($contenido, '}');
echo "📋 Llaves abiertas: $llaves_abiertas\n";
echo "📋 Llaves cerradas: $llaves_cerradas\n";

if($llaves_abiertas !== $llaves_cerradas) {
    echo "❌ Llaves NO balanceadas\n";
} else {
    echo "✅ Llaves balanceadas\n";
}

echo "\n";

// Examinar líneas específicas mencionadas en el error
echo "🔍 Examinando líneas específicas del error...\n";
echo "📋 Línea 414 (donde se abrió {):\n";
if(isset($lineas[413])) {
    echo "   " . ($lineas[413]) . "\n";
} else {
    echo "   ❌ Línea 414 no existe\n";
}

echo "📋 Línea 510 (donde falta }):\n";
if(isset($lineas[509])) {
    echo "   " . ($lineas[509]) . "\n";
} else {
    echo "   ❌ Línea 510 no existe\n";
}

echo "\n";

// Examinar contexto alrededor de las líneas problemáticas
echo "🔍 Examinando contexto alrededor de línea 414...\n";
for($i = 410; $i <= 420; $i++) {
    if(isset($lineas[$i])) {
        echo "   " . ($i + 1) . ": " . $lineas[$i] . "\n";
    }
}

echo "\n🔍 Examinando contexto alrededor de línea 510...\n";
for($i = 505; $i <= 515; $i++) {
    if(isset($lineas[$i])) {
        echo "   " . ($i + 1) . ": " . $lineas[$i] . "\n";
    }
}

echo "\n";

// Buscar funciones que podrían tener problemas
echo "🔍 Buscando funciones problemáticas...\n";
$funciones_problematicas = [];

for($i = 0; $i < count($lineas); $i++) {
    $linea = trim($lineas[$i]);
    
    // Buscar funciones que podrían tener problemas de sintaxis
    if(strpos($linea, 'function') !== false || 
       strpos($linea, 'success:') !== false ||
       strpos($linea, 'registrarDescargaDirecta') !== false) {
        
        $funciones_problematicas[] = [
            'linea' => $i + 1,
            'contenido' => $linea
        ];
    }
}

echo "📋 Funciones encontradas:\n";
foreach($funciones_problematicas as $funcion) {
    echo "   Línea " . $funcion['linea'] . ": " . $funcion['contenido'] . "\n";
}

echo "\n";

// Buscar llaves desbalanceadas por sección
echo "🔍 Analizando balance de llaves por sección...\n";
$secciones = [
    'Inicio' => [0, 100],
    'Medio' => [100, 200],
    'Final' => [200, count($lineas)]
];

foreach($secciones as $nombre => $rango) {
    $contenido_seccion = implode("\n", array_slice($lineas, $rango[0], $rango[1] - $rango[0]));
    $llaves_abiertas_seccion = substr_count($contenido_seccion, '{');
    $llaves_cerradas_seccion = substr_count($contenido_seccion, '}');
    
    echo "📋 $nombre (líneas " . ($rango[0] + 1) . "-" . $rango[1] . "):\n";
    echo "   Llaves abiertas: $llaves_abiertas_seccion\n";
    echo "   Llaves cerradas: $llaves_cerradas_seccion\n";
    
    if($llaves_abiertas_seccion !== $llaves_cerradas_seccion) {
        echo "   ❌ Desbalance en esta sección\n";
    } else {
        echo "   ✅ Balanceado\n";
    }
}

echo "\n";

// Crear script de corrección automática
echo "🔧 Creando script de corrección automática...\n";

$script_correccion = '<?php
/*=============================================
CORRECCIÓN AUTOMÁTICA DE SINTAXIS
=============================================*/

echo "🔧 Aplicando corrección automática de sintaxis...\n\n";

$archivo = "vistas/js/stock-transito-unificado.js";

if(!file_exists($archivo)) {
    echo "❌ Archivo no encontrado: $archivo\n";
    exit;
}

// Leer contenido
$contenido = file_get_contents($archivo);

// Crear backup
$backup = $archivo . ".backup." . date("Y-m-d-H-i-s");
if(copy($archivo, $backup)) {
    echo "💾 Backup creado: $backup\n";
}

// Aplicar correcciones automáticas
$correcciones = [
    // Corregir llaves duplicadas
    "/\\}\\s*\\}/" => "}",
    "/\\{\\s*\\{/" => "{",
    
    // Corregir comas faltantes
    "/\\}\\s*\\{/" => "}, {",
    
    // Corregir paréntesis
    "/\\)\\s*\\{/" => ") {",
    
    // Corregir puntos y comas
    "/\\}\\s*;\\s*\\}/" => "}}",
];

$contenido_corregido = $contenido;
foreach($correcciones as $patron => $reemplazo) {
    $contenido_corregido = preg_replace($patron, $reemplazo, $contenido_corregido);
}

// Escribir archivo corregido
if(file_put_contents($archivo, $contenido_corregido)) {
    echo "✅ Archivo corregido\n";
    
    // Verificar sintaxis
    $llaves_abiertas = substr_count($contenido_corregido, "{");
    $llaves_cerradas = substr_count($contenido_corregido, "}");
    echo "📋 Llaves abiertas: $llaves_abiertas\n";
    echo "📋 Llaves cerradas: $llaves_cerradas\n";
    
    if($llaves_abiertas === $llaves_cerradas) {
        echo "✅ Sintaxis corregida\n";
    } else {
        echo "❌ Aún hay problemas\n";
    }
} else {
    echo "❌ Error al escribir archivo\n";
}

echo "\n🎯 Corrección automática completada\n";
?>';

if(file_put_contents('corregir-sintaxis-automatica.php', $script_correccion)) {
    echo "✅ Script de corrección creado: corregir-sintaxis-automatica.php\n";
}

echo "\n🎯 Diagnóstico completado\n";
echo "🔧 Próximos pasos:\n";
echo "1. Revisar las líneas problemáticas identificadas\n";
echo "2. Ejecutar corregir-sintaxis-automatica.php\n";
echo "3. Verificar que los botones funcionan\n";
?>
