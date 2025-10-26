<?php
/*=============================================
OBTENER DATOS DE USUARIO ACTUAL (SIN SESIÓN)
=============================================*/

// Incluir conexión local
require_once "../modelos/conexion.php";

// Verificar que se especificó una acción
if(isset($_GET["accion"]) && $_GET["accion"] == "obtener_usuario_actual") {
    try {
        // Intentar obtener datos de la sesión si existe
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['id']) && isset($_SESSION['nombre'])) {
            // Usuario autenticado
            $usuario = [
                'id' => $_SESSION['id'],
                'nombre' => $_SESSION['nombre'],
                'perfil' => $_SESSION['perfil'] ?? 'Usuario'
            ];
            
            echo json_encode([
                'success' => true,
                'usuario' => $usuario
            ]);
        } else {
            // Usuario no autenticado - usar datos por defecto
            echo json_encode([
                'success' => true,
                'usuario' => [
                    'id' => '999',
                    'nombre' => 'Usuario Sistema',
                    'perfil' => 'Sistema'
                ]
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en obtener-usuario-actual.ajax.php: " . $e->getMessage());
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => '999',
                'nombre' => 'Usuario Sistema',
                'perfil' => 'Sistema'
            ]
        ]);
    }
} else {
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => '999',
            'nombre' => 'Usuario Sistema',
            'perfil' => 'Sistema'
        ]
    ]);
}

?>
