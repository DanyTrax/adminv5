<?php
/*=============================================
OBTENER SUCURSAL ACTUAL (datos públicos, sin credenciales BD)
=============================================*/

require_once "../modelos/conexion.php";

function obtenerConfiguracionLocalPublica() {
    try {
        $stmt = Conexion::conectar()->prepare("
            SELECT id, codigo_sucursal, nombre, direccion, telefono, email, url_base, url_api, es_principal, activo
            FROM sucursal_local WHERE id = 1
        ");
        $stmt->execute();
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sucursal) {
            return [
                'success' => true,
                'sucursal' => $sucursal
            ];
        }
        return [
            'success' => false,
            'error' => 'No se encontró configuración de sucursal'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Error obteniendo configuración'
        ];
    }
}

if (isset($_GET["accion"]) && $_GET["accion"] == "obtener_sucursal_actual") {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(obtenerConfiguracionLocalPublica());
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Acción no válida'
    ]);
}
