<?php
header('Content-Type: application/json');
require_once "conexion-central.php";

// Recibimos los datos por POST desde la sucursal
$sucursal_origen = $_POST['sucursal_origen'] ?? '';
$sucursal_destino = $_POST['sucursal_destino'] ?? ''; // <-- AÑADIDO: Leer la sucursal de destino
$usuario_solicitante = $_POST['usuario_solicitante'] ?? '';
$productos_json = $_POST['productos'] ?? '[]';

// Validamos que los datos básicos no estén vacíos
if (empty($sucursal_origen) || empty($sucursal_destino) || empty($usuario_solicitante) || empty($productos_json)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos requeridos.']);
    exit();
}

try {
    // CORRECCIÓN: Añadimos la columna y el parámetro para sucursal_destino
    $stmt = ConexionCentral::conectar()->prepare(
        "INSERT INTO solicitudes_transferencia (sucursal_origen, sucursal_destino, usuario_solicitante, productos, estado) 
         VALUES (:origen, :destino, :usuario, :productos, 'pendiente')"
    );

    $stmt->bindParam(":origen", $sucursal_origen, PDO::PARAM_STR);
    $stmt->bindParam(":destino", $sucursal_destino, PDO::PARAM_STR); // <-- AÑADIDO: Vincular el nuevo parámetro
    $stmt->bindParam(":usuario", $usuario_solicitante, PDO::PARAM_STR);
    $stmt->bindParam(":productos", $productos_json, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'ok', 'message' => 'Solicitud creada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear la solicitud.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}