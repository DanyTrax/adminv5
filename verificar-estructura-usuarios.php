<?php
require_once "modelos/conexion.php";

try {
    $conexion = Conexion::conectar();
    
    echo "=== ESTRUCTURA DE TABLA USUARIOS (BD LOCAL) ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $campos = $stmt->fetchAll();
    
    foreach ($campos as $campo) {
        echo "Campo: {$campo['Field']} | Tipo: {$campo['Type']} | Nulo: {$campo['Null']} | Clave: {$campo['Key']} | Default: {$campo['Default']}\n";
    }
    
    echo "\n=== USUARIOS EXISTENTES ===\n";
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, empresa FROM usuarios LIMIT 5");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "ID: {$usuario['id']} | Nombre: {$usuario['nombre']} | Usuario: {$usuario['usuario']} | Empresa: {$usuario['empresa']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
