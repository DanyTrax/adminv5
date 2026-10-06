<?php
/*=============================================
OBTENER DATOS DE SUCURSAL ACTUAL
=============================================*/

require_once __DIR__ . "/../src/AjaxAuth.php";
AjaxAuth::requireSession();

require_once "../modelos/conexion.php";

// Verificar que se especificó una acción
if(isset($_GET["accion"]) && $_GET["accion"] == "obtener_sucursal_actual") {
    try {
        // No exponer password_bd ni credenciales de BD al cliente
        $stmt = Conexion::conectar()->prepare("
            SELECT 
                id,
                codigo_sucursal,
                nombre,
                direccion,
                telefono,
                email,
                nombre_bd,
                host_bd,
                puerto_bd,
                url_base,
                url_api,
                es_principal,
                activo
            FROM sucursal_local 
            WHERE id = 1
        ");
        $stmt->execute();
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sucursal) {
            echo json_encode([
                'success' => true,
                'sucursal' => [
                    'id' => $sucursal['id'],
                    'codigo_sucursal' => $sucursal['codigo_sucursal'],
                    'nombre' => $sucursal['nombre'],
                    'direccion' => $sucursal['direccion'],
                    'telefono' => $sucursal['telefono'],
                    'email' => $sucursal['email'],
                    'nombre_bd' => $sucursal['nombre_bd'],
                    'host_bd' => $sucursal['host_bd'],
                    'puerto_bd' => $sucursal['puerto_bd'],
                    'url_base' => $sucursal['url_base'],
                    'url_api' => $sucursal['url_api'],
                    'es_principal' => $sucursal['es_principal'],
                    'activo' => $sucursal['activo']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró configuración de sucursal'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en obtener-sucursal-actual.ajax.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener datos de sucursal: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no especificada'
    ]);
}

?>
