<?php
/**
 * Script para probar el endpoint de usuarios de sucursal
 */

echo "<h2>🧪 Prueba del Endpoint de Usuarios de Sucursal</h2>";

// Simular la petición POST
$_POST["consultarUsuariosSucursal"] = true;

echo "<h3>📤 Probando endpoint local...</h3>";

try {
    ob_start();
    include "ajax/usuarios-sucursal.ajax.php";
    $output = ob_get_clean();
    
    echo "<h4>📋 Respuesta del endpoint:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Intentar decodificar como JSON
    $json = json_decode($output, true);
    
    if(json_last_error() === JSON_ERROR_NONE) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
        
        if(isset($json['success']) && $json['success']) {
            echo "<p style='color: green;'>✅ Endpoint funcionando correctamente</p>";
            echo "<p><strong>Total de usuarios:</strong> " . ($json['total'] ?? 0) . "</p>";
            
            if(isset($json['data']) && !empty($json['data'])) {
                echo "<h4>👥 Usuarios encontrados:</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Usuario</th><th>Nombre</th><th>Perfil</th><th>Estado</th></tr>";
                
                foreach($json['data'] as $usuario) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($usuario['usuario']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($usuario['perfil']) . "</td>";
                    echo "<td>" . ($usuario['estado'] ? 'Activo' : 'Inactivo') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠️ No hay usuarios en la base de datos local</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error en el endpoint: " . ($json['error'] ?? 'Error desconocido') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ JSON inválido: " . json_last_error_msg() . "</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error al ejecutar endpoint: " . $e->getMessage() . "</p>";
}

echo "<h3>📋 Próximos pasos:</h3>";
echo "<p>1. Si el endpoint funciona localmente, subirlo a las otras sucursales</p>";
echo "<p>2. Verificar que las URLs de las sucursales sean correctas</p>";
echo "<p>3. Probar la consulta desde usuarios centrales</p>";
?>
