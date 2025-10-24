<?php
/*=============================================
OBTENER SUCURSAL DESDE BD LOCAL
=============================================*/

echo "🔍 Obteniendo datos de sucursal desde BD local...\n\n";

// Incluir conexión
require_once "modelos/conexion.php";

try {
    // Conectar a la base de datos local
    $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local LIMIT 1");
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($sucursal) {
        echo "✅ Sucursal encontrada en BD local:\n";
        echo "📋 ID: " . $sucursal['id'] . "\n";
        echo "📋 Nombre: " . $sucursal['nombre'] . "\n";
        echo "📋 Dirección: " . ($sucursal['direccion'] ?? 'N/A') . "\n";
        echo "📋 Teléfono: " . ($sucursal['telefono'] ?? 'N/A') . "\n";
        echo "📋 Email: " . ($sucursal['email'] ?? 'N/A') . "\n";
        
        // Crear script para obtener sucursal via AJAX
        $script_ajax = '
// Función para obtener datos de sucursal
function obtenerSucursalLocal() {
    return $.ajax({
        url: "ajax/obtener-sucursal-local.ajax.php",
        method: "POST",
        dataType: "json"
    });
}';
        
        echo "\n📋 Script AJAX para obtener sucursal:\n";
        echo $script_ajax . "\n";
        
    } else {
        echo "❌ No se encontró sucursal en BD local\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error al conectar con BD local: " . $e->getMessage() . "\n";
}

echo "\n🎯 Obtención de sucursal completada\n";
?>
