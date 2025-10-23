<?php

// Script para probar la consulta de sucursales para stock
require_once "controladores/sucursales.controlador.php";

echo "<h2>🔍 Probando consulta de sucursales para stock</h2>";

try {
    echo "<h3>1. Consultando sucursales disponibles...</h3>";
    
    $sucursales = ControladorSucursales::ctrObtenerSucursalesDisponibles();
    
    echo "<pre>";
    echo "Respuesta completa:\n";
    print_r($sucursales);
    echo "</pre>";
    
    if ($sucursales && $sucursales['success']) {
        echo "<h3>✅ Sucursales encontradas: " . count($sucursales['data']) . "</h3>";
        
        foreach ($sucursales['data'] as $i => $sucursal) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<h4>Sucursal " . ($i + 1) . ": " . $sucursal['nombre'] . "</h4>";
            echo "<p><strong>ID:</strong> " . $sucursal['id'] . "</p>";
            echo "<p><strong>Activo:</strong> " . ($sucursal['activo'] ? 'Sí' : 'No') . "</p>";
            echo "<p><strong>Estado conexión:</strong> " . $sucursal['estado_conexion'] . "</p>";
            echo "<p><strong>Host BD:</strong> " . ($sucursal['host_bd'] ?? 'No configurado') . "</p>";
            echo "<p><strong>Nombre BD:</strong> " . ($sucursal['nombre_bd'] ?? 'No configurado') . "</p>";
            echo "<p><strong>Usuario BD:</strong> " . ($sucursal['usuario_bd'] ?? 'No configurado') . "</p>";
            echo "</div>";
        }
    } else {
        echo "<h3>❌ Error obteniendo sucursales</h3>";
        echo "<p>Mensaje: " . ($sucursales['message'] ?? 'Error desconocido') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Excepción capturada</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

echo "<h3>2. Verificando conexión a BD central directamente...</h3>";

try {
    require_once "api-transferencias/conexion-central.php";
    $pdo = ConexionCentral::conectar();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sucursales WHERE activo = 1");
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Conexión a BD central exitosa</p>";
    echo "<p>Sucursales activas en BD central: " . $resultado['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error conectando a BD central: " . $e->getMessage() . "</p>";
}

?>
