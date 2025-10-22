<?php
require_once "modelos/usuarios-central.modelo.php";

try {
    echo "=== PRUEBA DE ASIGNACIÓN DE SUCURSALES ===\n";
    
    // Datos de prueba
    $usuario_id = 1; // ID del usuario DIMARA
    $sucursales = [1, 2]; // IDs de sucursales
    
    echo "Usuario ID: $usuario_id\n";
    echo "Sucursales: " . json_encode($sucursales) . "\n";
    
    // Llamar al método
    $resultado = ModeloUsuariosCentral::mdlAsignarSucursalesUsuario($usuario_id, $sucursales);
    
    echo "Resultado: " . json_encode($resultado, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
