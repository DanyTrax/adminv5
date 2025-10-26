<?php
/*=============================================
OBTENER USUARIO ACTUAL SIN SESIÓN
=============================================*/

// Función para obtener usuario actual
function obtenerUsuarioActual() {
    // Intentar obtener datos de sesión
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION["id"]) && isset($_SESSION["nombre"])) {
        return [
            'success' => true,
            'usuario' => [
                'id' => $_SESSION['id'],
                'nombre' => $_SESSION['nombre'],
                'perfil' => $_SESSION['perfil'] ?? 'Usuario'
            ]
        ];
    } else {
        // Fallback a datos por defecto
        return [
            'success' => true,
            'usuario' => [
                'id' => '999',
                'nombre' => 'Usuario Sistema',
                'perfil' => 'Sistema'
            ]
        ];
    }
}

// Obtener usuario actual
$resultado = obtenerUsuarioActual();
echo json_encode($resultado);

?>