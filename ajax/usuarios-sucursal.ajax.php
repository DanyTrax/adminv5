<?php
/**
 * Endpoint AJAX para consultar usuarios de la sucursal local
 * Este archivo debe estar en cada sucursal para permitir consultas remotas
 */

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establecer headers JSON
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

// Verificar que sea una petición de consulta de usuarios
if (!isset($_POST["consultarUsuariosSucursal"])) {
    echo json_encode([
        "success" => false,
        "error" => "Parámetro de consulta no válido"
    ]);
    exit;
}

try {
    require_once "../modelos/conexion.php";
    
    // Obtener usuarios de la base de datos local
    $conexion = Conexion::conectar();
    
    $stmt = $conexion->prepare("
        SELECT 
            id, 
            nombre, 
            usuario, 
            perfil, 
            foto, 
            estado, 
            ultimo_login, 
            fecha_creacion
        FROM usuarios 
        ORDER BY nombre
    ");
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos para la respuesta
    $usuariosFormateados = [];
    foreach($usuarios as $usuario) {
        $usuariosFormateados[] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'usuario' => $usuario['usuario'],
            'perfil' => $usuario['perfil'],
            'foto' => $usuario['foto'],
            'estado' => (bool)$usuario['estado'],
            'ultimo_login' => $usuario['ultimo_login'],
            'fecha_creacion' => $usuario['fecha_creacion'],
            'fecha_creacion_formateada' => date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])),
            'ultimo_login_formateado' => $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca',
            'estado_texto' => $usuario['estado'] ? 'Activo' : 'Inactivo'
        ];
    }
    
    echo json_encode([
        "success" => true,
        "data" => $usuariosFormateados,
        "total" => count($usuariosFormateados),
        "sucursal" => [
            "nombre" => "Sucursal Local",
            "fecha_consulta" => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en usuarios-sucursal.ajax.php: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>
