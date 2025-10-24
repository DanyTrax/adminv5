<?php
/*=============================================
OBTENER USUARIO ACTUAL - AJAX
=============================================*/

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id']) || !isset($_SESSION['nombre'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

// Obtener datos del usuario actual
$usuario = [
    'id' => $_SESSION['id'],
    'nombre' => $_SESSION['nombre'],
    'perfil' => $_SESSION['perfil'] ?? 'Usuario'
];

// Respuesta exitosa
echo json_encode([
    'success' => true,
    'usuario' => $usuario
]);
?>
