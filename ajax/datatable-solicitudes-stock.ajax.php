<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// TEST 1: Verificar sesión
if (!isset($_SESSION['perfil'])) {
    die(json_encode([
        "error" => "Sin sesión activa"
    ]));
}

// TEST 2: Verificar archivos necesarios
if (!file_exists("../api-transferencias/conexion-central.php")) {
    die(json_encode([
        "error" => "Archivo conexion-central.php no encontrado"
    ]));
}

if (!file_exists("../controladores/solicitudes-stock.controlador.php")) {
    die(json_encode([
        "error" => "Controlador solicitudes-stock no encontrado"
    ]));
}

if (!file_exists("../modelos/solicitudes-stock.modelo.php")) {
    die(json_encode([
        "error" => "Modelo solicitudes-stock no encontrado"
    ]));
}

// TEST 3: Incluir archivos
try {
    require_once "../api-transferencias/conexion-central.php";
    require_once "../controladores/solicitudes-stock.controlador.php";
    require_once "../modelos/solicitudes-stock.modelo.php";
} catch (Exception $e) {
    die(json_encode([
        "error" => "Error incluyendo archivos: " . $e->getMessage()
    ]));
}

// TEST 4: Verificar conexión central
try {
    $conexion = ConexionCentral::conectar();
    if (!$conexion) {
        die(json_encode([
            "error" => "No se pudo conectar a la base de datos central"
        ]));
    }
} catch (Exception $e) {
    die(json_encode([
        "error" => "Error en conexión central: " . $e->getMessage()
    ]));
}

// TEST 5: Verificar tabla existe
try {
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'solicitudes_stock'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch();
    
    if (!$tabla_existe) {
        die(json_encode([
            "error" => "La tabla solicitudes_stock no existe en epicosie_central"
        ]));
    }
} catch (Exception $e) {
    die(json_encode([
        "error" => "Error verificando tabla: " . $e->getMessage()
    ]));
}

// TEST 6: Probar consulta básica
try {
    $solicitudes = ControladorSolicitudesStock::ctrMostrarSolicitudesCompletas();
    
    // Si llegó hasta aquí, todo funciona, retornar datos para DataTable
    if(count($solicitudes) == 0){
        echo '{"data": []}';
        exit;
    }

    // Por ahora, solo mostrar que funcionó
    echo '{"data": [["1", "SOL000001", "Sucursal Test", "Usuario Test", "Stock", "1 prod.", "Pendiente", "01/01/2024", "N/A", ""]]}';
    exit;
    
} catch (Exception $e) {
    die(json_encode([
        "error" => "Error ejecutando controlador: " . $e->getMessage()
    ]));
}