<?php
/**
 * Script para probar la carga del controlador de usuarios centrales
 */

echo "<h2>🧪 Prueba de Carga de Usuarios Centrales</h2>";

// Verificar si el archivo existe
$archivo = "controladores/usuarios-central.controlador.php";
echo "<p><strong>Archivo:</strong> $archivo</p>";
echo "<p><strong>Existe:</strong> " . (file_exists($archivo) ? "✅ SÍ" : "❌ NO") . "</p>";

if(file_exists($archivo)) {
    echo "<p><strong>Tamaño:</strong> " . filesize($archivo) . " bytes</p>";
    echo "<p><strong>Última modificación:</strong> " . date('Y-m-d H:i:s', filemtime($archivo)) . "</p>";
    
    // Intentar incluir el archivo
    echo "<h3>🔍 Intentando cargar el controlador...</h3>";
    
    try {
        ob_start();
        include $archivo;
        $output = ob_get_clean();
        
        if($output) {
            echo "<p style='color: orange;'>⚠️ Hay salida durante la inclusión:</p>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
        // Verificar si la clase existe
        if(class_exists('ControladorUsuariosCentral')) {
            echo "<p style='color: green;'>✅ Clase 'ControladorUsuariosCentral' cargada correctamente</p>";
            
            // Probar un método
            try {
                $sucursales = ControladorUsuariosCentral::ctrObtenerSucursalesDisponibles();
                echo "<p style='color: green;'>✅ Método 'ctrObtenerSucursalesDisponibles' ejecutado correctamente</p>";
                echo "<p><strong>Sucursales encontradas:</strong> " . count($sucursales) . "</p>";
            } catch(Exception $e) {
                echo "<p style='color: red;'>❌ Error en método: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Clase 'ControladorUsuariosCentral' NO encontrada</p>";
        }
        
    } catch(Exception $e) {
        echo "<p style='color: red;'>❌ Error al cargar archivo: " . $e->getMessage() . "</p>";
    } catch(Error $e) {
        echo "<p style='color: red;'>❌ Error fatal: " . $e->getMessage() . "</p>";
    }
}

// Verificar dependencias
echo "<h3>🔗 Verificando dependencias...</h3>";

$dependencias = [
    "modelos/usuarios-central.modelo.php",
    "api-transferencias/conexion-central.php",
    "modelos/conexion.php"
];

foreach($dependencias as $dep) {
    $existe = file_exists($dep);
    echo "<p><strong>$dep:</strong> " . ($existe ? "✅ SÍ" : "❌ NO") . "</p>";
    
    if(!$existe) {
        echo "<p style='color: red;'>⚠️ Esta dependencia faltante puede causar el error</p>";
    }
}

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>Si hay errores arriba, esos son los que necesitamos corregir.</p>";
echo "<p>Si todo está bien, el problema puede ser de permisos o configuración del servidor.</p>";
?>
