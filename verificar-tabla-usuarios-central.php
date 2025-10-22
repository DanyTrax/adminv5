<?php
require_once "api-transferencias/conexion-central.php";

try {
    $conexion = ConexionCentral::conectar();
    
    echo "=== ESTRUCTURA DE TABLA USUARIOS_CENTRAL ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuarios_central");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    foreach ($campos as $campo) {
        echo "Campo: {$campo['Field']} | Tipo: {$campo['Type']} | Nulo: {$campo['Null']} | Clave: {$campo['Key']} | Default: {$campo['Default']}\n";
    }
    
    echo "\n=== USUARIOS CENTRALES EXISTENTES ===\n";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, perfil FROM usuarios_central LIMIT 5");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "ID: {$usuario['id']} | Nombre: {$usuario['nombre']} | Usuario: {$usuario['usuario']} | Perfil: {$usuario['perfil']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
