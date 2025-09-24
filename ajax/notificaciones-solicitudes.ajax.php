<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// TEST 1: Verificar sesión
if (!isset($_SESSION['perfil'])) {
    die(json_encode([
        "success" => false,
        "error" => "Sin sesión activa",
        "session_data" => $_SESSION ?? []
    ]));
}

// TEST 2: Verificar archivos
if (!file_exists("../api-transferencias/conexion-central.php")) {
    die(json_encode([
        "success" => false, 
        "error" => "Archivo conexion-central.php no encontrado"
    ]));
}

// TEST 3: Incluir conexión
try {
    require_once "../api-transferencias/conexion-central.php";
} catch (Exception $e) {
    die(json_encode([
        "success" => false,
        "error" => "Error incluyendo conexion-central: " . $e->getMessage()
    ]));
}

// TEST 4: Verificar clase ConexionCentral
if (!class_exists('ConexionCentral')) {
    die(json_encode([
        "success" => false,
        "error" => "Clase ConexionCentral no existe"
    ]));
}

// TEST 5: Probar conexión
try {
    $conexion = ConexionCentral::conectar();
    if (!$conexion) {
        die(json_encode([
            "success" => false,
            "error" => "ConexionCentral::conectar() retornó null"
        ]));
    }
} catch (Exception $e) {
    die(json_encode([
        "success" => false,
        "error" => "Error en ConexionCentral::conectar(): " . $e->getMessage()
    ]));
}

// TEST 6: Verificar tabla
try {
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch();
    
    if (!$tabla_existe) {
        die(json_encode([
            "success" => false,
            "error" => "Tabla solicitudes_stock no existe en epicosie_central"
        ]));
    }
} catch (Exception $e) {
    die(json_encode([
        "success" => false,
        "error" => "Error verificando tabla: " . $e->getMessage()
    ]));
}

// TEST 7: Si llegó hasta aquí, todo está bien
die(json_encode([
    "success" => true,
    "message" => "Todos los tests pasaron correctamente",
    "perfil" => $_SESSION['perfil']
]));