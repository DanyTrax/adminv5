<?php
/*=============================================
OBTENER SUCURSAL ACTUAL SIN SESIÓN
=============================================*/

// Incluir conexión
require_once "../modelos/conexion.php";
require_once "../modelos/sucursales.modelo.php";

// Función para obtener configuración local
function obtenerConfiguracionLocal() {
    try {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM sucursal_local WHERE id = 1");
        $stmt->execute();
        $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($sucursal) {
            return [
                'success' => true,
                'sucursal' => $sucursal
            ];
        } else {
            return [
                'success' => false,
                'error' => 'No se encontró configuración de sucursal'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Error obteniendo configuración: ' . $e->getMessage()
        ];
    }
}

// Verificar acción
if (isset($_GET["accion"]) && $_GET["accion"] == "obtener_sucursal_actual") {
    $resultado = obtenerConfiguracionLocal();
    echo json_encode($resultado);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Acción no válida'
    ]);
}

?>
