<?php

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>📋 Agregando Módulo de Trazabilidad al Menú</h2>";
echo "<hr>";

try {
    echo "<h3>🔍 Buscando archivo de menú...</h3>";
    
    $archivosMenu = [
        'vistas/plantilla.php',
        'vistas/modulos/menu.php',
        'vistas/inc/menu.php',
        'includes/menu.php'
    ];
    
    $archivoMenu = null;
    foreach($archivosMenu as $archivo) {
        if(file_exists($archivo)) {
            $archivoMenu = $archivo;
            echo "<p>✅ Encontrado: $archivo</p>";
            break;
        }
    }
    
    if(!$archivoMenu) {
        echo "<p>❌ No se encontró archivo de menú. Buscando en otros archivos...</p>";
        
        // Buscar archivos que contengan "menu" o "nav"
        $archivos = glob('vistas/**/*.php');
        foreach($archivos as $archivo) {
            $contenido = file_get_contents($archivo);
            if(strpos($contenido, 'nav') !== false || strpos($contenido, 'menu') !== false) {
                echo "<p>🔍 Posible archivo de menú: $archivo</p>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>📝 Instrucciones para Agregar al Menú</h3>";
    echo "<div class='alert alert-info'>";
    echo "<h4>🔧 Pasos Manuales</h4>";
    echo "<ol>";
    echo "<li><strong>Buscar el archivo de menú</strong> (generalmente en vistas/plantilla.php o similar)</li>";
    echo "<li><strong>Agregar el siguiente código</strong> en la sección del menú:</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h4>📋 Código para Agregar al Menú:</h4>";
    echo "<div class='code-block'>";
    echo "<pre>";
    echo htmlspecialchars('<!-- Módulo de Trazabilidad -->
<li>
    <a href="trazabilidad-mercancia">
        <i class="fa fa-search"></i> <span>Trazabilidad</span>
    </a>
</li>');
    echo "</pre>";
    echo "</div>";
    
    echo "<h4>📋 Código Alternativo (si usa array de menú):</h4>";
    echo "<div class='code-block'>";
    echo "<pre>";
    echo htmlspecialchars('// Agregar al array de menú
"trazabilidad-mercancia" => [
    "titulo" => "Trazabilidad",
    "icono" => "fa-search",
    "perfil" => ["Administrador", "Transportador"]
],');
    echo "</pre>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🎯 Verificación del Módulo</h3>";
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Archivos del Módulo Creados</h4>";
    echo "<ul>";
    echo "<li><strong>vistas/modulos/trazabilidad-mercancia.php</strong> - Vista principal</li>";
    echo "<li><strong>vistas/js/trazabilidad-mercancia.js</strong> - JavaScript</li>";
    echo "<li><strong>ajax/trazabilidad.ajax.php</strong> - AJAX backend</li>";
    echo "<li><strong>crear-tablas-trazabilidad.php</strong> - Crear tablas</li>";
    echo "<li><strong>integrar-hooks-trazabilidad.php</strong> - Integrar hooks</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🚀 Pasos para Completar la Instalación</h3>";
    echo "<div class='alert alert-warning'>";
    echo "<h4>📋 Lista de Verificación</h4>";
    echo "<ol>";
    echo "<li>✅ Ejecutar <code>crear-tablas-trazabilidad.php</code> para crear las tablas</li>";
    echo "<li>✅ Ejecutar <code>integrar-hooks-trazabilidad.php</code> para agregar hooks</li>";
    echo "<li>⏳ <strong>PENDIENTE:</strong> Agregar 'Trazabilidad' al menú principal</li>";
    echo "<li>⏳ <strong>PENDIENTE:</strong> Probar el sistema completo</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🧪 Para Probar el Sistema</h3>";
    echo "<div class='alert alert-info'>";
    echo "<h4>🔍 Pruebas Recomendadas</h4>";
    echo "<ol>";
    echo "<li><strong>Crear un despacho</strong> en el módulo de despachos</li>";
    echo "<li><strong>Aceptar el despacho</strong> como transportador</li>";
    echo "<li><strong>Descargar productos</strong> en stock en tránsito</li>";
    echo "<li><strong>Verificar trazabilidad</strong> en el módulo de trazabilidad</li>";
    echo "<li><strong>Probar búsquedas</strong> por despacho y producto</li>";
    echo "<li><strong>Revisar reportes</strong> y estadísticas</li>";
    echo "</ol>";
    echo "</div>";
    
} catch(Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f5f5f5;
}

.alert {
    padding: 15px;
    margin: 10px 0;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6;
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
}

.alert-info {
    color: #31708f;
    background-color: #d9edf7;
    border-color: #bce8f1;
}

.alert-warning {
    color: #8a6d3b;
    background-color: #fcf8e3;
    border-color: #faebcc;
}

.code-block {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin: 10px 0;
}

.code-block pre {
    margin: 0;
    font-family: monospace;
    font-size: 12px;
    line-height: 1.4;
}

h2, h3, h4 {
    color: #333;
}

hr {
    border: 0;
    height: 1px;
    background-color: #ddd;
    margin: 20px 0;
}

code {
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
